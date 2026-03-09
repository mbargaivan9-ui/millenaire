<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeeSetting extends Model
{
    use HasFactory;

    protected $table = 'fees_settings';

    protected $fillable = [
        'class_id',
        'amount',
        'currency',
        'academic_year',
        'description',
        'payment_deadline',
        'is_active',
        'installments',
        'late_fine_amount',
        'apply_late_fine',
        'discount_percentage',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'late_fine_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'apply_late_fine' => 'boolean',
        'payment_deadline' => 'date',
        'installments' => 'array',
    ];

    /**
     * Relation with class
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    /**
     * Get students in this class (through class relationship)
     */
    public function students()
    {
        return $this->classe->students();
    }

    /**
     * Scope for active fees
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for current academic year
     */
    public function scopeCurrentYear($query)
    {
        return $query->where('academic_year', config('app.current_academic_year', date('Y') . '-' . (date('Y') + 1)));
    }

    /**
     * Calculate amount with discount
     */
    public function getDiscountedAmount(): float
    {
        $discount = ($this->amount * $this->discount_percentage) / 100;
        return (float)($this->amount - $discount);
    }

    /**
     * Calculate amount with late fine
     */
    public function getAmountWithLateFine(): float
    {
        if ($this->apply_late_fine && $this->late_fine_amount > 0) {
            return (float)($this->amount + $this->late_fine_amount);
        }
        return (float)$this->amount;
    }

    /**
     * Get installment schedule
     */
    public function getInstallmentSchedule(): array
    {
        if (!$this->installments) {
            return [];
        }

        return $this->installments;
    }

    /**
     * Check if payment is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->payment_deadline) {
            return false;
        }
        return now()->isAfter($this->payment_deadline);
    }

    /**
     * Get days until payment deadline
     */
    public function daysUntilDeadline(): ?int
    {
        if (!$this->payment_deadline) {
            return null;
        }
        return now()->diffInDays($this->payment_deadline, false);
    }

    /**
     * Scope for overdue payments
     */
    public function scopeOverdue($query)
    {
        return $query->where('payment_deadline', '<', now())
                     ->where('is_active', true);
    }
}
