<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * StudentBulletin
 * 
 * Individual student bulletin, generated from template
 * Contains grades, calculations, and export data
 */
class StudentBulletin extends Model
{
    use SoftDeletes;

    protected $table = 'student_bulletins';

    protected $fillable = [
        'template_id',
        'student_id',
        'classroom_id',
        'academic_year',
        'trimester',
        'general_average',
        'class_rank',
        'appreciation',
        'total_absences',
        'status',
        'exported_at',
        'pdf_path',
        'locked_at',
        'locked_by',
    ];

    protected $casts = [
        'general_average' => 'float',
        'class_rank' => 'integer',
        'total_absences' => 'integer',
        'exported_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    // Relations
    public function template(): BelongsTo
    {
        return $this->belongsTo(BulletinTemplate::class, 'template_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'classroom_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(BulletinGrade::class, 'bulletin_id');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    // Scopes
    public function scopeByClassroom($query, $classroomId)
    {
        return $query->where('classroom_id', $classroomId);
    }

    public function scopeByPeriod($query, $academicYear, $trimester)
    {
        return $query->where('academic_year', $academicYear)
                     ->where('trimester', $trimester);
    }

    public function scopeComplete($query)
    {
        return $query->where('status', 'complete');
    }

    public function scopeExported($query)
    {
        return $query->where('status', 'exported')
                     ->whereNotNull('exported_at');
    }

    // Helpers
    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }
}
