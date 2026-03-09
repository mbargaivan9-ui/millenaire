<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizSubmission extends Model
{
    protected $guarded = [];

    protected $casts = [
        'answers'      => 'array',
        'score'        => 'integer',
        'total_points' => 'integer',
        'is_graded'    => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function getPercentageAttribute(): float
    {
        if (!$this->total_points) return 0;
        return round(($this->score / $this->total_points) * 100, 1);
    }

    public function getPassedAttribute(): bool
    {
        return $this->percentage >= ($this->quiz?->pass_score ?? 50);
    }
}
