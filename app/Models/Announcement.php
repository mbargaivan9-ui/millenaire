<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Announcement extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured'  => 'boolean',
        'published_at' => 'datetime',
    ];

    // ─── Accessors & Mutators ─────────────────────────────────────────────

    /**
     * Chemins d'accès aux médias
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
    }

    public function getAttachedFileUrlAttribute(): ?string
    {
        return $this->attached_file ? asset('storage/' . $this->attached_file) : null;
    }

    public function getAttachmentDownloadNameAttribute(): string
    {
        return $this->attachment_name ?? 'attachment';
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // ─── Author ───────────────────────────────────────────────────────────

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Lifecycle ────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Announcement $a) {
            if (!$a->slug) {
                $a->slug = Str::slug($a->title) . '-' . Str::random(5);
            }
        });

        static::updated(function (Announcement $a) {
            if ($a->isDirty('is_published') && $a->is_published) {
                $a->published_at = $a->published_at ?? now();
                event(new \App\Events\AnnouncementPublished($a));
            }
        });

        // Nettoyage des fichiers lors de la suppression
        static::deleted(function (Announcement $a) {
            if ($a->cover_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($a->cover_image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($a->cover_image);
            }
            if ($a->attached_file && \Illuminate\Support\Facades\Storage::disk('public')->exists($a->attached_file)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($a->attached_file);
            }
        });
    }
}
