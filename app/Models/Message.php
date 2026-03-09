<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'body',
        'content',
        'sender_id',
        'type',          // 'text', 'image', 'file', 'system'
        'file_path',
        'file_name',
        'file_size',
        'is_edited',
        'edited_at',
        'is_deleted_for_sender',
        'is_deleted_for_all',
        'reply_to_id',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'is_deleted_for_sender' => 'boolean',
        'is_deleted_for_all' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Conversation parente.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Auteur du message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias pour user (sender).
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Message auquel on répond.
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /**
     * Statuts de lecture (read receipts).
     */
    public function readReceipts(): HasMany
    {
        return $this->hasMany(MessageReadReceipt::class);
    }

    /**
     * Réactions au message.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }

    /**
     * Pièces jointes.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    // ─── Accessors ──────────────────────────────────────────────────────────

    /**
     * Accessor: corps tronqué pour les aperçus.
     */
    public function getPreviewAttribute(): string
    {
        if ($this->is_deleted_for_all) {
            return '[Message supprimé]';
        }

        $content = $this->body ?? $this->content;

        return match($this->type) {
            'image'  => '📷 Image',
            'file'   => "📎 {$this->file_name}",
            'system' => "🔔 {$content}",
            default  => \Illuminate\Support\Str::limit($content, 60),
        };
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    /**
     * Exclure les messages supprimés.
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted_for_all', false);
    }

    /**
     * Seulement les messages non supprimés pour tous.
     */
    public function scopeVisible($query)
    {
        return $query->notDeleted();    }
}