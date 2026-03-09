<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Usage: Route::middleware('role:teacher')
     *        Route::middleware('role:parent,teacher')  (multiple roles allowed)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role ?? null;

        if (!in_array($userRole, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès non autorisé pour ce rôle.'], 403);
            }
            // Redirect to appropriate dashboard based on actual role
            return redirect()->route($this->dashboardRoute($userRole))
                             ->with('error', 'Accès non autorisé.');
        }

        return $next($request);
    }

    private function dashboardRoute(?string $role): string
    {
        return match($role) {
            'admin'   => 'admin.dashboard',
            'teacher' => 'teacher.dashboard',
            'parent'  => 'parent.dashboard',
            'student' => 'student.dashboard',
            default   => 'home',
        };
    }
}
