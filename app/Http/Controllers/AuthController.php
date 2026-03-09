<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Http\Requests\StudentRegisterRequest;
use App\DTOs\CreateStudentDTO;
use App\DTOs\LoginCredentialsDTO;
use App\Contracts\RedirectServiceInterface;
use App\Contracts\AuthenticationServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    /**
     * Constructeur avec injection de dépendances (SOLID)
     */
    public function __construct(
        private RedirectServiceInterface $redirectService,
        private AuthenticationServiceInterface $authService,
    ) {
    }

    /**
     * Affiche la page de login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email requis',
            'email.email' => 'Email invalide',
            'password.required' => 'Mot de passe requis',
            'password.min' => 'Min 6 caractères',
        ]);

        // Créer le DTO à partir des données validées
        $credentials = LoginCredentialsDTO::fromRequest($validated);

        // Authentifier l'utilisateur via le service — gestion des erreurs DB
        try {
            $user = $this->authService->authenticate($credentials);
        } catch (\Exception $e) {
            // Si la DB est indisponible, retourner un message lisible
            \Illuminate\Support\Facades\Log::error('Auth service error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Service d\'authentification indisponible. Réessayez plus tard.'])->withInput();
        }

        if (!$user) {
            \Illuminate\Support\Facades\Log::warning("Tentative de connexion échouée pour {$credentials->email}");
            return back()->withErrors(['email' => 'Identifiants invalides'])->withInput();
        }

        // Vérifier que l'utilisateur est actif
        if (!$this->authService->isUserActive($user)) {
            \Illuminate\Support\Facades\Log::warning("Tentative de connexion avec compte désactivé pour {$credentials->email}");
            return back()->withErrors(['email' => 'Compte désactivé'])->withInput();
        }

        // Authentifier au niveau Laravel
        try {
            Auth::login($user, $credentials->rememberMe);
            $request->session()->regenerate();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Session auth error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Erreur lors de l\'authentification. Réessayez.'])->withInput();
        }
        
        // Enregistrer la connexion (wrapped in try-catch for DB unavailability)
        try {
            $this->authService->logLogin($user);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Could not log user login: ' . $e->getMessage());
            // Continue anyway - don't prevent user from logging in if logLogin fails
        }

        // Redirection intelligente selon le rôle (via service injecté)
        try {
            return $this->redirectService->redirectByRole($user);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Redirect error: ' . $e->getMessage());
            // Fallback: redirect to home if redirectService fails
            return redirect()->route('home')->with('success', 'Connexion réussie!');
        }
    }

    /**
     * Affiche la page d'enregistrement (élèves uniquement)
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Traite l'enregistrement (élèves uniquement)
     */
    public function register(StudentRegisterRequest $request)
    {
        try {
            $dto = CreateStudentDTO::fromArray($request->validated());

            $user = User::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
                'role' => 'student',
                'date_of_birth' => $dto->date_of_birth,
                'gender' => $dto->gender,
                'is_active' => true,
            ]);

            Student::create([
                'user_id' => $user->id,
                'matricule' => 'STU-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
            ]);

            event(new Registered($user));

            return redirect()->route('login')
                ->with('success', 'Inscription réussie. Vous pouvez maintenant vous connecter.');
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error('Database error during registration: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Erreur base de données. Réessayez plus tard.'])->withInput();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Registration error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Erreur lors de l\'inscription. Réessayez.'])->withInput();
        }
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Déconnecté avec succès');
    }

    /**
     * Oubli de mot de passe
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Réinitialisation du mot de passe
     */
    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            // Vérifier si l'utilisateur existe (peut lancer une QueryException)
            $user = null;
            try {
                $user = \App\Models\User::where('email', $request->input('email'))->first();
            } catch (\Illuminate\Database\QueryException $e) {
                return back()->withErrors(['email' => 'Service indisponible pour l\'instant'])->withInput();
            }

            if (!$user) {
                return back()->withErrors(['email' => 'Aucun compte trouvé pour cet email'])->withInput();
            }

            \Illuminate\Support\Facades\Password::sendResetLink(
                $request->only('email')
            );

            return back()->with('success', 'Lien de réinitialisation envoyé');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Impossible d\'envoyer le lien pour l\'instant'])->withInput();
        }
    }

    /**
     * Affiche le formulaire de réinitialisation
     */
    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Réinitialise le mot de passe
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        try {
            // Attempt reset; underlying provider may query DB
            return \Illuminate\Support\Facades\Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->update(['password' => Hash::make($password)]);
                }
            );
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withErrors(['email' => 'Service indisponible pour la réinitialisation'])->withInput();
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Erreur lors de la réinitialisation'])->withInput();
        }
    }

    /**
     * Affiche et permet d'éditer le profil de l'utilisateur connecté
     */
    public function editProfile()
    {
        $user = Auth::user();
        return view('account.profile', compact('user'));
    }
}
