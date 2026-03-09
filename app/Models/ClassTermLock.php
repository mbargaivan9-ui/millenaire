<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ClassTermLock
 *
 * Verrouillage d'une classe pour un trimestre donné.
 * Une fois verrouillé par le Professeur Principal, aucun enseignant
 * ne peut modifier les notes sauf le Prof Principal lui-même.
 */
class ClassTermLock extends Model
{
    protected $table = 'class_term_locks';

    protected $fillable = [
        'class_id',
        'term',
        'academic_year',
        'is_locked',
        'locked_by',
        'locked_at',
        'lock_reason',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}
