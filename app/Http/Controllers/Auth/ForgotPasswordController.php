<?php

/**
 * Auth\ForgotPasswordController — Réinitialisation du mot de passe
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', app()->getLocale() === 'fr' ? 'Lien envoyé ! Vérifiez votre boîte mail.' : 'Link sent! Check your inbox.')
            : back()->withErrors(['email' => app()->getLocale() === 'fr' ? 'Aucun compte trouvé avec cet email.' : 'No account found with this email.']);
    }
}
