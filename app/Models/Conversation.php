<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'subject',
        'type',          // 'direct', 'group', 'announcement'
        'created_by',
        'last_message_at',
        'last_message_preview',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Participants de la conversation.
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot('last_read_at', 'is_muted')
            ->withTimestamps();
    }

    /**
     * Messages de la conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    /**
     * Dernier message.
     */
    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Créateur de la conversation.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Nombre de messages non lus pour l'utilisateur courant.
     */
    public function unreadCount(int $userId): int
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        if (!$participant) return 0;

        $lastRead = $participant->pivot->last_read_at;

        return $this->messages()
            ->where('user_id', '!=', $userId)
            ->when($lastRead, fn($q) => $q->where('created_at', '>', $lastRead))
            ->count();
    }
}
