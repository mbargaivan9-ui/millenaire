<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Centre de notifications complet
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Notification::forUser($user->id)->orderByDesc('created_at');

        // Filter by type
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            if ($request->status === 'unread') $query->unread();
            elseif ($request->status === 'read') $query->where('is_read', true);
        }

        $notifications    = $query->paginate(20);
        $unreadCount      = Notification::forUser($user->id)->unread()->count();
        $recentStream     = Notification::forUser($user->id)->recent(10)->get();
        $categories       = Notification::forUser($user->id)
                                ->selectRaw('category, COUNT(*) as count')
                                ->groupBy('category')->get();

        return view('notifications.index', compact(
            'notifications', 'unreadCount', 'recentStream', 'categories'
        ));
    }

    /**
     * Settings page: préférences de notification
     */
    public function settings()
    {
        $user = Auth::user();
        return view('notifications.settings', compact('user'));
    }

    /**
     * Sauvegarder les préférences
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $user = Auth::user();
        $user->update([
            'email_notifications'  => $request->boolean('email_notifications'),
            'push_notifications'   => $request->boolean('push_notifications'),
            'in_app_notifications' => $request->boolean('in_app_notifications'),
            'notif_security'       => $request->boolean('notif_security'),
            'notif_grades'         => $request->boolean('notif_grades'),
            'notif_payments'       => $request->boolean('notif_payments'),
            'notif_announcements'  => $request->boolean('notif_announcements'),
            'notif_messages'       => $request->boolean('notif_messages'),
            'notif_absences'       => $request->boolean('notif_absences'),
        ]);

        return response()->json(['success' => true, 'message' => 'Préférences sauvegardées']);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markRead(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== Auth::id()) abort(403);
        $notification->markAsRead();
        return response()->json(['success' => true, 'unread' => Auth::user()->getUnreadNotificationsCount()]);
    }

    /**
     * Marquer toutes comme lues
     */
    public function markAllRead(): JsonResponse
    {
        Notification::forUser(Auth::id())->unread()
            ->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Toutes les notifications marquées comme lues']);
    }

    /**
     * Supprimer une notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        if ($notification->user_id !== Auth::id()) abort(403);
        $notification->delete();
        return response()->json(['success' => true]);
    }

    /**
     * API: Récupérer les dernières notifications (topbar dropdown)
     */
    public function getLatest(): JsonResponse
    {
        $user = Auth::user();
        $notifications = Notification::forUser($user->id)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'title'      => $n->title,
                'message'    => $n->message,
                'type'       => $n->type,
                'category'   => $n->category,
                'icon'       => $n->icon,
                'color'      => $n->color,
                'bg'         => $n->bg,
                'is_read'    => $n->is_read,
                'action_url' => $n->action_url,
                'time'       => $n->created_at->diffForHumans(),
                'time_full'  => $n->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $user->getUnreadNotificationsCount(),
        ]);
    }

    /**
     * API: Récupérer les derniers messages (topbar dropdown)
     */
    public function getLatestMessages(): JsonResponse
    {
        $user = Auth::user();

        // Récupérer conversations récentes avec dernier message
        $conversations = $user->conversations()
            ->with(['messages' => fn($q) => $q->orderByDesc('created_at')->limit(1), 'messages.sender'])
            ->orderByDesc('last_message_at')
            ->limit(5)
            ->get()
            ->map(function ($conv) use ($user) {
                $lastMsg = $conv->messages->first();
                $other   = $conv->participants->where('id', '!=', $user->id)->first();
                return [
                    'id'             => $conv->id,
                    'name'           => $conv->type === 'group' ? $conv->name : ($other?->display_name ?? 'Conversation'),
                    'avatar'         => $other?->avatar_url ?? null,
                    'initials'       => $other?->initials ?? '?',
                    'last_message'   => $lastMsg?->content ?? '',
                    'time'           => $lastMsg?->created_at?->diffForHumans() ?? '',
                    'unread'         => $conv->pivot?->unread_count ?? 0,
                ];
            });

        return response()->json([
            'conversations'    => $conversations,
            'total_unread'     => $user->getUnreadMessagesCount(),
        ]);
    }
}
