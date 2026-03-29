<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\{MobilePayment, Student, User};
use App\Services\Payment\{PaymentOrchestrator, OrangeMoneyService, MtnMomoService};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth, Log};
use Illuminate\View\View;

/**
 * SchoolPayController
 *
 * Gère :
 *  - Interface de paiement parent (step-by-step)
 *  - Dashboard admin temps réel
 *  - Polling AJAX
 *  - Webhooks Orange Money & MTN MoMo
 *  - Reçus PDF
 */
class SchoolPayController extends Controller
{
    public function __construct(
        private readonly PaymentOrchestrator $orchestrator,
        private readonly OrangeMoneyService  $orangeService,
        private readonly MtnMomoService      $mtnService,
    ) {}

    // ══════════════════════════════════════════════════════════
    //  INTERFACE PARENT
    // ══════════════════════════════════════════════════════════

    /**
     * Page principale de paiement (étapes 1→4)
     */
    public function parentIndex(Request $request): View
    {
        $user     = Auth::user();
        $students = collect();

        // Récupérer les enfants du parent connecté
        if ($user->guardian) {
            $students = $user->guardian->students()->with('classe')->get();
        } elseif ($user->role === 'parent') {
            $students = Student::where('guardian_id', $user->id)
                ->orWhereHas('guardians', fn($q) => $q->where('user_id', $user->id))
                ->with('classe')->get();
        }

        // Historique des 5 derniers paiements
        $recentPayments = MobilePayment::where('payer_id', $user->id)
            ->with('student.user')
            ->latest()
            ->take(5)
            ->get();

        // Pré-sélection élève & montant
        $preStudent = $request->has('student_id')
            ? $students->firstWhere('id', $request->student_id)
            : null;

        return view('payment.parent.index', compact('students', 'recentPayments', 'preStudent'));
    }

    // ══════════════════════════════════════════════════════════
    //  API AJAX — PARENT
    // ══════════════════════════════════════════════════════════

