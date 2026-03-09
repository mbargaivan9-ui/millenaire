<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'action',
        'old_values',
        'new_values',
        'changed_by',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Polymorphic relation to loggable model
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who made the change
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scope: Get logs for a specific action
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Get logs by specific user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('changed_by', $userId);
    }

    /**
     * Scope: Get logs by model type
     */
    public function scopeOfType($query, string $modelClass)
    {
        return $query->where('loggable_type', $modelClass);
    }

    /**
     * Scope: Get logs within date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Get recent logs
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get a human-readable description of the change
     */
    public function getChangeDescription(): string
    {
        if ($this->description) {
            return $this->description;
        }

        $modelName = class_basename($this->loggable_type);
        
        return match($this->action) {
            'created' => "{$modelName} was created",
            'updated' => "{$modelName} was updated",
            'deleted' => "{$modelName} was deleted",
            'restored' => "{$modelName} was restored",
            'version_restored' => "{$modelName} was restored to previous version",
            default => "{$modelName} action: {$this->action}",
        };
    }

    /**
     * Get what changed in human-readable format
     */
    public function getChangedAttributes(): array
    {
        $changes = [];

        if ($this->action === 'created') {
            $changes = $this->new_values;
        } elseif ($this->action === 'updated') {
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? null;
                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'from' => $oldValue,
                        'to' => $newValue,
                    ];
                }
            }
        } elseif ($this->action === 'deleted') {
            $changes = $this->old_values;
        }

        return $changes;
    }
}
