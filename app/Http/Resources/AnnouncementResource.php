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
            'published_date' => $data['published_date'] ?? ($this->published_date?->format('d/m/Y') ?? null),
            'expires_at' => $data['expires_at'] ?? ($this->expires_at?->format('d/m/Y') ?? null),
            'visibility' => $data['visibility'] ?? $this->visibility ?? 'all',
            'is_pinned' => (bool)($data['is_pinned'] ?? $this->is_pinned ?? false),
            'is_active' => (bool)($data['is_active'] ?? $this->is_active ?? true),
            'author_name' => $data['author_name'] ?? $this->createdBy?->name ?? 'Admin',
            'slug' => $data['slug'] ?? $this->slug ?? null,
            'attachment_path' => $data['attachment_path'] ?? $this->attachment_path ?? null,
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

        // Normalize attachment path similarly
        if (!empty($out['attachment_path'])) {
            $att = $out['attachment_path'];
            if (!filter_var($att, FILTER_VALIDATE_URL)) {
                $out['attachment_path'] = asset('storage/' . ltrim($att, '/'));
            }
        }

        return $out;
    }
}
