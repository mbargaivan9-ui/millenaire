<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AdminSectionActivityLog Model
 * Tracks all activities in admin sections by users
 */
class AdminSectionActivityLog extends Model
{
    protected $table = 'admin_section_activity_logs';

    protected $fillable = [
        'user_id',
        'admin_role_section_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'changes',
        'ip_address',
        'logged_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'logged_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the section where action was performed
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(AdminRoleSection::class, 'admin_role_section_id');
    }

    /**
     * Log an action
     */
    public static function logAction(
        User $user,
        AdminRoleSection $section,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $description = null,
        ?array $changes = null
    ): self {
        return static::create([
            'user_id' => $user->id,
            'admin_role_section_id' => $section->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'logged_at' => now(),
        ]);
    }

    /**
     * Get activity logs for a specific user
     */
    public static function forUser(User $user)
    {
        return static::where('user_id', $user->id)
            ->orderByDesc('logged_at');
    }

    /**
     * Get activity logs for a specific section
     */
    public static function forSection(AdminRoleSection $section)
    {
        return static::where('admin_role_section_id', $section->id)
            ->orderByDesc('logged_at');
    }
}
