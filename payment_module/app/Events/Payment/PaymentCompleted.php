<?php

namespace App\Events\Payment;

use App\Models\MobilePayment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * PaymentCompleted
 *
 * Broadcasté sur le channel admin-payments
 * à chaque paiement validé, pour la mise à jour
 * en temps réel du dashboard admin.
 */
class PaymentCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly MobilePayment $payment) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('admin-payments'),
            new PresenceChannel('payment.' . $this->payment->payer_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'payment.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->payment->id,
            'transaction_ref' => $this->payment->transaction_ref,
            'student_name'    => $this->payment->student?->user?->name ?? 'N/A',
            'classe'          => $this->payment->student?->classe?->name ?? '—',
            'operator'        => $this->payment->operator,
            'operator_label'  => $this->payment->operator_label,
            'amount'          => $this->payment->amount,
            'total_amount'    => $this->payment->total_amount,
            'formatted_total' => $this->payment->formatted_total,
            'phone'           => $this->payment->phone,
            'tranche'         => $this->payment->tranche ?? '—',
            'status'          => $this->payment->status,
            'status_label'    => $this->payment->status_label,
            'status_color'    => $this->payment->status_color,
            'time'            => $this->payment->completed_at?->format('H:i'),
            'receipt_url'     => route('payment.receipt.show', $this->payment->transaction_ref),
        ];
    }
}
