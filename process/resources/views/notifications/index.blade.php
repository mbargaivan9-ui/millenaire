@extends('layouts.app')
@section('title', 'Centre de Notifications')

@push('styles')
<style>
.notif-center-layout { display: grid; grid-template-columns: 260px 1fr; gap: 20px; }
.notif-sidebar { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 20px; height: fit-content; position: sticky; top: 80px; }
.notif-nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: var(--radius); font-size: 13px; font-weight: 500; color: var(--text-secondary); cursor: pointer; transition: var(--transition); text-decoration: none; margin-bottom: 2px; }
.notif-nav-item:hover, .notif-nav-item.active { background: var(--primary-bg); color: var(--primary); }
.notif-nav-item .nav-count { margin-left: auto; font-size: 11px; font-weight: 700; background: var(--primary); color: #fff; padding: 2px 7px; border-radius: var(--radius-full); }
.notif-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; }
.notif-card-header { padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
.notif-item { display: flex; align-items: flex-start; gap: 14px; padding: 16px 20px; border-bottom: 1px solid var(--border-light); transition: var(--transition); cursor: pointer; }
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: var(--surface-2); }
.notif-item.unread { background: var(--primary-bg); border-left: 3px solid var(--primary); }
.notif-item.unread:hover { background: var(--primary-hover); }
.notif-icon { width: 42px; height: 42px; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.notif-title { font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 3px; }
.notif-msg { font-size: 12px; color: var(--text-secondary); line-height: 1.5; }
.notif-time { font-size: 11px; color: var(--text-muted); white-space: nowrap; margin-left: auto; }
.notif-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--primary); flex-shrink: 0; margin-top: 6px; }
.notif-actions { display: flex; gap: 6px; align-items: center; }
.btn-sm-icon { width: 28px; height: 28px; border-radius: var(--radius-sm); border: none; background: var(--surface-2); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: var(--transition); }
.btn-sm-icon:hover { background: var(--border); }
.channel-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 18px; display: flex; align-items: center; gap: 14px; }
.channel-icon { width: 44px; height: 44px; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stream-item { display: flex; align-items: flex-start; gap: 14px; padding: 14px 20px; border-bottom: 1px solid var(--border-light); }
.stream-item:last-child { border-bottom: none; }

@media (max-width: 768px) {
  .notif-center-layout { grid-template-columns: 1fr; }
  .notif-sidebar { position: static; }
}
</style>
@endpush

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Centre de Notifications</h1>
    <p class="page-subtitle">Gérez vos canaux, priorités et alertes.</p>
  </div>
  <div style="display:flex;gap:10px">
    <button onclick="markAllRead()" class="btn btn-outline" id="mark-all-btn">
      <i data-lucide="check-check" style="width:15px;height:15px"></i> Tout marquer lu
    </button>
    <a href="{{ route('notifications.settings') }}" class="btn btn-primary">
      <i data-lucide="settings" style="width:15px;height:15px"></i> Préférences
    </a>
  </div>
</div>

<div class="notif-center-layout">

  {{-- LEFT: Sidebar navigation --}}
  <div class="notif-sidebar">
    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:10px">Filtres</p>

    <a href="{{ route('notifications.index') }}" class="notif-nav-item {{ !request('category') && !request('status') ? 'active' : '' }}">
      <i data-lucide="bell" style="width:16px;height:16px"></i> Toutes
      @if($unreadCount > 0)
        <span class="nav-count">{{ $unreadCount }}</span>
      @endif
    </a>
    <a href="{{ route('notifications.index', ['status' => 'unread']) }}" class="notif-nav-item {{ request('status') === 'unread' ? 'active' : '' }}">
      <i data-lucide="circle" style="width:16px;height:16px"></i> Non lues
    </a>
    <a href="{{ route('notifications.index', ['status' => 'read']) }}" class="notif-nav-item {{ request('status') === 'read' ? 'active' : '' }}">
      <i data-lucide="check-circle" style="width:16px;height:16px"></i> Lues
    </a>

    <div style="margin: 14px 0; border-top: 1px solid var(--border)"></div>
    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:10px">Par catégorie</p>

    @php
      $catIcons = ['grade'=>'book-open','payment'=>'credit-card','absence'=>'user-x','message'=>'message-circle','security'=>'shield-check','announcement'=>'megaphone','system'=>'settings'];
      $catLabels = ['grade'=>'Notes','payment'=>'Paiements','absence'=>'Absences','message'=>'Messages','security'=>'Sécurité','announcement'=>'Annonces','system'=>'Système'];
    @endphp

    @foreach($categories as $cat)
    <a href="{{ route('notifications.index', ['category' => $cat->category]) }}" class="notif-nav-item {{ request('category') === $cat->category ? 'active' : '' }}">
      <i data-lucide="{{ $catIcons[$cat->category] ?? 'tag' }}" style="width:16px;height:16px"></i>
      {{ $catLabels[$cat->category] ?? ucfirst($cat->category) }}
      <span style="margin-left:auto;font-size:11px;color:var(--text-muted)">{{ $cat->count }}</span>
    </a>
    @endforeach

    <div style="margin: 14px 0; border-top: 1px solid var(--border)"></div>
    <a href="{{ route('notifications.settings') }}" class="notif-nav-item">
      <i data-lucide="sliders" style="width:16px;height:16px"></i> Préférences
    </a>
    <a href="#activity-log" class="notif-nav-item">
      <i data-lucide="clock" style="width:16px;height:16px"></i> Journal d'activité
    </a>
  </div>

  {{-- RIGHT: Main content --}}
  <div>

    {{-- Notifications list --}}
    <div class="notif-card" style="margin-bottom:20px">
      <div class="notif-card-header">
        <div>
          <span style="font-weight:700;font-size:15px">Notifications</span>
          @if($unreadCount > 0)
            <span style="margin-left:10px;font-size:11px;font-weight:700;background:var(--danger);color:#fff;padding:3px 9px;border-radius:var(--radius-full)">{{ $unreadCount }} non lues</span>
          @endif
        </div>
        <button onclick="markAllRead()" style="font-size:12px;font-weight:600;color:var(--primary);background:none;border:none;cursor:pointer">
          Marquer tout comme lu
        </button>
      </div>

      @forelse($notifications as $notif)
      @php
        $iconColors = ['success'=>'var(--success)','warning'=>'var(--warning)','danger'=>'var(--danger)','info'=>'var(--info)','primary'=>'var(--primary)'];
        $bgColors   = ['success'=>'var(--success-bg)','warning'=>'var(--warning-bg)','danger'=>'var(--danger-bg)','info'=>'var(--info-bg)','primary'=>'var(--primary-bg)'];
        $ic  = $iconColors[$notif->type] ?? 'var(--primary)';
        $bg  = $bgColors[$notif->type]   ?? 'var(--primary-bg)';
      @endphp
      <div class="notif-item {{ !$notif->is_read ? 'unread' : '' }}" id="notif-{{ $notif->id }}">
        @if(!$notif->is_read)
          <div class="notif-dot"></div>
        @else
          <div style="width:8px;flex-shrink:0"></div>
        @endif
        <div class="notif-icon" style="background:{{ $bg }}">
          <i data-lucide="{{ $notif->icon }}" style="width:18px;height:18px;color:{{ $ic }}"></i>
        </div>
        <div style="flex:1;min-width:0">
          <div class="notif-title">{{ $notif->title }}</div>
          <div class="notif-msg">{{ $notif->message }}</div>
          <div style="display:flex;align-items:center;gap:10px;margin-top:6px">
            <span style="font-size:11px;color:var(--text-muted)">{{ $notif->created_at->diffForHumans() }}</span>
            @if($notif->category)
              <span style="font-size:11px;background:var(--surface-2);padding:2px 8px;border-radius:var(--radius-full);color:var(--text-secondary)">{{ $catLabels[$notif->category] ?? $notif->category }}</span>
            @endif
          </div>
        </div>
        <div class="notif-actions">
          @if(!$notif->is_read)
          <button class="btn-sm-icon" onclick="markRead({{ $notif->id }})" title="Marquer comme lu">
            <i data-lucide="check" style="width:13px;height:13px;color:var(--primary)"></i>
          </button>
          @endif
          @if($notif->action_url)
          <a href="{{ $notif->action_url }}" class="btn-sm-icon" title="Voir">
            <i data-lucide="arrow-right" style="width:13px;height:13px;color:var(--text-muted)"></i>
          </a>
          @endif
          <button class="btn-sm-icon" onclick="deleteNotif({{ $notif->id }})" title="Supprimer">
            <i data-lucide="trash-2" style="width:13px;height:13px;color:var(--danger)"></i>
          </button>
        </div>
      </div>
      @empty
      <div style="padding:48px;text-align:center">
        <i data-lucide="bell-off" style="width:40px;height:40px;color:var(--text-muted);margin-bottom:12px"></i>
        <p style="font-size:14px;color:var(--text-muted)">Aucune notification</p>
      </div>
      @endforelse

      @if($notifications->hasPages())
        <div style="padding:16px 20px">
          {{ $notifications->withQueryString()->links() }}
        </div>
      @endif
    </div>

    {{-- Recent Stream --}}
    <div class="notif-card" id="activity-log">
      <div class="notif-card-header">
        <span style="font-weight:700;font-size:15px">Flux récent</span>
        <button class="btn btn-outline" style="font-size:12px;padding:6px 12px">
          <i data-lucide="filter" style="width:13px;height:13px"></i> Filtrer
        </button>
      </div>
      @forelse($recentStream as $item)
      @php
        $ic2 = $iconColors[$item->type] ?? 'var(--primary)';
        $bg2 = $bgColors[$item->type]  ?? 'var(--primary-bg)';
      @endphp
      <div class="stream-item">
        <div class="notif-icon" style="background:{{ $bg2 }};width:36px;height:36px">
          <i data-lucide="{{ $item->icon }}" style="width:15px;height:15px;color:{{ $ic2 }}"></i>
        </div>
        <div style="flex:1">
          <div style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ $item->title }}</div>
          <div style="font-size:12px;color:var(--text-secondary)">{{ $item->message }}</div>
        </div>
        <div style="font-size:11px;color:var(--text-muted);white-space:nowrap">{{ $item->created_at->diffForHumans() }}</div>
      </div>
      @empty
      <div style="padding:32px;text-align:center;font-size:13px;color:var(--text-muted)">Aucun flux récent</div>
      @endforelse

      <div style="padding:14px 20px;text-align:center;border-top:1px solid var(--border);font-size:12px;color:var(--text-muted)">
        © {{ date('Y') }} Millenaire Connect
        <span style="margin: 0 8px">·</span>
        <a href="{{ route('profile.security') }}" style="color:var(--text-muted);text-decoration:none">Sécurité</a>
        <span style="margin: 0 8px">·</span>
        <a href="{{ route('profile.show') }}" style="color:var(--text-muted);text-decoration:none">Profil</a>
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

