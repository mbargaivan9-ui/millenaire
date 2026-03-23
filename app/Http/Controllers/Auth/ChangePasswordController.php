<?php

/**
 * Auth\ChangePasswordController — Changement du mot de passe à la première connexion
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    /**
     * Afficher le formulaire de changement de mot de passe.
     */
    public function showChangePasswordForm()
    {
        // L'utilisateur doit être authentifié
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // L'utilisateur n'a pas besoin de changer son mot de passe
        if (!auth()->user()->must_change_password) {
            return redirect()->intended();
        }

        return view('auth.change-password', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Mettre à jour le mot de passe.
     */
    public function update(Request $request)
    {
        // Valider les données
        $rules = [
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                function ($attribute, $value, $fail) {
                    // Vérifier que le nouveau mot de passe n'est pas le même que l'ancien
                    if (Hash::check($value, auth()->user()->password)) {
                        $fail(app()->getLocale() === 'fr'
                            ? 'Le nouveau mot de passe doit être différent de l\'ancien.'
                            : 'The new password must be different from the old one.'
                        );
                    }
                },
            ],
        ];

        // Ajouter la validation du mot de passe actuel si ce n'est pas le premier changement
        if (!auth()->user()->must_change_password) {
            $rules['current_password'] = ['required', function ($attribute, $value, $fail) {
                if (!Hash::check($value, auth()->user()->password)) {
                    $fail(app()->getLocale() === 'fr'
                        ? 'Le mot de passe actuel est incorrect.'
                        : 'The current password is incorrect.'
                    );
                }
            }];
        }

        $validated = $request->validate($rules);

        $user = auth()->user();
        
        // Vérifier si ceci est le changement initial de mot de passe
        $isInitialChange = $user->must_change_password;

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false, // L'utilisateur a changé son mot de passe
            'password_changed_at' => now(),
        ]);

        // Enregistrer l'activité
        activity()
            ->causedBy($user)
            ->log($isInitialChange ? 'Mot de passe initial changé' : 'Mot de passe changé');

        // Message de succès
        $message = $isInitialChange
            ? (app()->getLocale() === 'fr'
                ? 'Mot de passe changé avec succès. Vous pouvez maintenant accéder à la plateforme.'
                : 'Password changed successfully. You can now access the platform.'
              )
            : (app()->getLocale() === 'fr'
                ? 'Mot de passe mis à jour avec succès.'
                : 'Password updated successfully.'
              );

        // Rediriger selon le chemin prévu ou selon le rôle
        if ($isInitialChange) {
            // Première connexion, rediriger vers le dashboard
            return redirect()->intended($this->redirectPath())->with('success', $message);
        }

        // Sinon, rediriger vers les paramètres du compte
        return back()->with('success', $message);
    }

    /**
     * Déterminer le chemin de redirection après le changement de mot de passe.
     */
    protected function redirectPath(): string
    {
        return match(auth()->user()->role) {
            'admin'   => route('admin.dashboard'),
            'teacher' => route('teacher.dashboard'),
            'parent'  => route('parent.dashboard'),
            'student' => route('student.dashboard'),
            default   => '/',
        };
    }
}
