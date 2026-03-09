<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Guardian Model — Tuteur / Parent
 *
 * Un tuteur peut avoir plusieurs enfants (élèves).
 * Relation many-to-many avec Student via guardian_student pivot.
 */
class Guardian extends Model
{
    protected $fillable = [
        'user_id',
        'relationship', // father | mother | uncle | guardian | other
        'profession',
        'address',
        'emergency_phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Enfants (élèves) liés à ce tuteur.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    /**
     * Rendez-vous pris par ce parent.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'parent_id', 'user_id');
    }

    /**
     * Paiements effectués.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'payer_id', 'user_id');
    }
}
