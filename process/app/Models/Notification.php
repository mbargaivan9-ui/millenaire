<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id', 'title', 'message', 'type', 'category',
        'icon', 'action_url', 'related_entity_type',
        'related_entity_id', 'is_read', 'read_at',
    ];

    protected $casts = [
        'is_read'  => 'boolean',
        'read_at'  => 'datetime',
    ];

    // ─── Types ─────────────────────────────────────────────
    const TYPE_SUCCESS  = 'success';
    const TYPE_WARNING  = 'warning';
    const TYPE_DANGER   = 'danger';
    const TYPE_INFO     = 'info';
    const TYPE_PRIMARY  = 'primary';

    // ─── Categories ────────────────────────────────────────
    const CAT_GRADE      = 'grade';
    const CAT_PAYMENT    = 'payment';
    const CAT_ABSENCE    = 'absence';
    const CAT_MESSAGE    = 'message';
    const CAT_SECURITY   = 'security';
    const CAT_SYSTEM     = 'system';
    const CAT_ANNOUNCE   = 'announcement';

    // ─── Relations ─────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ────────────────────────────────────────────
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }

    // ─── Actions ───────────────────────────────────────────
    public function markAsRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }

    // ─── Helpers ───────────────────────────────────────────
    public function getIconAttribute($value): string
    {
        if ($value) return $value;
        return match($this->category) {
            self::CAT_GRADE    => 'book-open',
            self::CAT_PAYMENT  => 'credit-card',
            self::CAT_ABSENCE  => 'user-x',
            self::CAT_MESSAGE  => 'message-circle',
            self::CAT_SECURITY => 'shield-check',
            self::CAT_ANNOUNCE => 'megaphone',
            default            => 'bell',
        };
    }

    public function getColorAttribute(): string
    {
        return match($this->type) {
            self::TYPE_SUCCESS => 'var(--success)',
            self::TYPE_WARNING => 'var(--warning)',
            self::TYPE_DANGER  => 'var(--danger)',
            self::TYPE_INFO    => 'var(--info)',
            default            => 'var(--primary)',
        };
    }

    public function getBgAttribute(): string
    {
        return match($this->type) {
            self::TYPE_SUCCESS => 'var(--success-bg)',
            self::TYPE_WARNING => 'var(--warning-bg)',
            self::TYPE_DANGER  => 'var(--danger-bg)',
            self::TYPE_INFO    => 'var(--info-bg)',
            default            => 'var(--primary-bg)',
        };
    }

    // ─── Factory method ────────────────────────────────────
    public static function send(
        int $userId,
        string $title,
        string $message,
        string $type = self::TYPE_INFO,
        string $category = self::CAT_SYSTEM,
        ?string $actionUrl = null,
        ?string $icon = null
    ): self {
        return self::create([
            'user_id'    => $userId,
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'category'   => $category,
            'action_url' => $actionUrl,
            'icon'       => $icon,
            'is_read'    => false,
        ]);
    }
}
