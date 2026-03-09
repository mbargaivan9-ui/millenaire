@extends('layouts.app')
@section('title', 'Créer Nouvel Horaire')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('admin.schedule.index') }}" style="color:var(--primary);text-decoration:none">Horaires</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">Créer</span>
    </div>
    <h1 class="page-title">Créer un Nouvel Horaire</h1>
    <p class="page-subtitle">Ajouter un horaire pour une classe</p>
  </div>
</div>

<div style="max-width:700px">
  <div class="card">
    <div class="card-header">
      <i data-lucide="calendar-plus" style="width:16px;height:16px"></i>
      <span>Nouvel Horaire</span>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.schedule.store') }}">
        @csrf

        <div style="margin-bottom:20px">
          <label class="form-label">Classe *</label>
          <select name="classe_id" class="form-control @error('classe_id') is-invalid @enderror" required>
            <option value="">-- Sélectionnez une classe --</option>
            @foreach($classes ?? [] as $classe)
              <option value="{{ $classe->id }}">{{ $classe->name }}</option>
            @endforeach
          </select>
          @error('classe_id')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Matière *</label>
          <select name="subject_id" class="form-control @error('subject_id') is-invalid @enderror" required>
            <option value="">-- Sélectionnez une matière --</option>
            @foreach($subjects ?? [] as $subject)
              <option value="{{ $subject->id }}">{{ $subject->name }}</option>
            @endforeach
          </select>
          @error('subject_id')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Enseignant *</label>
          <select name="teacher_id" class="form-control @error('teacher_id') is-invalid @enderror" required>
            <option value="">-- Sélectionnez un enseignant --</option>
            @foreach($teachers ?? [] as $teacher)
              <option value="{{ $teacher->id }}">{{ $teacher->user?->name }}</option>
            @endforeach
          </select>
          @error('teacher_id')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Jour de la Semaine *</label>
          <select name="day_of_week" class="form-control @error('day_of_week') is-invalid @enderror" required>
            <option value="">-- Sélectionnez --</option>
            <option value="Monday">Lundi</option>
            <option value="Tuesday">Mardi</option>
            <option value="Wednesday">Mercredi</option>
            <option value="Thursday">Jeudi</option>
            <option value="Friday">Vendredi</option>
            <option value="Saturday">Samedi</option>
          </select>
          @error('day_of_week')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px">
          <div>
            <label class="form-label">Heure de Début *</label>
            <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" required>
            @error('start_time')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
          </div>
          <div>
            <label class="form-label">Heure de Fin *</label>
            <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" required>
            @error('end_time')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
          </div>
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Salle / Classe</label>
          <input type="text" name="room" class="form-control" placeholder="Ex: Salle 101" value="{{ old('room') }}">
        </div>

        <div style="display:flex;gap:12px">
          <button type="submit" class="btn btn-primary">
            <i data-lucide="save" style="width:14px;height:14px"></i>
            Créer l'Horaire
          </button>
          <a href="{{ route('admin.schedule.index') }}" class="btn btn-secondary">
            Annuler
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection


