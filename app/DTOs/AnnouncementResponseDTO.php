<?php

namespace App\DTOs;

readonly class AnnouncementResponseDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string $content,
        public ?string $category,
        public ?string $cover_image,
        public ?string $cover_image_url,
        public ?string $attached_file,
        public ?string $attached_file_url,
        public ?string $attachment_name,
        public ?string $attachment_type,
        public ?int $attachment_size,
        public ?string $attachment_path,
        public string $published_date,
        public ?string $published_at,
        public bool $is_featured,
        public bool $is_published,
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
            cover_image: $announcement->cover_image,
            cover_image_url: $announcement->cover_image_url,
            attached_file: $announcement->attached_file,
            attached_file_url: $announcement->attached_file_url,
            attachment_name: $announcement->attachment_name,
            attachment_type: $announcement->attachment_type,
            attachment_size: $announcement->attachment_size,
            attachment_path: $announcement->attached_file ? 'storage/' . $announcement->attached_file : null,
            published_date: $announcement->published_at?->format('d/m/Y') ?? now()->format('d/m/Y'),
            published_at: $announcement->published_at?->format('d/m/Y H:i'),
            is_featured: $announcement->is_featured ?? false,
            is_published: $announcement->is_published ?? false,
            author_name: $announcement->author?->name ?? 'Admin',
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
            'cover_image' => $this->cover_image,
            'cover_image_url' => $this->cover_image_url,
            'attached_file' => $this->attached_file,
            'attached_file_url' => $this->attached_file_url,
            'attachment_name' => $this->attachment_name,
            'attachment_type' => $this->attachment_type,
            'attachment_size' => $this->attachment_size,
            'attachment_path' => $this->attachment_path,
            'published_date' => $this->published_date,
            'published_at' => $this->published_at,
            'is_featured' => $this->is_featured,
            'is_published' => $this->is_published,
            'author_name' => $this->author_name,
            'slug' => $this->slug,
        ];
    }
}
