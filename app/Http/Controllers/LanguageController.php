<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * LanguageController — Gestion de la langue
 */
class LanguageController extends Controller
{
    public function switch($lang = null, Request $request = null)
    {
        // Accept lang from URL parameter or request input
        $lang = $lang ?? $request?->input('lang');
        
        if (!in_array($lang, ['fr', 'en'])) {
            return back();
        }

        app()->setLocale($lang);
        session(['locale' => $lang]);

        // Persist to user if authenticated
        if ($user = Auth::user()) {
            $user->update(['preferred_language' => $lang]);
        }

        return back();
    }
}
