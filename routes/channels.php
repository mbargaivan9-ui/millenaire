<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels — Millénaire Connect
|--------------------------------------------------------------------------
| Real-time channels for chat, notifications, attendance, presence
*/

try {
    // ─── User presence channel ────────────────────────────────────────────────
    Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
        return (int) $user->id === (int) $id;
    });

    // ─── Private user notification channel ───────────────────────────────────
    Broadcast::channel('user.{userId}', function ($user, $userId) {
        return (int) $user->id === (int) $userId;
    });

    // ─── Chat conversation channel ────────────────────────────────────────────
    Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
        return $user->conversations()->where('conversations.id', $conversationId)->exists();
    });

    // ─── Class channel (attendance updates) ──────────────────────────────────
    Broadcast::channel('class.{classId}', function ($user, $classId) {
        // Teacher of the class, or admin
        if (in_array($user->role, ['admin'])) return true;

        if ($user->role === 'teacher') {
            return $user->teacher?->classes()->where('classes.id', $classId)->exists()
                || $user->teacher?->head_class_id == $classId;
        }

        if ($user->role === 'student') {
            return $user->student?->class_id == $classId;
        }

        return false;
    });

    // ─── Admin broadcast channel ──────────────────────────────────────────────
    Broadcast::channel('admin', function ($user) {
        return $user->role === 'admin';
    });

    // ─── Presence channel for online users ───────────────────────────────────
    Broadcast::channel('presence.online', function ($user) {
        return [
            'id'   => $user->id,
            'name' => $user->display_name ?? $user->name,
            'role' => $user->role,
        ];
    });
} catch (\Exception $e) {
    // Silently fail if broadcaster is not available
    // This allows the application to run even without broadcast features
}
