<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BulletinValidationHierarchy extends Model
{
    use HasFactory;

    protected $table = 'bulletin_validation_hierarchy';
    protected $fillable = [
        'class_id',
        'prof_principal_id',
        'censeur_id',
        'proviseur_id',
        'validation_level',
        'requires_censeur_validation',
        'requires_proviseur_validation',
        'academic_year',
    ];

    protected function casts(): array
    {
        return [
            'requires_censeur_validation' => 'boolean',
            'requires_proviseur_validation' => 'boolean',
        ];
    }

    /**
     * The class this hierarchy belongs to
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    /**
     * The principal teacher (professeur principal)
     */
    public function profPrincipal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prof_principal_id');
    }

    /**
     * The censeur (if assigned)
     */
    public function censeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'censeur_id');
    }

    /**
     * The principal (proviseur) (if assigned)
     */
    public function proviseur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proviseur_id');
    }

    /**
     * Get next validator user for a report card
     */
    public function getNextValidator(string $currentLevel = 'prof_principal'): ?User
    {
        return match($currentLevel) {
            'prof_principal' => $this->requires_censeur_validation ? $this->censeur : $this->proviseur,
            'censeur' => $this->requires_proviseur_validation ? $this->proviseur : null,
            default => null,
        };
    }
}
