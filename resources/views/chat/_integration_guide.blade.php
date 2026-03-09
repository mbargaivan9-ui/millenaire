{{--
═══════════════════════════════════════════════════════
  INTEGRATION GUIDE — Chat dans les layouts existants
═══════════════════════════════════════════════════════

1. AJOUTER LE LIEN CHAT DANS LA SIDEBAR
   Ouvrir : resources/views/layouts/partials/sidebar.blade.php
   Remplacer l'ancien lien "messages" par ce bloc :
═══════════════════════════════════════════════════════
--}}

{{-- ── Bloc à insérer dans sidebar.blade.php (remplace le lien messages existant) ── --}}
@php
    $chatUnread = 0;
    if (auth()->check()) {
        $chatUnread = \Illuminate\Support\Facades\DB::table('conversation_participants')
            ->where('user_id', auth()->id())
            ->sum('unread_count') ?: 0;
    }
@endphp

<a href="{{ route('chat.index') }}"
   class="sidebar-item {{ request()->routeIs('chat.*') ? 'active' : '' }}"
   id="sidebarChatLink">
    <span class="sidebar-icon"><i data-lucide="message-square"></i></span>
    <span class="sidebar-label">Messages</span>
    @if($chatUnread > 0)
        <span class="sidebar-badge"
              id="sidebarChatBadge"
              style="background:#1abc9c;color:#fff;border-radius:10px;font-size:.68rem;min-width:18px;height:18px;display:flex;align-items:center;justify-content:center;padding:0 4px;font-weight:700;margin-left:auto">
            {{ $chatUnread > 99 ? '99+' : $chatUnread }}
        </span>
    @else
        <span class="sidebar-badge" id="sidebarChatBadge" style="display:none;background:#1abc9c;color:#fff;border-radius:10px;font-size:.68rem;min-width:18px;height:18px;align-items:center;justify-content:center;padding:0 4px;font-weight:700;margin-left:auto"></span>
    @endif
</a>

{{--
═══════════════════════════════════════════════════════
  2. BADGE DANS LA TOPBAR
   Ouvrir : resources/views/layouts/partials/topbar.blade.php
   Ajouter ce bloc dans les actions de la topbar (à côté de la cloche) :
═══════════════════════════════════════════════════════
--}}

{{-- ── Bloc à insérer dans topbar.blade.php ── --}}
<a href="{{ route('chat.index') }}" class="topbar-icon-btn" style="position:relative" title="Messages">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
    </svg>
    @if(isset($chatUnread) && $chatUnread > 0)
        <span data-chat-unread
              style="position:absolute;top:-4px;right:-4px;background:#ef4444;color:#fff;border-radius:8px;font-size:.62rem;min-width:16px;height:16px;display:flex;align-items:center;justify-content:center;font-weight:700;border:2px solid #fff">
            {{ $chatUnread > 9 ? '9+' : $chatUnread }}
        </span>
    @else
        <span data-chat-unread
              style="position:absolute;top:-4px;right:-4px;background:#ef4444;color:#fff;border-radius:8px;font-size:.62rem;min-width:16px;height:16px;display:none;align-items:center;justify-content:center;font-weight:700;border:2px solid #fff">
        </span>
    @endif
</a>

{{--
═══════════════════════════════════════════════════════
  3. POLLING DU BADGE (dans app.blade.php, avant </body>)
   Ce script met à jour le badge non-lu toutes les 30s
   sur toutes les pages (pas seulement sur /chat)
═══════════════════════════════════════════════════════
--}}
@auth
<script>
(function() {
    function pollChatUnread() {
        fetch('{{ route('chat.unread') }}', {
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            const count = data.count || 0;

            // Badge topbar
            const topBadge = document.querySelector('[data-chat-unread]');
            if (topBadge) {
                topBadge.textContent = count > 9 ? '9+' : (count || '');
                topBadge.style.display = count > 0 ? 'flex' : 'none';
            }

            // Badge sidebar
            const sideBadge = document.getElementById('sidebarChatBadge');
            if (sideBadge) {
                sideBadge.textContent = count > 99 ? '99+' : (count || '');
                sideBadge.style.display = count > 0 ? 'flex' : 'none';
            }
        })
        .catch(() => {});
    }

    // Lancer après 5s puis toutes les 30s
    setTimeout(pollChatUnread, 5000);
    setInterval(pollChatUnread, 30000);
})();
</script>
@endauth

{{--
═══════════════════════════════════════════════════════
  4. ROUTES/WEB.PHP — Ajouter ces lignes
═══════════════════════════════════════════════════════

use App\Http\Controllers\Chat\ChatController;

// Inclure le fichier de routes du chat
require base_path('routes/chat.php');

Ou ajouter manuellement dans le groupe Route::middleware(['auth']) :

Route::prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('/conversations', [ChatController::class, 'listConversations'])->name('conversations.list');
    Route::get('/conversations/{conversation}', [ChatController::class, 'loadConversation'])->name('conversations.load');
    Route::post('/conversations', [ChatController::class, 'createConversation'])->name('conversations.create');
    Route::post('/conversations/{conversation}/read', [ChatController::class, 'markAsRead'])->name('conversations.read');
    Route::get('/conversations/{conversation}/poll', [ChatController::class, 'pollMessages'])->name('conversations.poll');
    Route::get('/search', [ChatController::class, 'searchConversations'])->name('search');
    Route::post('/messages', [ChatController::class, 'sendMessage'])->name('messages.send');
    Route::delete('/messages/{message}', [ChatController::class, 'deleteMessage'])->name('messages.delete');
    Route::post('/messages/{message}/react', [ChatController::class, 'react'])->name('messages.react');
    Route::post('/typing', [ChatController::class, 'typing'])->name('typing');
    Route::get('/users', [ChatController::class, 'availableUsers'])->name('users');
    Route::get('/unread', [ChatController::class, 'unreadCount'])->name('unread');
    Route::get('/attachments/{attachment}/download', [ChatController::class, 'downloadAttachment'])->name('attachment.download');
});

═══════════════════════════════════════════════════════
  5. APPEL DANS PAGE.CONTENT_FULL (sans padding)
   Si tu veux le chat en pleine hauteur sans padding :
═══════════════════════════════════════════════════════

Dans le layout app.blade.php, ajouter une section optionnelle :

@if(request()->routeIs('chat.*'))
    @yield('content')
@else
    <main class="page-content">
        @yield('content')
    </main>
@endif

--}}
