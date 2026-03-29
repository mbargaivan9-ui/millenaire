<?php

namespace App\Notifications\Payment;

use App\Models\MobilePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * AdminPaymentAlertNotification
 * Envoyée à tous les admins/intendants à chaque paiement réussi
 */
class AdminPaymentAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly MobilePayment $payment) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'admin_payment_alert',
            'title'           => '💰 Nouveau paiement reçu',
            'message'         => ($this->payment->student?->user?->name ?? 'N/A')
                               . ' — ' . number_format($this->payment->amount, 0, ',', ' ') . ' FCFA'
                               . ' via ' . $this->payment->operator_label,
            'transaction_ref' => $this->payment->transaction_ref,
            'amount'          => $this->payment->amount,
            'total_amount'    => $this->payment->total_amount,
            'operator'        => $this->payment->operator,
            'operator_label'  => $this->payment->operator_label,
            'student_name'    => $this->payment->student?->user?->name ?? 'N/A',
            'classe'          => $this->payment->student?->classe?->name ?? '—',
            'tranche'         => $this->payment->tranche ?? '—',
            'phone'           => $this->payment->phone,
            'time'            => $this->payment->completed_at?->format('H:i') ?? now()->format('H:i'),
            'receipt_url'     => route('payment.receipt.show', $this->payment->transaction_ref),
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
