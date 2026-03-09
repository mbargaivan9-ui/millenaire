<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * NotificationServiceImpl
 * 
 * Implements NotificationServiceInterface
 * Manages notifications for all 4 roles
 */
class NotificationServiceImpl implements NotificationServiceInterface
{
    /**
     * Send notification to a user
     */
    public function send(
        int $userId,
        string $title,
        string $message,
        string $type = 'info',
        string $category = 'system',
        ?string $actionUrl = null,
        ?string $icon = null
    ): Notification {
        return Notification::create([
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

    /**
     * Send to multiple users by role
     */
    public function sendToRole(
        string $role,
        string $title,
        string $message,
        string $type = 'info',
        string $category = 'system',
        ?string $actionUrl = null
    ): int {
        $users = User::where('role', $role)
            ->where('is_active', true)
            ->pluck('id');

        $count = 0;
        foreach ($users as $userId) {
            $this->send($userId, $title, $message, $type, $category, $actionUrl);
            $count++;
        }

        return $count;
    }

    /**
     * Send to admins
     */
    public function notifyAdmins(
        string $title,
        string $message,
        string $type = 'info',
        string $category = 'system',
        ?string $actionUrl = null
    ): int {
        $admins = User::whereIn('role', ['admin', 'censeur', 'intendant'])
            ->where('is_active', true)
            ->pluck('id');

        $count = 0;
        foreach ($admins as $userId) {
            $this->send($userId, $title, $message, $type, $category, $actionUrl);
            $count++;
        }

        return $count;
    }

    /**
     * Get user's unread notifications
     */
    public function getUnreadNotifications(int $userId, int $limit = 10): Collection
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): bool
    {
        return Notification::whereId($notificationId)
            ->update(['is_read' => true, 'read_at' => now()]) > 0;
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * Get unread count
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Delete a notification
     */
    public function delete(int $notificationId): bool
    {
        return Notification::whereId($notificationId)->delete() > 0;
    }

    /**
     * Payment received notification
     */
    public function paymentReceived(int $userId, string $amount, string $ref): Notification
    {
        return $this->send(
            $userId,
            'Paiement reçu',
            "Votre paiement de {$amount} XAF (réf: {$ref}) a été confirmé avec succès.",
            Notification::TYPE_SUCCESS,
            Notification::CAT_PAYMENT,
            route('student.report-cards.index')
        );
    }

    /**
     * New grade notification
     */
    public function newGrade(int $userId, string $subject, float $grade): Notification
    {
        return $this->send(
            $userId,
            'Nouvelle note disponible',
            "Votre note en {$subject} est disponible : {$grade}/20.",
            Notification::TYPE_INFO,
            Notification::CAT_GRADE,
            null,
            'book-open'
        );
    }

    /**
     * Absence recorded notification
     */
    public function absenceRecorded(int $userId, string $subject, string $date): Notification
    {
        return $this->send(
            $userId,
            'Absence enregistrée',
            "Une absence a été enregistrée en {$subject} le {$date}.",
            Notification::TYPE_WARNING,
            Notification::CAT_ABSENCE,
            null,
            'user-x'
        );
    }
}
