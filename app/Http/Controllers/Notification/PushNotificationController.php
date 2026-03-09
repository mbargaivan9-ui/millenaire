<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * PushNotificationController
 *
 * Gère les abonnements aux notifications push (Web Push API).
 * Le navigateur s'abonne → enregistré en base → notification envoyée côté serveur.
 */
class PushNotificationController extends Controller
{
    public function __construct(private readonly WebPushService $pushService) {}

    // ════════════════════════════════════════════════
    //  CLÉ VAPID PUBLIQUE (pour le JS du navigateur)
    // ════════════════════════════════════════════════

    public function vapidKey(): JsonResponse
    {
        return response()->json([
            'publicKey' => $this->pushService->getVapidPublicKey(),
        ]);
    }

    // ════════════════════════════════════════════════
    //  S'ABONNER
    // ════════════════════════════════════════════════

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint'        => 'required|url',
            'keys.p256dh'     => 'required|string',
            'keys.auth'       => 'required|string',
            'user_agent'      => 'nullable|string|max:300',
        ]);

        PushSubscription::updateOrCreate(
            [
                'user_id'  => Auth::id(),
                'endpoint' => $validated['endpoint'],
            ],
            [
                'p256dh'     => $validated['keys']['p256dh'],
                'auth'       => $validated['keys']['auth'],
                'user_agent' => $request->header('User-Agent', '') ?: ($validated['user_agent'] ?? ''),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Notifications activées.',
        ]);
    }

    // ════════════════════════════════════════════════
    //  SE DÉSABONNER
    // ════════════════════════════════════════════════

    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        PushSubscription::where('user_id', Auth::id())
            ->where('endpoint', $validated['endpoint'])
            ->delete();

        return response()->json(['success' => true, 'message' => 'Notifications désactivées.']);
    }

    // ════════════════════════════════════════════════
    //  TEST (admin uniquement)
    // ════════════════════════════════════════════════

    public function test(Request $request): JsonResponse
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $userId = $request->input('user_id', Auth::id());

        $result = $this->pushService->sendToUser($userId, [
            'title' => '🔔 Test — Millenaire Connect',
            'body'  => 'Vos notifications push fonctionnent correctement !',
            'url'   => '/',
        ]);

        return response()->json([
            'success' => true,
            'result'  => $result,
        ]);
    }

    // ════════════════════════════════════════════════
    //  STATUT (est-ce que cet utilisateur est abonné ?)
    // ════════════════════════════════════════════════

    public function status(): JsonResponse
    {
        $count = PushSubscription::where('user_id', Auth::id())->count();

        return response()->json([
            'subscribed'         => $count > 0,
            'subscription_count' => $count,
            'vapid_available'    => $this->pushService->isAvailable(),
        ]);
    }
}
