<?php

namespace App\Services\Payment;

use App\Models\MobilePayment;
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Support\Str;

/**
 * MtnMomoService
 *
 * Gère les paiements MTN Mobile Money Cameroun.
 * - Sandbox : simulation complète avec délai réaliste
 * - Production : MTN MoMo API v1_0 (requesttopay)
 *
 * ⚙️  Variables .env requises (production) :
 *   MTN_MOMO_SUBSCRIPTION_KEY=
 *   MTN_MOMO_API_USER=
 *   MTN_MOMO_API_KEY=
 *   MTN_MOMO_CALLBACK_KEY=
 *   MTN_MOMO_ENVIRONMENT=sandbox|production
 */
class MtnMomoService
{
    private const API_BASE     = 'https://sandbox.momodeveloper.mtn.com';
    private const SANDBOX_RATE = 90;

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
            // Étape 1 : Obtenir le token OAuth MTN
            $token = $this->getAccessToken();
            if (!$token) {
                return ['success' => false, 'message' => 'MTN MoMo authentification échouée'];
            }

            $phone = '237' . preg_replace('/^\+237/', '', $payment->phone);

            // Étape 2 : Request to Pay
            $response = Http::withToken($token)
                ->withHeaders([
                    'X-Reference-Id'            => $payment->transaction_ref,
                    'X-Target-Environment'      => config('services.mtn_momo.environment', 'sandbox'),
                    'Ocp-Apim-Subscription-Key' => config('services.mtn_momo.subscription_key'),
                    'X-Callback-Url'            => route('webhooks.payment.mtn'),
                ])
                ->timeout(15)
                ->post(self::API_BASE . '/collection/v1_0/requesttopay', [
                    'amount'       => (string) $payment->amount,
                    'currency'     => 'XAF',
                    'externalId'   => $payment->transaction_ref,
                    'payer'        => ['partyIdType' => 'MSISDN', 'partyId' => $phone],
                    'payerMessage' => 'Frais scolaires — ' . config('app.name'),
                    'payeeNote'    => 'Ref: ' . $payment->transaction_ref,
                ]);

            if ($response->status() === 202) {
                return [
                    'success'      => true,
                    'operator_ref' => $payment->transaction_ref,
                ];
            }

            return [
                'success' => false,
                'message' => 'MTN MoMo — demande refusée (' . $response->status() . ')',
            ];
        } catch (\Throwable $e) {
            Log::error('[MTNMoMo] Initiation échouée: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de connexion MTN MoMo'];
        }
    }

    // ─── Vérifier le statut ───────────────────────────────────────────────────

    public function checkStatus(MobilePayment $payment): array
    {
        if ($this->isSandbox) {
            return $this->sandboxCheckStatus($payment);
        }

        try {
            $token = $this->getAccessToken();
            if (!$token) return ['status' => 'error', 'message' => 'Auth failed'];

            $response = Http::withToken($token)
                ->withHeaders([
                    'X-Target-Environment'      => config('services.mtn_momo.environment', 'sandbox'),
                    'Ocp-Apim-Subscription-Key' => config('services.mtn_momo.subscription_key'),
                ])
                ->timeout(10)
                ->get(self::API_BASE . '/collection/v1_0/requesttopay/' . $payment->operator_ref);

            $data   = $response->json();
            $status = strtoupper($data['status'] ?? '');

            return match($status) {
                'SUCCESSFUL' => [
                    'status'       => 'success',
                    'operator_txn' => $data['financialTransactionId'] ?? null,
                ],
                'FAILED', 'REJECTED' => [
                    'status'  => 'failed',
                    'message' => $data['reason'] ?? 'Refusé',
                ],
                default => ['status' => 'pending'],
            };
        } catch (\Throwable $e) {
            Log::error('[MTNMoMo] Status check échoué: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // ─── Token OAuth ─────────────────────────────────────────────────────────

    private function getAccessToken(): ?string
    {
        try {
            $response = Http::withBasicAuth(
                config('services.mtn_momo.api_user'),
                config('services.mtn_momo.api_key')
            )
            ->withHeaders(['Ocp-Apim-Subscription-Key' => config('services.mtn_momo.subscription_key')])
            ->timeout(10)
            ->post(self::API_BASE . '/collection/token/');

            return $response->successful() ? $response->json('access_token') : null;
        } catch (\Throwable $e) {
            Log::error('[MTNMoMo] Token request failed: ' . $e->getMessage());
            return null;
        }
    }

    // ─── Vérifier callback key ────────────────────────────────────────────────

    public function verifyCallbackKey(string $key): bool
    {
        return $key === config('services.mtn_momo.callback_key');
    }

    // ─── USSD Code ────────────────────────────────────────────────────────────

    public function getUssdCode(MobilePayment $payment): string
    {
        $phone  = preg_replace('/^\+237/', '', $payment->phone);
        $amount = $payment->total_amount;
        return "*126*{$phone}*{$amount}#";
    }

    // ─── Sandbox ─────────────────────────────────────────────────────────────

    private function sandboxInitiate(MobilePayment $payment): array
    {
        Log::info('[MTNMoMo][SANDBOX] Initiation simulée: ' . $payment->transaction_ref);
        return [
            'success'      => true,
            'operator_ref' => 'SIM-MTN-' . strtoupper(Str::random(8)),
            'sandbox'      => true,
        ];
    }

    private function sandboxCheckStatus(MobilePayment $payment): array
    {
        $elapsed = now()->diffInSeconds($payment->initiated_at ?? now());

        if ($elapsed < 6) return ['status' => 'pending'];
        if ($payment->status !== MobilePayment::STATUS_PENDING) return ['status' => $payment->status];

        $isSuccess = rand(1, 100) <= self::SANDBOX_RATE;

        return [
            'status'       => $isSuccess ? 'success' : 'failed',
            'operator_txn' => $isSuccess ? ('MTN-' . strtoupper(Str::random(10))) : null,
            'message'      => $isSuccess ? 'Approuvé (sandbox)' : 'Refusé (sandbox)',
            'sandbox'      => true,
        ];
    }
}
