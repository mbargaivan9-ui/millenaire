<header class="topbar" id="topbar">

  {{-- Sidebar Toggle --}}
  <button class="topbar-toggle" data-sidebar-toggle aria-label="Toggle sidebar">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
      <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
    </svg>
  </button>

  {{-- Search --}}
  <div class="topbar-search">
    <svg class="topbar-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
      fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="text" placeholder="{{ __('app.search_placeholder') ?? 'Rechercher...' }}" id="global-search">
    <span class="topbar-kbd">/</span>
  </div>

  {{-- Home Link --}}
  <a href="{{ route('home') }}" class="topbar-btn" title="{{ app()->getLocale() === 'fr' ? 'Retour à l\'accueil' : 'Back to Home' }}" style="gap: 6px;">
    <i data-lucide="home"></i>
    <span style="font-size: 12px; font-weight: 600; display: none; margin-right: 4px;">{{ app()->getLocale() === 'fr' ? 'Accueil' : 'Home' }}</span>
  </a>

  {{-- Actions --}}
  <div class="topbar-actions">

    {{-- Language Switcher --}}
    <div class="dropdown">
      <div class="topbar-lang" data-dropdown="lang-menu">
        @if(app()->getLocale() === 'fr')
          <span>🇫🇷</span><span>FR</span>
        @else
          <span>🇺🇸</span><span>EN</span>
        @endif
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
          fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </div>
      <div class="dropdown-menu" id="lang-menu" style="min-width:160px">
        <div class="dropdown-item" data-lang="fr" style="gap:10px">
          <span>🇫🇷</span>
          <span style="font-size:13px;font-weight:500">Français</span>
          @if(app()->getLocale() === 'fr')
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
              fill="none" stroke="var(--primary)" stroke-width="2.5" style="margin-left:auto">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          @endif
        </div>
        <div class="dropdown-item" data-lang="en" style="gap:10px">
          <span>🇺🇸</span>
          <span style="font-size:13px;font-weight:500">English</span>
          @if(app()->getLocale() === 'en')
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
              fill="none" stroke="var(--primary)" stroke-width="2.5" style="margin-left:auto">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          @endif
        </div>
      </div>
    </div>

    <div class="topbar-divider"></div>

    {{-- Theme Toggle --}}
    <button class="topbar-btn" data-theme-toggle title="{{ __('app.toggle_theme') ?? 'Toggle theme' }}">
      <i data-lucide="sun" id="theme-icon-light"></i>
      <i data-lucide="moon" id="theme-icon-dark" style="display:none"></i>
    </button>

    {{-- ═══ NOTIFICATIONS DROPDOWN ═══ --}}
    <div class="dropdown" id="notif-dropdown-wrap">
      <button class="topbar-btn" data-dropdown="notif-menu" title="Notifications" id="notif-trigger">
        <i data-lucide="bell"></i>
        @php $unreadNotif = auth()->user()?->getUnreadNotificationsCount() ?? 0; @endphp
        @if($unreadNotif > 0)
          <span class="topbar-badge" id="notif-badge">{{ $unreadNotif > 9 ? '9+' : $unreadNotif }}</span>
        @else
          <span class="topbar-badge" id="notif-badge" style="display:none">0</span>
        @endif
      </button>

      <div class="dropdown-menu notif-dropdown" id="notif-menu" style="min-width:380px;right:0;max-height:520px;overflow:hidden;display:flex;flex-direction:column">
        {{-- Header --}}
        <div style="padding:16px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
          <div>
            <span style="font-size:15px;font-weight:700;color:var(--text-primary)">Notifications</span>
            <span id="notif-unread-label" style="margin-left:8px;font-size:11px;font-weight:700;background:var(--danger);color:#fff;padding:2px 8px;border-radius:var(--radius-full)">{{ $unreadNotif > 0 ? $unreadNotif.' non lues' : '' }}</span>
          </div>
          <button onclick="markAllReadDropdown()" style="font-size:12px;font-weight:600;color:var(--primary);background:none;border:none;cursor:pointer">
            Tout marquer lu
          </button>
        </div>

        {{-- Items list --}}
        <div id="notif-list" style="overflow-y:auto;flex:1">
          <div style="padding:32px;text-align:center;color:var(--text-muted);font-size:13px" id="notif-loading">
            <i data-lucide="loader" style="width:20px;height:20px;animation:spin 1s linear infinite"></i>
          </div>
        </div>

        {{-- Footer --}}
        <div style="padding:12px 18px;border-top:1px solid var(--border);text-align:center;flex-shrink:0">
          <a href="{{ route('notifications.index') }}" style="font-size:13px;font-weight:600;color:var(--primary);text-decoration:none">
            Voir toutes les notifications →
          </a>
        </div>
      </div>
    </div>

    {{-- ═══ MESSAGES DROPDOWN ═══ --}}
    <div class="dropdown" id="msg-dropdown-wrap">
      <button class="topbar-btn" data-dropdown="msg-menu" title="Messages" id="msg-trigger">
        <i data-lucide="mail"></i>
        @php $unreadMsg = auth()->user()?->getUnreadMessagesCount() ?? 0; @endphp
        @if($unreadMsg > 0)
          <span class="topbar-badge" style="background:var(--info)" id="msg-badge">{{ $unreadMsg > 9 ? '9+' : $unreadMsg }}</span>
        @else
          <span class="topbar-badge" style="background:var(--info)" id="msg-badge" style="display:none">0</span>
        @endif
      </button>

      <div class="dropdown-menu" id="msg-menu" style="min-width:360px;right:0;max-height:480px;overflow:hidden;display:flex;flex-direction:column">
        {{-- Header --}}
        <div style="padding:16px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
          <span style="font-size:15px;font-weight:700;color:var(--text-primary)">Messages</span>
          <a href="{{ route('messages.index') }}" style="font-size:12px;font-weight:600;color:var(--primary);text-decoration:none">Ouvrir le chat</a>
        </div>

        {{-- Messages list --}}
        <div id="msg-list" style="overflow-y:auto;flex:1">
          <div style="padding:32px;text-align:center;color:var(--text-muted);font-size:13px" id="msg-loading">
            <i data-lucide="loader" style="width:20px;height:20px;animation:spin 1s linear infinite"></i>
          </div>
        </div>

        {{-- Footer --}}
        <div style="padding:12px 18px;border-top:1px solid var(--border);text-align:center;flex-shrink:0">
          <a href="{{ route('messages.index') }}" style="font-size:13px;font-weight:600;color:var(--primary);text-decoration:none">
            Voir tous les messages →
          </a>
        </div>
      </div>
    </div>

    <div class="topbar-divider"></div>

    {{-- ═══ USER MENU ═══ --}}
    <div class="dropdown">
      <div class="topbar-user" data-dropdown="user-menu">
        @if(auth()->user()?->profile_photo)
          <img src="{{ auth()->user()->avatar_url }}" class="avatar-md" alt="{{ auth()->user()->name }}"
            style="border-radius:50%;width:36px;height:36px;object-fit:cover">
        @else
          <div class="user-avatar avatar-md" style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center">
            {{ auth()->user()?->initials ?? 'U' }}
          </div>
        @endif
        <div>
          <div class="topbar-user-name">{{ auth()->user()?->display_name ?? auth()->user()?->name ?? 'User' }}</div>
          <div class="topbar-user-role">{{ auth()->user()?->role_label ?? auth()->user()?->role ?? 'guest' }}</div>
        </div>
      </div>

      <div class="dropdown-menu" id="user-menu" style="min-width:280px;right:0">
        {{-- User info header --}}
        <div style="padding:16px 18px;border-bottom:1px solid var(--border)">
          <div style="display:flex;align-items:center;gap:12px">
            @if(auth()->user()?->profile_photo)
              <img src="{{ auth()->user()->avatar_url }}" style="width:42px;height:42px;border-radius:50%;object-fit:cover" alt="">
            @else
              <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;font-size:15px;font-weight:700;display:flex;align-items:center;justify-content:center">
                {{ auth()->user()?->initials ?? 'U' }}
              </div>
            @endif
            <div>
              <div style="font-size:14px;font-weight:700;color:var(--text-primary)">{{ auth()->user()?->display_name ?? auth()->user()?->name }}</div>
              <div style="font-size:12px;color:var(--text-muted)">{{ auth()->user()?->email }}</div>
            </div>
          </div>
          {{-- Profile score --}}
          @php $score = auth()->user()?->profile_score ?? 0; @endphp
          <div style="margin-top:10px">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px">
              <span style="font-size:11px;color:var(--text-muted)">Score profil</span>
              <span style="font-size:11px;font-weight:700;color:var(--primary)">{{ $score }}%</span>
            </div>
            <div style="height:4px;background:var(--border);border-radius:999px;overflow:hidden">
              <div style="height:100%;width:{{ $score }}%;background:linear-gradient(90deg,var(--primary),var(--primary-light));border-radius:999px"></div>
            </div>
          </div>
        </div>

        {{-- Menu items --}}
        <a href="{{ route('profile.show') }}" class="dropdown-item">
          <div class="dropdown-item-icon" style="background:var(--primary-bg)">
            <i data-lucide="user" style="width:15px;height:15px;color:var(--primary)"></i>
          </div>
          <div>
            <div class="dropdown-item-title">Mon Profil</div>
            <div class="dropdown-item-desc">Voir et modifier mon profil</div>
          </div>
        </a>

        <a href="{{ route('notifications.settings') }}" class="dropdown-item">
          <div class="dropdown-item-icon" style="background:var(--warning-bg)">
            <i data-lucide="bell" style="width:15px;height:15px;color:var(--warning)"></i>
          </div>
          <div>
            <div class="dropdown-item-title">Notifications</div>
            <div class="dropdown-item-desc">Gérer mes préférences</div>
          </div>
        </a>

        <a href="{{ route('profile.security') }}" class="dropdown-item">
          <div class="dropdown-item-icon" style="background:var(--info-bg)">
            <i data-lucide="shield" style="width:15px;height:15px;color:var(--info)"></i>
          </div>
          <div>
            <div class="dropdown-item-title">Sécurité</div>
            <div class="dropdown-item-desc">Mot de passe & sessions</div>
          </div>
        </a>

        @if(auth()->user()?->isAdmin() && Route::has('admin.settings.edit'))
        <a href="{{ route('admin.settings.edit') }}" class="dropdown-item">
          <div class="dropdown-item-icon" style="background:var(--surface-2)">
            <i data-lucide="settings" style="width:15px;height:15px;color:var(--text-muted)"></i>
          </div>
          <div>
            <div class="dropdown-item-title">Paramètres</div>
            <div class="dropdown-item-desc">Configuration système</div>
          </div>
        </a>
        @endif

        <div style="border-top:1px solid var(--border);padding:10px 14px">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="width:100%;display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:var(--radius);border:none;background:var(--danger-bg);color:var(--danger);font-size:13px;font-weight:600;cursor:pointer;transition:var(--transition)">
              <i data-lucide="log-out" style="width:15px;height:15px"></i>
              Déconnexion
            </button>
          </form>
        </div>
      </div>
    </div>

  </div>{{-- end topbar-actions --}}

