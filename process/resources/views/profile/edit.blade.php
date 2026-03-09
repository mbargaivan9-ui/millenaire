@extends('layouts.app')
@section('title', 'Modifier mon profil')

@push('styles')
<style>
.edit-section { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 20px; }
.edit-section h3 { font-size: 15px; font-weight: 700; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
.avatar-upload-zone { width: 120px; height: 120px; border-radius: 50%; border: 3px dashed var(--border); display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: var(--transition); position: relative; overflow: hidden; }
.avatar-upload-zone:hover { border-color: var(--primary); background: var(--primary-bg); }
.avatar-upload-zone img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
.avatar-upload-zone .upload-overlay { position: absolute; inset: 0; background: rgba(13,148,136,.8); display: flex; align-items: center; justify-content: center; opacity: 0; border-radius: 50%; transition: var(--transition); }
.avatar-upload-zone:hover .upload-overlay { opacity: 1; }
.meta-stat { text-align: center; }
.meta-stat-val { font-size: 1.2rem; font-weight: 800; color: var(--text-primary); }
.meta-stat-lbl { font-size: 11px; text-transform: uppercase; letter-spacing: .7px; color: var(--text-muted); }
</style>
@endpush

@section('content')
<div class="page-header">
  <div>
    <h1 class="page-title">Modifier mon profil</h1>
    <p class="page-subtitle">Mettez à jour vos informations personnelles</p>
  </div>
  <div style="display:flex;gap:10px">
    <a href="{{ route('profile.show') }}" class="btn btn-outline">
      <i data-lucide="eye" style="width:15px;height:15px"></i> Voir profil
    </a>
    <button form="profile-form" type="submit" class="btn btn-primary" id="save-btn">
      <i data-lucide="check" style="width:15px;height:15px"></i> Sauvegarder
    </button>
  </div>
</div>

{{-- User ID meta bar --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px">
  <div class="edit-section" style="text-align:center;padding:16px">
    <div class="info-label" style="font-size:10px;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted)">ID Utilisateur</div>
    <div style="font-weight:700;font-size:15px">USR-{{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }}</div>
  </div>
  <div class="edit-section" style="text-align:center;padding:16px">
    <div class="info-label" style="font-size:10px;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted)">Rôle</div>
    <div style="font-weight:700;font-size:15px;color:var(--primary)">{{ $user->role_label }}</div>
  </div>
  <div class="edit-section" style="text-align:center;padding:16px">
    <div class="info-label" style="font-size:10px;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted)">Dernière connexion</div>
    <div style="font-weight:700;font-size:15px">{{ $user->last_login?->diffForHumans() ?? 'N/A' }}</div>
  </div>
  <div class="edit-section" style="text-align:center;padding:16px">
    <div class="info-label" style="font-size:10px;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted)">Score profil</div>
    <div style="font-weight:700;font-size:15px;color:var(--success)">{{ $user->profile_score }}%</div>
  </div>
</div>

@if(session('success'))
  <div style="background:var(--success-bg);border:1px solid var(--success);color:var(--success);padding:14px 18px;border-radius:var(--radius);margin-bottom:20px;display:flex;align-items:center;gap:10px">
    <i data-lucide="check-circle-2" style="width:18px;height:18px"></i> {{ session('success') }}
  </div>
@endif

<form id="profile-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
@csrf @method('PUT')

<div style="display:grid;grid-template-columns:1fr 2fr;gap:20px">

  {{-- Left: Avatar --}}
  <div>
    <div class="edit-section">
      <h3>Photo de profil</h3>
      <div style="display:flex;flex-direction:column;align-items:center;gap:16px">
        <label for="profile_photo_input" style="cursor:pointer">
          <div class="avatar-upload-zone" id="avatar-zone">
            @if($user->profile_photo)
              <img src="{{ $user->avatar_url }}" id="avatar-preview">
            @else
              <div class="profile-avatar-initials" style="width:100%;height:100%;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;font-size:2rem;font-weight:700;display:flex;align-items:center;justify-content:center" id="avatar-initials">
                {{ $user->initials }}
              </div>
            @endif
            <div class="upload-overlay">
              <i data-lucide="camera" style="width:24px;height:24px;color:#fff"></i>
            </div>
          </div>
        </label>
        <input type="file" id="profile_photo_input" name="profile_photo" accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none">
        <p style="font-size:12px;color:var(--text-muted);text-align:center">JPG, PNG ou WEBP. Max 2MB.</p>
      </div>
    </div>

    {{-- Account status (read-only info) --}}
    <div class="edit-section">
      <h3>Statut du compte</h3>
      <div style="display:flex;flex-direction:column;gap:14px">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <div style="font-size:13px;font-weight:600">Statut</div>
            <div style="font-size:12px;color:var(--text-muted)">Compte actif sur la plateforme</div>
          </div>
          <span style="padding:4px 12px;border-radius:var(--radius-full);font-size:11px;font-weight:700;background:{{ $user->is_active ? 'var(--success-bg)' : 'var(--danger-bg)' }};color:{{ $user->is_active ? 'var(--success)' : 'var(--danger)' }}">
            {{ $user->is_active ? 'Actif' : 'Inactif' }}
          </span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <div style="font-size:13px;font-weight:600">Email vérifié</div>
          </div>
          <span style="font-size:13px;font-weight:600;color:{{ $user->email_verified_at ? 'var(--success)' : 'var(--warning)' }}">
            {{ $user->email_verified_at ? 'Oui' : 'Non' }}
          </span>
        </div>
        <div style="border-top:1px solid var(--border);padding-top:14px">
          <a href="{{ route('profile.security') }}" style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:var(--danger);text-decoration:none">
            <i data-lucide="shield" style="width:15px;height:15px"></i> Sécurité & Mot de passe
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- Right: Personal info --}}
  <div>
    <div class="edit-section">
      <h3>Informations personnelles</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        <div>
          <label class="form-label">Prénom</label>
          <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" placeholder="Prénom">
        </div>
        <div>
          <label class="form-label">Nom</label>
          <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}" placeholder="Nom de famille">
        </div>
        <div>
          <label class="form-label">Nom complet <span style="color:var(--danger)">*</span></label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $user->name) }}" required>
          @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div>
          <label class="form-label">Email <span style="color:var(--danger)">*</span></label>
          <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $user->email) }}" required>
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div>
          <label class="form-label">Téléphone</label>
          <input type="text" name="phoneNumber" class="form-control"
            value="{{ old('phoneNumber', $user->phoneNumber) }}" placeholder="+237 6XX XXX XXX">
        </div>
        <div>
          <label class="form-label">Date de naissance</label>
          <input type="date" name="date_of_birth" class="form-control"
            value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}">
        </div>
        <div>
          <label class="form-label">Genre</label>
          <select name="gender" class="form-control">
            <option value="">— Sélectionner —</option>
            <option value="M" {{ $user->gender === 'M' ? 'selected' : '' }}>Masculin</option>
            <option value="F" {{ $user->gender === 'F' ? 'selected' : '' }}>Féminin</option>
          </select>
        </div>
        <div>
          <label class="form-label">Ville</label>
          <input type="text" name="city" class="form-control" value="{{ old('city', $user->city) }}" placeholder="Douala, Yaoundé...">
        </div>
        <div style="grid-column:1/-1">
          <label class="form-label">Adresse</label>
          <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}" placeholder="Rue, quartier...">
        </div>
        <div style="grid-column:1/-1">
          <label class="form-label">Biographie</label>
          <textarea name="bio" class="form-control" rows="3" placeholder="Décrivez-vous en quelques mots...">{{ old('bio', $user->bio) }}</textarea>
        </div>

      </div>
    </div>

    {{-- Action buttons --}}
    <div style="display:flex;gap:12px;justify-content:flex-end">
      <a href="{{ route('profile.show') }}" class="btn btn-outline">Annuler</a>
      <button type="submit" class="btn btn-primary" id="save-btn2">
        <i data-lucide="check" style="width:15px;height:15px"></i> Sauvegarder les modifications
      </button>
    </div>
  </div>

</div>
</form>
@endsection

@push('scripts')
<script>
// Avatar preview
document.getElementById('profile_photo_input').addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (!file) return;
  if (file.size > 2 * 1024 * 1024) {
    alert('Le fichier ne doit pas dépasser 2MB');
    return;
  }
  const reader = new FileReader();
  reader.onload = function(ev) {
    const zone = document.getElementById('avatar-zone');
    zone.innerHTML = `<img src="${ev.target.result}" id="avatar-preview" style="width:100%;height:100%;object-fit:cover;border-radius:50%"><div class="upload-overlay" style="position:absolute;inset:0;background:rgba(13,148,136,.8);display:flex;align-items:center;justify-content:center;border-radius:50%;opacity:0"><i data-lucide="camera" style="width:24px;height:24px;color:#fff"></i></div>`;
    lucide.createIcons();
  };
  reader.readAsDataURL(file);
});
</script>
@endpush
