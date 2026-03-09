@extends('layouts.app')
@section('title', 'Mon Profil')

@push('styles')
<style>
.profile-hero { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 28px; margin-bottom: 24px; }
.profile-avatar-wrap { position: relative; display: inline-block; }
.profile-avatar-img { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary); }
.profile-avatar-initials { width: 90px; height: 90px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: #fff; font-size: 2rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
.profile-status-dot { position: absolute; bottom: 4px; right: 4px; width: 14px; height: 14px; background: var(--success); border-radius: 50%; border: 2px solid var(--surface); }
.profile-role-badge { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--primary); }
.profile-name { font-size: 1.6rem; font-weight: 800; color: var(--text-primary); }
.profile-meta { display: flex; gap: 18px; flex-wrap: wrap; margin-top: 8px; }
.profile-meta-item { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--text-secondary); }
.stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; }
.stat-card-icon { width: 44px; height: 44px; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; }
.stat-card-value { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); }
.stat-card-label { font-size: 11px; text-transform: uppercase; letter-spacing: .7px; color: var(--text-muted); }
.info-section { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; height: 100%; }
.info-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: var(--text-muted); margin-bottom: 4px; }
.info-value { font-size: 14px; font-weight: 600; color: var(--text-primary); }
.activity-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border-light); }
.activity-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 5px; flex-shrink: 0; }
.activity-text { font-size: 13px; font-weight: 500; color: var(--text-primary); }
.activity-time { font-size: 11px; color: var(--text-muted); }
.profile-score-bar { height: 6px; background: var(--border); border-radius: 999px; overflow: hidden; }
.profile-score-fill { height: 100%; background: linear-gradient(90deg, var(--primary), var(--primary-light)); border-radius: 999px; transition: width .6s ease; }
.btn-edit-profile { display: inline-flex; align-items: center; gap: 7px; background: var(--primary); color: #fff; padding: 9px 20px; border-radius: var(--radius); font-size: 13px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; }
.btn-edit-profile:hover { background: var(--primary-dark); color: #fff; }
.btn-share { display: inline-flex; align-items: center; gap: 7px; background: var(--surface); color: var(--text-secondary); padding: 9px 18px; border-radius: var(--radius); font-size: 13px; font-weight: 600; text-decoration: none; border: 1px solid var(--border); cursor: pointer; }
</style>
@endpush

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Mon Profil</h1>
    <p class="page-subtitle">{{ auth()->user()->role_label }} · Membre depuis {{ auth()->user()->created_at->format('M Y') }}</p>
  </div>
</div>

{{-- Hero Card --}}
<div class="profile-hero">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:20px">
      <div class="profile-avatar-wrap">
        @if($user->profile_photo)
          <img src="{{ $user->avatar_url }}" class="profile-avatar-img" alt="{{ $user->display_name }}">
        @else
          <div class="profile-avatar-initials">{{ $user->initials }}</div>
        @endif
        <span class="profile-status-dot"></span>
      </div>
      <div>
        <div class="profile-role-badge">{{ $user->role_label }}</div>
        <div class="profile-name">{{ $user->display_name }}</div>
        @if($user->bio)
          <p style="font-size:13px;color:var(--text-secondary);margin:4px 0 0;max-width:500px">{{ $user->bio }}</p>
        @endif
        <div class="profile-meta">
          @if($user->city || $user->country)
            <span class="profile-meta-item"><i data-lucide="map-pin" style="width:12px;height:12px"></i>{{ $user->city }}{{ $user->city && $user->country ? ', ' : '' }}{{ $user->country }}</span>
          @endif
          @if($user->last_login)
            <span class="profile-meta-item"><i data-lucide="clock" style="width:12px;height:12px"></i>Connecté {{ $user->last_login->diffForHumans() }}</span>
          @endif
          <span class="profile-meta-item"><i data-lucide="calendar" style="width:12px;height:12px"></i>Inscrit {{ $user->created_at->format('M Y') }}</span>
        </div>
      </div>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
      <a href="{{ route('profile.edit') }}" class="btn-edit-profile">
        <i data-lucide="edit-2" style="width:15px;height:15px"></i> Modifier
      </a>
      <button onclick="copyProfileLink()" class="btn-share">
        <i data-lucide="share-2" style="width:15px;height:15px"></i> Partager
      </button>
    </div>
  </div>
</div>

{{-- Stats Row --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
  @foreach($stats as $i => $stat)
  @php
    $colors = ['var(--primary-bg)','var(--success-bg)','var(--warning-bg)','var(--info-bg)'];
    $iconColors = ['var(--primary)','var(--success)','var(--warning)','var(--info)'];
  @endphp
  <div class="stat-card">
    <div>
      <div class="stat-card-label">{{ $stat['label'] }}</div>
      <div class="stat-card-value">{{ $stat['value'] }}</div>
    </div>
    <div class="stat-card-icon" style="background:{{ $colors[$i % 4] }}">
      <i data-lucide="{{ $stat['icon'] }}" style="width:20px;height:20px;color:{{ $iconColors[$i % 4] }}"></i>
    </div>
  </div>
  @endforeach
</div>

<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:20px">
  {{-- Left column --}}
  <div style="display:flex;flex-direction:column;gap:20px">

    {{-- About --}}
    <div class="info-section">
      <h3 style="font-size:15px;font-weight:700;margin-bottom:18px">À propos</h3>
      <p style="font-size:13px;color:var(--text-secondary);line-height:1.7;margin-bottom:20px">
        {{ $user->bio ?? 'Aucune biographie renseignée.' }}
      </p>
      <div style="display:flex;flex-direction:column;gap:12px">
        <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--text-secondary)">
          <i data-lucide="mail" style="width:15px;height:15px;color:var(--primary)"></i>
          {{ $user->email }}
        </div>
        @if($user->phoneNumber)
        <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--text-secondary)">
          <i data-lucide="phone" style="width:15px;height:15px;color:var(--primary)"></i>
          {{ $user->phoneNumber }}
        </div>
        @endif
        @if($user->address)
        <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--text-secondary)">
          <i data-lucide="map-pin" style="width:15px;height:15px;color:var(--primary)"></i>
          {{ $user->address }}{{ $user->city ? ', '.$user->city : '' }}
        </div>
        @endif
      </div>
    </div>

    {{-- Profile completeness --}}
    <div class="info-section">
      <h3 style="font-size:15px;font-weight:700;margin-bottom:16px">Complétude du profil</h3>
      <div style="display:flex;justify-content:space-between;margin-bottom:8px">
        <span style="font-size:13px;color:var(--text-secondary)">Score</span>
        <span style="font-size:14px;font-weight:700;color:var(--primary)">{{ $user->profile_score }}%</span>
      </div>
      <div class="profile-score-bar">
        <div class="profile-score-fill" style="width:{{ $user->profile_score }}%"></div>
      </div>
      @if($user->profile_score < 100)
        <a href="{{ route('profile.edit') }}" style="display:inline-flex;align-items:center;gap:5px;margin-top:12px;font-size:12px;color:var(--primary);text-decoration:none;font-weight:600">
          <i data-lucide="arrow-right" style="width:13px;height:13px"></i> Compléter mon profil
        </a>
      @endif
    </div>

  </div>

  {{-- Right column --}}
  <div style="display:flex;flex-direction:column;gap:20px">

    {{-- Work Overview --}}
    <div class="info-section">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
        <h3 style="font-size:15px;font-weight:700">Informations</h3>
        <a href="{{ route('profile.edit') }}" style="font-size:12px;color:var(--primary);text-decoration:none;font-weight:600">Modifier →</a>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div>
          <div class="info-label">Rôle</div>
          <div class="info-value">{{ $user->role_label }}</div>
        </div>
        <div>
          <div class="info-label">Statut</div>
          <div class="info-value" style="color:{{ $user->is_active ? 'var(--success)' : 'var(--danger)' }}">
            {{ $user->is_active ? 'Actif' : 'Inactif' }}
          </div>
        </div>
        <div>
          <div class="info-label">Email vérifié</div>
          <div class="info-value">{{ $user->email_verified_at ? 'Oui' : 'Non' }}</div>
        </div>
        <div>
          <div class="info-label">Date de naissance</div>
          <div class="info-value">{{ $user->date_of_birth?->format('d/m/Y') ?? '—' }}</div>
        </div>
        <div>
          <div class="info-label">Genre</div>
          <div class="info-value">{{ $user->gender === 'M' ? 'Masculin' : ($user->gender === 'F' ? 'Féminin' : '—') }}</div>
        </div>
        <div>
          <div class="info-label">Ville</div>
          <div class="info-value">{{ $user->city ?? '—' }}</div>
        </div>
        @if($user->isTeacher() && $user->teacher)
        <div>
          <div class="info-label">Prof. Principal</div>
          <div class="info-value">{{ $user->teacher->is_prof_principal ? 'Oui' : 'Non' }}</div>
        </div>
        @endif
        @if($user->isStudent() && $user->student?->classe)
        <div>
          <div class="info-label">Classe</div>
          <div class="info-value">{{ $user->student->classe->name }}</div>
        </div>
        @endif
      </div>
    </div>

    {{-- Recent Activity --}}
    <div class="info-section">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <h3 style="font-size:15px;font-weight:700">Activité récente</h3>
        <a href="{{ route('notifications.index') }}" style="font-size:12px;color:var(--primary);text-decoration:none;font-weight:600">Voir tout →</a>
      </div>
      @forelse($activities as $activity)
      @php
        $dotColor = match($activity->type) {
          'success' => 'var(--success)',
          'warning' => 'var(--warning)',
          'danger'  => 'var(--danger)',
          'info'    => 'var(--info)',
          default   => 'var(--primary)',
        };
      @endphp
      <div class="activity-item">
        <div class="activity-dot" style="background:{{ $dotColor }}"></div>
        <div style="flex:1">
          <div class="activity-text">{{ $activity->title }}</div>
          <div class="activity-time">{{ $activity->created_at->format('d/m/Y, H:i') }}</div>
        </div>
      </div>
      @empty
      <p style="font-size:13px;color:var(--text-muted);text-align:center;padding:20px 0">Aucune activité récente</p>
      @endforelse
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
function copyProfileLink() {
  navigator.clipboard.writeText(window.location.href).then(() => {
    const btn = event.currentTarget;
    btn.innerHTML = '<i data-lucide="check" style="width:15px;height:15px"></i> Copié !';
    lucide.createIcons();
    setTimeout(() => {
      btn.innerHTML = '<i data-lucide="share-2" style="width:15px;height:15px"></i> Partager';
      lucide.createIcons();
    }, 2000);
  });
}
</script>
@endpush
