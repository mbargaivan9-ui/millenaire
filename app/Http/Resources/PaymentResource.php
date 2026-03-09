<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'amount' => $this->amount,
            'amount_paid' => $this->amount_paid ?? 0,
            'amount_due' => $this->amount_due ?? 0,
            'status' => $this->status,
            'payment_method' => $this->payment_method ?? null,
            'transaction_ref' => $this->transaction_ref ?? null,
            'receipt_number' => $this->receipt_number ?? null,
            'due_date' => $this->due_date?->format('Y-m-d') ?? null,
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s') ?? null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'student' => [
                'id' => $this->student?->id,
                'name' => $this->student?->user?->name,
                'matricule' => $this->student?->matricule,
            ],
        ];
    }
}