</header>

@push('styles')
<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.notif-dropdown-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 13px 18px;
  border-bottom: 1px solid var(--border-light);
  cursor: pointer;
  transition: var(--transition);
}
.notif-dropdown-item:hover { background: var(--surface-2); }
.notif-dropdown-item.unread { background: var(--primary-bg); border-left: 3px solid var(--primary); }
.notif-dropdown-item.unread:hover { background: var(--primary-hover); }
.notif-icon-sm {
  width: 36px; height: 36px; border-radius: var(--radius-sm);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.msg-dropdown-item {
  display: flex; align-items: center; gap: 12px;
  padding: 13px 18px; border-bottom: 1px solid var(--border-light);
  cursor: pointer; transition: var(--transition); text-decoration: none;
}
.msg-dropdown-item:hover { background: var(--surface-2); }
.msg-avatar {
  width: 38px; height: 38px; border-radius: 50%; object-fit: cover;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: #fff; font-size: 13px; font-weight: 700;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
</style>
@endpush

@push('scripts')
<script>
// ─── Notification Dropdown Logic ─────────────────────────────
let notifLoaded = false;
let msgLoaded   = false;
const CSRF_T    = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// Load notifications when dropdown opens
document.addEventListener('DOMContentLoaded', function () {

  // Observe notif dropdown open
  const notifObs = new MutationObserver(() => {
    const menu = document.getElementById('notif-menu');
    if (menu && menu.classList.contains('show') && !notifLoaded) {
      loadNotifications();
      notifLoaded = true;
    }
  });
  const notifMenu = document.getElementById('notif-menu');
  if (notifMenu) notifObs.observe(notifMenu, { attributes: true, attributeFilter: ['class'] });

  // Observe msg dropdown open
  const msgObs = new MutationObserver(() => {
    const menu = document.getElementById('msg-menu');
    if (menu && menu.classList.contains('show') && !msgLoaded) {
      loadMessages();
      msgLoaded = true;
    }
  });
  const msgMenu = document.getElementById('msg-menu');
  if (msgMenu) msgObs.observe(msgMenu, { attributes: true, attributeFilter: ['class'] });

  // Polling every 90 seconds
  setInterval(refreshBadges, 90000);
});

async function loadNotifications() {
  try {
    const r = await fetch('/api/notifications/latest', {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_T }
    });
    const data = await r.json();
    renderNotifications(data.notifications, data.unread_count);
  } catch(e) {
    document.getElementById('notif-list').innerHTML =
      '<div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">Erreur de chargement</div>';
  }
}

function renderNotifications(items, unreadCount) {
  const list = document.getElementById('notif-list');
  updateNotifBadge(unreadCount);

  if (!items || items.length === 0) {
    list.innerHTML = `
      <div style="padding:40px;text-align:center">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none"
          stroke="var(--text-muted)" stroke-width="1.5" style="margin-bottom:10px">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          <line x1="2" y1="2" x2="22" y2="22"/>
        </svg>
        <p style="font-size:13px;color:var(--text-muted)">Aucune notification</p>
      </div>`;
    return;
  }

  const iconColors = { success: 'var(--success)', warning: 'var(--warning)', danger: 'var(--danger)', info: 'var(--info)', primary: 'var(--primary)' };
  const bgColors   = { success: 'var(--success-bg)', warning: 'var(--warning-bg)', danger: 'var(--danger-bg)', info: 'var(--info-bg)', primary: 'var(--primary-bg)' };

  list.innerHTML = items.map(n => `
    <div class="notif-dropdown-item ${!n.is_read ? 'unread' : ''}" id="nd-${n.id}"
      onclick="handleNotifClick(${n.id}, '${n.action_url ?? ''}', ${n.is_read})">
      <div class="notif-icon-sm" style="background:${bgColors[n.type] ?? 'var(--primary-bg)'}">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
          fill="none" stroke="${iconColors[n.type] ?? 'var(--primary)'}" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          ${getLucidePath(n.icon)}
        </svg>
      </div>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:${n.is_read ? '500' : '700'};color:var(--text-primary);margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
          ${n.title}
        </div>
        <div style="font-size:11px;color:var(--text-secondary);line-height:1.4;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical">
          ${n.message}
        </div>
        <div style="font-size:11px;color:var(--text-muted);margin-top:4px">${n.time}</div>
      </div>
      ${!n.is_read ? '<div style="width:7px;height:7px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:5px"></div>' : ''}
    </div>
  `).join('');
}

async function handleNotifClick(id, url, isRead) {
  if (!isRead) {
    await fetch(`/notifications/${id}/mark-read`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF_T, 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
      const el = document.getElementById(`nd-${id}`);
      if (el) {
        el.classList.remove('unread');
        el.querySelector('div[style*="border-radius:50%"]')?.remove();
      }
      updateNotifBadge(data.unread);
    });
  }
  if (url && url !== 'undefined' && url !== '') {
    window.location.href = url;
  }
  notifLoaded = false; // force reload next time
}

