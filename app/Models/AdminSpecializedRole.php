<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * AdminSpecializedRole Model
 * Represents specialized admin roles (Censeur, Intendant, Secrétaire, Surveillant Général)
 */
class AdminSpecializedRole extends Model
{
    protected $table = 'admin_specialized_roles';

    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'color',
        'hierarchy_level',
        'default_permissions',
        'is_active',
    ];

    protected $casts = [
        'default_permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get sections assigned to this role
     */
    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminRoleSection::class,
            'admin_role_section_assignments',
            'admin_specialized_role_id',
            'admin_role_section_id'
        )->withPivot('permissions', 'can_create', 'can_read', 'can_update', 'can_delete', 'can_export')
            ->withTimestamps();
    }

    /**
     * Get users assigned this role
     */
    public function users(): HasMany
    {
        return $this->hasMany(UserSpecializedRoleAssignment::class);
    }

    /**
     * Get all section assignments for this role
     */
    public function sectionAssignments(): HasMany
    {
        return $this->hasMany(AdminRoleSectionAssignment::class);
    }

    /**
     * Check if this role has access to a section
     */
    public function hasAccessToSection(string $sectionCode): bool
    {
        return $this->sections()
            ->where('admin_role_sections.code', $sectionCode)
            ->exists();
    }

    /**
     * Get permissions for a specific section
     */
    public function getSectionPermissions(string $sectionCode): array
    {
        $assignment = $this->sectionAssignments()
            ->whereHas('section', fn($q) => $q->where('code', $sectionCode))
            ->first();

        if (!$assignment) {
            return [];
        }

        return [
            'create' => $assignment->can_create,
            'read' => $assignment->can_read,
            'update' => $assignment->can_update,
            'delete' => $assignment->can_delete,
            'export' => $assignment->can_export,
        ];
    }

    /**
     * Static method to get specialized roles by code
     */
    public static function getByCode(string $code): ?self
    {
        return static::where('code', $code)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all active roles
     */
    public static function getActive()
    {
        return static::where('is_active', true)
            ->orderBy('hierarchy_level')
            ->get();
    }
}
