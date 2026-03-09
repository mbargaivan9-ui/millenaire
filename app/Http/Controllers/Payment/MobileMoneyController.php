<?php

/**
 * MobileMoneyController
 *
 * Contrôleur paiements Mobile Money — Orange Money & MTN MoMo
 * Phase 10 — Section 11.1 — Paiements Premium
 *
 * Flux: Initier → Polling statut → Webhook confirmation → Reçu PDF
 *
 * @package App\Http\Controllers\Payment
 */

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Invoice;
use App\Services\NotificationService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Hash, Http, Log};
use Illuminate\Support\Str;

class MobileMoneyController extends Controller
{
    private const ORANGE_API_BASE = 'https://api.orange.com/orange-money-webpay/cm/v1';
    private const MTN_API_BASE    = 'https://sandbox.momodeveloper.mtn.com';

    // Simulation sandbox: 90% succès, 10% échec
    private const SANDBOX_SUCCESS_RATE = 90;

    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    // ─── Page de paiement ──────────────────────────────────────────────────────

    public function showPaymentPage(Request $request): \Illuminate\View\View
    {
        $student = null;
        $invoice = null;

        if ($request->has('student_id')) {
            $student = Student::with('user', 'classe')->findOrFail($request->student_id);

            // Vérifier que l'utilisateur connecté est parent/tuteur de cet élève
            if (!auth()->user()->guardian?->students->contains($student->id)
                && auth()->user()->role !== 'admin') {
                abort(403);
            }
        }

        if ($request->has('invoice_id')) {
            $invoice = Invoice::findOrFail($request->invoice_id);
            $amount  = $invoice->amount_due;
            $feeType = $invoice->fee_type;
        } else {
            $amount  = $request->integer('amount', 0);
            $feeType = $request->get('fee_type', 'Frais scolaires');
        }

        return view('payment.mobile-money', compact('student', 'invoice', 'amount', 'feeType'));
    }

