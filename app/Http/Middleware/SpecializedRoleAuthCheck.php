<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserSpecializedRoleAssignment;
use App\Models\AdminRoleSection;

/**
 * SpecializedRoleAuthCheck Middleware
 * Verifies that the user has access to the requested admin section
 */
class SpecializedRoleAuthCheck
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Skip check for main admins
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Check if route requires specialized role access
        $routeName = $request->route()?->getName();
        if (!$routeName || !str_starts_with($routeName, 'admin.')) {
            return $next($request);
        }

        // Get the section code from route
        $sectionCode = $this->getSectionCodeFromRoute($routeName);
        if (!$sectionCode) {
            return $next($request);
        }

        // Check if user has access to this section
        if ($this->userHasAccessToSection($user, $sectionCode)) {
            return $next($request);
        }

        // Log unauthorized access attempt
        \Log::warning('Unauthorized section access', [
            'user_id' => $user->id,
            'route' => $routeName,
            'section' => $sectionCode,
            'ip' => $request->ip(),
        ]);

        return response()->view('errors.403', [], 403);
    }

    /**
     * Check if user has access to section
     */
    private function userHasAccessToSection($user, string $sectionCode): bool
    {
        $section = AdminRoleSection::getByCode($sectionCode);
        if (!$section) {
            return true; // Section not found, allow access
        }

        // Check specialized role assignments
        $assignment = UserSpecializedRoleAssignment::where('user_id', $user->id)
            ->where('deactivated_at', null)
            ->with('role')
            ->first();

        if (!$assignment) {
            return false; // No specialized role
        }

        // Check if role has access to this section
        return $assignment->hasAccessToSection($sectionCode);
    }

    /**
     * Extract section code from route name
     */
    private function getSectionCodeFromRoute(string $routeName): ?string
    {
        // admin.students.* => students
        // admin.classes.* => classes
        // admin.finance.* => finance
        // etc.

        $parts = explode('.', $routeName);
        if (count($parts) >= 2 && $parts[0] === 'admin') {
            return $parts[1];
        }

        return null;
    }
}
