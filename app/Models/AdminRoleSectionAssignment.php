<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AdminRoleSectionAssignment Model
 * Links specialized roles to their assigned sections with specific permissions
 */
class AdminRoleSectionAssignment extends Model
{
    protected $table = 'admin_role_section_assignments';

    protected $fillable = [
        'admin_specialized_role_id',
        'admin_role_section_id',
        'permissions',
        'can_create',
        'can_read',
        'can_update',
        'can_delete',
        'can_export',
        'assigned_at',
        'removed_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'can_create' => 'boolean',
        'can_read' => 'boolean',
        'can_update' => 'boolean',
        'can_delete' => 'boolean',
        'can_export' => 'boolean',
        'assigned_at' => 'datetime',
        'removed_at' => 'datetime',
    ];

    /**
     * Get the specialized role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(AdminSpecializedRole::class, 'admin_specialized_role_id');
    }

    /**
     * Get the section
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(AdminRoleSection::class, 'admin_role_section_id');
    }

    /**
     * Check if role has specific permission for this section
     */
    public function hasPermission(string $permission): bool
    {
        return match ($permission) {
            'create' => $this->can_create,
            'read' => $this->can_read,
            'update' => $this->can_update,
            'delete' => $this->can_delete,
            'export' => $this->can_export,
            default => false,
        };
    }

    /**
     * Get all permissions as array
     */
    public function getPermissions(): array
    {
        return [
            'create' => $this->can_create,
            'read' => $this->can_read,
            'update' => $this->can_update,
            'delete' => $this->can_delete,
            'export' => $this->can_export,
        ];
    }
}
