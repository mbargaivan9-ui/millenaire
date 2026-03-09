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
        return $this->belongsTo(User::class, 'author_id');
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
    }
}
