<?php

namespace App\Notifications\Payment;

use App\Models\MobilePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly MobilePayment $payment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'payment_failed',
            'title'           => '❌ Paiement échoué',
            'message'         => 'Votre paiement de ' . number_format($this->payment->amount, 0, ',', ' ')
                               . ' FCFA a échoué : ' . ($this->payment->failure_reason ?? 'raison inconnue'),
            'transaction_ref' => $this->payment->transaction_ref,
            'amount'          => $this->payment->amount,
            'operator'        => $this->payment->operator,
            'failure_reason'  => $this->payment->failure_reason,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
