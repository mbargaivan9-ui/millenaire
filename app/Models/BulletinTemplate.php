<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BulletinTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'classroom_id',
        'created_by',
        'name',
        'academic_year',
        'trimester',
        'structure_json',
        'html_template',
        'original_image_path',
        'ocr_confidence_score',
        'is_validated',
        'validated_at',
        'version',
        // Legacy fields kept for backward compatibility
        'classe_id',
        'template_image_path',
        'image_width',
        'image_height',
        'field_zones',
        'metadata',
        'is_active',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'structure_json' => 'array',
            'field_zones' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
            'is_validated' => 'boolean',
            'validated_at' => 'datetime',
            'ocr_confidence_score' => 'float',
            'trimester' => 'integer',
            'version' => 'integer',
        ];
    }

    /**
     * Relation: School (establishment)
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(EstablishmentSetting::class, 'school_id');
    }

    /**
     * Relation avec Classe (classroom)
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'classroom_id');
    }

    /**
     * Alias for classe
     */
    public function classroom(): BelongsTo
    {
        return $this->classe();
    }

    /**
     * Relation with creator user (professor principal)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation: Student bulletins generated from this template
     */
    public function studentBulletins(): HasMany
    {
        return $this->hasMany(StudentBulletin::class, 'template_id');
    }

    /**
     * Relation: Teacher-subject assignments for this template
     */
    public function subjectAssignments(): HasMany
    {
        return $this->hasMany(TemplateSubjectAssignment::class, 'template_id');
    }

    // Scopes
    public function scopeValidated($query)
    {
        return $query->where('is_validated', true);
    }

    public function scopeByClassroom($query, $classroomId)
    {
        return $query->where('classroom_id', $classroomId);
    }

    public function scopeByPeriod($query, $academicYear, $trimester)
    {
        return $query->where('academic_year', $academicYear)
                     ->where('trimester', $trimester);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Relation avec updater user
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get a specific field zone by subject ID
     */
    public function getFieldZoneBySubjectId(int $subjectId): array|null
    {
        $zones = $this->field_zones ?? [];

        foreach ($zones as $zone) {
            if ($zone['subject_id'] === $subjectId) {
                return $zone;
            }
        }

        return null;
    }

    /**
     * Add or update a field zone
     */
    public function updateFieldZone(int $subjectId, array $zoneData): void
    {
        $zones = $this->field_zones ?? [];

        // Find and update or create
        $found = false;
        foreach ($zones as &$zone) {
            if ($zone['subject_id'] === $subjectId) {
                $zone = array_merge($zone, $zoneData);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $zones[] = array_merge(['subject_id' => $subjectId], $zoneData);
        }

        $this->field_zones = $zones;
        $this->save();
    }

    /**
     * Remove a field zone by subject ID
     */
    public function removeFieldZone(int $subjectId): void
    {
        $zones = $this->field_zones ?? [];
        $zones = array_filter($zones, fn ($zone) => $zone['subject_id'] !== $subjectId);
        $this->field_zones = array_values($zones); // Re-index array
        $this->save();
    }
}
