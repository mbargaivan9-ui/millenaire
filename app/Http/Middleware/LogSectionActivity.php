<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserSpecializedRoleAssignment;
use App\Models\AdminRoleSection;
use App\Models\AdminSectionActivityLog;

/**
 * LogSectionActivity Middleware
 * Logs all activity in admin sections for audit trail
 */
class LogSectionActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log successful admin section requests
        if (!$request->user() || $response->getStatusCode() >= 400) {
            return $response;
        }

        $routeName = $request->route()?->getName();
        if (!$routeName || !str_starts_with($routeName, 'admin.')) {
            return $response;
        }

        // Get section code
        $sectionCode = $this->getSectionCodeFromRoute($routeName);
        if (!$sectionCode) {
            return $response;
        }

        // Get section
        $section = AdminRoleSection::getByCode($sectionCode);
        if (!$section) {
            return $response;
        }

        // Determine action type
        $method = $request->getMethod();
        $action = match ($method) {
            'GET' => 'read',
            'POST', 'PUT', 'PATCH' => $this->detectCreateOrUpdate($request),
            'DELETE' => 'delete',
            default => 'other',
        };

        // Log the activity
        try {
            AdminSectionActivityLog::logAction(
                user: $request->user(),
                section: $section,
                action: $action,
                description: "{$method} {$request->path()}",
            );
        } catch (\Exception $e) {
            \Log::error('Failed to log section activity', ['error' => $e->getMessage()]);
        }

        return $response;
    }

    /**
     * Detect if it's a create or update based on route/request
     */
    private function detectCreateOrUpdate(Request $request): string
    {
        $routeName = $request->route()?->getName() ?? '';
        
        if (str_contains($routeName, '.store') || str_contains($routeName, 'create')) {
            return 'create';
        }
        
        if (str_contains($routeName, '.update')) {
            return 'update';
        }

        return 'create'; // Default to create for POST
    }

    private function getSectionCodeFromRoute(string $routeName): ?string
    {
        $parts = explode('.', $routeName);
        if (count($parts) >= 2 && $parts[0] === 'admin') {
            return $parts[1];
        }
        return null;
    }
}
