<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypingIndicator extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'conversation_id',
        'user_id',
        'last_typed_at',
    ];

    protected $casts = [
        'last_typed_at' => 'datetime',
    ];

    /**
     * Conversation
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
