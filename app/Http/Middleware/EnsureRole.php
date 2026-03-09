<?php

/**
 * EnsureRole Middleware — Contrôle d'accès basé sur les rôles
 *
 * Usage dans les routes: ->middleware('role:teacher')
 * Multiple roles:        ->middleware('role:admin,teacher')
 * Prof Principal:        ->middleware('role:prof_principal,admin')
 * 
 * Note: prof_principal est un rôle composite (teacher + is_prof_principal flag)
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $userRole = $user->role;

        // Admin has access to everything
        if ($userRole === 'admin') {
            return $next($request);
        }

        // Check if the user has one of the required roles
        // prof_principal is a special role that requires teacher role + is_prof_principal flag
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
            abort(403, app()->getLocale() === 'fr'
                ? 'Accès non autorisé. Vous n\'avez pas les droits nécessaires.'
                : 'Access denied. You do not have the required permissions.'
            );
        }

        return $next($request);
    }
}

