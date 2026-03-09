<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Classes extends Model
{
    protected $table = 'classes';
    protected $fillable = [
        'name',
        'level',
        'section',
        'capacity',
        'prof_principal_id',
        'head_teacher_id',
        'description',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relations
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'class_id');
    }

    public function profPrincipal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prof_principal_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject_teacher', 'class_id', 'subject_id')
                    ->withPivot('teacher_id')
                    ->withTimestamps();
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'class_subject_teacher', 'class_id', 'teacher_id')
                    ->withPivot('subject_id')
                    ->withTimestamps();
    }

    public function headTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'head_teacher_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'class_id');
    }

    public function bulletins(): HasMany
    {
        return $this->hasMany(Bulletin::class, 'class_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }
}
