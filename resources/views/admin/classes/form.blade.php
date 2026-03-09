@extends('layouts.app')
@section('title', isset($classe) ? 'Éditer Classe' : 'Créer Classe')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('admin.classes.index') }}" style="color:var(--primary);text-decoration:none">Classes</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ isset($classe) ? 'Éditer' : 'Créer' }}</span>
    </div>
    <h1 class="page-title">{{ isset($classe) ? 'Éditer Classe' : 'Créer Nouvelle Classe' }}</h1>
    <p class="page-subtitle">{{ isset($classe) ? 'Mettre à jour les informations' : 'Ajouter une nouvelle classe' }}</p>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 350px;gap:20px;margin-bottom:40px">
  {{-- Main Content --}}
  <div>
    <div class="card">
      <div class="card-header">
        <i data-lucide="layers" style="width:16px;height:16px"></i>
        <span>{{ isset($classe) ? 'Éditer Classe' : 'Nouvelle Classe' }}</span>
      </div>
      <div class="card-body">
        <form action="{{ isset($classe) ? route('admin.classes.update', $classe) : route('admin.classes.store') }}" method="POST">
          @csrf
          @if(isset($classe))
            @method('PUT')
          @endif

          {{-- Row 1: Nom et Niveau --}}
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px">
            <div>
              <label class="form-label">Nom de la Classe *</label>
              <input type="text" class="form-control @error('name') is-invalid @enderror" 
                     name="name" value="{{ $classe->name ?? old('name') }}" placeholder="ex: 6A, 3C, Terminale S1" required>
              @error('name')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
              <label class="form-label">{{ __('classes.level') }} *</label>
              <x-level-select 
                name="level" 
                :selected="$classe->level ?? null"
                placeholder="{{ __('classes.select_level') }}"
                required
              />
            </div>
          </div>

          {{-- Row 2: Section et Capacité --}}
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px">
            <div>
              <label class="form-label">Section (optionnel)</label>
              <input type="text" class="form-control" 
                     name="section" value="{{ $classe->section ?? old('section') }}" placeholder="ex: A, B, C">
            </div>

            <div>
              <label class="form-label">Capacité Maximale *</label>
              <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                     name="capacity" value="{{ $classe->capacity ?? old('capacity') }}" 
                     min="10" max="100" required>
              @error('capacity')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>
          </div>

          {{-- Professeur Principal --}}
          <div style="margin-bottom:20px">
            <label class="form-label">Professeur Principal</label>
            <select class="form-control @error('prof_principal_id') is-invalid @enderror" 
                    name="prof_principal_id">
              <option value="">-- Aucun (à assigner ultérieurement) --</option>
              @foreach($profPrincipals ?? [] as $prof)
              <option value="{{ $prof->id }}" 
                      {{ (isset($classe) && $classe->prof_principal_id === $prof->id) || old('prof_principal_id') == $prof->id ? 'selected' : '' }}>
                {{ $prof->user?->name ?? $prof->name }} ({{ $prof->user?->email ?? $prof->email }})
              </option>
              @endforeach
            </select>
            @error('prof_principal_id')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
          </div>

          {{-- Description --}}
          <div style="margin-bottom:20px">
            <label class="form-label">Description (optionnel)</label>
            <textarea class="form-control" name="description" rows="3" 
                      placeholder="Informations supplémentaires sur cette classe...">{{ $classe->description ?? old('description') }}</textarea>
          </div>

          {{-- Actions --}}
          <div style="display:flex;gap:12px">
            <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">
              Annuler
            </a>
            <button type="submit" class="btn btn-primary">
              <i data-lucide="save" style="width:14px;height:14px"></i>
              {{ isset($classe) ? 'Mettre à jour' : 'Créer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Sidebar --}}
  <div>
    <div class="card">
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
            <i data-lucide="users" style="width:14px;height:14px;color:var(--primary);flex-shrink:0;margin-top:2px"></i>
            <span>Capacité: nombre d'élèves maximum</span>
          </li>
          <li style="display:flex;gap:8px;font-size:12px">
            <i data-lucide="user-check" style="width:14px;height:14px;color:var(--info);flex-shrink:0;margin-top:2px"></i>
            <span>Professeur principal gère la classe</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

@media (max-width: 768px) {
  div[style*="grid-template-columns:1fr 350px"] {
    grid-template-columns: 1fr !important;
  }
}

@endsection


