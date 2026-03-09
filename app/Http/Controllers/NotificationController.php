<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * NotificationController — Gestion des Notifications
 */
class NotificationController extends Controller
{
    /**
     * Affiche la page de toutes les notifications.
     */
    public function index(): View
    {
        $notifications = auth()->user()->notifications()
            ->latest()
            ->paginate(25)
            ->through(fn($n) => (object)[
                'id'         => $n->id,
                'is_read'    => $n->read_at !== null,
                'title'      => $n->data['title'] ?? 'Notification',
                'message'    => $n->data['message'] ?? '',
                'type'       => $n->data['type'] ?? 'info',
                'category'   => $n->data['category'] ?? 'system',
                'created_at' => $n->created_at,
            ]);

        $unreadCount = auth()->user()->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Marquer une notification comme lue.
     */
    public function markRead(string $id): JsonResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Marquer toutes les notifications comme lues.
     */
    public function markAllRead(): JsonResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'fr' ? 'Toutes les notifications ont été lues.' : 'All notifications marked as read.',
        ]);
    }

    /**
     * Retourner les notifications non lues (pour header badge).
     */
    public function unread(): JsonResponse
    {
        $notifications = auth()->user()->unreadNotifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'title'      => $n->data['title'] ?? 'Notification',
                'message'    => $n->data['message'] ?? '',
                'icon'       => $n->data['icon'] ?? 'bell',
                'url'        => $n->data['url'] ?? '#',
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'count'         => auth()->user()->unreadNotifications()->count(),
        ]);
    }
}
