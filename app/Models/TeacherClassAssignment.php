<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherClassAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teacher_class_assignments';

    protected $fillable = [
        'teacher_id',
        'class_id',
        'assigned_by_user_id',
        'date_debut',
        'date_fin',
        'statut',
        'notes',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    /**
     * Enseignant assigné
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Classe assignée
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    /**
     * Administrateur qui a effectué l'assignation
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    /**
     * Scope: Assignations actives
     */
    public function scopeActive($query)
    {
        return $query->where('statut', 'actif')
            ->whereNull('date_fin');
    }

    /**
     * Scope: Assignations archivées
     */
    public function scopeArchived($query)
    {
        return $query->where('statut', 'archivé')
            ->orWhereNotNull('date_fin');
    }

    /**
     * Vérifier si l'assignation est actuelle
     */
    public function isActive(): bool
    {
        return $this->statut === 'actif' && $this->date_fin === null;
    }

    /**
     * Désactiver l'assignation (archiver)
     */
    public function deactivate(): bool
    {
        return $this->update([
            'statut' => 'archivé',
            'date_fin' => now(),
        ]);
    }
}
