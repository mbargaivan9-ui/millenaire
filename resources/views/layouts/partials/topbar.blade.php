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
  <div  class="topbar-search">
    <svg class="topbar-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
      fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="text" placeholder="{{ __('search') ?? 'Rechercher...' }}" id="global-search">
    <span class="topbar-kbd">/</span>
  </div>

  {{-- Actions --}}
  <div class="topbar-actions">

    {{-- Language Switcher --}}
    <div class="dropdown">
      <div class="topbar-lang" data-dropdown="lang-menu">
        @if(app()->getLocale() === 'fr')
          <span>🇫🇷</span><span><i data-lucide="french-flag"></i>FR</span>
        @else
          <span>🇺🇸</span><span><i data-lucide="usa-flag"></i>EN</span>
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
    <button class="topbar-btn" data-theme-toggle title="{{ __('toggle theme') ?? 'Toggle theme' }}">
      <i data-lucide="sun" id="theme-icon-light"></i>
      <i data-lucide="moon" id="theme-icon-dark" style="display:none"></i>
    </button>

    {{-- Notifications --}}
    <div class="dropdown">
      <button class="topbar-btn" data-dropdown="notif-menu" title="{{ __('notifications') ?? 'Notifications' }}">
        <i data-lucide="bell"></i>
        @php $unreadNotif = auth()->user()?->unread_notifications_count ?? 0; @endphp
        @if($unreadNotif > 0)
          <span class="topbar-badge">{{ $unreadNotif > 9 ? '9+' : $unreadNotif }}</span>
        @endif
      </button>
      <div class="dropdown-menu" id="notif-menu" style="min-width:340px;right:0">
        <div class="dropdown-header">
          <span class="dropdown-title">{{ __('notifications') ?? 'Notifications' }}</span>
        </div>
        @forelse(auth()->user()?->notifications?->take(5) ?? [] as $notif)
        <div class="dropdown-item">
          <div class="dropdown-item-icon" style="background:var(--primary-bg)">
            <i data-lucide="bell"
               style="width:16px;height:16px;color:var(--primary)"></i>
          </div>
          <div style="flex:1;min-width:0">
            <div class="dropdown-item-title">{{ $notif->title ?? $notif->data['title'] ?? 'Notification' }}</div>
            <div class="dropdown-item-desc">{{ substr($notif->body ?? $notif->data['body'] ?? '', 0, 50) }}...</div>
          </div>
          <span class="dropdown-item-time">{{ $notif->created_at->diffForHumans(null, true) }}</span>
        </div>
        @empty
        <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">
          {{ __('app.no_notifications') ?? 'No notifications' }}
        </div>
        @endforelse
        <div class="dropdown-footer">
          <a href="{{ route('notifications.index') }}">{{ __('view all notifications') ?? 'View all' }}</a>
        </div>
      </div>
    </div>

    {{-- Messages --}}
    <div class="dropdown">
      <button class="topbar-btn" data-dropdown="msg-menu" title="{{ __('messages') ?? 'Messages' }}">
        <i data-lucide="mail"></i>
        @php $unreadMsg = auth()->user()?->unread_messages_count ?? 0; @endphp
        @if($unreadMsg > 0)
          <span class="topbar-badge" style="background:var(--info)">{{ $unreadMsg > 9 ? '9+' : $unreadMsg }}</span>
        @endif
      </button>
      <div class="dropdown-menu" id="msg-menu" style="min-width:300px">
        <div class="dropdown-header">
          <span class="dropdown-title">{{ __('messages') ?? 'Messages' }}</span>
          <a class="dropdown-link" href="{{ route('messages.index') }}">{{ __('view all') ?? 'View all' }}</a>
        </div>
        <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px">
          {{ __('No messages') ?? 'No new messages' }}
        </div>
        <div class="dropdown-footer">
          <a href="{{ route('messages.index') }}">{{ __('open inbox') ?? 'Open Inbox' }}</a>
        </div>
      </div>
    </div>

    <div class="topbar-divider"></div>

    {{-- User Menu --}}
    <div class="dropdown">
      <div class="topbar-user" data-dropdown="user-menu">
        @if(auth()->user()?->profile_photo)
          <img src="{{ auth()->user()->avatar_url }}" class="user-avatar avatar-md" alt="{{ auth()->user()->name }}" style="image-rendering:crisp-edges;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;" id="topbar-avatar">
        @else
          <div class="user-avatar avatar-md" style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;font-weight:700;font-size:14px;">
            {{ substr(auth()->user()?->name ?? 'U', 0, 1) }}
          </div>
        @endif
        
      </div>
      <div class="dropdown-menu" id="user-menu" style="min-width:280px;right:0">
        <div class="dropdown-header">
          <span class="dropdown-title">{{ auth()->user()?->name ?? 'User' }}</span>
        </div>
        @if(Route::has('profile'))
        <a href="{{ route('account.profile') }}" class="dropdown-item">
          <div class="dropdown-item-icon" style="background:var(--primary-bg)">
            <i data-lucide="user" style="width:16px;height:16px;color:var(--primary)"></i>
          </div>
          <div>
            <div class="dropdown-item-title">{{ __('profile') ?? 'Profile' }}</div>
            <div class="dropdown-item-desc">{{ __('view profile') ?? 'View your profile' }}</div>
          </div>
        </a>
        @endif

        @if(Route::has('profile.security'))
        <a href="{{ route('profile.security') }}" class="dropdown-item">
          <div class="dropdown-item-icon" style="background:var(--warning-bg)">
            <i data-lucide="shield" style="width:16px;height:16px;color:var(--warning)"></i>
          </div>
          <div>
            <div class="dropdown-item-title">{{ __('security') ?? 'Security' }}</div>
            <div class="dropdown-item-desc">{{ __('change_password') ?? 'Change password' }}</div>
          </div>
        </a>
        @endif

        @if(Route::has('admin.settings.edit'))
        <a href="{{ route('admin.settings.edit') }}" class="dropdown-item">
          <div class="dropdown-item-icon" style="background:var(--info-bg)">
            <i data-lucide="settings" style="width:16px;height:16px;color:var(--info)"></i>
          </div>
          <div>
            <div class="dropdown-item-title">{{ __('settings') ?? 'Settings' }}</div>
            <div class="dropdown-item-desc">{{ __('manage settings') ?? 'Manage settings' }}</div>
          </div>
        </a>
        @endif

        <div class="dropdown-footer" style="border-top:1px solid var(--border);padding:10px 16px;text-align:left">
          <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="dropdown-link" style="color:var(--danger);font-size:13px">
              {{ __('logout') ?? 'Logout' }}
            </button>
          </form>
        </div>
      </div>
    </div>

  </div>{{-- end topbar-actions --}}

</header>
