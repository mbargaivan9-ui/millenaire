<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

// ─────────────────────────────────────────────────────────────────────────────
// Payment
// ─────────────────────────────────────────────────────────────────────────────
class Payment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount'       => 'float',
        'amount_due'   => 'float',
        'paid_at'      => 'datetime',
    ];

    const STATUS_PENDING  = 'pending';
    const STATUS_SUCCESS  = 'success';
    const STATUS_FAILED   = 'failed';
    const STATUS_EXPIRED  = 'expired';

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Get human-readable status label
     */
    public function getStatusLabel(): string
    {
        $labels = [
            'pending' => 'Pending',
            'success' => 'Completed',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            'processing' => 'Processing',
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        $colors = [
            'pending' => 'yellow',
            'success' => 'green',
            'completed' => 'green',
            'failed' => 'red',
            'expired' => 'red',
            'cancelled' => 'gray',
            'processing' => 'blue',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, ['success', 'completed']);
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending' || $this->status === 'processing';
    }
}
