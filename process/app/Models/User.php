<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{HasOne, HasMany, BelongsToMany};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'first_name', 'last_name', 'email', 'phoneNumber', 'role',
        'gender', 'date_of_birth', 'address', 'city', 'country',
        'profile_photo', 'bio', 'password', 'is_active', 'last_login',
        'is_main_teacher', 'class_id', 'theme', 'email_notifications',
        'push_notifications', 'in_app_notifications',
        'notif_security', 'notif_grades', 'notif_payments',
        'notif_announcements', 'notif_messages', 'notif_absences',
        'two_factor_enabled', 'force_password_change',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at'    => 'datetime',
        'is_active'            => 'boolean',
        'is_main_teacher'      => 'boolean',
        'last_login'           => 'datetime',
        'date_of_birth'        => 'date',
        'password'             => 'hashed',
        'email_notifications'  => 'boolean',
        'push_notifications'   => 'boolean',
        'in_app_notifications' => 'boolean',
        'notif_security'       => 'boolean',
        'notif_grades'         => 'boolean',
        'notif_payments'       => 'boolean',
        'notif_announcements'  => 'boolean',
        'notif_messages'       => 'boolean',
        'notif_absences'       => 'boolean',
        'two_factor_enabled'   => 'boolean',
        'force_password_change'=> 'boolean',
    ];

    protected $appends = ['avatar_url', 'display_name', 'role_label'];

    // ─── Accessors ─────────────────────────────────────────

    public function getAvatarUrlAttribute(): string
    {
        if ($this->profile_photo && file_exists(storage_path('app/public/' . $this->profile_photo))) {
            return asset('storage/' . $this->profile_photo);
        }
        // Gravatar fallback
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=200";
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }
        return $this->name;
    }

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
            'parent'        => 'Parent',
            'student'       => 'Élève',
            default         => ucfirst($this->role),
        };
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->display_name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }

    public function getProfileScoreAttribute(): int
    {
        $score = 0;
        if ($this->name) $score += 15;
        if ($this->email) $score += 15;
        if ($this->phoneNumber) $score += 10;
        if ($this->date_of_birth) $score += 10;
        if ($this->address) $score += 10;
        if ($this->city) $score += 10;
        if ($this->country) $score += 10;
        if ($this->profile_photo) $score += 20;
        return $score;
    }

    // ─── Relations ─────────────────────────────────────────

    public function mainTeacherClass()
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function assignedClasses(): HasMany
    {
        return $this->hasMany(AssignmentHistory::class, 'new_teacher_id');
    }

    public function previousAssignments(): HasMany
    {
        return $this->hasMany(AssignmentHistory::class, 'old_teacher_id');
    }

    public function assignmentsMade(): HasMany
    {
        return $this->hasMany(AssignmentHistory::class, 'assigned_by');
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function guardian(): HasOne
    {
        return $this->hasOne(Guardian::class);
    }

    public function parent()
    {
        return $this->guardian();
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['unread_count', 'last_read_at', 'is_muted', 'is_archived'])
            ->withTimestamps();
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'student_id');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // ─── Notification helpers ──────────────────────────────

    public function getUnreadNotificationsCount(): int
    {
        return $this->appNotifications()->where('is_read', false)->count();
    }

    public function getUnreadMessagesCount(): int
    {
        return $this->conversations()
            ->wherePivot('unread_count', '>', 0)
            ->count();
    }

    // ─── Role helpers ──────────────────────────────────────

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'censeur', 'intendant', 'secretaire', 'surveillant']);
    }

    public function isTeacher(): bool
    {
        return in_array($this->role, ['professeur', 'prof_principal']);
    }

    public function isStudent(): bool { return $this->role === 'student'; }
    public function isParent(): bool  { return $this->role === 'parent'; }

    public function logLogin(): void
    {
        $this->update(['last_login' => now()]);
    }

    public function isMainTeacher(): bool
    {
        return $this->is_main_teacher === true && $this->class_id !== null;
    }

    public function canManageAssignments(): bool
    {
        return in_array($this->role, ['censeur', 'intendant', 'admin']);
    }
}
