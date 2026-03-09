<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminAuditLog extends Model
{
    use HasFactory;

    protected $table = 'admin_audit_logs';
    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'changes',
        'reason',
        'metadata',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * User who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an administrative action
     */
    public static function logAction(
        User $user,
        string $action,
        string $entityType,
        int $entityId,
        ?array $changes = null,
        ?string $reason = null,
        ?string $ipAddress = null
    ): self {
        return self::create([
            'user_id' => $user->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'changes' => $changes,
            'reason' => $reason,
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Get audit logs for an entity
     */
    public static function forEntity(string $type, int $id)
    {
        return self::where('entity_type', $type)
            ->where('entity_id', $id)
            ->orderByDesc('created_at')
            ->get();
    }
}
