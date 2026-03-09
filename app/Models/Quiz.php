<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_published'   => 'boolean',
        'available_from' => 'datetime',
        'available_until'=> 'datetime',
        'pass_score'     => 'integer',
        'time_limit_minutes' => 'integer',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(QuizSubmission::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeAvailable($query)
    {
        return $query->published()
                     ->where(fn($q) => $q->whereNull('available_from')->orWhere('available_from', '<=', now()))
                     ->where(fn($q) => $q->whereNull('available_until')->orWhere('available_until', '>=', now()));
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->questions->sum('points');
    }
}
