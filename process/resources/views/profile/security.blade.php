@extends('layouts.app')
@section('title', 'Sécurité du compte')

@push('styles')
<style>
.security-section { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 20px; }
.security-section h3 { font-size: 15px; font-weight: 700; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
.security-toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid var(--border-light); }
.security-toggle-row:last-child { border-bottom: none; }
.toggle-switch { position: relative; width: 44px; height: 24px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; inset: 0; background: var(--border); border-radius: 999px; cursor: pointer; transition: .3s; }
.toggle-slider:before { content: ''; position: absolute; width: 18px; height: 18px; left: 3px; top: 3px; background: white; border-radius: 50%; transition: .3s; }
input:checked + .toggle-slider { background: var(--primary); }
input:checked + .toggle-slider:before { transform: translateX(20px); }
.danger-zone { background: var(--danger-bg); border: 1px solid #fca5a5; border-radius: var(--radius-lg); padding: 24px; }
.session-item { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-bottom: 1px solid var(--border-light); }
.session-item:last-child { border-bottom: none; }
.pw-strength { height: 4px; border-radius: 999px; background: var(--border); margin-top: 6px; overflow: hidden; }
.pw-strength-bar { height: 100%; border-radius: 999px; transition: width .3s, background .3s; }
</style>
@endpush

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Sécurité du compte</h1>
    <p class="page-subtitle">Gérez votre mot de passe et la sécurité de votre compte</p>
  </div>
  <a href="{{ route('profile.show') }}" class="btn btn-outline">← Mon profil</a>
</div>

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid var(--success);color:var(--success);padding:14px 18px;border-radius:var(--radius);margin-bottom:20px;display:flex;align-items:center;gap:10px">
    <i data-lucide="check-circle-2" style="width:18px;height:18px"></i> {{ session('success') }}
  </div>
@endif
@if($errors->any())
  <div style="background:var(--danger-bg);border:1px solid var(--danger);color:var(--danger);padding:14px 18px;border-radius:var(--radius);margin-bottom:20px">
    @foreach($errors->all() as $error)<p style="margin:0;font-size:13px">• {{ $error }}</p>@endforeach
  </div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

  {{-- Change Password --}}
  <div class="security-section">
    <h3><i data-lucide="key" style="width:15px;height:15px;margin-right:6px;color:var(--primary)"></i>Changer le mot de passe</h3>
    <form method="POST" action="{{ route('profile.change-password') }}" id="pwd-form">
      @csrf
      <div class="form-group">
        <label class="form-label">Mot de passe actuel</label>
        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror"
          placeholder="Mot de passe actuel" required>
        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="form-group" style="margin-top:14px">
        <label class="form-label">Nouveau mot de passe</label>
        <input type="password" name="password" class="form-control" id="new-password"
          placeholder="Min. 8 caractères" required minlength="8">
        <div class="pw-strength"><div class="pw-strength-bar" id="pw-bar" style="width:0%"></div></div>
        <div id="pw-feedback" style="font-size:11px;color:var(--text-muted);margin-top:4px"></div>
      </div>
      <div class="form-group" style="margin-top:14px">
        <label class="form-label">Confirmer le mot de passe</label>
        <input type="password" name="password_confirmation" class="form-control"
          placeholder="Confirmer le nouveau mot de passe" required>
      </div>
      <button type="submit" class="btn btn-primary" style="margin-top:18px;width:100%">
        <i data-lucide="lock" style="width:15px;height:15px"></i> Mettre à jour le mot de passe
      </button>
    </form>
  </div>

  {{-- 2FA & Options --}}
  <div style="display:flex;flex-direction:column;gap:20px">

    <div class="security-section" style="margin-bottom:0">
      <h3><i data-lucide="shield-check" style="width:15px;height:15px;margin-right:6px;color:var(--primary)"></i>Options de sécurité</h3>
      <div class="security-toggle-row">
        <div>
          <div style="font-size:13px;font-weight:600">Authentification 2 facteurs</div>
          <div style="font-size:12px;color:var(--text-muted)">Sécurisez votre connexion avec un code SMS</div>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" id="2fa-toggle" {{ $user->two_factor_enabled ? 'checked' : '' }}
            onchange="toggle2FA(this.checked)">
          <span class="toggle-slider"></span>
        </label>
      </div>
      <div class="security-toggle-row">
        <div>
          <div style="font-size:13px;font-weight:600">Alertes de connexion</div>
          <div style="font-size:12px;color:var(--text-muted)">Être notifié des nouvelles connexions</div>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" checked>
          <span class="toggle-slider"></span>
        </label>
      </div>
    </div>

    {{-- Active Sessions --}}
    <div class="security-section" style="margin-bottom:0">
      <h3><i data-lucide="monitor" style="width:15px;height:15px;margin-right:6px;color:var(--primary)"></i>Sessions actives</h3>
      @forelse($sessions as $session)
      <div class="session-item">
        <div style="width:36px;height:36px;background:var(--surface-2);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <i data-lucide="{{ str_contains($session['agent'], 'Mobile') ? 'smartphone' : 'monitor' }}" style="width:16px;height:16px;color:var(--text-muted)"></i>
        </div>
        <div style="flex:1">
          <div style="font-size:13px;font-weight:600">{{ $session['ip'] ?? 'Inconnue' }}</div>
          <div style="font-size:11px;color:var(--text-muted)">{{ $session['last_active'] }}</div>
        </div>
        @if($session['is_current'])
          <span style="font-size:11px;font-weight:700;color:var(--success);background:var(--success-bg);padding:3px 10px;border-radius:var(--radius-full)">Actuelle</span>
        @endif
      </div>
      @empty
      <p style="font-size:13px;color:var(--text-muted);text-align:center;padding:12px 0">Aucune session active</p>
      @endforelse
      <form method="POST" action="{{ route('profile.logout-all') }}" style="margin-top:16px" onsubmit="return confirm('Déconnecter de tous les appareils ?')">
        @csrf
        <button type="submit" class="btn btn-danger" style="width:100%;font-size:13px">
          <i data-lucide="log-out" style="width:15px;height:15px"></i> Déconnecter tous les appareils
        </button>
      </form>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
// Password strength
document.getElementById('new-password').addEventListener('input', function() {
  const v = this.value;
  let score = 0;
  if (v.length >= 8) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^a-zA-Z0-9]/.test(v)) score++;
  const labels = ['', 'Faible', 'Moyen', 'Fort', 'Très fort'];
  const colors = ['', 'var(--danger)', 'var(--warning)', 'var(--success)', 'var(--primary)'];
  const bar = document.getElementById('pw-bar');
  const fb = document.getElementById('pw-feedback');
  bar.style.width = (score * 25) + '%';
  bar.style.background = colors[score] || 'var(--border)';
  fb.textContent = v ? labels[score] || '' : '';
  fb.style.color = colors[score] || 'var(--text-muted)';
});
function toggle2FA(enabled) {
  // Could call AJAX here to toggle 2FA
  console.log('2FA:', enabled);
}
</script>
@endpush
