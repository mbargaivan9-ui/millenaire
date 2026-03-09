<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RateLimitChat
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Rate limiting pour les messages : max 10 messages par minute par utilisateur
        if ($request->isMethod('post') && $request->routeIs('api.chat.messages.store')) {
            $key = 'chat.messages.' . Auth::id();
            $limit = 10; // messages par minute
            $decaySeconds = 60;

            if (app(RateLimiter::class)->tooManyAttempts($key, $limit)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous envoyez trop de messages. Veuillez attendre quelques secondes.',
                ], 429);
            }

            app(RateLimiter::class)->hit($key, $decaySeconds);
        }

        return $next($request);
    }
}
