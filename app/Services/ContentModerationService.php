<?php

namespace App\Services;

/**
 * Service de modération de contenu pour les élèves
 */
class ContentModerationService
{
    /**
     * Liste de mots interdits/inappropriés
     */
    protected $bannedWords = [
        // Ajoutez vos mots à filtrer ici
        'badword1', 'badword2', 'badword3',
    ];

    /**
     * Vérifier si le contenu est approprié
     */
    public function isContentAppropriate(string $content): bool
    {
        foreach ($this->bannedWords as $word) {
            if (stripos($content, $word) !== false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Filtrer le contenu inapproprié
     */
    public function filterContent(string $content): string
    {
        foreach ($this->bannedWords as $word) {
            $content = str_ireplace(
                $word,
                str_repeat('*', strlen($word)),
                $content
            );
        }
        return $content;
    }

    /**
     * Vérifier si l'utilisateur est élève
     */
    public function shouldModerateUser($user): bool
    {
        return $user->role === 'student';
    }

    /**
     * Vérifier si le message devrait être bloqué
     */
    public function shouldBlockMessage(string $content): bool
    {
        // Logique pour détecter le spam ou les messages offensants
        return !$this->isContentAppropriate($content);
    }
}
