<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check if user must change their password on first login
 * 
 * If a user has must_change_password = true, they are redirected to the
 * password change form unless they are already on the change password page.
 */
class CheckMustChangePassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Check if user is authenticated and must change password
        if ($user && $user->must_change_password) {
            // Allow access to password change routes only
            $allowedRoutes = ['auth.change-password', 'auth.update-password', 'logout'];
            
            if (!in_array($request->route()?->getName(), $allowedRoutes)) {
                return redirect()->route('auth.change-password')
                    ->with('info', app()->getLocale() === 'fr' 
                        ? 'Veuillez modifier votre mot de passe avant de continuer.'
                        : 'Please change your password before continuing.'
                    );
            }
        }

        return $next($request);
    }
}
