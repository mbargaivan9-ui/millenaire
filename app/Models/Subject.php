<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// ─────────────────────────────────────────────────────────────────────────────
// Subject
// ─────────────────────────────────────────────────────────────────────────────
class Subject extends Model
{
    protected $guarded = [];

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'class_subject_teachers', 'subject_id', 'teacher_id')
                    ->withPivot('class_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(CourseMaterial::class);
    }
}
