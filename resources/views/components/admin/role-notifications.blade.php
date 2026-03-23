{{-- components/admin/role-notifications.blade.php --}}
@php
$user = auth()->user();
$isFr = app()->getLocale() === 'fr';
$unreadCount = $user->notifications()->whereNull('read_at')->count();
@endphp

<div class="role-notifications-widget">
    {{-- Notifications Dropdown --}}
    <div class="dropdown" style="display:inline-block">
        <button class="btn btn-sm btn-ghost position-relative"
                type="button" id="notificationsDropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i data-lucide="bell" style="width:20px"></i>
            @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                  style="font-size:0.6rem;padding:0.25rem 0.4rem">
                {{ $unreadCount }}
            </span>
            @endif
        </button>

        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown"
             style="width:350px;max-height:400px;overflow-y:auto">
            <div class="dropdown-header">
                <strong>{{ $isFr ? 'Notifications' : 'Notifications' }}</strong>
                @if($unreadCount > 0)
                <button type="button" class="btn btn-sm btn-link"
                        onclick="markAllNotificationsRead()"
                        style="float:right;padding:0;font-size:0.8rem">
                    {{ $isFr ? 'Marquer tout comme lu' : 'Mark all as read' }}
                </button>
                @endif
            </div>

            @php
            $notifications = $user->notifications()
                ->latest()
                ->limit(10)
                ->get();
            @endphp

            @forelse($notifications as $notification)
            <a href="#" class="dropdown-item notification-item"
               data-notification-id="{{ $notification->id }}"
               style="border-bottom:1px solid var(--border);padding:1rem;text-decoration:none;color:inherit;{{ !$notification->read_at ? 'background:rgba(99,102,241,0.05)' : '' }}"
               onclick="markNotificationRead(event, '{{ $notification->id }}')">
                <div style="display:flex;align-items:flex-start;gap:0.75rem">
                    <div style="font-size:1.5rem;flex-shrink:0">
                        {{ $notification->data['icon'] ?? '📌' }}
                    </div>
                    <div style="flex:1;min-width:0">
                        <div class="fw-600" style="font-size:0.9rem">
                            {{ $notification->data['title'] ?? 'Notification' }}
                        </div>
                        <div style="font-size:0.85rem;color:var(--text-secondary);margin-top:0.25rem">
                            {{ $notification->data['message'] ?? '' }}
                        </div>
                        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.5rem">
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </a>
            @empty
            <div style="padding:2rem;text-align:center;color:var(--text-secondary)">
                <i data-lucide="inbox" style="width:24px;margin-bottom:0.5rem;opacity:0.5;display:block"></i>
                {{ $isFr ? 'Aucune notification' : 'No notifications' }}
            </div>
            @endforelse

            <div class="dropdown-divider"></div>
            <a href="{{ route('admin.dashboard') }}" class="dropdown-item text-center"
               style="padding:0.75rem;font-size:0.85rem;color:var(--primary)">
                {{ $isFr ? 'Voir tout' : 'View all' }}
            </a>
        </div>
    </div>

    {{-- Chat/Messages Dropdown --}}
    <div class="dropdown ms-2" style="display:inline-block">
        <button class="btn btn-sm btn-ghost position-relative"
                type="button" id="chatDropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i data-lucide="message-square" style="width:20px"></i>
            @php
            $unreadMessages = \App\Models\Conversation::where('user_id', $user->id)
                ->orWhereHas('participants', fn($q) => $q->where('user_id', $user->id))
                ->whereHas('messages', function($q) {
                    $q->whereNull('read_at')
                      ->whereNot('sender_id', auth()->id());
                })
                ->count();
            @endphp
            @if($unreadMessages > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success"
                  style="font-size:0.6rem;padding:0.25rem 0.4rem">
                {{ $unreadMessages }}
            </span>
            @endif
        </button>

        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="chatDropdown"
             style="width:350px;max-height:400px;overflow-y:auto">
            <div class="dropdown-header">
                <strong>{{ $isFr ? 'Messages' : 'Messages' }}</strong>
                <a href="{{ route('chat.index') }}" class="btn btn-sm btn-link"
                   style="float:right;padding:0;font-size:0.8rem">
                    <i data-lucide="external-link" style="width:14px"></i>
                </a>
            </div>

            @php
            $conversations = \App\Models\Conversation::where(function($q) {
                    $q->where('user_id', auth()->id())
                      ->orWhereHas('participants', fn($p) => $p->where('user_id', auth()->id()));
                })
                ->with(['participants', 'messages' => function($q) {
                    $q->latest()->limit(1);
                }])
                ->latest('updated_at')
                ->limit(8)
                ->get();
            @endphp

            @forelse($conversations as $conversation)
            <a href="{{ route('chat.conversations.load', $conversation->id) }}"
               class="dropdown-item conversation-item"
               style="border-bottom:1px solid var(--border);padding:1rem;text-decoration:none;color:inherit">
                <div style="display:flex;align-items:flex-start;gap:0.75rem">
                    <div style="position:relative">
                        <img src="{{ $conversation->participants->first()->user->avatar_url }}"
                             alt="{{ $conversation->participants->first()->user->name }}"
                             style="width:40px;height:40px;border-radius:50%;object-fit:cover">
                        @if($conversation->participants->first()->user->is_online)
                        <div style="position:absolute;bottom:0;right:0;width:10px;height:10px;background:#10b981;border-radius:50%;border:2px solid white"></div>
                        @endif
                    </div>
                    <div style="flex:1;min-width:0">
                        <div class="fw-600" style="font-size:0.9rem">
                            {{ $conversation->participants->first()->user->name }}
                        </div>
                        <div style="font-size:0.85rem;color:var(--text-secondary);margin-top:0.25rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $conversation->messages->first()?->content ?? $isFr ? 'Aucun message' : 'No messages' }}
                        </div>
                    </div>
                </div>
            </a>
            @empty
            <div style="padding:2rem;text-align:center;color:var(--text-secondary)">
                <i data-lucide="message-circle" style="width:24px;margin-bottom:0.5rem;opacity:0.5;display:block"></i>
                {{ $isFr ? 'Aucune conversation' : 'No conversations' }}
            </div>
            @endforelse

            <div class="dropdown-divider"></div>
            <a href="{{ route('chat.index') }}" class="dropdown-item text-center"
               style="padding:0.75rem;font-size:0.85rem;color:var(--primary)">
                {{ $isFr ? 'Ouvrir le chat' : 'Open Chat' }}
            </a>
        </div>
    </div>

    {{-- User Menu --}}
    <div class="dropdown ms-2" style="display:inline-block">
        <button class="btn btn-sm btn-ghost"
                type="button" id="userDropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                 style="width:28px;height:28px;border-radius:50%;object-fit:cover">
        </button>

        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <a class="dropdown-item" href="{{ route('profile.show') }}">
                <i data-lucide="user" style="width:16px" class="me-2"></i>
                {{ $isFr ? 'Mon Profil' : 'My Profile' }}
            </a>
            <a class="dropdown-item" href="{{ route('profile.security') }}">
                <i data-lucide="lock" style="width:16px" class="me-2"></i>
                {{ $isFr ? 'Sécurité' : 'Security' }}
            </a>
            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit" class="dropdown-item text-danger">
                    <i data-lucide="log-out" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Déconnexion' : 'Logout' }}
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function markNotificationRead(event, notificationId) {
    event.preventDefault();
    
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    }).then(response => {
        if (response.ok) {
            document.querySelector(`[data-notification-id="${notificationId}"]`)
                .style.background = '';
        }
    });
}

function markAllNotificationsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    }).then(response => {
        if (response.ok) {
            location.reload();
        }
    });
}

// Real-time notifications with WebSocket or polling
setInterval(function() {
    fetch('/api/notifications/unread-count')
        .then(r => r.json())
        .then(data => {
            const badge = document.querySelector('#notificationsDropdown .badge');
            if (data.count > 0) {
                if (!badge) {
                    const span = document.createElement('span');
                    span.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                    span.style.cssText = 'font-size:0.6rem;padding:0.25rem 0.4rem';
                    span.textContent = data.count;
                    document.getElementById('notificationsDropdown').appendChild(span);
                } else {
                    badge.textContent = data.count;
                }
            }
        });
}, 30000); // Check every 30 seconds
</script>

<style>
.role-notifications-widget {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-ghost {
    background: transparent;
    border: none;
    color: var(--text-secondary);
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.15s;
    cursor: pointer;
}

.btn-ghost:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
}

.notification-item:hover {
    background: var(--bg-secondary) !important;
}

.conversation-item:hover {
    background: var(--bg-secondary) !important;
}

.dropdown-menu {
    border: 1px solid var(--border);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>
