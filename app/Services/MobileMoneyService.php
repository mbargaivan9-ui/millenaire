<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MobileMoneyService
 * 
 * SOLID - Single Responsibility Principle
 * Handles integration with mobile money payment providers
 * Supports: Monetbil, Campay, Orange Money, MTN Money
 */
class MobileMoneyService
{
    /**
     * Initiate a payment through mobile money
     * 
     * @param Student $student
     * @param float $amount Amount in XAF
     * @param string $phone Phone number (format: 6XX XX XX XX)
     * @param string $provider Provider: 'campay', 'orange', 'mtn'
     * @return array Transaction reference and status
     */
    public function initiatePayment(Student $student, float $amount, string $phone, string $provider = 'campay'): array
    {
        // Validate phone format
        if (!$this->validatePhoneNumber($phone)) {
            throw new \InvalidArgumentException('Invalid phone number format. Expected: 6XX XX XX XX');
        }

        // Validate amount
        if ($amount < 100 || $amount > 1000000) {
            throw new \InvalidArgumentException('Amount must be between 100 and 1,000,000 XAF');
        }

        return match ($provider) {
            'campay' => $this->initiateCampayPayment($student, $amount, $phone),
            'orange' => $this->initiateOrangeMoneyPayment($student, $amount, $phone),
            'mtn' => $this->initiateMTNMoneyPayment($student, $amount, $phone),
            default => throw new \InvalidArgumentException("Unsupported provider: {$provider}"),
        };
    }

