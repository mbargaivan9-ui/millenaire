<?php

namespace App\Services;

use App\Contracts\AnnouncementServiceInterface;
use App\DTOs\CreateAnnouncementDTO;
use App\DTOs\AnnouncementResponseDTO;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AnnouncementService implements AnnouncementServiceInterface
{
    /**
     * Créer une nouvelle annonce
     */
    public function create(CreateAnnouncementDTO $dto, User $creator): AnnouncementResponseDTO
    {
        $data = $dto->toArray();
        $data['slug'] = $this->generateUniqueSlug($dto->title);
        $data['published_date'] = $data['published_date'] ? new Carbon($data['published_date']) : null;
        $data['expires_at'] = $data['expires_at'] ? new Carbon($data['expires_at']) : null;
        $data['created_by'] = $creator->id;

        $announcement = Announcement::create($data);

        return AnnouncementResponseDTO::fromModel($announcement);
    }

    /**
     * Mettre à jour une annonce
     */
    public function update(int $id, CreateAnnouncementDTO $dto): AnnouncementResponseDTO
    {
        $announcement = Announcement::findOrFail($id);
        
        $data = $dto->toArray();
        $data['slug'] = $data['slug'] ?? $this->generateUniqueSlug($dto->title, $id);
        $data['published_date'] = $data['published_date'] ? new Carbon($data['published_date']) : null;
        $data['expires_at'] = $data['expires_at'] ? new Carbon($data['expires_at']) : null;

        $announcement->update($data);

        return AnnouncementResponseDTO::fromModel($announcement->fresh());
    }

    /**
     * Supprimer une annonce
     */
    public function delete(int $id): bool
    {
        $announcement = Announcement::findOrFail($id);

        // Supprimer l'image si elle existe
        if ($announcement->featured_image) {
            $this->deleteFeaturedImage($announcement->featured_image);
        }

        // Supprimer le fichier attaché si présent
        if ($announcement->attachment_path) {
            Storage::disk('public')->delete($announcement->attachment_path);
        }

        return $announcement->delete();
    }

    /**
     * Récupérer une annonce par ID
     */
    public function getById(int $id): ?AnnouncementResponseDTO
    {
        $announcement = Announcement::find($id);
        
        return $announcement ? AnnouncementResponseDTO::fromModel($announcement) : null;
    }

    /**
     * Récupérer une annonce par slug
     */
    public function getBySlug(string $slug): ?AnnouncementResponseDTO
    {
        $announcement = Announcement::where('slug', $slug)->first();
        
        return $announcement ? AnnouncementResponseDTO::fromModel($announcement) : null;
    }

    /**
     * Récupérer les annonces publiées
     */
    public function getPublished(int $limit = 10): array
    {
        $announcements = Announcement::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('published_date')
                    ->orWhere('published_date', '<=', now());
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_date')
            ->limit($limit)
            ->get()
            ->map(fn(Announcement $a) => AnnouncementResponseDTO::fromModel($a))
            ->toArray();

        return $announcements;
    }

    /**
     * Récupérer toutes les annonces paginées
     */
    public function getAllPaginated(int $perPage = 15): Paginator
    {
        return Announcement::orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->simplePaginate($perPage);
    }

    /**
     * Récupérer les annonces épinglées
     */
    public function getPinned(): array
    {
        $announcements = Announcement::where('is_pinned', true)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->orderByDesc('published_date')
            ->get()
            ->map(fn(Announcement $a) => AnnouncementResponseDTO::fromModel($a))
            ->toArray();

        return $announcements;
    }

    /**
     * Uploader l'image featured
     */
    public function uploadFeaturedImage(string $filePath): string
    {
        try {
            // Le fichier est déjà stocké, on retourne juste le chemin
            if (Storage::disk('public')->exists($filePath)) {
                return $filePath;
            }
        } catch (\Exception $e) {
            \Log::error('Error uploading featured image: ' . $e->getMessage());
        }

        return '';
    }

    /**
     * Supprimer l'image featured
     */
    public function deleteFeaturedImage(string $imagePath): bool
    {
        try {
            return Storage::disk('public')->delete($imagePath);
        } catch (\Exception $e) {
            \Log::error('Error deleting featured image: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Générer un slug unique
     */
    private function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Vérifier si un slug existe déjà
     */
    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Announcement::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Épingler une annonce
     */
    public function pin(int $id): bool
    {
        return Announcement::findOrFail($id)->update(['is_pinned' => true]);
    }

    /**
     * Dépingler une annonce
     */
    public function unpin(int $id): bool
    {
        return Announcement::findOrFail($id)->update(['is_pinned' => false]);
    }

    /**
     * Publier une annonce
     */
    public function publish(int $id, User $publishedBy): bool
    {
        $announcement = Announcement::findOrFail($id);
        $result = $announcement->update([
            'is_active' => true,
            'published_at' => now(),
            'published_by' => $publishedBy->id,
        ]);

        // Envoyer des notifications aux utilisateurs
        try {
            $this->sendToRole($id, 'admin');
            $this->sendToRole($id, 'teacher');
            $this->sendToRole($id, 'parent');
            $this->sendToRole($id, 'student');
        } catch (\Exception $e) {
            \Log::error('Error sending announcement notifications: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Dépublier une annonce
     */
    public function unpublish(int $id): bool
    {
        return Announcement::findOrFail($id)->update(['is_active' => false]);
    }

    /**
     * Envoyer une annonce à des utilisateurs spécifiques
     */
    public function sendToUsers(int $announcementId, array $userIds): int
    {
        $announcement = Announcement::findOrFail($announcementId);
        $count = 0;

        foreach ($userIds as $userId) {
            try {
                // Créer une notification pour chaque utilisateur
                \App\Models\Notification::send(
                    $userId,
                    $announcement->title,
                    \Illuminate\Support\Str::limit($announcement->content, 100),
                    \App\Models\Notification::TYPE_INFO,
                    \App\Models\Notification::CAT_ANNOUNCEMENT,
                    route('announcements.show', $announcement->slug),
                    'megaphone'
                );
                $count++;
            } catch (\Exception $e) {
                \Log::error("Error sending announcement to user {$userId}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Envoyer une annonce à un rôle
     */
    public function sendToRole(int $announcementId, string $role): int
    {
        $announcement = Announcement::findOrFail($announcementId);
        $userIds = User::where('role', $role)->pluck('id')->toArray();

        return $this->sendToUsers($announcementId, $userIds);
    }

    /**
     * Obtenir les annonces pour un utilisateur
     */
    public function getForUser(User $user, int $limit = 10): array
    {
        $query = Announcement::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('published_date')
                  ->orWhere('published_date', '<=', now());
            });

        // Filtrer par rôle si nécessaire
        if ($user->isStudent()) {
            // Les étudiants voient les annonces générales et celles de leur classe
            $query->whereNull('class_id')
                  ->orWhere('class_id', $user->class_id);
        }

        return $query->orderByDesc('is_pinned')
                     ->orderByDesc('published_date')
                     ->limit($limit)
                     ->get()
                     ->map(fn(Announcement $a) => AnnouncementResponseDTO::fromModel($a))
                     ->toArray();
    }
}
