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

    Route::put('/messages/{message}', [ChatController::class, 'editMessage'])
        ->name('messages.edit');

    Route::delete('/messages/{message}', [ChatController::class, 'deleteMessage'])
        ->name('messages.delete');

    Route::post('/messages/{message}/delete-sender', [ChatController::class, 'deleteMessageForSender'])
        ->name('messages.delete-sender');

    Route::post('/messages/{message}/delete-all', [ChatController::class, 'deleteMessageForAll'])
        ->name('messages.delete-all');

    // ── API : Statuts de lecture ──
    Route::post('/messages/{message}/read', [ChatController::class, 'markMessageAsRead'])
        ->name('messages.mark-read');

    Route::get('/messages/{message}/read-status', [ChatController::class, 'getReadStatus'])
        ->name('messages.read-status');

    Route::post('/messages/{message}/react', [ChatController::class, 'react'])
        ->name('messages.react');

    // ── API : Appels WebRTC ──
    Route::post('/calls/initiate', [ChatController::class, 'initiateCall'])
        ->name('calls.initiate');

    Route::post('/calls/answer', [ChatController::class, 'answerCall'])
        ->name('calls.answer');

    Route::post('/calls/end', [ChatController::class, 'endCall'])
        ->name('calls.end');

    // ── API : Présence utilisateurs ──
    Route::post('/status/online', [ChatController::class, 'updateOnlineStatus'])
        ->name('status.online');

    Route::get('/conversations/{conversation}/poll', [ChatController::class, 'pollMessages'])
        ->name('conversations.poll');

    // ── API : Typing indicator ──
    Route::post('/typing', [ChatController::class, 'typing'])
        ->name('typing');

    // ── API : Utilisateurs disponibles ──
    Route::get('/users', [ChatController::class, 'availableUsers'])
        ->name('users');

    Route::get('/search-users', [ChatController::class, 'searchUsers'])
        ->name('search-users');

    // ── API : Badge non-lu (topbar) ──
    Route::get('/unread', [ChatController::class, 'unreadCount'])
        ->name('unread');

    // ── Téléchargement de fichier ──
    Route::get('/attachments/{attachment}/download', [ChatController::class, 'downloadAttachment'])
        ->name('attachment.download');
});
