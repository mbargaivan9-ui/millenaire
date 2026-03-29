<?php

namespace App\Notifications\Payment;

use App\Models\MobilePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PaymentSuccessNotification
 * Envoyée au parent après confirmation du paiement
 */
class PaymentSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly MobilePayment $payment) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('✅ Paiement confirmé — ' . config('app.name'))
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre paiement de **' . number_format($this->payment->amount, 0, ',', ' ') . ' FCFA** a été confirmé avec succès.')
            ->line('**Élève :** ' . ($this->payment->student?->user?->name ?? 'N/A'))
            ->line('**Opérateur :** ' . $this->payment->operator_label)
            ->line('**Référence :** ' . $this->payment->transaction_ref)
            ->action('Voir le reçu', route('payment.receipt.show', $this->payment->transaction_ref))
            ->line('Merci de votre confiance.')
            ->salutation('— L\'équipe ' . config('app.name'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'            => 'payment_success',
            'title'           => 'Paiement confirmé',
            'message'         => number_format($this->payment->amount, 0, ',', ' ') . ' FCFA crédités via ' . $this->payment->operator_label,
            'transaction_ref' => $this->payment->transaction_ref,
            'amount'          => $this->payment->amount,
            'operator'        => $this->payment->operator,
            'student_name'    => $this->payment->student?->user?->name,
            'receipt_url'     => route('payment.receipt.show', $this->payment->transaction_ref),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
