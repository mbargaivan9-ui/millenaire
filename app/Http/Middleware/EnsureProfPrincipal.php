<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureProfPrincipal — Middleware pour vérifier accès prof principal
 * 
 * Vérifie que l'utilisateur est:
 * 1. Un prof_principal (role professeur + is_prof_principal flag)
 * 2. OU un admin
 */
class EnsureProfPrincipal
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Admin a accès total
        if ($user->is_admin) {
            return $next($request);
        }

        // Vérifier si prof_principal
        if ($user->hasRole('professeur') && $user->is_prof_principal) {
            return $next($request);
        }

        return response()->json(['error' => 'Forbidden: Prof Principal access only'], 403);
    }
}
