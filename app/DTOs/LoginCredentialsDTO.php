<?php

namespace App\DTOs;

/**
 * Data Transfer Object pour les identifiants de connexion
 * Suit le Single Responsibility Principle du SOLID
 * 
 * @author Laravel 12 - Millénaire Connect
 */
class LoginCredentialsDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly bool $rememberMe = false,
    ) {
    }

    /**
     * Crée une instance à partir d'un tableau de requête
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
            rememberMe: boolval($data['remember'] ?? false),
        );
    }
}
