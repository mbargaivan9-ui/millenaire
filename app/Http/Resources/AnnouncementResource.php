<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = is_array($this->resource) ? $this->resource : parent::toArray($request);

        $out = [
            'id' => $data['id'] ?? $this->id ?? null,
            'title' => $data['title'] ?? $this->title ?? '',
            'content' => $data['content'] ?? $this->content ?? '',
            'category' => $data['category'] ?? $this->category ?? null,
            'featured_image' => $data['featured_image'] ?? $this->featured_image ?? null,
            'published_date' => $data['published_date'] ?? ($this->published_date?->format('d/m/Y') ?? $this->published_at?->format('d/m/Y') ?? null),
            'expires_at' => $data['expires_at'] ?? ($this->expires_at?->format('d/m/Y') ?? null),
            'visibility' => $data['visibility'] ?? $this->visibility ?? 'all',
            'is_pinned' => (bool)($data['is_pinned'] ?? $this->is_pinned ?? false),
            'is_featured' => (bool)($data['is_featured'] ?? $this->is_featured ?? false),
            'is_active' => (bool)($data['is_active'] ?? $this->is_active ?? true),
            'is_published' => (bool)($data['is_published'] ?? $this->is_published ?? false),
            'author_name' => $data['author_name'] ?? $this->author?->name ?? 'Admin',
            'slug' => $data['slug'] ?? $this->slug ?? null,
            'attachment_path' => $data['attachment_path'] ?? $this->attachment_path ?? null,
            
            // Nouveaux champs - Phase 11
            'cover_image' => $data['cover_image'] ?? $this->cover_image ?? null,
            'cover_image_url' => $data['cover_image_url'] ?? null,
            'attached_file' => $data['attached_file'] ?? $this->attached_file ?? null,
            'attached_file_url' => $data['attached_file_url'] ?? null,
            'attachment_name' => $data['attachment_name'] ?? $this->attachment_name ?? null,
            'attachment_type' => $data['attachment_type'] ?? $this->attachment_type ?? null,
            'attachment_size' => $data['attachment_size'] ?? $this->attachment_size ?? null,
            'attachment_size_readable' => $data['attachment_size_readable'] ?? ($this->attachment_size ? $this->formatBytes($this->attachment_size) : null),
        ];

        // Normalize featured_image: accept full URLs or storage paths
        $featured = $out['featured_image'];
        if ($featured) {
            if (filter_var($featured, FILTER_VALIDATE_URL)) {
                $out['featured_image'] = $featured;
            } else {
                $out['featured_image'] = asset('storage/' . ltrim($featured, '/'));
            }
        } else {
            $out['featured_image'] = null;
        }

        // Normalize cover_image (Phase 11)
        if (!empty($out['cover_image'])) {
            $cover = $out['cover_image'];
            if (filter_var($cover, FILTER_VALIDATE_URL)) {
                $out['cover_image_url'] = $cover;
            } else {
                $out['cover_image_url'] = asset('storage/' . ltrim($cover, '/'));
            }
        }

        // Normalize attachment path similarly
        if (!empty($out['attachment_path'])) {
            $att = $out['attachment_path'];
            if (!filter_var($att, FILTER_VALIDATE_URL)) {
                $out['attachment_path'] = asset('storage/' . ltrim($att, '/'));
            }
        }

        // Normalize attached_file (Phase 11)
        if (!empty($out['attached_file'])) {
            $file = $out['attached_file'];
            if (filter_var($file, FILTER_VALIDATE_URL)) {
                $out['attached_file_url'] = $file;
            } else {
                $out['attached_file_url'] = asset('storage/' . ltrim($file, '/'));
            }
        }

        return $out;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
