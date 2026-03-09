<?php

namespace App\Providers;

use App\Contracts\PaymentInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CampayPaymentProvider
 * 
 * Implementation for Campay payment gateway
 * Supports Orange Money and MTN Mobile Money in Cameroon and other African countries
 */
class CampayPaymentProvider implements PaymentInterface
{
    private string $apiKey;
    private string $apiUrl;
    private string $webhookSecret;

    public function __construct()
    {
        $this->apiKey = config('payment.campay.api_key', '');
        $this->apiUrl = config('payment.campay.api_url', 'https://api.campay.net');
        $this->webhookSecret = config('payment.campay.webhook_secret', '');
    }

    /**
     * {@inheritDoc}
     */
    public function initiate(
        float $amount,
        string $phoneNumber,
        string $description = '',
        array $metadata = []
    ): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Campay not configured',
            ];
        }

        try {
            // Normalize phone number (Campay format: +237XXXXXXXXX)
            $normalizedPhone = $this->normalizePhone($phoneNumber);

            // Create payment request
            $payload = [
                'amount' => $amount,
                'currency' => 'XAF',
                'phone_number' => $normalizedPhone,
                'description' => $description ?: 'School Fee Payment',
                'external_reference' => $metadata['payment_id'] ?? uniqid('pay_'),
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->apiUrl}/api/v1/collect", $payload)
            ->throw();

            $data = $response->json();

            if (!isset($data['reference'])) {
                Log::warning('Campay payment initiation failed', $data);

                return [
                    'success' => false,
                    'error' => $data['detail'] ?? 'Failed to initiate payment',
                ];
            }

            Log::info('Campay payment initiated', [
                'reference' => $data['reference'],
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'transaction_id' => $data['reference'],
                'data' => [
                    'reference' => $data['reference'],
                    'status' => $data['status'] ?? 'pending',
                    'message' => $data['message'] ?? 'Payment initiated',
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Campay payment initiation error', [
                'error' => $e->getMessage(),
                'phone' => $phoneNumber,
                'amount' => $amount,
            ]);

            return [
                'success' => false,
                'error' => 'Payment initiation failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function verify(string $transactionId): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Campay not configured',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->get("{$this->apiUrl}/api/v1/get_payment/{$transactionId}")
            ->throw();

            $data = $response->json();

            if (!isset($data['status'])) {
                Log::warning('Campay payment verification failed', $data);

                return [
                    'success' => false,
                    'error' => 'Unable to verify payment',
                ];
            }

            // Campay status codes: PENDING, SUCCESS, FAILED
            $status = strtolower($data['status']);

            return [
                'success' => true,
                'status' => $status === 'success' ? 'completed' : $status,
                'data' => [
                    'reference' => $data['reference'] ?? $transactionId,
                    'amount' => $data['amount'] ?? null,
                    'account' => $data['phone'] ?? null,
                    'timestamp' => $data['timestamp'] ?? null,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Campay payment verification error', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => false,
                'error' => 'Verification failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function handleWebhook(array $payload): array
    {
        // Verify webhook signature
        if (!$this->verifyWebhookSignature($payload)) {
            Log::warning('Invalid Campay webhook signature', $payload);

            return [
                'success' => false,
                'message' => 'Invalid signature',
            ];
        }

        try {
            $reference = $payload['reference'] ?? null;
            $status = $payload['status'] ?? null;

            if (!$reference || !$status) {
                return [
                    'success' => false,
                    'message' => 'Missing required fields',
                ];
            }

            // Find payment by transaction ID
            $payment = \App\Models\Payment::where('transaction_id', $reference)->first();

            if (!$payment) {
                Log::warning('Payment not found for webhook', ['reference' => $reference]);

                return [
                    'success' => false,
                    'message' => 'Payment not found',
                ];
            }

            // Update payment status
            if (strtolower($status) === 'success') {
                $payment->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'provider_data' => $payload,
                ]);

                // Update student financial status
                $this->updateStudentFinancialStatus($payment->student);

                Log::info('Webhook: Payment confirmed', ['payment_id' => $payment->id]);
            } elseif (in_array(strtolower($status), ['failed', 'error'])) {
                $payment->update([
                    'status' => 'failed',
                    'provider_data' => $payload,
                ]);

                Log::info('Webhook: Payment failed', ['payment_id' => $payment->id]);
            }

            return [
                'success' => true,
                'message' => 'Webhook processed',
            ];
        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'message' => 'Webhook processing failed',
            ];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getProviderName(): string
    {
        return 'campay';
    }

    /**
     * {@inheritDoc}
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiUrl);
    }

    /**
     * Normalize phone number to Campay format
     */
    private function normalizePhone(string $phone): string
    {
        // Remove spaces and special characters
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // If it starts with 00, replace with +
        if (substr($phone, 0, 2) === '00') {
            $phone = '+' . substr($phone, 2);
        } elseif (substr($phone, 0, 1) !== '+') {
            // If no +, assume Cameroon (+237)
            $phone = '+237' . ltrim($phone, '0');
        }

        return $phone;
    }

    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature(array $payload): bool
    {
        // Campay typically sends signature in header or payload
        // Implement based on Campay's actual signature method
        // For now, return true (implement according to Campay docs)
        return true;
    }

    /**
     * Update student financial status based on payments
     */
    private function updateStudentFinancialStatus($student): void
    {
        $totalPaid = $student->payments()
            ->where('status', 'completed')
            ->sum('amount');

        // Get total fees required (implement based on your fee structure)
        $totalRequired = 500000; // Example: 500,000 XAF

        if ($totalPaid >= $totalRequired) {
            $student->update(['financial_status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $student->update(['financial_status' => 'partial']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusLabel(string $status): string
    {
        $labels = [
            'pending' => 'En attente',
            'processing' => 'En traitement',
            'completed' => 'Complété',
            'failed' => 'Échoué',
            'cancelled' => 'Annulé',
            'refunded' => 'Remboursé',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * {@inheritDoc}
     */
    public function cancel(string $transactionId): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/transaction/{$transactionId}/cancel");

            if ($response->successful()) {
                Log::info("Payment cancelled: {$transactionId}");
                return true;
            }

            Log::error("Failed to cancel payment: {$transactionId}", $response->json());
            return false;
        } catch (\Exception $e) {
            Log::error("Error cancelling payment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function refund(string $transactionId, ?float $amount = null): array
    {
        try {
            $refundData = [
                'transaction_id' => $transactionId,
            ];

            if ($amount) {
                $refundData['amount'] = $amount;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/transaction/refund", $refundData);

            if ($response->successful()) {
                Log::info("Payment refunded: {$transactionId}, Amount: {$amount}");
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error("Failed to refund payment: {$transactionId}", $response->json());
            return ['success' => false, 'error' => 'Refund failed'];
        } catch (\Exception $e) {
            Log::error("Error refunding payment: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
