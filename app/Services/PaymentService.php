<?php

/**
 * PaymentService — Intégration Mobile Money Cameroun
 *
 * Phase 10 — Section 11.1
 * Support: Orange Money Cameroun + MTN MoMo Cameroun
 * Architecture: Initiation → Polling statut → Webhook confirmation → Reçu PDF
 *
 * @package App\Services
 */

namespace App\Services;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * ─── Orange Money Cameroun ────────────────────────────────────────────────
     */
    private string $orangeApiBase;
    private string $orangeMerchantKey;
    private string $orangeXApiKey;
    private bool   $sandboxMode;

    /**
     * ─── MTN MoMo ─────────────────────────────────────────────────────────────
     */
    private string $mtnApiBase;
    private string $mtnSubscriptionKey;
    private string $mtnApiUser;
    private string $mtnApiKey;
    private string $mtnCollectionUrl;

    public function __construct()
    {
        $this->sandboxMode       = config('app.env') !== 'production';

        // Orange Money
        $this->orangeApiBase     = config('services.orange_money.api_base', 'https://api.orange.com/orange-money-webpay/cm/v1');
        $this->orangeMerchantKey = config('services.orange_money.merchant_key', '');
        $this->orangeXApiKey     = config('services.orange_money.x_api_key', '');

        // MTN MoMo
        $this->mtnApiBase        = config('services.mtn_momo.api_base', 'https://proxy.momoapi.mtn.com');
        $this->mtnSubscriptionKey= config('services.mtn_momo.subscription_key', '');
        $this->mtnApiUser        = config('services.mtn_momo.api_user', '');
        $this->mtnApiKey         = config('services.mtn_momo.api_key', '');
        $this->mtnCollectionUrl  = '/collection/v1_0';
    }

    /**
     * ─── Initier un paiement Orange Money ───────────────────────────────────
     *
     * @param string $phone       Numéro sans préfixe international (ex: 655123456)
     * @param float  $amount      Montant en XAF
     * @param int    $studentId   ID de l'élève
     * @param string $description Description de la transaction
     * @return array{success: bool, transaction_ref: string, message: string}
     */
    public function initiateOrangeMoney(string $phone, float $amount, int $studentId, string $description = ''): array
    {
        $transactionRef = 'OM-' . strtoupper(Str::random(12));

        // Enregistrer la transaction en BDD avec statut pending
        $payment = Payment::create([
            'transaction_ref' => $transactionRef,
            'operator'        => 'orange',
            'phone_number'    => $phone,
            'amount'          => $amount,
            'student_id'      => $studentId,
            'description'     => $description ?: 'Frais de scolarité',
            'status'          => 'pending',
            'initiated_at'    => now(),
        ]);

        // Sandbox: simuler la réponse
        if ($this->sandboxMode) {
            Log::info("[PaymentService] SANDBOX Orange Money initié", ['ref' => $transactionRef, 'phone' => $phone, 'amount' => $amount]);
            return ['success' => true, 'transaction_ref' => $transactionRef, 'message' => 'SANDBOX: Paiement initié.'];
        }

        // Production: appel API réel
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getOrangeAccessToken(),
                'X-AUTH-TOKEN'  => $this->orangeMerchantKey,
                'Content-Type'  => 'application/json',
            ])->post("{$this->orangeApiBase}/webpayment", [
                'merchant_key'    => $this->orangeMerchantKey,
                'currency'        => 'XAF',
                'order_id'        => $transactionRef,
                'amount'          => (int) $amount,
                'return_url'      => route('payment.status', $transactionRef),
                'cancel_url'      => route('payment.mobile-money'),
                'notif_url'       => route('webhook.orange'),
                'lang'            => 'fr',
                'reference'       => $transactionRef,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $payment->update(['provider_ref' => $data['pay_token'] ?? null]);
                return ['success' => true, 'transaction_ref' => $transactionRef, 'pay_token' => $data['pay_token'] ?? null];
            }

            $error = $response->json('message') ?? 'Erreur Orange Money';
            Log::error("[PaymentService] Orange Money error", ['status' => $response->status(), 'body' => $response->body()]);
            $payment->update(['status' => 'failed', 'error_message' => $error]);
            return ['success' => false, 'message' => $error, 'transaction_ref' => $transactionRef];

        } catch (\Exception $e) {
            Log::error("[PaymentService] Orange Money exception", ['message' => $e->getMessage()]);
            $payment->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erreur de connexion au service Orange.', 'transaction_ref' => $transactionRef];
        }
    }

    /**
     * ─── Initier un paiement MTN MoMo ──────────────────────────────────────
     */
    public function initiateMTNMoMo(string $phone, float $amount, int $studentId, string $description = ''): array
    {
        $transactionRef = 'MTN-' . Str::uuid()->toString();

        $payment = Payment::create([
            'transaction_ref' => $transactionRef,
            'operator'        => 'mtn',
            'phone_number'    => $phone,
            'amount'          => $amount,
            'student_id'      => $studentId,
            'description'     => $description ?: 'Frais de scolarité',
            'status'          => 'pending',
            'initiated_at'    => now(),
        ]);

        if ($this->sandboxMode) {
            Log::info("[PaymentService] SANDBOX MTN MoMo initié", ['ref' => $transactionRef, 'phone' => $phone, 'amount' => $amount]);
            return ['success' => true, 'transaction_ref' => $transactionRef, 'message' => 'SANDBOX: En attente USSD.'];
        }

        try {
            $accessToken = $this->getMtnAccessToken();

            // MTN MoMo: Request to Pay
            $response = Http::withHeaders([
                'Authorization'          => "Bearer {$accessToken}",
                'X-Reference-Id'         => $transactionRef,
                'X-Target-Environment'   => 'production',
                'Ocp-Apim-Subscription-Key' => $this->mtnSubscriptionKey,
                'Content-Type'           => 'application/json',
            ])->post("{$this->mtnApiBase}{$this->mtnCollectionUrl}/requesttopay", [
                'amount'       => (string)(int)$amount,
                'currency'     => 'XAF',
                'externalId'   => $transactionRef,
                'payer'        => ['partyIdType' => 'MSISDN', 'partyId' => '237' . ltrim($phone, '0')],
                'payerMessage' => 'Paiement frais scolaires',
                'payeeNote'    => $description ?: 'Millénaire Connect',
            ]);

            if ($response->status() === 202) {
                return ['success' => true, 'transaction_ref' => $transactionRef, 'message' => 'Demande USSD envoyée. Validez sur votre téléphone.'];
            }

            $error = $response->json('message') ?? 'Erreur MTN MoMo';
            $payment->update(['status' => 'failed', 'error_message' => $error]);
            return ['success' => false, 'message' => $error, 'transaction_ref' => $transactionRef];

        } catch (\Exception $e) {
            Log::error("[PaymentService] MTN MoMo exception", ['message' => $e->getMessage()]);
            $payment->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erreur de connexion MTN.', 'transaction_ref' => $transactionRef];
        }
    }

    /**
     * ─── Vérifier le statut d'un paiement ──────────────────────────────────
     */
    public function checkPaymentStatus(string $transactionRef): array
    {
        $payment = Payment::where('transaction_ref', $transactionRef)->first();
        if (!$payment) {
            return ['status' => 'not_found', 'message' => 'Transaction introuvable.'];
        }

        // Sandbox: simuler succès après quelques polls (90% de chance)
        if ($this->sandboxMode) {
            if ($payment->status === 'pending' && $payment->created_at->diffInSeconds(now()) > 8) {
                $success = rand(1, 10) <= 9;
                $payment->update([
                    'status'       => $success ? 'success' : 'failed',
                    'confirmed_at' => $success ? now() : null,
                    'error_message'=> $success ? null : 'SANDBOX: Paiement refusé (simulation).',
                ]);
            }
            return [
                'status'          => $payment->status,
                'transaction_ref' => $transactionRef,
                'amount'          => $payment->amount,
                'operator'        => $payment->operator,
                'confirmed_at'    => $payment->confirmed_at?->toIso8601String(),
            ];
        }

        // Production: interroger l'API selon l'opérateur
        try {
            $apiStatus = $payment->operator === 'orange'
                ? $this->checkOrangeStatus($transactionRef, $payment->provider_ref)
                : $this->checkMtnStatus($transactionRef);

            if ($apiStatus !== $payment->status) {
                $payment->update([
                    'status'       => $apiStatus,
                    'confirmed_at' => $apiStatus === 'success' ? now() : null,
                ]);
            }

            return [
                'status'          => $apiStatus,
                'transaction_ref' => $transactionRef,
                'amount'          => $payment->amount,
                'operator'        => $payment->operator,
                'confirmed_at'    => $payment->confirmed_at?->toIso8601String(),
            ];
        } catch (\Exception $e) {
            return ['status' => $payment->status, 'transaction_ref' => $transactionRef];
        }
    }

    /**
     * ─── Traiter un webhook Orange Money ───────────────────────────────────
     */
    public function processOrangeWebhook(array $payload): void
    {
        $transactionRef = $payload['notif_token'] ?? $payload['order_id'] ?? null;
        if (!$transactionRef) return;

        $payment = Payment::where('transaction_ref', $transactionRef)->first();
        if (!$payment) return;

        $status = match($payload['status'] ?? '') {
            'SUCCESS'   => 'success',
            'FAILED'    => 'failed',
            'CANCELLED' => 'failed',
            default     => 'pending',
        };

        $payment->update([
            'status'       => $status,
            'confirmed_at' => $status === 'success' ? now() : null,
            'provider_ref' => $payload['txnid'] ?? $payment->provider_ref,
        ]);

        if ($status === 'success') {
            $this->onPaymentSuccess($payment);
        }
    }

    /**
     * ─── Traiter un webhook MTN MoMo ───────────────────────────────────────
     */
    public function processMtnWebhook(array $payload): void
    {
        $transactionRef = $payload['externalId'] ?? null;
        $status         = match($payload['status'] ?? '') {
            'SUCCESSFUL' => 'success',
            'FAILED'     => 'failed',
            default      => 'pending',
        };

        if (!$transactionRef) return;

        $payment = Payment::where('transaction_ref', $transactionRef)->first();
        if (!$payment) return;

        $payment->update(['status' => $status, 'confirmed_at' => $status === 'success' ? now() : null]);

        if ($status === 'success') {
            $this->onPaymentSuccess($payment);
        }
    }

    /**
     * ─── Actions après paiement confirmé ───────────────────────────────────
     */
    private function onPaymentSuccess(Payment $payment): void
    {
        // Marquer la facture comme payée
        // Envoyer la notification au parent
        $student = $payment->student?->load('guardian.user');
        
        activity()
            ->performedOn($payment)
            ->withProperties(['amount' => $payment->amount, 'operator' => $payment->operator])
            ->log('Paiement confirmé');
    }

    /**
     * ─── Helpers OAuth ──────────────────────────────────────────────────────
     */
    private function getOrangeAccessToken(): string
    {
        $response = Http::withBasicAuth($this->orangeMerchantKey, $this->orangeXApiKey)
            ->asForm()
            ->post('https://api.orange.com/oauth/v3/token', ['grant_type' => 'client_credentials']);

        return $response->json('access_token') ?? '';
    }

    private function getMtnAccessToken(): string
    {
        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $this->mtnSubscriptionKey,
        ])->withBasicAuth($this->mtnApiUser, $this->mtnApiKey)
          ->post("{$this->mtnApiBase}{$this->mtnCollectionUrl}/token/");

        return $response->json('access_token') ?? '';
    }

    private function checkOrangeStatus(string $ref, ?string $payToken): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getOrangeAccessToken(),
        ])->post("{$this->orangeApiBase}/transactionstatus", [
            'order_id'  => $ref,
            'pay_token' => $payToken,
        ]);

        return match($response->json('status') ?? '') {
            'SUCCESS'   => 'success',
            'FAILED'    => 'failed',
            default     => 'pending',
        };
    }

    private function checkMtnStatus(string $transactionRef): string
    {
        $response = Http::withHeaders([
            'Authorization'          => 'Bearer ' . $this->getMtnAccessToken(),
            'X-Target-Environment'   => 'production',
            'Ocp-Apim-Subscription-Key' => $this->mtnSubscriptionKey,
        ])->get("{$this->mtnApiBase}{$this->mtnCollectionUrl}/requesttopay/{$transactionRef}");

        return match($response->json('status') ?? '') {
            'SUCCESSFUL' => 'success',
            'FAILED'     => 'failed',
            default      => 'pending',
        };
    }
}
