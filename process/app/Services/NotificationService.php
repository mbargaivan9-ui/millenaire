<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Envoyer une notification à un utilisateur
     */
    public static function send(
        int $userId,
        string $title,
        string $message,
        string $type     = Notification::TYPE_INFO,
        string $category = Notification::CAT_SYSTEM,
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
     * Envoyer à tous les utilisateurs d'un rôle
     */
    public static function sendToRole(
        string $role,
        string $title,
        string $message,
        string $type     = Notification::TYPE_INFO,
        string $category = Notification::CAT_SYSTEM,
        ?string $actionUrl = null
    ): int {
        $users = User::where('role', $role)->where('is_active', true)->pluck('id');
        $count = 0;
        foreach ($users as $uid) {
            self::send($uid, $title, $message, $type, $category, $actionUrl);
            $count++;
        }
        return $count;
    }

    /**
     * Envoyer à tous les admins
     */
    public static function notifyAdmins(
        string $title,
        string $message,
        string $type     = Notification::TYPE_INFO,
        string $category = Notification::CAT_SYSTEM,
        ?string $actionUrl = null
    ): int {
        $admins = User::whereIn('role', ['admin', 'censeur', 'intendant'])
            ->where('is_active', true)->pluck('id');
        $count = 0;
        foreach ($admins as $uid) {
            self::send($uid, $title, $message, $type, $category, $actionUrl);
            $count++;
        }
        return $count;
    }

    /**
     * Notification de paiement
     */
    public static function paymentReceived(int $userId, string $amount, string $ref): Notification
    {
        return self::send(
            $userId,
            'Paiement reçu',
            "Votre paiement de {$amount} XAF (réf: {$ref}) a été confirmé avec succès.",
            Notification::TYPE_SUCCESS,
            Notification::CAT_PAYMENT,
            route('student.report-cards.index')
        );
    }

    /**
     * Notification de nouvelle note
     */
    public static function newGrade(int $userId, string $subject, float $grade): Notification
    {
        return self::send(
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
     * Notification d'absence
     */
    public static function absenceRecorded(int $userId, string $subject, string $date): Notification
    {
        return self::send(
            $userId,
            'Absence enregistrée',
            "Une absence a été enregistrée en {$subject} le {$date}.",
            Notification::TYPE_WARNING,
            Notification::CAT_ABSENCE,
            null,
            'user-x'
        );
    }

    /**
     * Notification de sécurité
     */
    public static function securityAlert(int $userId, string $event, string $ip): Notification
    {
        return self::send(
            $userId,
            'Alerte de sécurité',
            "{$event} depuis l'adresse IP {$ip}. Si ce n'est pas vous, changez votre mot de passe.",
            Notification::TYPE_DANGER,
            Notification::CAT_SECURITY,
            route('profile.security'),
            'shield-check'
        );
    }

    /**
     * Notification d'annonce
     */
    public static function announcement(int $userId, string $title, string $preview): Notification
    {
        return self::send(
            $userId,
            $title,
            $preview,
            Notification::TYPE_INFO,
            Notification::CAT_ANNOUNCE,
            route('announcements.index'),
            'megaphone'
        );
    }
}
