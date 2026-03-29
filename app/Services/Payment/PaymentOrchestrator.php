<?php

namespace App\Services\Payment;

use App\Models\{MobilePayment, Student, User};
use App\Notifications\Payment\{PaymentSuccessNotification, PaymentFailedNotification};
use App\Events\Payment\PaymentCompleted;
use Illuminate\Support\Facades\{DB, Log, Notification};

/**
 * PaymentOrchestrator
 *
 * Orchestre le flux complet :
 *   Initiation → Polling → Finalisation → Notifications → Reçu PDF
 */
class PaymentOrchestrator
{
    public function __construct(
        private readonly OrangeMoneyService $orangeService,
        private readonly MtnMomoService     $mtnService,
        private readonly ReceiptService     $receiptService,
    ) {}

    // ─── 1. Initier le paiement ───────────────────────────────────────────────

    public function initiate(array $data, User $payer): array
    {
        return DB::transaction(function () use ($data, $payer) {
            $amount = (int) $data['amount'];
            $fees   = MobilePayment::calculateFees($amount);

            $payment = MobilePayment::create([
                'transaction_ref' => MobilePayment::generateRef(),
                'operator'        => $data['operator'],
                'phone'           => '+237' . ltrim($data['phone'], '+237'),
                'amount'          => $amount,
                'fees'            => $fees,
                'total_amount'    => $amount + $fees,
                'status'          => MobilePayment::STATUS_PENDING,
                'student_id'      => $data['student_id'] ?? null,
                'payer_id'        => $payer->id,
                'invoice_id'      => $data['invoice_id'] ?? null,
                'fee_type'        => $data['fee_type'] ?? 'Frais scolaires',
                'description'     => $data['description'] ?? null,
                'tranche'         => $data['tranche'] ?? null,
                'initiated_at'    => now(),
                'expires_at'      => now()->addMinutes(10),
                'is_sandbox'      => app()->isLocal() || config('app.payment_sandbox', true),
                'api_request_log' => $data,
            ]);

            // Appel opérateur
            $service = $data['operator'] === 'orange' ? $this->orangeService : $this->mtnService;
            $result  = $service->initiate($payment);

            if (!$result['success']) {
                $payment->update(['status' => MobilePayment::STATUS_FAILED, 'failure_reason' => $result['message']]);
                return ['success' => false, 'message' => $result['message']];
            }

            $payment->update([
                'operator_ref'     => $result['operator_ref'] ?? null,
                'status'           => MobilePayment::STATUS_PROCESSING,
                'api_response_log' => $result,
            ]);

            Log::info("[Payment] Initié {$payment->transaction_ref} — {$data['operator']} — {$amount} XAF");

            return [
                'success'         => true,
                'transaction_ref' => $payment->transaction_ref,
                'payment_id'      => $payment->id,
                'ussd_code'       => $service->getUssdCode($payment),
                'expires_at'      => $payment->expires_at->toIso8601String(),
                'is_sandbox'      => $payment->is_sandbox,
            ];
        });
    }

    // ─── 2. Polling du statut ─────────────────────────────────────────────────

    public function poll(string $transactionRef): array
    {
        $payment = MobilePayment::where('transaction_ref', $transactionRef)->firstOrFail();

        // Déjà terminé
        if ($payment->isSuccess()) {
            return [
                'status'      => 'success',
                'receipt_url' => route('payment.receipt.show', $payment->transaction_ref),
            ];
        }

        if ($payment->isFailed()) {
            return ['status' => 'failed', 'message' => $payment->failure_reason ?? 'Paiement refusé'];
        }

        // Expiré
        if ($payment->is_expired) {
            $payment->update(['status' => MobilePayment::STATUS_EXPIRED]);
            return ['status' => 'expired', 'message' => 'Délai de paiement expiré'];
        }

        // Interroger l'opérateur
        $service = $payment->isOrange() ? $this->orangeService : $this->mtnService;
        $result  = $service->checkStatus($payment);

        if ($result['status'] === 'success') {
            $this->finalize($payment, true, $result['operator_txn'] ?? null);
            return [
                'status'      => 'success',
                'receipt_url' => route('payment.receipt.show', $payment->transaction_ref),
            ];
        }

        if ($result['status'] === 'failed') {
            $this->finalize($payment, false, null, $result['message'] ?? 'Refusé');
            return ['status' => 'failed', 'message' => $result['message'] ?? 'Paiement refusé'];
        }

        return ['status' => 'pending', 'elapsed' => now()->diffInSeconds($payment->initiated_at)];
    }

    // ─── 3. Finaliser ────────────────────────────────────────────────────────

    public function finalize(
        MobilePayment $payment,
        bool          $success,
        ?string       $operatorTxnId = null,
        ?string       $failureReason = null
    ): void {
        if (!$payment->isPending()) return; // Déjà traité

        DB::transaction(function () use ($payment, $success, $operatorTxnId, $failureReason) {
            $payment->update([
                'status'           => $success ? MobilePayment::STATUS_SUCCESS : MobilePayment::STATUS_FAILED,
                'operator_txn_id'  => $operatorTxnId,
                'failure_reason'   => $failureReason,
                'completed_at'     => now(),
            ]);

            if ($success) {
                // Générer le numéro de reçu
                $payment->update(['receipt_number' => MobilePayment::generateReceiptNumber()]);

                // Notifier le payeur
                $payment->payer->notify(new PaymentSuccessNotification($payment));

                // Notifier tous les admins
                $admins = User::where('role', 'admin')->orWhere('role', 'intendant')->get();
                Notification::send($admins, new \App\Notifications\Payment\AdminPaymentAlertNotification($payment));

                // Émettre l'event (broadcast temps réel)
                event(new PaymentCompleted($payment));

                // Mettre à jour la facture si liée
                if ($payment->invoice_id) {
                    \App\Models\Invoice::where('id', $payment->invoice_id)
                        ->update(['status' => 'paid', 'paid_at' => now()]);
                }

                Log::info("[Payment] ✅ SUCCESS {$payment->transaction_ref} — {$payment->amount} XAF");
            } else {
                // Notifier l'échec
                $payment->payer->notify(new PaymentFailedNotification($payment));
                Log::info("[Payment] ❌ FAILED {$payment->transaction_ref} — {$failureReason}");
            }
        });
    }

    // ─── Statistiques admin temps réel ───────────────────────────────────────

    public function getAdminStats(): array
    {
        $today     = MobilePayment::success()->today();
        $thisMonth = MobilePayment::success()->thisMonth();

        return [
            'today_total'        => $today->sum('amount'),
            'today_count'        => $today->count(),
            'month_total'        => $thisMonth->sum('amount'),
            'month_count'        => $thisMonth->count(),
            'pending_count'      => MobilePayment::pending()->count(),
            'orange_total'       => MobilePayment::success()->orange()->thisMonth()->sum('amount'),
            'mtn_total'          => MobilePayment::success()->mtn()->thisMonth()->sum('amount'),
            'success_rate'       => $this->computeSuccessRate(),
            'recent_transactions'=> MobilePayment::with('student.user', 'payer')
                                        ->latest()->take(10)->get(),
        ];
    }

    private function computeSuccessRate(): float
    {
        $total   = MobilePayment::thisMonth()->count();
        $success = MobilePayment::success()->thisMonth()->count();
        return $total > 0 ? round(($success / $total) * 100, 1) : 0;
    }
}
