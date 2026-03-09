<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * HasLogs Trait
 * 
 * Automatically logs all model actions (create, update, delete, restore)
 * to the activity_logs table for comprehensive audit trail.
 */
trait HasLogs
{
    /**
     * Boot the HasLogs trait
     */
    protected static function bootHasLogs()
    {
        static::created(function (Model $model) {
            $model->logAction('created', [], $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            $original = $model->getOriginal();
            
            $oldValues = [];
            foreach ($changes as $key => $newValue) {
                if (isset($original[$key])) {
                    $oldValues[$key] = $original[$key];
                }
            }

            $model->logAction('updated', $oldValues, $changes);
        });

        static::deleted(function (Model $model) {
            $model->logAction('deleted', $model->getAttributes(), []);
        });

        static::restored(function (Model $model) {
            $model->logAction('restored', [], $model->getAttributes());
        });
    }

    /**
     * Log an action for this model
     * 
     * @param string $action The action being performed (created, updated, deleted, restored, etc)
     * @param array $oldValues The previous values (for updates)
     * @param array $newValues The new values or full model attributes
     * @param string|null $description Optional human-readable description
     * @return ActivityLog|null
     */
    public function logAction(
        string $action,
        array $oldValues = [],
        array $newValues = [],
        ?string $description = null
    ): ?ActivityLog {
        try {
            $activityLog = ActivityLog::create([
                'loggable_type' => self::class,
                'loggable_id' => $this->getKey(),
                'action' => $action,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'changed_by' => auth()->id(),
                'description' => $description,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);

            Log::info("Action logged for " . class_basename($this), [
                'action' => $action,
                'id' => $this->getKey(),
                'user_id' => auth()->id(),
            ]);

            return $activityLog;
        } catch (\Exception $e) {
            Log::error("Failed to log action: " . $e->getMessage(), [
                'model' => self::class,
                'action' => $action,
            ]);
            return null;
        }
    }

    /**
     * Get all activity logs for this model
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all activity logs with pagination
     * 
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getActivityLogsPaginated(int $perPage = 20)
    {
        return $this->activityLogs()
            ->paginate($perPage);
    }

    /**
     * Get activity logs filtered by action
     * 
     * @param string $action The action to filter by
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActivityLogsByAction(string $action)
    {
        return $this->activityLogs()
            ->where('action', $action)
            ->get();
    }

    /**
     * Get activity logs within a date range
     * 
     * @param \DateTime|string $startDate
     * @param \DateTime|string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActivityLogsDateRange($startDate, $endDate)
    {
        return $this->activityLogs()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }

    /**
     * Get activity logs by user
     * 
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActivityLogsByUser(int $userId)
    {
        return $this->activityLogs()
            ->where('changed_by', $userId)
            ->get();
    }

    /**
     * Restore model to a previous version (if has soft deletes)
     * 
     * @param \Carbon\Carbon|string|null $timestamp
     * @return bool
     */
    public function restoreToVersion($timestamp = null)
    {
        try {
            $log = $timestamp
                ? $this->activityLogs()->where('created_at', '<=', $timestamp)->latest()->first()
                : $this->activityLogs()->where('action', 'updated')->latest()->first();

            if (!$log || empty($log->old_values)) {
                return false;
            }

            // Filter out non-fillable or primary key columns
            $fillable = $this->getFillable();
            $restoreData = [];

            foreach ($log->old_values as $key => $value) {
                if (in_array($key, $fillable) && $key !== $this->getKeyName()) {
                    $restoreData[$key] = $value;
                }
            }

            if (empty($restoreData)) {
                return false;
            }

            $this->update($restoreData);
            $this->logAction('version_restored', [], $restoreData, "Restored to previous version from {$log->created_at}");

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to restore version: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the complete change history for a specific attribute
     * 
     * @param string $attribute
     * @return array
     */
    public function getAttributeHistory(string $attribute)
    {
        return $this->activityLogs()
            ->get()
            ->filter(function ($log) use ($attribute) {
                return isset($log->old_values[$attribute]) || isset($log->new_values[$attribute]);
            })
            ->map(function ($log) use ($attribute) {
                return [
                    'action' => $log->action,
                    'old_value' => $log->old_values[$attribute] ?? null,
                    'new_value' => $log->new_values[$attribute] ?? null,
                    'changed_by' => $log->changed_by,
                    'changed_at' => $log->created_at,
                    'description' => $log->description,
                ];
            })
            ->toArray();
    }

    /**
     * Clear activity logs for this model (use with caution)
     * 
     * @return int Number of logs deleted
     */
    public function clearActivityLogs(): int
    {
        return $this->activityLogs()->delete();
    }

    /**
     * Export activity logs as JSON
     * 
     * @return string
     */
    public function exportActivityLogsJson(): string
    {
        return json_encode(
            $this->activityLogs()
                ->get()
                ->map(fn($log) => $log->toArray())
                ->toArray(),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Get a summary of changes
     * 
     * @return array
     */
    public function getChangeSummary(): array
    {
        $logs = $this->activityLogs()->get();

        return [
            'total_actions' => $logs->count(),
            'created_at' => $logs->where('action', 'created')->first()?->created_at,
            'last_updated' => $logs->where('action', 'updated')->first()?->created_at,
            'deleted_at' => $logs->where('action', 'deleted')->first()?->created_at,
            'restored_at' => $logs->where('action', 'restored')->first()?->created_at,
            'by_action' => $logs->groupBy('action')->map->count()->toArray(),
            'by_user' => $logs->groupBy('changed_by')->map->count()->toArray(),
        ];
    }
}
