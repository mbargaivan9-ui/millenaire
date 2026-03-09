<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mark extends Model
{
    protected $guarded = [];

    protected $casts = [
        'score' => 'float',
        'term'  => 'integer',
        'sequence' => 'integer',
        'class_id' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopeForSequence($query, int $term, int $sequence)
    {
        return $query->where('term', $term)->where('sequence', $sequence);
    }

    public function scopeForClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }
}
