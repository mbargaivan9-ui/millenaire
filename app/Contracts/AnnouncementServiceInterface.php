<?php

namespace App\Contracts;

use App\DTOs\CreateAnnouncementDTO;
use App\DTOs\AnnouncementResponseDTO;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Pagination\Paginator;

/**
 * AnnouncementServiceInterface
 * 
 * Contract for announcement management
 * Supports all 4 roles: Admin, Teacher, Parent, Student
 * 
 * - Admin/Censeur: Create, edit, delete, publish announcements
 * - Teacher: Create announcements for their classes
 * - Parent: View class announcements
 * - Student: View class announcements
 */
interface AnnouncementServiceInterface
{
    /**
     * Crée une nouvelle annonce - triggers notification
     */
    public function create(CreateAnnouncementDTO $dto, User $creator): AnnouncementResponseDTO;

    /**
     * Met à jour une annonce existante
     */
    public function update(int $id, CreateAnnouncementDTO $dto): AnnouncementResponseDTO;

    /**
     * Supprime une annonce
     */
    public function delete(int $id): bool;

    /**
     * Récupère une annonce par son ID
     */
    public function getById(int $id): ?AnnouncementResponseDTO;

    /**
     * Récupère une annonce par son slug
     */
    public function getBySlug(string $slug): ?AnnouncementResponseDTO;

    /**
     * Récupère toutes les annonces publiées (pour l'accueil)
     */
    public function getPublished(int $limit = 10): array;

    /**
     * Récupère toutes les annonces avec pagination (admin)
     */
    public function getAllPaginated(int $perPage = 15): Paginator;

    /**
     * Récupère les annonces épinglées
     */
    public function getPinned(): array;

    /**
     * Épingler une annonce
     */
    public function pin(int $id): bool;

    /**
     * Dépingler une annonce
     */
    public function unpin(int $id): bool;

    /**
     * Publier une annonce - triggers notifications
     */
    public function publish(int $id, User $publishedBy): bool;

    /**
     * Dépublier une annonce
     */
    public function unpublish(int $id): bool;

    /**
     * Envoyer une annonce à des utilisateurs spécifiques
     */
    public function sendToUsers(int $announcementId, array $userIds): int;

    /**
     * Envoyer une annonce à un rôle
     */
    public function sendToRole(int $announcementId, string $role): int;

    /**
     * Obtenir les annonces pour un utilisateur selon son rôle
     */
    public function getForUser(User $user, int $limit = 10): array;
}
