<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

/**
 * Interface pour la gestion des redirections intelligentes
 * Cette interface suit le principe de Dependency Inversion du SOLID
 * 
 * @author Laravel 12 - Millénaire Connect
 */
interface RedirectServiceInterface
{
    /**
     * Redirige l'utilisateur selon son rôle
     * @param User $user - L'utilisateur authentifié
     * @return RedirectResponse - La réponse de redirection
     */
    public function redirectByRole(User $user): RedirectResponse;
}
