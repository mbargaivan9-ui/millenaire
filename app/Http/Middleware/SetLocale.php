<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     * Priority: 1) URL param  2) User preference  3) Session  4) Default (fr)
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $locale = null;

        // 1. URL query param: ?lang=en
        if ($request->has('lang')) {
            $locale = $request->query('lang');
        }
        // 2. Authenticated user preference
        elseif (auth()->check() && auth()->user()->preferred_language) {
            $locale = auth()->user()->preferred_language;
        }
        // 3. Session
        elseif (session()->has('locale')) {
            $locale = session('locale');
        }

        // Validate and apply
        if ($locale && in_array($locale, ['fr', 'en'])) {
            App::setLocale($locale);
            session(['locale' => $locale]);
        } else {
            App::setLocale(config('app.locale', 'fr'));
        }

        return $next($request);
    }
}