async function markRead(id) {
  const r = await fetch(`/notifications/${id}/mark-read`, {
    method: 'POST',
    headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'}
  });
  const data = await r.json();
  if (data.success) {
    const item = document.getElementById(`notif-${id}`);
    item.classList.remove('unread');
    item.querySelector('.notif-dot')?.remove();
    // Update badge in topbar
    updateBadge(data.unread);
  }
}

async function markAllRead() {
  const r = await fetch('/notifications/mark-all-read', {
    method: 'POST',
    headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'}
  });
  const data = await r.json();
  if (data.success) {
    document.querySelectorAll('.notif-item.unread').forEach(el => {
      el.classList.remove('unread');
      el.querySelector('.notif-dot')?.remove();
    });
    updateBadge(0);
  }
}

async function deleteNotif(id) {
  if (!confirm('Supprimer cette notification ?')) return;
  const r = await fetch(`/notifications/${id}`, {
    method: 'DELETE',
    headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'}
  });
  const data = await r.json();
  if (data.success) {
    document.getElementById(`notif-${id}`)?.remove();
  }
}

function updateBadge(count) {
  const badge = document.querySelector('[data-dropdown="notif-menu"] .topbar-badge');
  if (badge) {
    if (count > 0) badge.textContent = count > 9 ? '9+' : count;
    else badge.remove();
  }
}
</script>
@endpush
