<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Supporte les rôles simples et le rôle composite prof_principal
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRole = $user->role;

        // Check if the user has one of the required roles
        // prof_principal is a special composite role that requires teacher role + is_prof_principal flag
        $hasAccess = false;
        
        foreach ($roles as $role) {
            if ($role === 'prof_principal') {
                // Check if user is a professor principal (teacher with is_prof_principal flag)
                if ($userRole === 'teacher' && $user->teacher?->is_prof_principal) {
                    $hasAccess = true;
                    break;
                }
            } elseif ($userRole === $role) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            abort(403, 'Accès non autorisé');
        }

        return $next($request);
    }
}
