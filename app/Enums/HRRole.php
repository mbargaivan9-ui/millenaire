<?php

namespace App\Enums;

/**
 * HR Administrative Roles Enum
 * Defines the roles for HR administrative governance
 */
enum HRRole: string
{
    /**
     * Censeur - Deputy Headmaster/Academic Supervisor
     * Responsible for academic affairs and discipline
     */
    case CENSEUR = 'censeur';

    /**
     * Intendant - Finance Manager
     * Responsible for finance, supplies, and logistics
     */
    case INTENDANT = 'intendant';

    /**
     * Secrétaire - Secretary/Administrative Staff
     * Responsible for administrative tasks and documentation
     */
    case SECRETAIRE = 'secretaire';

    /**
     * Surveillant Général - General Supervisor
     * Responsible for overall supervision and discipline enforcement
     */
    case SURVEILLANT_GENERAL = 'surveillant_general';

    /**
     * Get the label/display name for the role
     */
    public function label(): string
    {
        return match ($this) {
            self::CENSEUR => 'Censeur (Deputy Headmaster)',
            self::INTENDANT => 'Intendant (Finance Manager)',
            self::SECRETAIRE => 'Secrétaire (Secretary)',
            self::SURVEILLANT_GENERAL => 'Surveillant Général (General Supervisor)',
        };
    }

    /**
     * Get the description for the role
     */
    public function description(): string
    {
        return match ($this) {
            self::CENSEUR => 'Academic Supervisor and Discipline Manager',
            self::INTENDANT => 'Finance and Logistics Manager',
            self::SECRETAIRE => 'Administrative Staff',
            self::SURVEILLANT_GENERAL => 'General Supervision and Discipline Enforcement',
        };
    }

    /**
     * Check if role can manage teacher assignments
     */
    public function canManageAssignments(): bool
    {
        return $this === self::CENSEUR || $this === self::INTENDANT;
    }

    /**
     * Check if role can view reports
     */
    public function canViewReports(): bool
    {
        return in_array($this, [
            self::CENSEUR,
            self::INTENDANT,
            self::SURVEILLANT_GENERAL,
        ]);
    }

    /**
     * Get all HR roles
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Get all role values
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
