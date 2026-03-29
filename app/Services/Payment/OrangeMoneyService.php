<?php

namespace App\Services\Payment;

use App\Models\MobilePayment;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Support\Str;

/**
 * OrangeMoneyService
 *
 * Gère les paiements Orange Money Cameroun.
 * - Sandbox : simulation complète (90% succès, délai 5–10s)
 * - Production : Orange Money WebPay API v1 (Cameroun)
 *
 * ⚙️  Variables .env requises (production) :
 *   ORANGE_MONEY_API_KEY=
 *   ORANGE_MONEY_MERCHANT_KEY=
 *   ORANGE_MONEY_WEBHOOK_SECRET=
 */
class OrangeMoneyService
{
    private const API_BASE      = 'https://api.orange.com/orange-money-webpay/cm/v1';
    private const SANDBOX_RATE  = 90; // % de succès en sandbox

    public function __construct(
        private readonly bool $isSandbox = true
    ) {}

    // ─── Initier un paiement ──────────────────────────────────────────────────

    public function initiate(MobilePayment $payment): array
    {
        if ($this->isSandbox) {
            return $this->sandboxInitiate($payment);
        }

        try {
            $response = Http::withToken(config('services.orange_money.api_key'))
                ->timeout(15)
                ->post(self::API_BASE . '/webpayment', [
                    'merchant_key'  => config('services.orange_money.merchant_key'),
                    'currency'      => 'XAF',
                    'order_id'      => $payment->transaction_ref,
                    'amount'        => $payment->amount,
                    'return_url'    => route('payment.callback.orange'),
                    'cancel_url'    => route('payment.cancel'),
                    'notif_url'     => route('webhooks.payment.orange'),
                    'lang'          => 'fr',
                    'reference'     => $payment->transaction_ref,
                    'customer_msisdn' => ltrim($payment->phone, '+'),
                ]);

            $data = $response->json();

            $payment->update(['api_response_log' => $data]);

            if ($response->successful() && isset($data['pay_token'])) {
                return [
                    'success'      => true,
                    'operator_ref' => $data['pay_token'],
                    'ussd_code'    => $data['notif_token'] ?? null,
                    'payment_url'  => $data['payment_url'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Orange Money indisponible',
            ];
        } catch (\Throwable $e) {
            Log::error('[OrangeMoney] Initiation échouée: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de connexion Orange Money'];
        }
    }

    // ─── Vérifier le statut ───────────────────────────────────────────────────

    public function checkStatus(MobilePayment $payment): array
    {
        if ($this->isSandbox) {
            return $this->sandboxCheckStatus($payment);
        }

        try {
            $response = Http::withToken(config('services.orange_money.api_key'))
                ->timeout(10)
                ->get(self::API_BASE . '/paymentstatus', [
                    'order_id' => $payment->transaction_ref,
                ]);

            $data = $response->json();

            if ($response->successful()) {
                $isSuccess = strtolower($data['status'] ?? '') === 'success';
                return [
                    'status'        => $isSuccess ? 'success' : ($data['status'] === 'pending' ? 'pending' : 'failed'),
                    'operator_txn'  => $data['txn_id'] ?? null,
                    'message'       => $data['message'] ?? '',
                ];
            }

            return ['status' => 'unknown', 'message' => 'Impossible de vérifier'];
        } catch (\Throwable $e) {
            Log::error('[OrangeMoney] Status check échoué: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // ─── Vérifier le webhook ──────────────────────────────────────────────────

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha256', $payload, config('services.orange_money.webhook_secret'));
        return hash_equals($expected, $signature);
    }

    // ─── USSD Code ────────────────────────────────────────────────────────────

    public function getUssdCode(MobilePayment $payment): string
    {
        $phone  = preg_replace('/^\+237/', '', $payment->phone);
        $amount = $payment->total_amount;
        return "#150*4*1*{$phone}*{$amount}#";
    }

    // ─── Sandbox ─────────────────────────────────────────────────────────────

    private function sandboxInitiate(MobilePayment $payment): array
    {
        Log::info('[OrangeMoney][SANDBOX] Initiation simulée: ' . $payment->transaction_ref);

        return [
            'success'      => true,
            'operator_ref' => 'SIM-OM-' . strtoupper(Str::random(8)),
            'sandbox'      => true,
        ];
    }

    private function sandboxCheckStatus(MobilePayment $payment): array
    {
        $elapsed = now()->diffInSeconds($payment->initiated_at ?? now());

        // Délai simulé : 5–10 secondes avant résultat
        if ($elapsed < 5) {
            return ['status' => 'pending'];
        }

        // Une seule simulation par paiement (éviter les doubles)
        if ($payment->status !== MobilePayment::STATUS_PENDING) {
            return ['status' => $payment->status];
        }

        $isSuccess = rand(1, 100) <= self::SANDBOX_RATE;

        return [
            'status'       => $isSuccess ? 'success' : 'failed',
            'operator_txn' => $isSuccess ? ('OM-TXN-' . strtoupper(Str::random(8))) : null,
            'message'      => $isSuccess ? 'Paiement accepté (sandbox)' : 'Solde insuffisant (sandbox)',
            'sandbox'      => true,
        ];
    }
}
