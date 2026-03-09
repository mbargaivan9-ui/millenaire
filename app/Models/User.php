<?php

/**
 * User Model — Modèle Utilisateur Principal
 *
 * Rôles: admin, teacher, parent, student
 * Relations: teacher, student, guardian + notifications, activity
 *
 * @package App\Models
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\CausesActivity;

class User extends Authenticatable
{
    use Notifiable, CausesActivity;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',                  // admin | teacher | parent | student
        'profile_photo',
        'preferred_language',    // fr | en
        'is_online',
        'last_login',
        'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login'        => 'datetime',
        'is_online'         => 'boolean',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function guardian(): HasOne
    {
        return $this->hasOne(Guardian::class);
    }

    /**
     * Conversations où l'utilisateur est participant.
     */
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('last_read_at', 'is_muted', 'is_archived')
            ->withTimestamps();
    }

    /**
     * Messages envoyés par l'utilisateur.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Reçus de lecture (messages lus par cet utilisateur).
     */
    public function readReceipts(): HasMany
    {
        return $this->hasMany(MessageReadReceipt::class);
    }

    /**
     * Notifications de l'utilisateur.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Push subscriptions pour les notifications web.
     */
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isProfPrincipal(): bool
    {
        return $this->isTeacher() && $this->teacher?->is_prof_principal;
    }

    /**
     * Enregistre la dernière connexion de l'utilisateur
     */
    public function logLogin(): void
    {
        $this->update([
            'last_login' => now(),
            'is_online' => true,
        ]);
    }

    /**
     * Marquer l'utilisateur comme hors ligne.
     */
    public function markOffline(): void
    {
        $this->update(['is_online' => false]);
    }

    /**
     * Vérifie si l'utilisateur est toujours en ligne.
     */
    public function isOnline(): bool
    {
        return $this->is_online === true;
    }

    /**
     * Obtenir le timestamp de dernière connexion formaté.
     */
    public function getLastSeenAttribute(): string
    {
        if (!$this->last_login) {
            return 'Jamais';
        }

        return $this->last_login->diffForHumans();
    }

    /**
     * Avatar URL or generated initials avatar.
     */
    public function getAvatarAttribute(): string
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }
        $name = urlencode($this->display_name ?? $this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=0d9488&color=fff&size=128";
    }

    /**
     * Boot: set display_name automatically.
     * Note: display_name column not created in migrations, skipping
     */
    protected static function booted(): void
    {
        // Disabled: display_name column does not exist in database
        // static::creating(function (User $user) {
        //     if (!$user->display_name) {
        //         $user->display_name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? $user->name));
        //     }
        // });
    }
}
