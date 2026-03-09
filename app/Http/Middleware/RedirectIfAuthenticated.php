<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$guards): mixed
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            try {
                if (Auth::guard($guard)->check()) {
                    $user = Auth::guard($guard)->user();
                    
                    // Redirect to appropriate dashboard based on user role
                    if ($user->isAdmin()) {
                        return redirect(route('admin.dashboard'));
                    } elseif ($user->isTeacher()) {
                        return redirect(route('teacher.dashboard'));
                    } elseif ($user->isParent()) {
                        return redirect(route('parent.dashboard'));
                    } elseif ($user->isStudent()) {
                        return redirect(route('student.dashboard'));
                    }
                    
                    // Fallback to home if role is unknown
                    return redirect(route('home'));
                }
            } catch (\Exception $e) {
                // If database is unavailable, allow the request to proceed
                // This prevents crashes when MySQL is not running
                continue;
            }
        }

        return $next($request);
    }
}
