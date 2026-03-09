@extends('layouts.app')
@section('title', 'Préférences de Notifications')

@push('styles')
<style>
.settings-layout { display: grid; grid-template-columns: 220px 1fr; gap: 20px; }
.settings-nav { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 16px; height: fit-content; }
.settings-nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: var(--radius); font-size: 13px; font-weight: 500; color: var(--text-secondary); cursor: pointer; transition: var(--transition); text-decoration: none; margin-bottom: 2px; }
.settings-nav-item:hover, .settings-nav-item.active { background: var(--primary-bg); color: var(--primary); }
.settings-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 20px; }
.settings-card h3 { font-size: 15px; font-weight: 700; margin-bottom: 6px; }
.settings-card .subtitle { font-size: 13px; color: var(--text-muted); margin-bottom: 20px; }
.channel-card { border: 1px solid var(--border); border-radius: var(--radius); padding: 18px 20px; display: flex; align-items: center; gap: 14px; transition: var(--transition); }
.channel-card:hover { border-color: var(--primary); }
.channel-icon-wrap { width: 46px; height: 46px; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rule-row { display: flex; align-items: center; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid var(--border-light); }
.rule-row:last-child { border-bottom: none; }
.priority-badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: var(--radius-full); }
.toggle-switch { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; inset: 0; background: var(--border); border-radius: 999px; cursor: pointer; transition: .3s; }
.toggle-slider:before { content: ''; position: absolute; width: 18px; height: 18px; left: 3px; top: 3px; background: white; border-radius: 50%; transition: .3s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
input:checked + .toggle-slider { background: var(--primary); }
input:checked + .toggle-slider:before { transform: translateX(20px); }

@media (max-width: 768px) {
  .settings-layout { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">{{ __('Notification Center') ?? 'Centre de Notifications' }}</h1>
    <p class="page-subtitle">{{ __('Control your channels, priorities and alert routing.') ?? 'Contrôlez vos canaux, priorités et routage d\'alertes.' }}</p>
  </div>
  <div style="display:flex;gap:10px">
    <button onclick="saveSettings()" class="btn btn-primary" id="save-prefs-btn">
      <i data-lucide="save" style="width:15px;height:15px"></i> {{ __('Save preferences') ?? 'Sauvegarder les préférences' }}
    </button>
  </div>
</div>

<div id="save-toast" style="display:none;position:fixed;bottom:24px;right:24px;background:var(--success);color:#fff;padding:14px 20px;border-radius:var(--radius);font-size:13px;font-weight:600;box-shadow:var(--shadow-lg);z-index:9999;display:flex;align-items:center;gap:8px">
  <i data-lucide="check-circle-2" style="width:18px;height:18px"></i> {{ __('Preferences saved!') ?? 'Préférences sauvegardées !' }}
</div>

<div class="settings-layout">

  {{-- LEFT NAV --}}
  <div class="settings-nav">
    <a href="{{ route('profile.edit') }}" class="settings-nav-item">
      <i data-lucide="user" style="width:15px;height:15px"></i> {{ __('General') ?? 'Général' }}
    </a>
    <a href="{{ route('notifications.settings') }}" class="settings-nav-item active">
      <i data-lucide="bell" style="width:15px;height:15px"></i> {{ __('Notifications') ?? 'Notifications' }}
    </a>
    <a href="{{ route('profile.security') }}" class="settings-nav-item">
      <i data-lucide="clock" style="width:15px;height:15px"></i> {{ __('Activity & Sessions') ?? 'Activité & Sessions' }}
    </a>
  </div>

  {{-- RIGHT CONTENT --}}
  <div>

    {{-- Channel Cards --}}
    <div class="settings-card">
      <h3>{{ __('Notification Channels') ?? 'Canaux de notification' }}</h3>
      <p class="subtitle">{{ __('Choose how you want to be notified.') ?? 'Choisissez comment vous souhaitez être notifié.' }}</p>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px">

        <div class="channel-card">
          <div class="channel-icon-wrap" style="background:var(--success-bg)">
            <i data-lucide="mail" style="width:20px;height:20px;color:var(--success)"></i>
          </div>
          <div style="flex:1">
            <div style="font-size:13px;font-weight:700;margin-bottom:2px">{{ __('Email') }}</div>
            <div style="font-size:11px;color:var(--text-muted)">{{ __('Important updates') ?? 'Mises à jour importantes' }}</div>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="email_notifications" {{ $user->email_notifications ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="channel-card">
          <div class="channel-icon-wrap" style="background:var(--info-bg)">
            <i data-lucide="smartphone" style="width:20px;height:20px;color:var(--info)"></i>
          </div>
          <div style="flex:1">
            <div style="font-size:13px;font-weight:700;margin-bottom:2px">{{ __('Push') }}</div>
            <div style="font-size:11px;color:var(--text-muted)">{{ __('Mobile & desktop notifications') ?? 'Notifications mobile & bureau' }}</div>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="push_notifications" {{ $user->push_notifications ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

        <div class="channel-card">
          <div class="channel-icon-wrap" style="background:var(--warning-bg)">
            <i data-lucide="layout-dashboard" style="width:20px;height:20px;color:var(--warning)"></i>
          </div>
          <div style="flex:1">
            <div style="font-size:13px;font-weight:700;margin-bottom:2px">{{ __('In-App') }}</div>
            <div style="font-size:11px;color:var(--text-muted)">{{ __('Notifications in dashboard') ?? 'Notifications dans le dashboard' }}</div>
          </div>
          <label class="toggle-switch">
            <input type="checkbox" name="in_app_notifications" {{ $user->in_app_notifications ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>

      </div>
    </div>

    {{-- Rule Preferences --}}
    <div class="settings-card">
      <h3>{{ __('Notification Rules') ?? 'Règles de notification' }}</h3>
      <p class="subtitle">{{ __('Configure the priority of each alert type.') ?? 'Configurez la priorité de chaque type d\'alerte.' }}</p>

      @php
        $rules = [
          ['name' => 'notif_security',      'label' => __('Security Alerts') ?? 'Alertes de sécurité',    'desc' => __('New logins, password changes') ?? 'Nouvelles connexions, changements de mot de passe', 'priority' => 'HIGH',   'priority_color' => 'var(--danger)',  'priority_bg' => 'var(--danger-bg)'],
          ['name' => 'notif_grades',        'label' => __('Grades & Bulletins') ?? 'Notes & Bulletins',       'desc' => __('New grades, available bulletins') ?? 'Nouvelles notes, bulletins disponibles',            'priority' => 'HIGH',   'priority_color' => 'var(--danger)',  'priority_bg' => 'var(--danger-bg)'],
          ['name' => 'notif_absences',      'label' => __('Absences') ?? 'Absences',                'desc' => __('New absences recorded') ?? 'Nouvelles absences enregistrées',                   'priority' => 'MEDIUM', 'priority_color' => 'var(--warning)', 'priority_bg' => 'var(--warning-bg)'],
          ['name' => 'notif_payments',      'label' => __('Payments & Billing') ?? 'Paiements & Facturation', 'desc' => __('Receipts, payment reminders, new invoices') ?? 'Reçus, rappels de paiement, nouvelles factures',    'priority' => 'HIGH',   'priority_color' => 'var(--danger)',  'priority_bg' => 'var(--danger-bg)'],
          ['name' => 'notif_announcements', 'label' => __('Announcements') ?? 'Annonces',                'desc' => __('New establishment announcements') ?? 'Nouvelles annonces de l\'établissement',            'priority' => 'MEDIUM', 'priority_color' => 'var(--warning)', 'priority_bg' => 'var(--warning-bg)'],
          ['name' => 'notif_messages',      'label' => __('Messages') ?? 'Messages',                'desc' => __('New private and group messages') ?? 'Nouveaux messages privés et de groupe',             'priority' => 'LOW',    'priority_color' => 'var(--info)',    'priority_bg' => 'var(--info-bg)'],
        ];
      @endphp

      @foreach($rules as $rule)
      <div class="rule-row">
        <div>
          <div style="font-size:13px;font-weight:600;margin-bottom:2px">{{ $rule['label'] }}</div>
          <div style="font-size:12px;color:var(--text-muted)">{{ $rule['desc'] }}</div>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
          <span class="priority-badge" style="background:{{ $rule['priority_bg'] }};color:{{ $rule['priority_color'] }}">
            {{ $rule['priority'] }}
          </span>
          <label class="toggle-switch">
            <input type="checkbox" name="{{ $rule['name'] }}" {{ $user->{$rule['name']} ? 'checked' : '' }}>
            <span class="toggle-slider"></span>
          </label>
        </div>
      </div>
      @endforeach
    </div>

    {{-- Save button bottom --}}
    <div style="display:flex;justify-content:flex-end;gap:10px">
      <a href="{{ route('notifications.index') }}" class="btn btn-outline">← {{ __('Back to notifications') ?? 'Retour aux notifications' }}</a>
      <button onclick="saveSettings()" class="btn btn-primary">
        <i data-lucide="save" style="width:15px;height:15px"></i> {{ __('Save') ?? 'Sauvegarder' }}
      </button>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
async function saveSettings() {
  const inputs = document.querySelectorAll('input[type="checkbox"]');
  const data   = {};
  inputs.forEach(inp => { if (inp.name) data[inp.name] = inp.checked ? 1 : 0; });

  const r = await fetch('{{ route("notifications.save-settings") }}', {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(data)
  });

  const res = await r.json();
  if (res.success) {
    const toast = document.getElementById('save-toast');
    toast.style.display = 'flex';
    setTimeout(() => toast.style.display = 'none', 3000);
  }
}

// Ensure lucide icons render
if (typeof lucide !== 'undefined') {
  lucide.createIcons();
}
</script>
@endpush
