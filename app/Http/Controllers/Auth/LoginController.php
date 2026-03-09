<?php

/**
 * Auth\LoginController — Authentification
 *
 * Phase 1 — Auth Laravel standard enrichi
 * Redirection par rôle après connexion
 *
 * @package App\Http\Controllers\Auth
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Afficher le formulaire de connexion.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect($this->redirectPath());
        }
        return view('auth.login');
    }

    /**
     * Traiter la tentative de connexion.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Mettre à jour le statut en ligne
            Auth::user()->update(['is_online' => true, 'last_login' => now()]);

            // Appliquer la langue préférée de l'utilisateur
            if ($lang = Auth::user()->preferred_language) {
                session(['locale' => $lang]);
                app()->setLocale($lang);
            }

            activity()
                ->causedBy(Auth::user())
                ->log('Connexion');

            return redirect()->intended($this->redirectPath());
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => app()->getLocale() === 'fr'
                ? 'Ces identifiants ne correspondent pas à nos enregistrements.'
                : 'These credentials do not match our records.'
            ]);
    }

    /**
     * Déconnecter l'utilisateur.
     */
    public function logout(Request $request)
    {
        // Mettre à jour le statut hors ligne
        Auth::user()?->update(['is_online' => false]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirection selon le rôle après connexion.
     */
    protected function redirectPath(): string
    {
        return match(Auth::user()->role) {
            'admin'   => route('admin.dashboard'),
            'teacher' => route('teacher.dashboard'),
            'parent'  => route('parent.dashboard'),
            'student' => route('student.dashboard'),
            default   => '/',
        };
    }
}
