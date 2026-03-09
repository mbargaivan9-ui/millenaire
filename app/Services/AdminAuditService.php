<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use App\Models\User;

/**
 * AdminAuditService
 * Provides audit logging functionality for administrative actions
 * Follows Single Responsibility Principle
 */
class AdminAuditService
{
    /**
     * Log an administrative action
     */
    public static function log(
        User $user,
        string $action,
        string $entityType,
        int $entityId,
        ?array $changes = null,
        ?string $reason = null
    ): AdminAuditLog {
        return AdminAuditLog::logAction(
            $user,
            $action,
            $entityType,
            $entityId,
            $changes,
            $reason,
            request()->ip()
        );
    }

    /**
     * Get audit logs for an entity
     */
    public static function getEntityLogs(string $type, int $id)
    {
        return AdminAuditLog::forEntity($type, $id);
    }

    /**
     * Get recent admin activity
     */
    public static function getRecentActivity(int $limit = 50)
    {
        return AdminAuditLog::orderByDesc('created_at')
            ->limit($limit)
            ->with('user')
            ->get();
    }

    /**
     * Get activity by user
     */
    public static function getUserActivity(User $user, int $limit = 50)
    {
        return AdminAuditLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
