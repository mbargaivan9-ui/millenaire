<?php

/**
 * ═══════════════════════════════════════════════════════
 *  ROUTES DU CHAT — Millenaire Connect
 *  À ajouter dans routes/web.php
 *
 *  use App\Http\Controllers\Chat\ChatController;
 *  require base_path('routes/chat.php');
 * ═══════════════════════════════════════════════════════
 */

use App\Http\Controllers\Chat\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:60,1'])->prefix('chat')->name('chat.')->group(function () {

    // ── PAGE PRINCIPALE (tous les rôles authentifiés) ──
    Route::get('/', [ChatController::class, 'index'])
        ->name('index');

    // ── API : Conversations ──
    Route::get('/conversations', [ChatController::class, 'listConversations'])
        ->name('conversations.list');

    Route::get('/conversations/{conversation}', [ChatController::class, 'loadConversation'])
        ->name('conversations.load');

    Route::post('/conversations', [ChatController::class, 'createConversation'])
        ->name('conversations.create');

    Route::post('/conversations/{conversation}/read', [ChatController::class, 'markAsRead'])
        ->name('conversations.read');

    Route::get('/search', [ChatController::class, 'searchConversations'])
        ->name('search');

    // ── API : Messages ──
    Route::post('/messages', [ChatController::class, 'sendMessage'])
        ->name('messages.send');

    Route::delete('/messages/{message}', [ChatController::class, 'deleteMessage'])
        ->name('messages.delete');

    Route::post('/messages/{message}/react', [ChatController::class, 'react'])
        ->name('messages.react');

    // ── API : Polling (fallback sans WebSocket) ──
    Route::get('/conversations/{conversation}/poll', [ChatController::class, 'pollMessages'])
        ->name('conversations.poll');

    // ── API : Typing indicator ──
    Route::post('/typing', [ChatController::class, 'typing'])
        ->name('typing');

    // ── API : Utilisateurs disponibles ──
    Route::get('/users', [ChatController::class, 'availableUsers'])
        ->name('users');

    // ── API : Badge non-lu (topbar) ──
    Route::get('/unread', [ChatController::class, 'unreadCount'])
        ->name('unread');

    // ── Téléchargement de fichier ──
    Route::get('/attachments/{attachment}/download', [ChatController::class, 'downloadAttachment'])
        ->name('attachment.download');
});
