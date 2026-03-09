<?php

namespace App\Services;

use App\Contracts\PaymentInterface;
use App\Models\{Payment, Student};
use Illuminate\Support\Facades\Log;

/**
 * PaymentGatewayService
 * 
 * Manages payment operations through various providers
 * Implements the Strategy pattern with dependency injection
 */
class PaymentGatewayService
{
    private PaymentInterface $provider;

    public function __construct(PaymentInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Set payment provider
     */
    public function setProvider(PaymentInterface $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * Get current provider
     */
    public function getProvider(): PaymentInterface
    {
        return $this->provider;
    }

    /**
     * Initiate a payment
     */
    public function initiatePayment(
        Student $student,
        float $amount,
        string $purpose = 'tuition_fees',
        string $description = ''
    ): array {
        if (!$this->provider->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Payment provider not configured',
            ];
        }

        // Create payment record in database
        $payment = Payment::create([
            'student_id' => $student->id,
            'amount' => $amount,
            'currency' => config('payment.currency', 'XAF'),
            'status' => 'pending',
            'purpose' => $purpose,
            'description' => $description,
            'phone_number' => $student->user->phoneNumber,
            'provider' => $this->provider->getProviderName(),
        ]);

        // Initiate payment with provider
        $result = $this->provider->initiate(
            amount: $amount,
            phoneNumber: $student->user->phoneNumber,
            description: $description,
            metadata: [
                'payment_id' => $payment->id,
                'student_id' => $student->id,
                'student_name' => $student->user->name,
                'matricule' => $student->matricule,
            ]
        );

        if (!$result['success']) {
            $payment->update(['status' => 'failed']);
            Log::warning("Payment initiation failed for student {$student->id}", $result);

            return [
                'success' => false,
                'message' => 'Payment initiation failed',
                'error' => $result['error'] ?? 'Unknown error',
            ];
        }

        // Update payment with transaction ID
        $payment->update([
            'transaction_id' => $result['transaction_id'],
            'provider_data' => $result['data'] ?? [],
        ]);

        Log::info("Payment initiated for student {$student->id}", [
            'amount' => $amount,
            'transaction_id' => $result['transaction_id'],
        ]);

        return [
            'success' => true,
            'message' => 'Payment initiated successfully',
            'payment_id' => $payment->id,
            'transaction_id' => $result['transaction_id'],
            'data' => $result['data'] ?? [],
        ];
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(Payment $payment): array
    {
        if ($payment->status !== 'pending') {
            return [
                'success' => true,
                'status' => $payment->status,
                'message' => "Payment already {$payment->status}",
            ];
        }

        $result = $this->provider->verify($payment->transaction_id);

        if (!$result['success']) {
            return [
                'success' => false,
                'message' => 'Verification failed',
                'error' => $result['error'] ?? 'Unknown error',
            ];
        }

        $status = $result['status'] ?? 'unknown';

        // Update payment status
        if (in_array($status, ['completed', 'succeeded', 'paid'])) {
            $this->confirmPayment($payment);
        } elseif (in_array($status, ['failed', 'cancelled'])) {
            $payment->update(['status' => 'failed']);
        }

        return [
            'success' => true,
            'status' => $payment->status,
            'message' => "Payment {$payment->status}",
        ];
    }

    /**
     * Confirm a payment and update student status
     */
    public function confirmPayment(Payment $payment): void
    {
        $payment->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $student = $payment->student;

        // Update student's financial status based on fee settings
        $totalOutstanding = $student->payments()
            ->where('status', 'completed')
            ->sum('amount');

        // Get required fees from fee setting, not guardian
        $classe = $student->classe;
        if (!$classe) {
            return;
        }

        $feeSetting = \App\Models\FeeSetting::where('class_id', $classe->id)
            ->currentYear()
            ->active()
            ->first();

        $totalRequired = $feeSetting?->getDiscountedAmount() ?? 0;

        if ($totalRequired <= 0 || $totalOutstanding >= $totalRequired) {
            $student->update(['financial_status' => 'paid']);
        } else {
            $student->update(['financial_status' => 'partial']);
        }

        Log::info("Payment confirmed for student {$student->id}", [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
        ]);
    }

    /**
     * Handle webhook from payment provider
     */
    public function handleWebhook(array $payload): array
    {
        return $this->provider->handleWebhook($payload);
    }

    /**
     * Get payment history for student
     */
    public function getPaymentHistory(Student $student): array
    {
        $payments = $student->payments()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'amount' => $p->amount,
                'currency' => $p->currency,
                'status' => $p->status,
                'purpose' => $p->purpose,
                'provider' => $p->provider,
                'completed_at' => $p->completed_at,
                'created_at' => $p->created_at,
            ]);

        return [
            'student_id' => $student->id,
            'payments' => $payments,
            'total_completed' => $payments->where('status', 'completed')->sum('amount'),
            'total_pending' => $payments->where('status', 'pending')->sum('amount'),
            'total_failed' => $payments->where('status', 'failed')->sum('amount'),
        ];
    }

    /**
     * Generate payment receipt
     */
    public function generateReceipt(Payment $payment): string
    {
        if ($payment->status !== 'completed') {
            throw new \Exception('Can only generate receipt for completed payments');
        }

        return app(ReceiptService::class)->generatePDF($payment);
    }
}
