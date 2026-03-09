<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure teacher users have an associated Teacher record
 * 
 * This middleware verifies that users with the 'teacher' role have a corresponding
 * Teacher record in the database, preventing 403 errors.
 */
class EnsureTeacherRecordExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Only check for users with 'teacher' role
        if ($user && in_array($user->role, ['teacher', 'professeur', 'prof_principal'])) {
            // If teacher record doesn't exist, create one automatically
            if (!$user->teacher) {
                $user->teacher()->create([
                    'is_active' => true,
                    'is_prof_principal' => $user->role === 'prof_principal',
                    'years_experience' => 0,
                ]);
            }
        }

        return $next($request);
    }
}
