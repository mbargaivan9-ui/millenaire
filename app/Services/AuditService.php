<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

/**
 * AuditService
 * 
 * SOLID - Single Responsibility Principle
 * Handles audit logging for administrative governance and compliance
 * OWASP Compliance: Security Logging and Monitoring
 */
class AuditService
{
    /**
     * Log an activity/action
     * 
     * @param string $action Activity name
     * @param string $model Model name (e.g., 'Grade', 'Payment')
     * @param int $modelId ID of the model
     * @param array $changes Before/after changes
     * @param string $status 'success', 'failed', 'pending'
     * @param string|null $notes Additional notes
     * @return ActivityLog
     */
    public function log(
        string $action,
        string $model,
        int $modelId,
        array $changes = [],
        string $status = 'success',
        ?string $notes = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'changes' => json_encode($changes),
            'status' => $status,
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Log a grade entry/modification
     * 
     * @param \App\Models\Grade $grade
     * @param array $changes
     * @param string $action 'create' or 'update'
     * @return ActivityLog
     */
    public function logGradeChange($grade, array $changes = [], string $action = 'update'): ActivityLog
    {
        return $this->log(
            action: $action === 'create' ? 'GRADE_CREATED' : 'GRADE_MODIFIED',
            model: 'Grade',
            modelId: $grade->id,
            changes: $changes,
            notes: "Student: {$grade->student->user->name}, Subject: {$grade->subject->name}, Score: {$grade->score}/20"
        );
    }

    /**
     * Log a payment transaction
     * 
     * @param \App\Models\Payment $payment
     * @param string $action
     * @return ActivityLog
     */
    public function logPayment($payment, string $action = 'created'): ActivityLog
    {
        return $this->log(
            action: 'PAYMENT_' . strtoupper($action),
            model: 'Payment',
            modelId: $payment->id,
            changes: ['amount' => $payment->amount, 'status' => $payment->status],
            notes: "Student: {$payment->student->user->name}, Amount: {$payment->amount} XAF, Provider: {$payment->provider}"
        );
    }

    /**
     * Log admin action
     * 
     * @param string $action
     * @param array $data
     * @return ActivityLog
     */
    public function logAdminAction(string $action, array $data = []): ActivityLog
    {
        return $this->log(
            action: 'ADMIN_' . strtoupper($action),
            model: 'Admin',
            modelId: Auth::id(),
            changes: $data,
            notes: null
        );
    }

    /**
     * Log bulletin lock
     * 
     * @param \App\Models\ReportCard $bulletin
     * @return ActivityLog
     */
    public function logBulletinLock($bulletin): ActivityLog
    {
        return $this->log(
            action: 'BULLETIN_LOCKED',
            model: 'ReportCard',
            modelId: $bulletin->id,
            changes: ['is_locked' => true],
            notes: "Student: {$bulletin->student->user->name}, Sequence: {$bulletin->sequence}"
        );
    }

    /**
     * Log security event (failed login, unauthorized access, etc.)
     * 
     * @param string $eventType
     * @param string|null $userId
     * @param string|null $notes
     * @param string $severity 'info', 'warning', 'critical'
     * @return ActivityLog
     */
    public function logSecurityEvent(
        string $eventType,
        ?string $userId = null,
        ?string $notes = null,
        string $severity = 'warning'
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => 'SECURITY_' . strtoupper($eventType),
            'model' => 'Security',
            'model_id' => 0,
            'changes' => json_encode(['severity' => $severity]),
            'status' => 'logged',
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Log file access/download
     * 
     * @param string $filePath
     * @param string $fileType
     * @return ActivityLog
     */
    public function logFileAccess(string $filePath, string $fileType = 'unknown'): ActivityLog
    {
        return $this->log(
            action: 'FILE_ACCESSED',
            model: 'File',
            modelId: 0,
            notes: "File: {$filePath}, Type: {$fileType}"
        );
    }

    /**
     * Log data export
     * 
     * @param string $exportType
     * @param array $filters
     * @param string $format
     * @return ActivityLog
     */
    public function logExport(string $exportType, array $filters = [], string $format = 'pdf'): ActivityLog
    {
        return $this->log(
            action: 'DATA_EXPORTED',
            model: 'Export',
            modelId: 0,
            changes: ['type' => $exportType, 'format' => $format, 'filters' => $filters],
            notes: "Export Type: {$exportType}, Format: {$format}"
        );
    }

    /**
     * Get audit trail for a specific resource
     * 
     * @param string $model
     * @param int $modelId
     * @return \Illuminate\Support\Collection
     */
    public function getAuditTrail(string $model, int $modelId)
    {
        return ActivityLog::where('model', $model)
            ->where('model_id', $modelId)
            ->with('user')
            ->orderBy('timestamp', 'desc')
            ->get();
    }

    /**
     * Get recent activities
     * 
     * @param int $limit
     * @param string|null $action Filter by action
     * @return \Illuminate\Support\Collection
     */
    public function getRecentActivities(int $limit = 50, ?string $action = null)
    {
        $query = ActivityLog::with('user')
            ->orderBy('timestamp', 'desc');

        if ($action) {
            $query->where('action', $action);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get user activity (what has this user done)
     * 
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getUserActivity(int $userId, int $limit = 100)
    {
        return ActivityLog::where('user_id', $userId)
            ->orderBy('timestamp', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get security events
     * 
     * @param int|null $userId
     * @param int $days How many days back to look
     * @return \Illuminate\Support\Collection
     */
    public function getSecurityEvents(?int $userId = null, int $days = 30)
    {
        $query = ActivityLog::where('action', 'LIKE', 'SECURITY_%')
            ->where('timestamp', '>=', now()->subDays($days))
            ->orderBy('timestamp', 'desc');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    /**
     * Generate audit report
     * 
     * @param array $filters
     * @return array
     */
    public function generateAuditReport(array $filters = []): array
    {
        $query = ActivityLog::query();

        if (isset($filters['start_date'])) {
            $query->where('timestamp', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('timestamp', '<=', $filters['end_date']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $activities = $query->orderBy('timestamp', 'desc')->get();

        return [
            'total_activities' => $activities->count(),
            'by_action' => $activities->groupBy('action')->map->count(),
            'by_user' => $activities->groupBy('user_id')->map->count(),
            'by_status' => $activities->groupBy('status')->map->count(),
            'activities' => $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'user' => $activity->user->name ?? 'System',
                    'action' => $activity->action,
                    'model' => $activity->model,
                    'status' => $activity->status,
                    'timestamp' => $activity->timestamp->format('Y-m-d H:i:s'),
                    'ip' => $activity->ip_address,
                ];
            }),
        ];
    }
}
