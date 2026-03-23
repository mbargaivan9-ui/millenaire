<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * AdminRoleSection Model
 * Represents sections of the admin interface that can be managed
 */
class AdminRoleSection extends Model
{
    protected $table = 'admin_role_sections';

    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'route',
        'order',
        'required_permissions',
        'is_active',
    ];

    protected $casts = [
        'required_permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get roles assigned to this section
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminSpecializedRole::class,
            'admin_role_section_assignments',
            'admin_role_section_id',
            'admin_specialized_role_id'
        )->withPivot('permissions', 'can_create', 'can_read', 'can_update', 'can_delete', 'can_export');
    }

    /**
     * Get all section assignments for this section
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(AdminRoleSectionAssignment::class);
    }

    /**
     * Get activity logs for this section
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(AdminSectionActivityLog::class);
    }

    /**
     * Get all active sections
     */
    public static function getActive()
    {
        return static::where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    /**
     * Get section by code
     */
    public static function getByCode(string $code): ?self
    {
        return static::where('code', $code)
            ->where('is_active', true)
            ->first();
    }
}
