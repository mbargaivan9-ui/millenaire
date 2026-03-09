@extends('layouts.app')
@section('title', isset($assignment) ? 'Éditer Affectation' : 'Créer Affectation')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('admin.assignments.index') }}" style="color:var(--primary);text-decoration:none">Affectations</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ isset($assignment) ? 'Éditer' : 'Créer' }}</span>
    </div>
    <h1 class="page-title">{{ isset($assignment) ? 'Éditer Affectation' : 'Créer Affectation' }}</h1>
    <p class="page-subtitle">{{ isset($assignment) ? 'Modifier l\'affectation d\'un professeur' : 'Affecter un professeur à une classe' }}</p>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 350px;gap:20px;margin-bottom:40px">
  {{-- Main Content --}}
  <div>
    <div class="card">
      <div class="card-header">
        <i data-lucide="user-check" style="width:16px;height:16px"></i>
        <span>{{ isset($assignment) ? 'Éditer' : 'Nouvelle' }} Affectation</span>
      </div>
      <div class="card-body">
        <form action="{{ isset($assignment) ? route('admin.assignments.update', $assignment) : route('admin.assignments.store') }}" 
              method="POST">
          @csrf
          @if(isset($assignment))
            @method('PUT')
          @endif

          {{-- Professeur Selection --}}
          <div style="margin-bottom:20px">
            <label class="form-label">Professeur *</label>
            <select class="form-control @error('prof_id') is-invalid @enderror" 
                    name="prof_id" required>
              <option value="">-- Sélectionnez un professeur --</option>
              @foreach($teachers ?? [] as $teacher)
              <option value="{{ $teacher->id }}" 
                      {{ (isset($assignment) && $assignment->prof_id === $teacher->id) || old('prof_id') == $teacher->id ? 'selected' : '' }}>
                {{ $teacher->user?->name ?? $teacher->name }} ({{ $teacher->user?->email ?? $teacher->email }})
              </option>
              @endforeach
            </select>
            @error('prof_id')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
          </div>

          {{-- Class Selection --}}
          <div style="margin-bottom:20px">
            <label class="form-label">Classe *</label>
            <select class="form-control @error('class_id') is-invalid @enderror" 
                    name="class_id" required>
              <option value="">-- Sélectionnez une classe --</option>
              @foreach($classes ?? [] as $clase)
              <option value="{{ $clase->id }}" 
                      {{ (isset($assignment) && $assignment->class_id === $clase->id) || old('class_id') == $clase->id ? 'selected' : '' }}>
                {{ $clase->name }}
              </option>
              @endforeach
            </select>
            @error('class_id')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
          </div>

          {{-- Subject Selection --}}
          <div style="margin-bottom:20px">
            <label class="form-label">Matière *</label>
            <select class="form-control @error('subject_id') is-invalid @enderror" 
                    name="subject_id" required>
              <option value="">-- Sélectionnez une matière --</option>
              @foreach($subjects ?? [] as $subject)
              <option value="{{ $subject->id }}" 
                      {{ (isset($assignment) && $assignment->subject_id === $subject->id) || old('subject_id') == $subject->id ? 'selected' : '' }}>
                {{ $subject->name }} ({{ $subject->code }})
              </option>
              @endforeach
            </select>
            @error('subject_id')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
          </div>

          {{-- Row: Schedule and Room --}}
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px">
            <div>
              <label class="form-label">Horaires (optionnel)</label>
              <input type="text" class="form-control" 
                     name="schedule" value="{{ $assignment->schedule ?? old('schedule') }}" 
                     placeholder="ex: Lun/Mer 10h-11h">
            </div>

            <div>
              <label class="form-label">Salle (optionnel)</label>
              <input type="text" class="form-control" 
                     name="room" value="{{ $assignment->room ?? old('room') }}" 
                     placeholder="ex: Salle 201">
            </div>
          </div>

          {{-- Notes --}}
          <div style="margin-bottom:20px">
            <label class="form-label">Remarques</label>
            <textarea class="form-control" name="notes" rows="3">{{ $assignment->notes ?? old('notes') }}</textarea>
          </div>

          {{-- Status --}}
          <div style="margin-bottom:20px">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
              <input type="checkbox" name="is_active" value="1" 
                     {{ (isset($assignment) && $assignment->is_active) || old('is_active') ? 'checked' : '' }} style="width:18px;height:18px">
              <span>Cette affectation est active</span>
            </label>
          </div>

          {{-- Actions --}}
          <div style="display:flex;gap:12px;justify-content:flex-end">
            <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">
              Annuler
            </a>
            <button type="submit" class="btn btn-primary">
              <i data-lucide="save" style="width:14px;height:14px"></i>
              {{ isset($assignment) ? 'Mettre à jour' : 'Créer' }}
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
            <i data-lucide="alert-circle" style="width:14px;height:14px;color:var(--warning);flex-shrink:0;margin-top:2px"></i>
            <span>Une affectation = 1 Prof + 1 Classe + 1 Matière</span>
          </li>
          <li style="display:flex;gap:8px;font-size:12px">
            <i data-lucide="calendar" style="width:14px;height:14px;color:var(--info);flex-shrink:0;margin-top:2px"></i>
            <span>Date de création auto-enregistrée</span>
          </li>
        </ul>
      </div>
    </div>

    @if(isset($assignment))
    <div class="card">
      <div class="card-header">
        <i data-lucide="history" style="width:16px;height:16px"></i>
        <span>Historique</span>
      </div>
      <div class="card-body">
        <small style="color:var(--text-muted);display:flex;gap:8px;margin-bottom:8px">
          <i data-lucide="calendar" style="width:14px;height:14px;flex-shrink:0"></i>
          <span>Créée: {{ $assignment->created_at->format('d/m/Y H:i') }}</span>
        </small>
        <small style="color:var(--text-muted);display:flex;gap:8px">
          <i data-lucide="refresh-cw" style="width:14px;height:14px;flex-shrink:0"></i>
          <span>Modifiée: {{ $assignment->updated_at->format('d/m/Y H:i') }}</span>
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


