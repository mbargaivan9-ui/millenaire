<?php

namespace App\DTOs;

readonly class CreateAnnouncementDTO
{
    public function __construct(
        public string $title,
        public string $content,
        public ?string $category = null,
        public ?string $featured_image = null,
        public ?\DateTime $published_date = null,
        public ?\DateTime $expires_at = null,
        public string $visibility = 'all',
        public bool $is_pinned = false,
        public ?string $attachment_path = null,
        public bool $is_active = true,
        public int $created_by = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            content: $data['content'],
            category: $data['category'] ?? null,
            featured_image: $data['featured_image'] ?? null,
            published_date: isset($data['published_date']) ? new \DateTime($data['published_date']) : null,
            expires_at: isset($data['expires_at']) ? new \DateTime($data['expires_at']) : null,
            visibility: $data['visibility'] ?? 'all',
            is_pinned: (bool)($data['is_pinned'] ?? false),
            attachment_path: $data['attachment_path'] ?? null,
            is_active: (bool)($data['is_active'] ?? true),
            created_by: $data['created_by'] ?? auth()->id() ?? 0,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'category' => $this->category,
            'featured_image' => $this->featured_image,
            'published_date' => $this->published_date?->format('Y-m-d'),
            'expires_at' => $this->expires_at?->format('Y-m-d'),
            'visibility' => $this->visibility,
            'is_pinned' => $this->is_pinned,
            'attachment_path' => $this->attachment_path,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
        ];
    }
}