    /**
     * Initier un paiement (AJAX)
     */
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'operator'   => 'required|in:orange,mtn',
            'phone'      => ['required', 'regex:/^6[0-9]{8}$/'],
            'amount'     => 'required|integer|min:500|max:5000000',
            'student_id' => 'nullable|exists:students,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'fee_type'   => 'nullable|string|max:100',
            'tranche'    => 'nullable|string|max:50',
        ]);

        // Vérifier l'autorisation parent→enfant
        if ($validated['student_id'] ?? null) {
            $this->authorizeParentStudent($validated['student_id']);
        }

        $result = $this->orchestrator->initiate($validated, Auth::user());

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }

        return response()->json($result);
    }

    /**
     * Polling du statut (AJAX — appelé toutes les 2s)
     */
    public function poll(string $transactionRef): JsonResponse
    {
        $payment = MobilePayment::where('transaction_ref', $transactionRef)
            ->where('payer_id', Auth::id())
            ->firstOrFail();

        $result = $this->orchestrator->poll($transactionRef);

        return response()->json($result);
    }

    /**
     * Récupérer les frais d'un élève (AJAX)
     */
    public function studentFees(Student $student): JsonResponse
    {
        $this->authorizeParentStudent($student->id);

        $student->load('classe');
        $paid = MobilePayment::where('student_id', $student->id)->success()->sum('amount');
        $due  = $student->classe?->annual_fee ?? 0;

        return response()->json([
            'student'     => [
                'id'    => $student->id,
                'name'  => $student->user?->name ?? 'N/A',
                'classe'=> $student->classe?->name ?? 'N/A',
            ],
            'annual_fee'  => $due,
            'total_paid'  => $paid,
            'balance'     => max(0, $due - $paid),
            'tranches'    => $this->computeTranches($due, $paid),
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  REÇUS
    // ══════════════════════════════════════════════════════════

    public function showReceipt(string $transactionRef): View
    {
        $payment = MobilePayment::where('transaction_ref', $transactionRef)
            ->where('status', MobilePayment::STATUS_SUCCESS)
            ->with('student.user', 'payer')
            ->firstOrFail();

        // Seul le payeur ou un admin peut voir le reçu
        if ($payment->payer_id !== Auth::id() && !Auth::user()?->isAdmin()) {
            abort(403);
        }

        return view('payment.receipt', compact('payment'));
    }

    // ══════════════════════════════════════════════════════════
    //  DASHBOARD ADMIN — TEMPS RÉEL
    // ══════════════════════════════════════════════════════════

    public function adminDashboard(): View
    {
        $stats = $this->orchestrator->getAdminStats();

        $payments = MobilePayment::with('student.user', 'payer', 'student.classe')
            ->latest()
            ->paginate(20);

        return view('payment.admin.dashboard', compact('stats', 'payments'));
    }

    /**
     * Stats JSON pour le dashboard admin en temps réel (polling toutes les 5s)
     */
    public function adminStats(): JsonResponse
    {
        $stats = $this->orchestrator->getAdminStats();

        // Sérialiser les transactions récentes
        $stats['recent_transactions'] = $stats['recent_transactions']->map(fn($p) => [
            'id'              => $p->id,
            'transaction_ref' => $p->transaction_ref,
            'student_name'    => $p->student?->user?->name ?? 'N/A',
            'classe'          => $p->student?->classe?->name ?? '—',
            'operator'        => $p->operator,
            'operator_label'  => $p->operator_label,
            'amount'          => $p->amount,
            'formatted_total' => $p->formatted_total,
            'phone'           => $p->phone,
            'status'          => $p->status,
            'status_label'    => $p->status_label,
            'status_color'    => $p->status_color,
            'tranche'         => $p->tranche ?? '—',
            'time'            => $p->created_at->format('H:i'),
            'date'            => $p->created_at->format('d/m/Y'),
            'receipt_url'     => $p->isSuccess() ? route('payment.receipt.show', $p->transaction_ref) : null,
        ]);

        return response()->json($stats);
    }

    // ══════════════════════════════════════════════════════════
    //  WEBHOOKS
    // ══════════════════════════════════════════════════════════

    /**
     * Webhook Orange Money
     */
    public function webhookOrange(Request $request): JsonResponse
    {
        $signature = $request->header('X-Orange-Signature', '');

        if (!$this->orangeService->verifyWebhookSignature($request->getContent(), $signature)) {
            Log::warning('[Webhook][Orange] Signature invalide');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data   = $request->json()->all();
        $ref    = $data['transaction_id'] ?? $data['order_id'] ?? null;
        $status = strtolower($data['status'] ?? '');

        if (!$ref) return response()->json(['error' => 'Missing ref'], 400);

        $payment = MobilePayment::where('transaction_ref', $ref)
            ->orWhere('operator_ref', $ref)->first();

        if ($payment) {
            $isSuccess = $status === 'success';
            $this->orchestrator->finalize(
                $payment,
                $isSuccess,
                $isSuccess ? ($data['txn_id'] ?? null) : null,
                !$isSuccess ? ($data['message'] ?? 'Refusé') : null
            );
        }

        return response()->json(['received' => true]);
    }

    /**
     * Webhook MTN MoMo
     */
    public function webhookMtn(Request $request): JsonResponse
    {
        $key = $request->header('X-Callback-Key', '');

        if (!$this->mtnService->verifyCallbackKey($key)) {
            Log::warning('[Webhook][MTN] Clé callback invalide');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data       = $request->json()->all();
        $externalId = $data['externalId'] ?? null;
        $status     = strtoupper($data['status'] ?? '');

        $payment = MobilePayment::where('transaction_ref', $externalId)->first();

        if ($payment) {
            $isSuccess = $status === 'SUCCESSFUL';
            $this->orchestrator->finalize(
                $payment,
                $isSuccess,
                $isSuccess ? ($data['financialTransactionId'] ?? null) : null,
                !$isSuccess ? ($data['reason'] ?? 'Refusé') : null
            );
        }

        return response()->json(['received' => true]);
    }

    // ──────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────

    private function authorizeParentStudent(int $studentId): void
    {
        $user = Auth::user();
        if ($user->isAdmin()) return;

        $allowed = $user->guardian?->students->pluck('id')->contains($studentId)
            ?? Student::where('guardian_id', $user->id)->where('id', $studentId)->exists();

        if (!$allowed) abort(403, 'Accès non autorisé à cet élève');
    }

    private function computeTranches(int $annualFee, int $alreadyPaid): array
    {
        $tranches = [
            ['id' => 'T1', 'label' => '1ère Tranche (40%)',  'pct' => 0.40],
            ['id' => 'T2', 'label' => '2ème Tranche (35%)',  'pct' => 0.35],
            ['id' => 'T3', 'label' => '3ème Tranche (25%)',  'pct' => 0.25],
            ['id' => 'FULL', 'label' => 'Paiement intégral', 'pct' => 1.00],
        ];

        return array_map(fn($t) => [
            ...$t,
            'amount'   => (int) round($annualFee * $t['pct']),
            'available'=> ($annualFee - $alreadyPaid) > 0,
        ], $tranches);
    }
}
