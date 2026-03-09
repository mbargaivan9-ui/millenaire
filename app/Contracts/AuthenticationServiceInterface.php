<?php

namespace App\Contracts;

use App\DTOs\LoginCredentialsDTO;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Interface pour la gestion de l'authentification
 * Suit le Interface Segregation et Dependency Inversion Principles du SOLID
 * 
 * Supporte les 4 rôles: Admin, Enseignant, Parent, Élève
 * 
 * @author Laravel 12 - Millénaire Connect
 */
interface AuthenticationServiceInterface
{
    /**
     * Authentifie un utilisateur avec ses identifiants
     * 
     * @param LoginCredentialsDTO $credentials - Les identifiants
     * @return User|null - L'utilisateur authentifié ou null
     */
    public function authenticate(LoginCredentialsDTO $credentials): ?User;

    /**
     * Valide que l'utilisateur est actif
     * 
     * @param User $user - L'utilisateur
     * @return bool - True si actif
     */
    public function isUserActive(User $user): bool;

    /**
     * Enregistre un événement de connexion avec logging et notification
     * 
     * @param User $user - L'utilisateur
     * @return void
     */
    public function logLogin(User $user): void;

    /**
     * Enregistre un événement de déconnexion
     * 
     * @param User $user - L'utilisateur
     * @return void
     */
    public function logLogout(User $user): void;

    /**
     * Envoie une notification de sécurité lors de connexion suspecte
     * 
     * @param User $user
     * @param string $ipAddress
     * @return void
     */
    public function notifySecurityEvent(User $user, string $ipAddress): void;

    /**
     * Vérifie les permissions selon le rôle de l'utilisateur
     * 
     * Rôles supportés:
     *  - admin, censeur, intendant (Administrateurs)
     *  - professeur, prof_principal (Enseignants)
     *  - parent (Parents)
     *  - student (Élèves)
     * 
     * @param User $user
     * @param string[] $allowedRoles
     * @return bool
     */
    public function hasPermissionFor(User $user, array $allowedRoles): bool;

    /**
     * La authentification à deux facteurs est activée?
     * 
     * @param User $user
     * @return bool
     */
    public function isTwoFactorEnabled(User $user): bool;
}
