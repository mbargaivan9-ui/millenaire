<?php

namespace App\DTOs;

readonly class AnnouncementResponseDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $content,
        public ?string $category,
        public ?string $featured_image,
        public string $published_date,
        public ?string $expires_at,
        public string $visibility,
        public bool $is_pinned,
        public bool $is_active,
        public string $author_name,
        public string $slug,
    ) {}

    public static function fromModel(\App\Models\Announcement $announcement): self
    {
        return new self(
            id: $announcement->id,
            title: $announcement->title,
            content: $announcement->content,
            category: $announcement->category,
            featured_image: $announcement->featured_image,
            published_date: $announcement->published_date?->format('d/m/Y') ?? '',
            expires_at: $announcement->expires_at?->format('d/m/Y'),
            visibility: $announcement->visibility,
            is_pinned: $announcement->is_pinned,
            is_active: $announcement->is_active,
            author_name: $announcement->createdBy?->name ?? 'Admin',
            slug: $announcement->slug ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'category' => $this->category,
            'featured_image' => $this->featured_image,
            'published_date' => $this->published_date,
            'expires_at' => $this->expires_at,
            'visibility' => $this->visibility,
            'is_pinned' => $this->is_pinned,
            'is_active' => $this->is_active,
            'author_name' => $this->author_name,
            'slug' => $this->slug,
        ];
    }
}