async function markAllReadDropdown() {
  const r = await fetch('/notifications/mark-all-read', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF_T, 'Accept': 'application/json' }
  });
  const data = await r.json();
  if (data.success) {
    document.querySelectorAll('.notif-dropdown-item.unread').forEach(el => {
      el.classList.remove('unread');
    });
    updateNotifBadge(0);
    notifLoaded = false;
  }
}

function updateNotifBadge(count) {
  const badge = document.getElementById('notif-badge');
  const label = document.getElementById('notif-unread-label');
  if (badge) {
    if (count > 0) {
      badge.textContent = count > 9 ? '9+' : count;
      badge.style.display = 'flex';
    } else {
      badge.style.display = 'none';
    }
  }
  if (label) {
    label.textContent = count > 0 ? `${count} non lue${count > 1 ? 's' : ''}` : '';
  }
}

async function loadMessages() {
  try {
    const r = await fetch('/api/messages/latest', {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_T }
    });
    const data = await r.json();
    renderMessages(data.conversations, data.total_unread);
  } catch(e) {
    document.getElementById('msg-list').innerHTML =
      '<div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">Erreur de chargement</div>';
  }
}

function renderMessages(convs, totalUnread) {
  const list = document.getElementById('msg-list');
  const badge = document.getElementById('msg-badge');

  if (badge) {
    if (totalUnread > 0) {
      badge.textContent = totalUnread > 9 ? '9+' : totalUnread;
      badge.style.display = 'flex';
    } else {
      badge.style.display = 'none';
    }
  }

  if (!convs || convs.length === 0) {
    list.innerHTML = `
      <div style="padding:40px;text-align:center">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none"
          stroke="var(--text-muted)" stroke-width="1.5" style="margin-bottom:10px">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <p style="font-size:13px;color:var(--text-muted)">Aucun message</p>
      </div>`;
    return;
  }

  list.innerHTML = convs.map(c => `
    <a href="/messages/${c.id}" class="msg-dropdown-item">
      ${c.avatar
        ? `<img src="${c.avatar}" class="msg-avatar" alt="${c.name}">`
        : `<div class="msg-avatar">${c.initials}</div>`
      }
      <div style="flex:1;min-width:0">
        <div style="display:flex;justify-content:space-between;margin-bottom:3px">
          <span style="font-size:13px;font-weight:700;color:var(--text-primary)">${c.name}</span>
          <span style="font-size:11px;color:var(--text-muted)">${c.time}</span>
        </div>
        <div style="font-size:12px;color:var(--text-secondary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
          ${c.last_message}
        </div>
      </div>
      ${c.unread > 0
        ? `<span style="min-width:20px;height:20px;border-radius:999px;background:var(--primary);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 5px">${c.unread}</span>`
        : ''
      }
    </a>
  `).join('');
}

async function refreshBadges() {
  try {
    const r = await fetch('/api/notifications/latest', {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_T }
    });
    const data = await r.json();
    updateNotifBadge(data.unread_count);
    notifLoaded = false; // force reload on next open
  } catch(e) {}
}

// Lucide path lookup for dynamic SVG rendering
function getLucidePath(icon) {
  const paths = {
    'bell'          : '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
    'book-open'     : '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
    'credit-card'   : '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
    'user-x'        : '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" y1="8" x2="23" y2="14"/><line x1="23" y1="8" x2="17" y2="14"/>',
    'message-circle': '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
    'shield-check'  : '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
    'megaphone'     : '<path d="M3 11l19-9-9 19-2-8-8-2z"/>',
    'settings'      : '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
    'check-circle'  : '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
  };
  return paths[icon] ?? paths['bell'];
}
</script>
@endpush
