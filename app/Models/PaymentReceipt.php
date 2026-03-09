<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PaymentReceipt Model
 * Reçu PDF avec QR Code vérifiable.
 */
class PaymentReceipt extends Model
{
    protected $fillable = [
        'payment_id',
        'verification_token',
        'verify_url',
        'qr_code_svg',
        'receipt_number',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
