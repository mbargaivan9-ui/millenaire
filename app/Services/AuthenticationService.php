<?php

namespace App\Services;

use App\Contracts\AuthenticationServiceInterface;
use App\DTOs\LoginCredentialsDTO;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Service pour la gestion de l'authentification
 * Implémente le Single Responsibility et Dependency Inversion Principles du SOLID
 * 
 * @author Laravel 12 - Millénaire Connect
 */
class AuthenticationService implements AuthenticationServiceInterface
{
    /**
     * Authentifie un utilisateur avec ses identifiants
     * 
     * @param LoginCredentialsDTO $credentials - Les identifiants
     * @return User|null - L'utilisateur authentifié ou null
     */
    public function authenticate(LoginCredentialsDTO $credentials): ?User
    {
        try {
            $user = User::where('email', $credentials->email)->first();

            // Vérifier que l'utilisateur existe et que le mot de passe est correct
            if (!$user || !Hash::check($credentials->password, $user->password)) {
                Log::warning("Tentative de connexion échouée pour {$credentials->email}");
                return null;
            }

            return $user;
        } catch (\Illuminate\Database\QueryException $e) {
            // DB unreachable — log and return null so caller can handle gracefully
            Log::error('Database unavailable during authentication: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('Unexpected error during authentication: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Valide que l'utilisateur est actif
     * 
     * @param User $user - L'utilisateur
     * @return bool - True si actif
     */
    public function isUserActive(User $user): bool
    {
        return $user->is_active === true;
    }

    /**
     * Enregistre un événement de connexion
     * 
     * @param User $user - L'utilisateur
     * @return void
     */
    public function logLogin(User $user): void
    {
        $user->logLogin();
    }

    /**
     * Enregistre un événement de déconnexion
     * 
     * @param User $user - L'utilisateur
     * @return void
     */
    public function logLogout(User $user): void
    {
        try {
            ActivityLog::create([
                'user_id'    => $user->id,
                'action'     => 'Déconnexion',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            Log::info("User {$user->email} logged out");
        } catch (\Exception $e) {
            Log::error("Error logging logout for user {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * Envoie une notification de sécurité lors de connexion suspecte
     * 
     * @param User $user
     * @param string $ipAddress
     * @return void
     */
    public function notifySecurityEvent(User $user, string $ipAddress): void
    {
        try {
            Notification::send(
                $user->id,
                'Alerte de sécurité',
                "Une connexion a été détectée depuis l'adresse IP {$ipAddress}. Si ce n'est pas vous, changez votre mot de passe immédiatement.",
                Notification::TYPE_DANGER,
                Notification::CAT_SECURITY,
                route('profile.security'),
                'shield-check'
            );
            Log::warning("Security alert sent to user {$user->email} for IP {$ipAddress}");
        } catch (\Exception $e) {
            Log::error("Error sending security notification: " . $e->getMessage());
        }
    }

    /**
     * Vérifie les permissions selon le rôle de l'utilisateur
     * 
     * @param User $user
     * @param string[] $allowedRoles
     * @return bool
     */
    public function hasPermissionFor(User $user, array $allowedRoles): bool
    {
        return in_array($user->role, $allowedRoles);
    }

    /**
     * La authentification à deux facteurs est activée?
     * 
     * @param User $user
     * @return bool
     */
    public function isTwoFactorEnabled(User $user): bool
    {
        return $user->two_factor_enabled === true;
    }
}
