<?php

namespace App\Services;

use App\Models\User;
use App\Contracts\RedirectServiceInterface;
use Illuminate\Http\RedirectResponse;

/**
 * Service pour gérer la redirection intelligente selon le rôle
 * Implémente le pattern Strategy et le SOLID
 * 
 * @author Laravel 12 - Millénaire Connect
 */
class RedirectService implements RedirectServiceInterface
{
    /**
     * Tableau de mappage des rôles aux routes de dashboard
     */
    private const ROLE_ROUTES = [
        'admin' => 'admin.dashboard',
        'teacher' => 'teacher.dashboard',
        'parent' => 'parent.dashboard',
        'student' => 'student.dashboard',
    ];

    /**
     * Redirige l'utilisateur selon son rôle
     * Suit le Single Responsibility Principle et Open/Closed Principle
     *
     * @param User $user - L'utilisateur authentifié
     * @return RedirectResponse - La réaction de redirection appropriée
     */
    public function redirectByRole(User $user): RedirectResponse
    {
        // Vérifier que l'utilisateur a un rôle valide
        $role = $user->role ?? 'student';
        
        // Obtenir la route associée au rôle
        $route = self::ROLE_ROUTES[$role] ?? 'home';
        
        // Logger la redirection pour audit
        \Log::info("Redirection utilisateur {$user->id} vers {$route} (rôle: {$role})");
        
        return redirect()->route($route);
    }
}
