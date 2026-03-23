<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * PaymentSimulationService
 * 
 * Simulates Orange Money & MTN MoMo payment flow in sandbox mode
 * PHASE 10: 11.1.3 Simulation Sandbox
 * - 90% succès rate, 10% échec en sandbox
 * - Délai aléatoire 2-5 secondes
 */
class PaymentSimulationService
{
    private bool $useSimulation;

    public function __construct()
    {
        $this->useSimulation = config('payment.default') === 'simulation';
    }

    /**
     * Simulate Orange Money payment initiation
     * Returns success response with delay simulation
     */
    public function simulateOrangeMoneyInitiation(
        float $amount,
        string $phoneNumber,
        string $description = ''
    ): array {
        if (!$this->useSimulation) {
            return ['success' => false, 'error' => 'Simulation mode not enabled'];
        }

        $transactionId = 'OM-SIM-' . strtoupper(Str::random(16));

        Log::info('[PaymentSimulation] Orange Money initiation simulated', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'phone' => $phoneNumber,
        ]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'operator' => 'orange',
            'amount' => $amount,
            'phone' => $phoneNumber,
            'status' => 'pending',
            'message' => 'SIMULATION: Paiement Orange Money en cours...',
            'simulated_delay' => rand(2, 5), // 2-5 secondes
        ];
    }

    /**
     * Simulate MTN MoMo payment initiation
     * Returns success response with delay simulation
     */
    public function simulateMTNMoMoInitiation(
        float $amount,
        string $phoneNumber,
        string $description = ''
    ): array {
        if (!$this->useSimulation) {
            return ['success' => false, 'error' => 'Simulation mode not enabled'];
        }

        $transactionId = 'MTN-SIM-' . strtoupper(Str::random(16));

        Log::info('[PaymentSimulation] MTN MoMo initiation simulated', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'phone' => $phoneNumber,
        ]);

        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'operator' => 'mtn',
            'amount' => $amount,
            'phone' => $phoneNumber,
            'status' => 'pending',
            'message' => 'SIMULATION: Paiement MTN MoMo en cours...',
            'simulated_delay' => rand(2, 5), // 2-5 secondes
        ];
    }

    /**
     * Simulate payment status check
     * 90% succès, 10% échec
     */
    public function simulatePaymentVerification(string $transactionId): array
    {
        if (!$this->useSimulation) {
            return ['success' => false, 'error' => 'Simulation mode not enabled'];
        }

        // 90% chance de succès
        $success = rand(1, 100) <= 90;

        if ($success) {
            Log::info('[PaymentSimulation] Payment verification SUCCESS', [
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => true,
                'status' => 'completed',
                'transaction_id' => $transactionId,
                'verified_at' => now()->toIso8601String(),
                'message' => 'SIMULATION: Paiement approuvé',
            ];
        } else {
            Log::warning('[PaymentSimulation] Payment verification FAILED', [
                'transaction_id' => $transactionId,
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'transaction_id' => $transactionId,
                'error' => 'SIMULATION: Paiement échoué (10% test case)',
                'message' => 'Le paiement a échoué. Veuillez réessayer.',
            ];
        }
    }

    /**
     * Simulate webhook callback from payment provider
     * Used for testing webhook handling
     */
    public function simulateWebhookCallback(Payment $payment, bool $success = true): array
    {
        $operator = $payment->provider ?? 'orange';
        $timestamp = now()->timestamp;

        // Build webhook payload similar to real provider response
        $payload = [
            'transaction_id' => $payment->transaction_id ?? 'SIM-' . Str::uuid(),
            'operator' => $operator,
            'phone' => $payment->phone_number,
            'amount' => $payment->amount,
            'status' => $success ? 'completed' : 'failed',
            'timestamp' => $timestamp,
            'reference' => $payment->id,
        ];

        // Calculate HMAC signature (same as production)
        $payload['signature'] = $this->generateHmacSignature($payload);

        Log::info('[PaymentSimulation] Webhook callback simulated', [
            'transaction_id' => $payload['transaction_id'],
            'status' => $payload['status'],
        ]);

        return $payload;
    }

    /**
     * Generate HMAC signature for webhook verification
     */
    public function generateHmacSignature(array $payload): string
    {
        $secret = config('payment.webhook_secret', 'test-secret');
        $data = json_encode($payload);
        return hash_hmac('sha256', $data, $secret);
    }

    /**
     * Save complete payment with receipt data (sandbox)
     */
    public function completePaymentSimulation(Payment $payment): array
    {
        $payment->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Log::info('[PaymentSimulation] Payment completed', [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
        ]);

        return [
            'success' => true,
            'payment_id' => $payment->id,
            'status' => 'completed',
            'message' => 'Paiement complété avec succès',
        ];
    }

    /**
     * Check if simulation mode is enabled
     */
    public function isEnabled(): bool
    {
        return $this->useSimulation;
    }
}
