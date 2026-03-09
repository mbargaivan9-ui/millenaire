<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsToMany};
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminRole extends Model
{
    use HasFactory;

    protected $table = 'admin_roles';
    protected $fillable = [
        'code',
        'name',
        'description',
        'hierarchy_level',
        'permissions',
        'can_validate_bulletins',
        'can_manage_assignments',
        'can_manage_finances',
        'can_generate_schedules',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'can_validate_bulletins' => 'boolean',
            'can_manage_assignments' => 'boolean',
            'can_manage_finances' => 'boolean',
            'can_generate_schedules' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Users assigned to this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'admin_role_users')
            ->withPivot('assigned_at', 'assigned_by', 'deactivated_at')
            ->wherePivotNull('deactivated_at');
    }

    /**
     * Check if this role has permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }
        return in_array($permission, $this->permissions);
    }

    /**
     * Get all permissions as array
     */
    public function getPermissionsArray(): array
    {
        return $this->permissions ?? [];
    }

    /**
     * Virtual accessors for views
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? '';
    }

    public function getRoleTypeAttribute(): string
    {
        return $this->code ?? '';
    }
}
