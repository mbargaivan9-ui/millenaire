@extends('layouts.app')
@section('title', 'Gestion des Utilisateurs')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">Utilisateurs</span>
    </div>
    <h1 class="page-title">Gestion des Utilisateurs</h1>
    <p class="page-subtitle">Gérer tous les utilisateurs du système</p>
  </div>
  <div class="page-actions">
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
      <i data-lucide="user-plus" style="width:14px;height:14px"></i>
      Ajouter Utilisateur
    </a>
  </div>
</div>

{{-- Filters Card --}}
<div class="card mb-20">
  <div class="card-header">
    <i data-lucide="filter" style="width:16px;height:16px"></i>
    <span>Filtres</span>
  </div>
  <div class="card-body">
    <form method="GET" class="search-filters">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;align-items:flex-end">
        <div>
          <label class="form-label">Recherche</label>
          <input type="text" class="form-control" name="search" 
                 placeholder="Nom ou email..." value="{{ request('search') }}">
        </div>
        <div>
          <label class="form-label">Rôle</label>
          <select class="form-control" name="role">
            <option value="">Tous les rôles</option>
            @foreach($roles ?? [] as $role)
            <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
              {{ ucfirst(str_replace('_', ' ', $role)) }}
            </option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="form-label">Statut</label>
          <select class="form-control" name="status">
            <option value="">Tous les statuts</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
          </select>
        </div>
        <div>
          <button type="submit" class="btn btn-primary w-100">
            <i data-lucide="search" style="width:13px;height:13px"></i>
            Filtrer
          </button>
        </div>
        <div>
          <a href="{{ route('admin.users.index') }}" class="btn btn-outline w-100">
            <i data-lucide="rotate-ccw" style="width:13px;height:13px"></i>
            Réinitialiser
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Users Table --}}
<div class="card">
  <div class="card-header">
    <i data-lucide="users" style="width:16px;height:16px"></i>
    <span>Utilisateurs</span>
    <span style="margin-left:auto;font-size:12px;color:var(--text-muted)">
      {{ $users?->total() ?? 0 }} total
    </span>
  </div>
  <div class="card-body">
    <div style="overflow-x:auto">
      <table class="table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Genre</th>
            <th>Téléphone</th>
            <th>Statut</th>
            <th>Dernière connexion</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users ?? [] as $user)
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <img src="{{ $user->avatar_url }}" class="avatar-sm" style="border-radius:50%;width:36px;height:36px;object-fit:cover" alt="{{ $user->name }}">
                <div>
                  <div style="font-weight:600;font-size:13px">{{ $user->name }}</div>
                  <div style="font-size:11px;color:var(--text-muted)">ID: {{ $user->id }}</div>
                </div>
              </div>
            </td>
            <td>
              <div style="font-size:12px">{{ $user->email }}</div>
            </td>
            <td>
              @php
                $roleColor = match($user->role ?? 'student') {
                  'admin' => 'var(--danger)',
                  'professeur', 'prof_principal' => 'var(--primary)',
                  'parent' => 'var(--success)',
                  'student' => 'var(--info)',
                  default => 'var(--text-muted)'
                };
              @endphp
              <span style="background:{{ $roleColor }}20;color:{{ $roleColor }};padding:4px 8px;border-radius:4px;
                           font-size:11px;font-weight:600">
                {{ ucfirst(str_replace('_', ' ', $user->role ?? 'student')) }}
              </span>
            </td>
            <td>
              <div style="font-size:12px">
                {{ $user->gender === 'M' ? '♂ Masculin' : ($user->gender === 'F' ? '♀ Féminin' : '-') }}
              </div>
            </td>
            <td>
              <div style="font-size:12px;color:var(--text-muted)">
                {{ $user->phoneNumber ?? '-' }}
              </div>
            </td>
            <td>
              @if($user->is_active)
                <span style="background:var(--success-bg);color:var(--success);padding:4px 8px;border-radius:4px;
                             font-size:11px;font-weight:600">Actif</span>
              @else
                <span style="background:var(--danger-bg);color:var(--danger);padding:4px 8px;border-radius:4px;
                             font-size:11px;font-weight:600">Inactif</span>
              @endif
            </td>
            <td>
              <div style="font-size:11px;color:var(--text-muted)">
                {{ $user->last_login?->diffForHumans() ?? 'Jamais' }}
              </div>
            </td>
            <td>
              <div style="display:flex;gap:6px">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm" title="Éditer"
                   style="background:var(--primary-bg);color:var(--primary)">
                  <i data-lucide="edit-2" style="width:13px;height:13px"></i>
                </a>
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline"
                      onsubmit="return confirm('Êtes-vous sûr ?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm" title="Supprimer"
                          style="background:var(--danger-bg);color:var(--danger)">
                    <i data-lucide="trash-2" style="width:13px;height:13px"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" style="text-align:center;padding:40px 20px">
              <i data-lucide="inbox" style="width:40px;height:40px;color:var(--text-muted);margin-bottom:16px;display:block"></i>
              <div style="color:var(--text-muted);font-size:13px">Aucun utilisateur trouvé</div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if($users?->hasPages())
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
      <small style="color:var(--text-muted);font-size:12px">
        Affichage {{ $users->firstItem() }} à {{ $users->lastItem() }} sur {{ $users->total() }} résultats
      </small>
      <div style="display:flex;gap:4px">
        {!! $users->links('pagination::simple-tailwind') !!}
      </div>
    </div>
    @endif
  </div>
</div>

@endsection
