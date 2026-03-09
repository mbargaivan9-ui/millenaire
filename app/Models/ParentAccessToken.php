<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * ParentAccessToken
 * 
 * Generates unique access tokens for parents to link their accounts to student records
 * and view their children's grades/bulletins.
 * 
 * Token generation: secure 32-character string
 * One-time use: tokens are marked as used after successful account creation
 */
class ParentAccessToken extends Model
{
    protected $fillable = [
        'teacher_id',
        'student_id',
        'user_id',
        'token',
        'email',
        'phone',
        'relationship',
        'expires_at',
        'used_at',
        'is_revoked',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_revoked' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_revoked', false)
                     ->whereNull('used_at')
                     ->where(function($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    public function scopeUsed($query)
    {
        return $query->whereNotNull('used_at');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now())
                     ->whereNull('used_at')
                     ->where('is_revoked', false);
    }

    public function scopeRevoked($query)
    {
        return $query->where('is_revoked', true);
    }

    // ─── Methods ───────────────────────────────────────────────────────

    /**
     * Generate a new unique access token
     * 
     * @return string 32-character secure token
     */
    public static function generateToken(): string
    {
        return Str::random(32);
    }

    /**
     * Check if token is still valid (not revoked, not used, not expired)
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        if ($this->is_revoked) {
            return false;
        }

        if ($this->used_at !== null) {
            return false; // Already used
        }

        if ($this->expires_at && $this->expires_at < now()) {
            return false; // Expired
        }

        return true;
    }

    /**
     * Mark token as used by a parent/user
     * 
     * @param int $userId User ID who used the token
     * @return void
     */
    public function markAsUsed(int $userId): void
    {
        $this->update([
            'user_id' => $userId,
            'used_at' => now(),
        ]);
    }

    /**
     * Revoke a token (parent principal can revoke access)
     * 
     * @return void
     */
    public function revoke(): void
    {
        $this->update(['is_revoked' => true]);
    }

    /**
     * Get the parent student's name (for display)
     * 
     * @return string
     */
    public function getStudentNameAttribute(): string
    {
        return $this->student?->user?->display_name ?? 'Unknown Student';
    }

    /**
     * Get relationship label
     * 
     * @return string
     */
    public function getRelationshipLabelAttribute(): string
    {
        return match($this->relationship) {
            'parent' => 'Parent',
            'guardian' => 'Guardian',
            'tutor' => 'Tutor',
            'other' => 'Other',
            default => ucfirst($this->relationship),
        };
    }
}
