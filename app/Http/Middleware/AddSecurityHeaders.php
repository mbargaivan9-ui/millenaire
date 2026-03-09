<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Security Headers Middleware
 * 
 * Add security headers to all responses in production
 */
class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (config('app.env') === 'production') {
            // HSTS - Strict Transport Security
            if (config('security.headers.hsts.enabled')) {
                $hsts = 'max-age=' . config('security.headers.hsts.max_age');
                if (config('security.headers.hsts.include_subdomains')) {
                    $hsts .= '; includeSubDomains';
                }
                if (config('security.headers.hsts.preload')) {
                    $hsts .= '; preload';
                }
                $response->header('Strict-Transport-Security', $hsts);
            }

            // CSP - Content Security Policy
            if (config('security.headers.csp.enabled')) {
                $csp = $this->buildCSP(config('security.headers.csp.directives'));
                $response->header('Content-Security-Policy', $csp);
            }

            // X-Frame-Options
            $response->header('X-Frame-Options', config('security.headers.x-frame-options'));

            // X-Content-Type-Options
            $response->header('X-Content-Type-Options', config('security.headers.x-content-type-options'));

            // X-XSS-Protection
            $response->header('X-XSS-Protection', config('security.headers.x-xss-protection'));

            // Referrer-Policy
            $response->header('Referrer-Policy', config('security.headers.referrer-policy'));

            // Permissions-Policy
            $response->header('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        }

        return $response;
    }

    private function buildCSP(array $directives): string
    {
        $csp = [];
        foreach ($directives as $directive => $sources) {
            $csp[] = $directive . ' ' . implode(' ', $sources);
        }
        return implode('; ', $csp);
    }
}
