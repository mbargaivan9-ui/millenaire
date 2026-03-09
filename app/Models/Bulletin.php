<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Bulletin extends Model
{
    protected $guarded = [];

    protected $casts = [
        'moyenne'       => 'float',
        'rang'          => 'integer',
        'published_at'  => 'datetime',
        'submitted_at'  => 'datetime',
        'validated_at'  => 'datetime',
        'processed_at'  => 'datetime',
        'ocr_zones'     => 'array',
    ];

    // Status constants — Workflow de Validation Hiérarchique
    const STATUS_DRAFT     = 'draft';     // Enseignant saisit les notes
    const STATUS_SUBMITTED = 'submitted'; // Enseignant soumet pour validation
    const STATUS_VALIDATED = 'validated'; // Prof Principal / Censeur valide
    const STATUS_PUBLISHED = 'published'; // Admin/Super Admin publie (visible aux parents/élèves)

    // ─── Relationships ────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class, 'student_id', 'student_id')
                    ->where('class_id', $this->class_id)
                    ->where('term', $this->term ?? $this->trimester ?? 1)
                    ->where('sequence', $this->sequence ?? 1);
    }

    // Qui a validé ce bulletin (Prof Principal / Censeur)
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Qui a publié ce bulletin (Admin / Super Admin)
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeValidated($query)
    {
        return $query->where('status', self::STATUS_VALIDATED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeForClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForTerm($query, int $term)
    {
        return $query->where('term', $term);
    }

    public function scopeForSequence($query, int $sequence)
    {
        return $query->where('sequence', $sequence);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ─── Lifecycle ────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Bulletin $bulletin) {
            if (!$bulletin->verification_token) {
                $bulletin->verification_token = Str::random(40);
            }
        });

        static::updating(function (Bulletin $bulletin) {
            // Genérer les timestamps pour les changements de statut
            if ($bulletin->isDirty('status')) {
                $newStatus = $bulletin->status;
                
                if ($newStatus === self::STATUS_SUBMITTED && !$bulletin->submitted_at) {
                    $bulletin->submitted_at = now();
                }
                
                if ($newStatus === self::STATUS_VALIDATED && !$bulletin->validated_at) {
                    $bulletin->validated_at = now();
                }
                
                if ($newStatus === self::STATUS_PUBLISHED && !$bulletin->published_at) {
                    $bulletin->published_at = now();
                    // Déclencher l'événement de publication
                    event(new \App\Events\BulletinPublished($bulletin));
                }
            }
        });
    }

    // ─── Accessors ────────────────────────────────────────────────────────

    public function getVerifyUrlAttribute(): string
    {
        return route('bulletin.verify', $this->verification_token);
    }

    // Obtenir le statut lisible en français
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT     => 'En rédaction',
            self::STATUS_SUBMITTED => 'Soumis pour validation',
            self::STATUS_VALIDATED => 'Validé',
            self::STATUS_PUBLISHED => 'Publié',
            default                => $this->status,
        };
    }

    // Obtenir le statut lisible en anglais
    public function getStatusLabelEnAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT     => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted for validation',
            self::STATUS_VALIDATED => 'Validated',
            self::STATUS_PUBLISHED => 'Published',
            default                => $this->status,
        };
    }
}
