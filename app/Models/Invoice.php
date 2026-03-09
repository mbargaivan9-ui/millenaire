<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Invoice Model — Facture / Note de Frais
 * 
 * Représente une facture scolaire à payer par l'élève/tuteur.
 */
class Invoice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount_due'   => 'decimal:2',
        'amount_paid'  => 'decimal:2',
        'due_date'     => 'date',
        'paid_at'      => 'datetime',
        'is_paid'      => 'boolean',
    ];

    // ─── Relationships ─────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ─── Scopes ────────────────────────────────────────────────────────────

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────

    public function getBalanceAttribute(): float
    {
        return (float) ($this->amount_due - $this->amount_paid);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_paid) return 'Payée';
        if ($this->balance <= 0) return 'Soldée';
        if ($this->due_date && $this->due_date->isPast()) return 'En retard';
        return 'En attente';
    }
}
