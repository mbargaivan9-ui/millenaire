<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * WebPushService
 *
 * Envoie des notifications push gratuites via le protocole Web Push (VAPID).
 * Supporte Chrome, Firefox, Edge, Safari (iOS 16.4+).
 *
 * Installation requise :
 *   composer require minishlink/web-push
 *
 * Configuration :
 *   php artisan webpush:vapid   (génère les clés VAPID)
 *   Ou utiliser : https://web-push-codelab.glitch.me/
 */
class WebPushService
{
    private array $vapidKeys;
    private bool $available;

    public function __construct()
    {
        $this->vapidKeys = [
            'subject'     => config('webpush.subject', 'mailto:admin@millenaire.cm'),
            'publicKey'   => config('webpush.vapid_public_key', ''),
            'privateKey'  => config('webpush.vapid_private_key', ''),
        ];

        $this->available = class_exists(\Minishlink\WebPush\WebPush::class)
            && ! empty($this->vapidKeys['publicKey']);
    }

    // ════════════════════════════════════════════════
    //  ENVOYER UNE NOTIFICATION À UN UTILISATEUR
    // ════════════════════════════════════════════════

    /**
     * Envoie une notification push à tous les appareils d'un utilisateur.
     */
    public function sendToUser(int $userId, array $notification): array
    {
        $subscriptions = PushSubscription::where('user_id', $userId)->get();

        if ($subscriptions->isEmpty()) {
            return ['sent' => 0, 'skipped' => 0, 'errors' => []];
        }

        $results = ['sent' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($subscriptions as $sub) {
            $result = $this->sendToSubscription($sub, $notification);
            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['errors'][] = $result['error'];
            }
        }

        return $results;
    }

    /**
     * Envoie une notification à plusieurs utilisateurs.
     */
    public function sendToUsers(array $userIds, array $notification): void
    {
        foreach ($userIds as $userId) {
            try {
                $this->sendToUser($userId, $notification);
            } catch (\Exception $e) {
                Log::warning("WebPush: Erreur pour user {$userId}: " . $e->getMessage());
            }
        }
    }

    /**
     * Broadcast à tous les utilisateurs abonnés.
     */
    public function broadcast(array $notification, ?string $role = null): int
    {
        $query = PushSubscription::query();
        if ($role) {
            $query->whereHas('user', fn($q) => $q->where('role', $role));
        }

        $subscriptions = $query->with('user')->get();
        $sent = 0;

        foreach ($subscriptions as $sub) {
            $result = $this->sendToSubscription($sub, $notification);
            if ($result['success']) $sent++;
        }

        return $sent;
    }

    // ════════════════════════════════════════════════
    //  ENVOYER À UNE SOUSCRIPTION SPÉCIFIQUE
    // ════════════════════════════════════════════════

    private function sendToSubscription(PushSubscription $sub, array $notification): array
    {
        if (! $this->available) {
            // Mode démo : log seulement
            Log::info("WebPush [DEMO]: Notification à user {$sub->user_id}", $notification);
            return ['success' => true];
        }

        try {
            $webPush = new \Minishlink\WebPush\WebPush([
                'VAPID' => $this->vapidKeys,
            ]);

            $subscription = \Minishlink\WebPush\Subscription::create([
                'endpoint'        => $sub->endpoint,
                'keys'            => [
                    'p256dh' => $sub->p256dh,
                    'auth'   => $sub->auth,
                ],
                'contentEncoding' => 'aesgcm',
            ]);

            $payload = json_encode([
                'title' => $notification['title'] ?? 'Millenaire Connect',
                'body'  => $notification['body'] ?? '',
                'icon'  => $notification['icon'] ?? '/icons/icon-192.png',
                'badge' => '/icons/badge-72.png',
                'url'   => $notification['url'] ?? '/',
                'data'  => $notification['data'] ?? [],
            ]);

            $webPush->queueNotification($subscription, $payload);
            $reports = iterator_to_array($webPush->flush());
            $report  = $reports[0] ?? null;

            if ($report && ! $report->isSuccess()) {
                // Supprimer la souscription si expirée (410 Gone)
                if ($report->getResponse()?->getStatusCode() === 410) {
                    $sub->delete();
                }
                return ['success' => false, 'error' => $report->getReason()];
            }

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error("WebPush: Erreur d'envoi", ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ════════════════════════════════════════════════
    //  HELPERS POUR LES ÉVÉNEMENTS
    // ════════════════════════════════════════════════

    /** Notification : paiement confirmé */
    public function notifyPaymentConfirmed(int $userId, float $amount, string $reference): void
    {
        $this->sendToUser($userId, [
            'title' => '✅ Paiement confirmé',
            'body'  => 'Votre paiement de ' . number_format($amount, 0, ',', ' ') . ' XAF a été reçu. Réf: ' . $reference,
            'url'   => '/payment/history',
            'icon'  => '/icons/icon-192.png',
        ]);
    }

    /** Notification : note critique */
    public function notifyLowGrade(int $parentUserId, string $studentName, string $subject, float $score): void
    {
        $this->sendToUser($parentUserId, [
            'title' => '⚠️ Alerte résultat scolaire',
            'body'  => "{$studentName} a obtenu {$score}/20 en {$subject}.",
            'url'   => '/parent/monitoring',
            'icon'  => '/icons/icon-192.png',
        ]);
    }

    /** Notification : nouvelle annonce */
    public function notifyNewAnnouncement(array $userIds, string $title): void
    {
        $this->sendToUsers($userIds, [
            'title' => '📢 Nouvelle annonce',
            'body'  => $title,
            'url'   => '/announcements',
            'icon'  => '/icons/icon-192.png',
        ]);
    }

    // ════════════════════════════════════════════════
    //  GESTION DES SOUSCRIPTIONS
    // ════════════════════════════════════════════════

    public function getVapidPublicKey(): string
    {
        return $this->vapidKeys['publicKey'];
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }
}
