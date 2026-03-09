<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * CamPayService
 *
 * Intégration de l'API CamPay pour les paiements Mobile Money
 * au Cameroun (MTN Mobile Money & Orange Money).
 *
 * Documentation CamPay : https://documenter.getpostman.com/view/2471422/campay-api
 */
class CamPayService
{
    private string $baseUrl;
    private string $appUsername;
    private string $appPassword;
    private ?string $token = null;
    private ?int $tokenExpiry = null;

    public function __construct()
    {
        $this->baseUrl     = config('campay.base_url', 'https://demo.campay.net/api');
        $this->appUsername = config('campay.username', '');
        $this->appPassword = config('campay.password', '');
    }

    // ════════════════════════════════════════════════
    //  1. AUTHENTIFICATION (token Bearer)
    // ════════════════════════════════════════════════

    /**
     * Obtenir ou renouveler le token d'accès CamPay.
     */
    public function getToken(): string
    {
        // Réutiliser si valide (expire après ~60 min)
        if ($this->token && $this->tokenExpiry && now()->timestamp < $this->tokenExpiry) {
            return $this->token;
        }

        $response = Http::post("{$this->baseUrl}/token/", [
            'username' => $this->appUsername,
            'password' => $this->appPassword,
        ]);

        if (! $response->successful()) {
            Log::error('CamPay: Échec d\'authentification', ['body' => $response->body()]);
            throw new \RuntimeException('Impossible de s\'authentifier auprès de CamPay.');
        }

        $data              = $response->json();
        $this->token       = $data['token'];
        $this->tokenExpiry = now()->addMinutes(50)->timestamp;

        return $this->token;
    }

    // ════════════════════════════════════════════════
    //  2. INITIER UN PAIEMENT (Collect / Pull)
    // ════════════════════════════════════════════════

    /**
     * Initie une demande de paiement Mobile Money.
     * L'élève/parent reçoit une notification USSD sur son téléphone.
     *
     * @param  string  $phone        Numéro de téléphone (format: 237XXXXXXXXX)
     * @param  float   $amount       Montant en XAF (entier !)
     * @param  string  $description  Description de la transaction
     * @param  string  $reference    Référence interne (ex: FRAIS-2025-0042)
     * @return array   { reference, ussd_code, operator, status }
     */
    public function collect(
        string $phone,
        float $amount,
        string $description,
        string $reference
    ): array {
        $token = $this->getToken();

        // Normaliser le numéro (enlever le + si présent)
        $phone = preg_replace('/^\+/', '', $phone);

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/collect/", [
                'amount'          => (int) $amount,
                'from'            => $phone,
                'description'     => $description,
                'external_reference' => $reference,
            ]);

        $data = $response->json();

        if (! $response->successful() || empty($data['reference'])) {
            Log::error('CamPay: Échec collect', ['phone' => $phone, 'body' => $data]);
            throw new \RuntimeException(
                $data['detail'] ?? $data['message'] ?? 'Erreur lors de l\'initiation du paiement.'
            );
        }

        Log::info('CamPay: Paiement initié', [
            'campay_ref' => $data['reference'],
            'phone'      => $phone,
            'amount'     => $amount,
        ]);

        return [
            'campay_reference' => $data['reference'],
            'ussd_code'        => $data['ussd_code'] ?? null,
            'operator'         => $data['operator'] ?? $this->detectOperator($phone),
            'status'           => 'pending',
        ];
    }

    // ════════════════════════════════════════════════
    //  3. VÉRIFIER LE STATUT D'UN PAIEMENT
    // ════════════════════════════════════════════════

    /**
     * Vérifie le statut d'une transaction CamPay.
     *
     * @param  string $campayReference  La référence CamPay retournée par collect()
     * @return array  { status: 'SUCCESSFUL'|'FAILED'|'PENDING', operator, amount }
     */
    public function checkStatus(string $campayReference): array
    {
        $token = $this->getToken();

        $response = Http::withToken($token)
            ->get("{$this->baseUrl}/transaction/{$campayReference}/");

        if (! $response->successful()) {
            Log::warning('CamPay: Impossible de vérifier le statut', ['ref' => $campayReference]);
            return ['status' => 'PENDING', 'operator' => null, 'amount' => null];
        }

        $data = $response->json();

        Log::info('CamPay: Statut vérifié', [
            'ref'    => $campayReference,
            'status' => $data['status'] ?? 'UNKNOWN',
        ]);

        return [
            'status'   => $data['status'] ?? 'PENDING', // SUCCESSFUL | FAILED | PENDING
            'operator' => $data['operator'] ?? null,
            'amount'   => $data['amount'] ?? null,
            'phone'    => $data['from'] ?? null,
            'message'  => $data['message'] ?? null,
        ];
    }

    // ════════════════════════════════════════════════
    //  4. SOLDE DU COMPTE MARCHAND
    // ════════════════════════════════════════════════

    public function getBalance(): array
    {
        $token    = $this->getToken();
        $response = Http::withToken($token)->get("{$this->baseUrl}/get_balance/");

        return $response->json() ?? ['total' => 0];
    }

    // ════════════════════════════════════════════════
    //  5. WEBHOOK — Vérifier la signature
    // ════════════════════════════════════════════════

    /**
     * Vérifie qu'une requête webhook vient bien de CamPay.
     * CamPay envoie un header X-Webhook-Secret.
     */
    public function verifyWebhookSignature(string $secret): bool
    {
        $expected = config('campay.webhook_secret', '');
        return hash_equals($expected, $secret);
    }

    // ════════════════════════════════════════════════
    //  6. HELPERS
    // ════════════════════════════════════════════════

    /**
     * Détecter l'opérateur depuis le numéro.
     */
    public function detectOperator(string $phone): string
    {
        $phone  = preg_replace('/^\+?237/', '', $phone);
        $prefix = substr($phone, 0, 2);

        $mtn    = ['65', '67', '68', '69', '66', '6500', '6501', '6502'];
        $orange = ['69', '699', '695', '696', '697'];

        foreach ($orange as $p) {
            if (str_starts_with($phone, ltrim($p, '0'))) return 'orange';
        }
        foreach ($mtn as $p) {
            if (str_starts_with($phone, ltrim($p, '0'))) return 'mtn';
        }

        return 'unknown';
    }

    /**
     * Formater le montant pour l'affichage (XAF).
     */
    public static function formatAmount(float $amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' XAF';
    }
}
