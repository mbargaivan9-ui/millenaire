<?php

namespace App\Contracts;

use App\Models\Notification;
use App\Models\User;

/**
 * NotificationServiceInterface
 * 
 * Contract for notification management
 * Supports all 4 roles: Admin, Teacher, Parent, Student
 */
interface NotificationServiceInterface
{
    /**
     * Send notification to a user
     * 
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param string $type
     * @param string $category
     * @param string|null $actionUrl
     * @param string|null $icon
     * @return Notification
     */
    public function send(
        int $userId,
        string $title,
        string $message,
        string $type = 'info',
        string $category = 'system',
        ?string $actionUrl = null,
        ?string $icon = null
    ): Notification;

    /**
     * Send to multiple users by role
     * 
     * @param string $role
     * @param string $title
     * @param string $message
     * @param string $type
     * @param string $category
     * @param string|null $actionUrl
     * @return int Number of notifications sent
     */
    public function sendToRole(
        string $role,
        string $title,
        string $message,
        string $type = 'info',
        string $category = 'system',
        ?string $actionUrl = null
    ): int;

    /**
     * Send to admins
     * 
     * @param string $title
     * @param string $message
     * @param string $type
     * @param string $category
     * @param string|null $actionUrl
     * @return int
     */
    public function notifyAdmins(
        string $title,
        string $message,
        string $type = 'info',
        string $category = 'system',
        ?string $actionUrl = null
    ): int;

    /**
     * Get user's unread notifications
     * 
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getUnreadNotifications(int $userId, int $limit = 10);

    /**
     * Mark notification as read
     * 
     * @param int $notificationId
     * @return bool
     */
    public function markAsRead(int $notificationId): bool;

    /**
     * Mark all notifications as read for a user
     * 
     * @param int $userId
     * @return int Number of updated notifications
     */
    public function markAllAsRead(int $userId): int;

    /**
     * Get unread count
     * 
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int;

    /**
     * Delete a notification
     * 
     * @param int $notificationId
     * @return bool
     */
    public function delete(int $notificationId): bool;

    /**
     * Payment received notification
     * 
     * @param int $userId
     * @param string $amount
     * @param string $ref
     * @return Notification
     */
    public function paymentReceived(int $userId, string $amount, string $ref): Notification;

    /**
     * New grade notification
     * 
     * @param int $userId
     * @param string $subject
     * @param float $grade
     * @return Notification
     */
    public function newGrade(int $userId, string $subject, float $grade): Notification;

    /**
     * Absence recorded notification
     * 
     * @param int $userId
     * @param string $subject
     * @param string $date
     * @return Notification
     */
    public function absenceRecorded(int $userId, string $subject, string $date): Notification;
}
