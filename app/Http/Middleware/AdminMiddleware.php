<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès refusé — Réservé aux administrateurs.'], 403);
            }
            abort(403, 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}