    /**
     * Initiate payment via Campay (Monetbil)
     * 
     * @param Student $student
     * @param float $amount
     * @param string $phone
     * @return array
     */
    private function initiateCampayPayment(Student $student, float $amount, string $phone): array
    {
        try {
            $payload = [
                'invoice_number' => 'INV-' . uniqid(),
                'description' => "School Fees - {$student->user->name}",
                'amount' => (int) $amount,
                'phone' => $this->formatPhoneNumber($phone, 'cameroon'),
                'merchant_id' => config('payment.campay.merchant_id'),
            ];

            // Call Campay API
            $response = Http::post('https://campay.net/api/send/', $payload)
                ->json();

            if (!$response['success'] ?? false) {
                throw new \Exception('Campay API error: ' . ($response['message'] ?? 'Unknown error'));
            }

            // Create payment record
            $payment = Payment::create([
                'student_id' => $student->id,
                'amount' => $amount,
                'phone' => $phone,
                'provider' => 'campay',
                'transaction_ref' => $response['data']['transaction_id'] ?? null,
                'status' => 'pending',
                'metadata' => $response,
            ]);

            Log::info("Campay payment initiated", ['payment_id' => $payment->id]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'transaction_ref' => $payment->transaction_ref,
                'status' => 'pending',
                'message' => 'Payment initiated. Check your phone for prompt.',
            ];

        } catch (\Exception $e) {
            Log::error("Campay payment error: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Initiate payment via Orange Money
     * 
     * @param Student $student
     * @param float $amount
     * @param string $phone
     * @return array
     */
    private function initiateOrangeMoneyPayment(Student $student, float $amount, string $phone): array
    {
        try {
            $payload = [
                'merchantId' => config('payment.orange.merchant_id'),
                'orderId' => 'ORD-' . uniqid(),
                'amount' => (int) $amount,
                'currency' => 'XAF',
                'subscriberNumber' => $this->formatPhoneNumber($phone, 'cameroon'),
                'description' => "School Fees - {$student->user->name}",
            ];

            // Call Orange Money API
            $response = Http::withToken(config('payment.orange.api_key'))
                ->post('https://api.orange.cm/am/v1/payments/request/', $payload)
                ->json();

            if (!$response['status'] ?? false) {
                throw new \Exception('Orange Money API error: ' . ($response['message'] ?? 'Unknown error'));
            }

            // Create payment record
            $payment = Payment::create([
                'student_id' => $student->id,
                'amount' => $amount,
                'phone' => $phone,
                'provider' => 'orange',
                'transaction_ref' => $response['data']['transactionId'] ?? null,
                'status' => 'pending',
                'metadata' => $response,
            ]);

            Log::info("Orange Money payment initiated", ['payment_id' => $payment->id]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'transaction_ref' => $payment->transaction_ref,
                'status' => 'pending',
                'message' => 'Payment initiated. Check your phone for prompt.',
            ];

        } catch (\Exception $e) {
            Log::error("Orange Money payment error: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Initiate payment via MTN Money
     * 
     * @param Student $student
     * @param float $amount
     * @param string $phone
     * @return array
     */
    private function initiateMTNMoneyPayment(Student $student, float $amount, string $phone): array
    {
        try {
            $payload = [
                'apiKey' => config('payment.mtn.api_key'),
                'msisdn' => $this->formatPhoneNumber($phone, 'cameroon'),
                'amount' => (int) $amount,
                'currency' => 'XAF',
                'externalId' => 'SCH-' . uniqid(),
                'payerMessage' => "School Fees - {$student->user->name}",
                'payeeNote' => "School Fees Payment",
            ];

            // Call MTN Money API
            $response = Http::post('https://api.mtnmobilemoneydev.com/api/v1_0/requesttopay', $payload)
                ->json();

            if (($response['status'] ?? null) !== 'PENDING') {
                throw new \Exception('MTN Money API error: ' . ($response['message'] ?? 'Unknown error'));
            }

            // Create payment record
            $payment = Payment::create([
                'student_id' => $student->id,
                'amount' => $amount,
                'phone' => $phone,
                'provider' => 'mtn',
                'transaction_ref' => $response['transactionId'] ?? null,
                'status' => 'pending',
                'metadata' => $response,
            ]);

            Log::info("MTN Money payment initiated", ['payment_id' => $payment->id]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'transaction_ref' => $payment->transaction_ref,
                'status' => 'pending',
                'message' => 'Payment initiated. Check your phone for prompt.',
            ];

        } catch (\Exception $e) {
            Log::error("MTN Money payment error: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Check payment status
     * 
     * @param Payment $payment
     * @return array Updated payment status
     */
    public function checkStatus(Payment $payment): array
    {
        return match ($payment->provider) {
            'campay' => $this->checkCampayStatus($payment),
            'orange' => $this->checkOrangeStatus($payment),
            'mtn' => $this->checkMTNStatus($payment),
            default => ['success' => false, 'message' => 'Unknown provider'],
        };
    }

    /**
     * Check Campay payment status
     * 
     * @param Payment $payment
     * @return array
     */
    private function checkCampayStatus(Payment $payment): array
    {
        try {
            $response = Http::get("https://campay.net/api/check-transaction/{$payment->transaction_ref}")
                ->json();

            if ($response['success'] ?? false) {
                if ($response['data']['status'] === 'successful') {
                    $payment->update(['status' => 'completed', 'completed_at' => now()]);
                    return ['success' => true, 'status' => 'completed'];
                } elseif ($response['data']['status'] === 'failed') {
                    $payment->update(['status' => 'failed', 'failed_at' => now()]);
                    return ['success' => false, 'status' => 'failed', 'message' => 'Payment failed'];
                }
            }

            return ['success' => true, 'status' => 'pending'];

        } catch (\Exception $e) {
            Log::error("Error checking Campay status: {$e->getMessage()}");
            return ['success' => false, 'message' => 'Could not check payment status'];
        }
    }

    /**
     * Check Orange Money payment status
     * 
     * @param Payment $payment
     * @return array
     */
    private function checkOrangeStatus(Payment $payment): array
    {
        try {
            $response = Http::withToken(config('payment.orange.api_key'))
                ->get("https://api.orange.cm/am/v1/payments/status/{$payment->transaction_ref}")
                ->json();

            if ($response['data']['status'] === 'SUCCESSFUL') {
                $payment->update(['status' => 'completed', 'completed_at' => now()]);
                return ['success' => true, 'status' => 'completed'];
            } elseif ($response['data']['status'] === 'FAILED') {
                $payment->update(['status' => 'failed', 'failed_at' => now()]);
                return ['success' => false, 'status' => 'failed'];
            }

            return ['success' => true, 'status' => 'pending'];

        } catch (\Exception $e) {
            Log::error("Error checking Orange status: {$e->getMessage()}");
            return ['success' => false, 'message' => 'Could not check payment status'];
        }
    }

    /**
     * Check MTN Money payment status
     * 
     * @param Payment $payment
     * @return array
     */
    private function checkMTNStatus(Payment $payment): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('payment.mtn.api_key'),
            ])->get("https://api.mtnmobilemoneydev.com/api/v1_0/requesttopay/{$payment->transaction_ref}")
                ->json();

            if ($response['status'] === 'SUCCESSFUL') {
                $payment->update(['status' => 'completed', 'completed_at' => now()]);
                return ['success' => true, 'status' => 'completed'];
            } elseif ($response['status'] === 'FAILED') {
                $payment->update(['status' => 'failed', 'failed_at' => now()]);
                return ['success' => false, 'status' => 'failed'];
            }

            return ['success' => true, 'status' => 'pending'];

        } catch (\Exception $e) {
            Log::error("Error checking MTN status: {$e->getMessage()}");
            return ['success' => false, 'message' => 'Could not check payment status'];
        }
    }

    /**
     * Validate phone number format (Cameroon)
     * 
     * @param string $phone
     * @return bool
     */
    private function validatePhoneNumber(string $phone): bool
    {
        // Accept formats: 6XX XXX XXXX, 6XXXXXXXX, +237 6XX XXX XXXX, etc.
        return preg_match('/^(\+237|237)?6[0-9]{8}$/', preg_replace('/\s+/', '', $phone));
    }

    /**
     * Format phone number to standard format for API calls
     * 
     * @param string $phone
     * @param string $country
     * @return string
     */
    private function formatPhoneNumber(string $phone, string $country = 'cameroon'): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if ($country === 'cameroon') {
            // Remove leading 0 if present, add country code
            $cleaned = ltrim($cleaned, '0');
            if (strlen($cleaned) === 9) {
                return '237' . $cleaned;
            }
            return $cleaned;
        }

        return $cleaned;
    }

    /**
     * Refund a payment
     * 
     * @param Payment $payment
     * @param float|null $amount Partial refund amount (null for full refund)
     * @return array
     */
    public function refund(Payment $payment, ?float $amount = null): array
    {
        if ($payment->status !== 'completed') {
            throw new \Exception('Can only refund completed payments');
        }

        $refundAmount = $amount ?? $payment->amount;

        // Create refund record
        $refund = Payment::create([
            'student_id' => $payment->student_id,
            'amount' => -$refundAmount, // Negative amount indicates refund
            'phone' => $payment->phone,
            'provider' => $payment->provider,
            'transaction_ref' => null,
            'status' => 'completed',
            'reference_payment_id' => $payment->id,
            'notes' => "Refund for payment {$payment->id}",
        ]);

        Log::info("Payment refunded", ['original_payment_id' => $payment->id, 'refund_id' => $refund->id]);

        return [
            'success' => true,
            'refund_id' => $refund->id,
            'amount' => $refundAmount,
            'message' => 'Refund processed successfully',
        ];
    }
}
