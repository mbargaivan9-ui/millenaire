<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * MobilePayment — Modèle de paiement Mobile Money
 *
 * @property int         $id
 * @property string      $transaction_ref
 * @property string      $operator          orange|mtn
 * @property string      $phone
 * @property int         $amount
 * @property int         $fees
 * @property int         $total_amount
 * @property string      $status
 * @property int|null    $student_id
 * @property int         $payer_id
 * @property string|null $tranche
 * @property string|null $receipt_number
 */
class MobilePayment extends Model
{
    protected $table = 'mobile_payments';

    protected $guarded = [];

    protected $casts = [
        'amount'           => 'integer',
        'fees'             => 'integer',
        'total_amount'     => 'integer',
        'is_sandbox'       => 'boolean',
        'initiated_at'     => 'datetime',
        'completed_at'     => 'datetime',
        'expires_at'       => 'datetime',
        'api_request_log'  => 'array',
        'api_response_log' => 'array',
        'webhook_payload'  => 'array',
    ];

    // ─── Statuts ──────────────────────────────────────────────────────────────
    const STATUS_PENDING    = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESS    = 'success';
    const STATUS_FAILED     = 'failed';
    const STATUS_EXPIRED    = 'expired';
    const STATUS_CANCELLED  = 'cancelled';

    // ─── Opérateurs ───────────────────────────────────────────────────────────
    const OP_ORANGE = 'orange';
    const OP_MTN    = 'mtn';

    // ─── Taux de frais de service (1.5%) ─────────────────────────────────────
    const SERVICE_FEE_RATE = 0.015;

    // ─── Relations ────────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)      { return $query->where('status', self::STATUS_PENDING); }
    public function scopeSuccess($query)      { return $query->where('status', self::STATUS_SUCCESS); }
    public function scopeFailed($query)       { return $query->where('status', self::STATUS_FAILED); }
    public function scopeOrange($query)       { return $query->where('operator', self::OP_ORANGE); }
    public function scopeMtn($query)          { return $query->where('operator', self::OP_MTN); }
    public function scopeToday($query)        { return $query->whereDate('created_at', today()); }
    public function scopeThisMonth($query)    { return $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year); }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getOperatorLabelAttribute(): string
    {
        return $this->operator === self::OP_ORANGE ? 'Orange Money' : 'MTN Mobile Money';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SUCCESS    => '✅ Validé',
            self::STATUS_PENDING    => '⏳ En attente',
            self::STATUS_PROCESSING => '🔄 En cours',
            self::STATUS_FAILED     => '❌ Échoué',
            self::STATUS_EXPIRED    => '⌛ Expiré',
            self::STATUS_CANCELLED  => '🚫 Annulé',
            default                 => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SUCCESS    => 'success',
            self::STATUS_PENDING,
            self::STATUS_PROCESSING => 'warning',
            self::STATUS_FAILED,
            self::STATUS_EXPIRED    => 'danger',
            self::STATUS_CANCELLED  => 'secondary',
            default                 => 'secondary',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', ' ') . ' FCFA';
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_amount, 0, ',', ' ') . ' FCFA';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && now()->isAfter($this->expires_at) && $this->status === self::STATUS_PENDING;
    }

    // ─── Méthodes statiques ───────────────────────────────────────────────────

    public static function generateRef(): string
    {
        return 'MC-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(6));
    }

    public static function generateReceiptNumber(): string
    {
        return 'REC-' . date('Y') . '-' . str_pad(self::count() + 1, 6, '0', STR_PAD_LEFT);
    }

    public static function calculateFees(int $amount): int
    {
        return (int) round($amount * self::SERVICE_FEE_RATE);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isSuccess(): bool   { return $this->status === self::STATUS_SUCCESS; }
    public function isPending(): bool   { return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]); }
    public function isFailed(): bool    { return in_array($this->status, [self::STATUS_FAILED, self::STATUS_EXPIRED, self::STATUS_CANCELLED]); }
    public function isOrange(): bool    { return $this->operator === self::OP_ORANGE; }
    public function isMtn(): bool       { return $this->operator === self::OP_MTN; }
}
