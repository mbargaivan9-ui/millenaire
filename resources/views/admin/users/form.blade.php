@extends('layouts.app')
@section('title', isset($user) ? 'Éditer' : 'Créer' . ' Utilisateur')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('admin.users.index') }}" style="color:var(--primary);text-decoration:none">Utilisateurs</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ isset($user) ? 'Éditer' : 'Créer' }}</span>
    </div>
    <h1 class="page-title">{{ isset($user) ? 'Éditer Utilisateur' : 'Créer Utilisateur' }}</h1>
    <p class="page-subtitle">{{ isset($user) ? 'Modifier les informations de l\'utilisateur' : 'Ajouter un nouvel utilisateur' }}</p>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 350px;gap:20px;margin-bottom:40px">
  {{-- Main Content --}}
  <div>
    <div class="card">
      <div class="card-header">
        <i data-lucide="{{ isset($user) ? 'edit' : 'plus-circle' }}" style="width:16px;height:16px"></i>
        <span>{{ isset($user) ? 'Éditer' : 'Nouvel' }} Utilisateur</span>
      </div>
      <div class="card-body">
        <form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" 
              method="POST" enctype="multipart/form-data">
          @csrf
          @if(isset($user))
            @method('PUT')
          @endif

          {{-- Row 1: Name and Email --}}
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px">
            <div>
              <label class="form-label">Nom Complet *</label>
              <input type="text" class="form-control @error('name') is-invalid @enderror" 
                     name="name" value="{{ $user->name ?? old('name') }}" required>
              @error('name')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
              <label class="form-label">Email *</label>
              <input type="email" class="form-control @error('email') is-invalid @enderror" 
                     name="email" value="{{ $user->email ?? old('email') }}" required>
              @error('email')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>
          </div>

          {{-- Row 2: Role and Gender --}}
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px">
            <div>
              <label class="form-label">Rôle *</label>
              <select class="form-control @error('role') is-invalid @enderror" 
                      name="role" required>
                <option value="">-- Sélectionnez un rôle --</option>
                @foreach($roles ?? [] as $role)
                <option value="{{ $role }}" 
                        {{ (isset($user) && $user->role === $role) || old('role') === $role ? 'selected' : '' }}>
                  {{ ucfirst(str_replace('_', ' ', $role)) }}
                </option>
                @endforeach
              </select>
              @error('role')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
              <label class="form-label">Genre *</label>
              <select class="form-control @error('gender') is-invalid @enderror" 
                      name="gender" required>
                <option value="">-- Sélectionnez --</option>
                <option value="M" {{ (isset($user) && $user->gender === 'M') || old('gender') === 'M' ? 'selected' : '' }}>
                  Masculin
                </option>
                <option value="F" {{ (isset($user) && $user->gender === 'F') || old('gender') === 'F' ? 'selected' : '' }}>
                  Féminin
                </option>
              </select>
              @error('gender')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>
          </div>

          {{-- Row 3: Phone and DOB --}}
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px">
            <div>
              <label class="form-label">Téléphone</label>
              <input type="tel" class="form-control @error('phoneNumber') is-invalid @enderror" 
                     name="phoneNumber" value="{{ $user->phoneNumber ?? old('phoneNumber') }}">
              @error('phoneNumber')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
              <label class="form-label">Date de Naissance</label>
              <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                     name="date_of_birth" value="{{ isset($user) && $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : old('date_of_birth') }}">
              @error('date_of_birth')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>
          </div>

          {{-- Row 4: Address and City --}}
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px">
            <div>
              <label class="form-label">Adresse</label>
              <input type="text" class="form-control" 
                     name="address" value="{{ $user->address ?? old('address') }}">
            </div>

            <div>
              <label class="form-label">Ville</label>
              <input type="text" class="form-control" 
                     name="city" value="{{ $user->city ?? old('city') }}">
            </div>
          </div>

          {{-- Password Section --}}
          @if(!isset($user))
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px">
            <div>
              <label class="form-label">Mot de Passe *</label>
              <input type="password" class="form-control @error('password') is-invalid @enderror" 
                     name="password" required>
              @error('password')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
              <label class="form-label">Confirmer Mot de Passe *</label>
              <input type="password" class="form-control" 
                     name="password_confirmation" required>
            </div>
          </div>
          @else
          <div style="background:var(--text-bg);border-left:3px solid var(--primary);padding:12px;border-radius:4px;margin-bottom:20px">
            <small style="color:var(--text-muted);display:flex;align-items:center;gap:8px">
              <i data-lucide="info" style="width:14px;height:14px"></i>
              Pour changer le mot de passe, utilisez la fonction "Réinitialiser le mot de passe"
            </small>
          </div>
          @endif

          {{-- Active Status --}}
          <div style="margin-bottom:20px">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
              <input type="checkbox" name="is_active" value="1" 
                     {{ (isset($user) && $user->is_active) || old('is_active') ? 'checked' : '' }} style="width:18px;height:18px">
              <span>Cet utilisateur est actif</span>
            </label>
          </div>

          {{-- Actions --}}
          <div style="display:flex;gap:12px;justify-content:flex-end">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
              Annuler
            </a>
            <button type="submit" class="btn btn-primary">
              <i data-lucide="save" style="width:14px;height:14px"></i>
              {{ isset($user) ? 'Mettre à jour' : 'Créer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Sidebar --}}
  <div>
    <div class="card" style="margin-bottom:20px">
      <div class="card-header">
        <i data-lucide="info" style="width:16px;height:16px"></i>
        <span>Informations</span>
      </div>
      <div class="card-body">
        <ul style="list-style:none;padding:0;margin:0">
          <li style="margin-bottom:12px;display:flex;gap:8px;font-size:12px">
            <i data-lucide="check-circle" style="width:14px;height:14px;color:var(--success);flex-shrink:0;margin-top:2px"></i>
            <span>Tous les champs avec * sont obligatoires</span>
          </li>
          <li style="margin-bottom:12px;display:flex;gap:8px;font-size:12px">
            <i data-lucide="lock" style="width:14px;height:14px;color:var(--primary);flex-shrink:0;margin-top:2px"></i>
            <span>Les mots de passe doivent contenir au moins 8 caractères</span>
          </li>
          <li style="display:flex;gap:8px;font-size:12px">
            <i data-lucide="shield" style="width:14px;height:14px;color:var(--info);flex-shrink:0;margin-top:2px"></i>
            <span>Les données sensibles sont chiffrées</span>
          </li>
        </ul>
      </div>
    </div>

    @if(isset($user))
    <div class="card">
      <div class="card-header">
        <i data-lucide="history" style="width:16px;height:16px"></i>
        <span>Historique</span>
      </div>
      <div class="card-body">
        <small style="color:var(--text-muted);display:flex;gap:8px;margin-bottom:8px">
          <i data-lucide="calendar" style="width:14px;height:14px;flex-shrink:0"></i>
          <span>Créé: {{ $user->created_at->format('d/m/Y H:i') }}</span>
        </small>
        <small style="color:var(--text-muted);display:flex;gap:8px">
          <i data-lucide="refresh-cw" style="width:14px;height:14px;flex-shrink:0"></i>
          <span>Modifié: {{ $user->updated_at->format('d/m/Y H:i') }}</span>
        </small>
      </div>
    </div>
    @endif
  </div>
</div>

@media (max-width: 768px) {
  div[style*="grid-template-columns:1fr 350px"] {
    grid-template-columns: 1fr !important;
  }
}

@endsection


