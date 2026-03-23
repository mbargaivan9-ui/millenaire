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
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
        'role',                      // admin | teacher | parent | student
        'profile_photo',
        'avatar_url',
        'preferred_language',        // fr | en
        'is_online',
        'last_login',
        'is_active',
        'must_change_password',      // Forcer changement MDP à première connexion
        'force_password_change',     // Alias pour must_change_password
        'password_changed_at',       // Date dernière modification MDP
        'phone',
        'phoneNumber',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'country',
        'bio',
        'two_fa_enabled',
        'two_factor_enabled',        // Alias
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $appends = ['children', 'avatar_url', 'display_name', 'role_label', 'initials', 'profile_score'];

    protected $casts = [
        'email_verified_at'     => 'datetime',
        'last_login'            => 'datetime',
        'password_changed_at'   => 'datetime',
        'is_online'             => 'boolean',
        'is_active'             => 'boolean',
        'must_change_password'  => 'boolean',
        'force_password_change' => 'boolean',
        'two_fa_enabled'        => 'boolean',
        'two_factor_enabled'    => 'boolean',
        'password'              => 'hashed',
        'date_of_birth'         => 'date',
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
     * Get specialized role assignments for this user
     */
    public function specializedRoles()
    {
        return $this->hasMany(UserSpecializedRoleAssignment::class);
    }

    /**
     * Get activity logs for this user in admin sections
     */
    public function adminSectionLogs()
    {
        return $this->hasMany(AdminSectionActivityLog::class);
    }

    /**
     * Accessor pour les enfants du parent via Guardian
     */
    public function getChildrenAttribute()
    {
        if (!$this->guardian) {
            return collect();
        }
        return $this->guardian->students;
    }

    /**
     * Accessor pour l'URL de l'avatar
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->profile_photo && file_exists(storage_path('app/public/' . $this->profile_photo))) {
            return asset('storage/' . $this->profile_photo);
        }
        // Gravatar fallback
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=200";
    }

    /**
     * Accessor pour le nom affiché
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }
        return $this->name;
    }

    /**
     * Accessor pour le libellé du rôle
     */
    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'admin'         => 'Administrateur',
            'censeur'       => 'Censeur',
            'intendant'     => 'Intendant',
            'secretaire'    => 'Secrétaire',
            'surveillant'   => 'Surveillant',
            'professeur'    => 'Professeur',
            'prof_principal'=> 'Prof. Principal',
            'teacher'       => 'Professeur',
            'prof'          => 'Professeur',
            'parent'        => 'Parent',
            'student'       => 'Élève',
            default         => ucfirst($this->role),
        };
    }

    /**
     * Accessor pour les initiales
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->display_name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }

    /**
     * Accessor pour le score du profil
     */
    public function getProfileScoreAttribute(): int
    {
        $score = 0;
        if ($this->name) $score += 15;
        if ($this->email) $score += 15;
        if ($this->phoneNumber || $this->phone) $score += 10;
        if ($this->date_of_birth) $score += 10;
        if ($this->address) $score += 10;
        if ($this->city) $score += 10;
        if ($this->country) $score += 10;
        if ($this->profile_photo) $score += 20;
        return $score;
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

    /**
     * Vérifie si l'utilisateur est professeur principal
     * Force le rechargement des données depuis la BD pour garantir la fraîcheur des données
     * Important: Après une assignation depuis l'admin, cette méthode recharge automatiquement
     */
    public function isProfPrincipal(): bool
    {
        if (!$this->isTeacher()) {
            return false;
        }

        // Recharger la relation teacher depuis la BD pour éviter les data stale
        // Si les données en mémoire ne correspondent pas à la BD
        $teacher = $this->teacher ?? $this->fresh()?->teacher;
        
        return $teacher?->is_prof_principal ?? false;
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
     * Obtains the dashboard route based on user role
     */
    public function getDashboardRoute(): string
    {
        return match($this->role) {
            'admin', 'censeur', 'intendant', 'secretaire', 'surveillant' => route('admin.dashboard'),
            'professeur', 'prof_principal', 'teacher', 'prof' => route('teacher.dashboard'),
            'parent' => route('parent.dashboard'),
            'student' => route('student.dashboard'),
            default => route('home'),
        };
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