    // ─── Initier le paiement ───────────────────────────────────────────────────

    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'operator'   => 'required|in:orange,mtn',
            'phone'      => 'required|regex:/^6[0-9]{8}$/',
            'amount'     => 'required|integer|min:100|max:5000000',
            'student_id' => 'nullable|exists:students,id',
            'invoice_id' => 'nullable|exists:invoices,id',
        ]);

        $transactionRef = 'MC-' . strtoupper(Str::random(12));

        // Créer le paiement en BDD (statut: pending)
        $payment = Payment::create([
            'transaction_ref'   => $transactionRef,
            'operator'          => $request->operator,
            'phone'             => '+237' . $request->phone,
            'amount'            => $request->amount,
            'status'            => 'pending',
            'student_id'        => $request->student_id,
            'invoice_id'        => $request->invoice_id,
            'initiated_by'      => auth()->id(),
            'initiated_at'      => now(),
        ]);

        // Appel API opérateur (ou simulation sandbox)
        $apiResult = app()->isLocal() || config('app.payment_sandbox')
            ? $this->simulateSandbox($request->operator, $transactionRef)
            : $this->callOperatorApi($request->operator, $request->phone, $request->amount, $transactionRef);

        if (!$apiResult['success']) {
            $payment->update(['status' => 'failed', 'failure_reason' => $apiResult['message']]);
            return response()->json(['success' => false, 'message' => $apiResult['message']], 422);
        }

        // Mettre à jour avec le ref opérateur
        $payment->update(['operator_ref' => $apiResult['operator_ref'] ?? null]);

        Log::info("[MobileMoney] Payment initiated: {$transactionRef} via {$request->operator} for {$request->amount} XAF");

        return response()->json([
            'success'         => true,
            'transaction_ref' => $transactionRef,
            'message'         => 'Paiement initié — vérifiez votre téléphone',
        ]);
    }

    // ─── Polling du statut ─────────────────────────────────────────────────────

    public function checkStatus(string $transactionRef): JsonResponse
    {
        $payment = Payment::where('transaction_ref', $transactionRef)->firstOrFail();

        // Simulation sandbox: après 3-5 secondes, donner un résultat
        if (app()->isLocal() || config('app.payment_sandbox')) {
            $secondsElapsed = now()->diffInSeconds($payment->initiated_at);

            if ($secondsElapsed < 4) {
                return response()->json(['status' => 'pending']);
            }

            // Simuler 90% de succès
            $isSuccess = rand(1, 100) <= self::SANDBOX_SUCCESS_RATE;

            if ($payment->status === 'pending') {
                // Ne simuler qu'une seule fois
                $newStatus = $isSuccess ? 'success' : 'failed';
                $this->finalizePayment($payment, $newStatus, $isSuccess
                    ? 'SIM-' . strtoupper(Str::random(8))
                    : null);
            }
        }

        // Statut actuel en BDD
        if ($payment->status === 'success') {
            return response()->json([
                'status'          => 'success',
                'transaction_ref' => $payment->transaction_ref,
                'receipt_url'     => route('payment.receipt', $payment->id),
            ]);
        }

        if ($payment->status === 'failed') {
            return response()->json([
                'status'  => 'failed',
                'message' => $payment->failure_reason ?? 'Paiement refusé',
            ]);
        }

        return response()->json(['status' => 'pending']);
    }

    // ─── Webhook Orange Money ──────────────────────────────────────────────────

    public function handleOrangeWebhook(Request $request): JsonResponse
    {
        // Vérifier signature HMAC
        $signature = $request->header('X-Orange-Signature');
        $expected  = hash_hmac('sha256', $request->getContent(), config('services.orange_money.webhook_secret'));

        if (!hash_equals($expected, $signature ?? '')) {
            Log::warning('[OrangeWebhook] Invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = $request->json()->all();
        $ref  = $data['transaction_id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$ref) {
            return response()->json(['error' => 'Missing transaction_id'], 400);
        }

        $payment = Payment::where('transaction_ref', $ref)
            ->orWhere('operator_ref', $ref)
            ->first();

        if ($payment) {
            $isSuccess = strtolower($status) === 'success';
            $this->finalizePayment($payment, $isSuccess ? 'success' : 'failed',
                $isSuccess ? ($data['operator_txn_id'] ?? null) : null,
                !$isSuccess ? ($data['message'] ?? 'Refusé par Orange Money') : null
            );
        }

        return response()->json(['received' => true]);
    }

    // ─── Webhook MTN MoMo ─────────────────────────────────────────────────────

    public function handleMtnWebhook(Request $request): JsonResponse
    {
        // Vérifier X-Callback-Key MTN
        $callbackKey = $request->header('X-Callback-Key');
        if ($callbackKey !== config('services.mtn_momo.callback_key')) {
            Log::warning('[MTNWebhook] Invalid callback key');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data      = $request->json()->all();
        $externalId = $data['externalId'] ?? null;
        $status     = $data['status'] ?? null;

        $payment = Payment::where('transaction_ref', $externalId)->first();

        if ($payment) {
            $isSuccess = strtolower($status) === 'successful';
            $this->finalizePayment($payment, $isSuccess ? 'success' : 'failed',
                $isSuccess ? ($data['financialTransactionId'] ?? null) : null,
                !$isSuccess ? ($data['reason'] ?? 'Refusé par MTN') : null
            );
        }

        return response()->json(['received' => true]);
    }

    // ─── Reçu PDF ─────────────────────────────────────────────────────────────

    public function showReceipt(Payment $payment): \Illuminate\View\View
    {
        // Vérifier que l'utilisateur connecté peut voir ce reçu
        if ($payment->initiated_by !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $payment->load('student.user', 'invoice');

        return view('payment.receipt', compact('payment'));
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function finalizePayment(
        Payment $payment,
        string  $status,
        ?string $operatorTxnId = null,
        ?string $failureReason = null
    ): void {
        if ($payment->status !== 'pending') return; // Déjà traité

        $updates = [
            'status'       => $status,
            'completed_at' => now(),
        ];

        if ($operatorTxnId) $updates['operator_txn_id'] = $operatorTxnId;
        if ($failureReason) $updates['failure_reason']  = $failureReason;

        $payment->update($updates);

        if ($status === 'success') {
            // Marquer la facture comme payée
            if ($payment->invoice_id) {
                Invoice::where('id', $payment->invoice_id)->update(['status' => 'paid', 'paid_at' => now()]);
            }

            // Notifier le payeur
            $user = \App\Models\User::find($payment->initiated_by);
            if ($user) {
                $this->notificationService->sendPaymentConfirmation($user, $payment->toArray());
            }

            Log::info("[MobileMoney] Payment SUCCESS: {$payment->transaction_ref} — XAF {$payment->amount}");
        } else {
            Log::info("[MobileMoney] Payment FAILED: {$payment->transaction_ref} — {$failureReason}");
        }

        // Logger
        activity()
            ->withProperties($payment->toArray())
            ->log("Paiement Mobile Money {$status}");
    }

    private function simulateSandbox(string $operator, string $ref): array
    {
        // Simulation instantanée — le polling gérera le délai
        return [
            'success'      => true,
            'operator_ref' => 'SIM-' . strtoupper(Str::random(8)),
        ];
    }

    private function callOperatorApi(string $operator, string $phone, int $amount, string $ref): array
    {
        try {
            if ($operator === 'orange') {
                return $this->callOrangeMoneyApi($phone, $amount, $ref);
            } else {
                return $this->callMtnMomoApi($phone, $amount, $ref);
            }
        } catch (\Throwable $e) {
            Log::error("[MobileMoney] API call failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de connexion à l\'opérateur'];
        }
    }

    private function callOrangeMoneyApi(string $phone, int $amount, string $ref): array
    {
        $response = Http::withToken(config('services.orange_money.api_key'))
            ->timeout(15)
            ->post(self::ORANGE_API_BASE . '/webpayment', [
                'merchant_key'   => config('services.orange_money.merchant_key'),
                'currency'       => 'XAF',
                'order_id'       => $ref,
                'amount'         => $amount,
                'return_url'     => route('payment.callback.orange'),
                'cancel_url'     => route('payment.cancel'),
                'notif_url'      => route('payment.webhook.orange'),
                'lang'           => 'fr',
                'reference'      => $ref,
            ]);

        if ($response->successful() && $response->json('status') === '200') {
            return ['success' => true, 'operator_ref' => $response->json('pay_token')];
        }

        return ['success' => false, 'message' => $response->json('message') ?? 'Orange Money indisponible'];
    }

    private function callMtnMomoApi(string $phone, int $amount, string $ref): array
    {
        // Obtenir le token OAuth MTN
        $tokenRes = Http::withBasicAuth(
            config('services.mtn_momo.api_user'),
            config('services.mtn_momo.api_key')
        )
        ->withHeaders(['Ocp-Apim-Subscription-Key' => config('services.mtn_momo.subscription_key')])
        ->timeout(10)
        ->post(self::MTN_API_BASE . '/collection/token/');

        if (!$tokenRes->successful()) {
            return ['success' => false, 'message' => 'MTN MoMo authentification échouée'];
        }

        $accessToken = $tokenRes->json('access_token');

        // Initier le paiement Request to Pay
        $requestRes = Http::withToken($accessToken)
            ->withHeaders([
                'X-Reference-Id'             => $ref,
                'X-Target-Environment'       => config('services.mtn_momo.environment', 'sandbox'),
                'Ocp-Apim-Subscription-Key'  => config('services.mtn_momo.subscription_key'),
            ])
            ->timeout(15)
            ->post(self::MTN_API_BASE . '/collection/v1_0/requesttopay', [
                'amount'     => (string)$amount,
                'currency'   => 'XAF',
                'externalId' => $ref,
                'payer'      => ['partyIdType' => 'MSISDN', 'partyId' => '237' . $phone],
                'payerMessage' => 'Frais scolaires — Millénaire Connect',
                'payeeNote'    => "Ref: {$ref}",
            ]);

        if ($requestRes->status() === 202) {
            return ['success' => true, 'operator_ref' => $ref];
        }

        return ['success' => false, 'message' => 'MTN MoMo — demande refusée'];
    }

    /**
     * Télécharger le reçu de paiement en PDF.
     */
    public function receiptPdf(string $transactionRef)
    {
        $payment = \App\Models\Payment::where('transaction_ref', $transactionRef)->firstOrFail();

        // Security: parent can only download their children's receipts
        if (auth()->user()->role === 'parent') {
            $guardian = auth()->user()->guardian;
            abort_unless(
                $guardian && $payment->student?->guardian_id === $guardian->id,
                403
            );
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.payment-receipt', compact('payment'))
            ->setPaper([0, 0, 311, 567]); // ~110mm × 200mm receipt size

        return $pdf->download("recu-{$transactionRef}.pdf");
    }
}

    // ─── Route aliases ────────────────────────────────────────────────────────
    public function show(Request $request): \Illuminate\View\View
    {
        return $this->showPaymentPage($request);
    }

    public function webhookOrange(Request $request): JsonResponse
    {
        return $this->handleOrangeWebhook($request);
    }

    public function webhookMtn(Request $request): JsonResponse
    {
        return $this->handleMtnWebhook($request);
}
