<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageAttachment extends Model
{
    protected $fillable = [
        'message_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
    ];

    /**
     * Message parent
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Obtenir l'URL publique du fichier
     */
    public function getUrlAttribute(): string
    {
        return url('storage/' . $this->file_path);
    }
}
