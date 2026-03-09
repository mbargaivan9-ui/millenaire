<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseMaterial extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_published' => 'boolean',
        'view_count'   => 'integer',
    ];

    const TYPE_PDF   = 'pdf';
    const TYPE_VIDEO = 'video';
    const TYPE_DOC   = 'doc';
    const TYPE_OTHER = 'other';

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function getIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_PDF   => 'file-text',
            self::TYPE_VIDEO => 'play-circle',
            self::TYPE_DOC   => 'file',
            default          => 'paperclip',
        };
    }
}
