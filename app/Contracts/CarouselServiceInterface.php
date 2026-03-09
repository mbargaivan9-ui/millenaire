<?php

namespace App\Contracts;

use App\DTOs\CarouselImageDTO;
use Illuminate\Support\Collection;

/**
 * CarouselServiceInterface
 * 
 * Contract for carousel/slider management
 * Supports displaying carousel content for all 4 roles
 * 
 * - Admin: Create and manage carousel content
 * - Teacher: View carousel content on dashboard
 * - Parent: View carousel content on dashboard
 * - Student: View carousel content on dashboard
 */
interface CarouselServiceInterface
{
    /**
     * Retourne un tableau de CarouselImageDTO
     * @param array $settings Carousel settings
     * @return CarouselImageDTO[]
     */
    public function getFromSettings(array $settings): array;

    /**
     * Résout une URL de fichier
     * @param string|null $path Fichier path
     * @return string|null URL d'accès
     */
    public function resolveUrl(?string $path): ?string;

    /**
     * Get all carousel items for public display
     * @return Collection
     */
    public function getPublishableItems(): Collection;

    /**
     * Get carousel items for a specific role
     * Role-based carousel customization
     * 
     * @param string $role admin|teacher|parent|student
     * @return Collection
     */
    public function getItemsForRole(string $role): Collection;

    /**
     * Create a new carousel item
     * @param array $data Carousel item data
     * @return array Created item
     */
    public function create(array $data): array;

    /**
     * Update carousel item
     * @param int $id Item ID
     * @param array $data Updated data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete carousel item
     * @param int $id Item ID
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Publish carousel item
     * @param int $id Item ID
     * @return bool
     */
    public function publish(int $id): bool;

    /**
     * Unpublish carousel item
     * @param int $id Item ID
     * @return bool
     */
    public function unpublish(int $id): bool;
}
