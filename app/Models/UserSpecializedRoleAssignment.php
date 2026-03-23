<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserSpecializedRoleAssignment Model
 * Links users to their specialized roles with section assignments
 */
class UserSpecializedRoleAssignment extends Model
{
    protected $table = 'user_specialized_role_assignments';

    protected $fillable = [
        'user_id',
        'admin_specialized_role_id',
        'assigned_sections',
        'notes',
        'assigned_at',
        'assigned_by_id',
        'deactivated_at',
    ];

    protected $casts = [
        'assigned_sections' => 'array',
        'assigned_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the specialized role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(AdminSpecializedRole::class, 'admin_specialized_role_id');
    }

    /**
     * Get assigned by user
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }

    /**
     * Check if assignment is active
     */
    public function isActive(): bool
    {
        return $this->deactivated_at === null;
    }

    /**
     * Deactivate the assignment
     */
    public function deactivate(): void
    {
        $this->update(['deactivated_at' => now()]);
    }

    /**
     * Check if user has access to a section
     */
    public function hasAccessToSection(string $sectionCode): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        // If no specific sections assigned, grant access to all role sections
        if (empty($this->assigned_sections)) {
            return $this->role->hasAccessToSection($sectionCode);
        }

        // Check if section is in assigned sections
        $section = AdminRoleSection::getByCode($sectionCode);
        return $section && in_array($section->id, $this->assigned_sections);
    }

    /**
     * Get accessible sections for this assignment
     */
    public function getAccessibleSections()
    {
        if (!$this->isActive()) {
            return collect();
        }

        if (empty($this->assigned_sections)) {
            return $this->role->sections()->active()->get();
        }

        return AdminRoleSection::whereIn('id', $this->assigned_sections)
            ->where('is_active', true)
            ->get();
    }
}
