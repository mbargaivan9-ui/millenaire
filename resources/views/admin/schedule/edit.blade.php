@extends('layouts.app')
@section('title', 'Éditer Horaire')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('admin.schedule.index') }}" style="color:var(--primary);text-decoration:none">Horaires</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">Éditer</span>
    </div>
    <h1 class="page-title">Éditer Horaire</h1>
    <p class="page-subtitle">Modifier l'horaire</p>
  </div>
</div>

<div style="max-width:700px">
  <div class="card">
    <div class="card-header">
      <i data-lucide="calendar-edit" style="width:16px;height:16px"></i>
      <span>Éditer Horaire</span>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.schedule.update', $schedule) }}">
        @csrf @method('PUT')

        <div style="margin-bottom:20px">
          <label class="form-label">Classe *</label>
          <select name="classe_id" class="form-control @error('classe_id') is-invalid @enderror" required>
            @foreach($classes as $classe)
              <option value="{{ $classe->id }}" {{ old('classe_id', $schedule->classe_id) == $classe->id ? 'selected' : '' }}>
                {{ $classe->name }}
              </option>
            @endforeach
          </select>
          @error('classe_id')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Matière *</label>
          <select name="subject_id" class="form-control @error('subject_id') is-invalid @enderror" required>
            @foreach($subjects as $subject)
              <option value="{{ $subject->id }}" {{ old('subject_id', $schedule->subject_id) == $subject->id ? 'selected' : '' }}>
                {{ $subject->name }}
              </option>
            @endforeach
          </select>
          @error('subject_id')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Enseignant *</label>
          <select name="teacher_id" class="form-control @error('teacher_id') is-invalid @enderror" required>
            @foreach($teachers as $teacher)
              <option value="{{ $teacher->id }}" {{ old('teacher_id', $schedule->teacher_id) == $teacher->id ? 'selected' : '' }}>
                {{ $teacher->user?->name }}
              </option>
            @endforeach
          </select>
          @error('teacher_id')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Jour de la Semaine *</label>
          <select name="day_of_week" class="form-control @error('day_of_week') is-invalid @enderror" required>
            <option value="Monday" {{ old('day_of_week', $schedule->day_of_week) === 'Monday' ? 'selected' : '' }}>Lundi</option>
            <option value="Tuesday" {{ old('day_of_week', $schedule->day_of_week) === 'Tuesday' ? 'selected' : '' }}>Mardi</option>
            <option value="Wednesday" {{ old('day_of_week', $schedule->day_of_week) === 'Wednesday' ? 'selected' : '' }}>Mercredi</option>
            <option value="Thursday" {{ old('day_of_week', $schedule->day_of_week) === 'Thursday' ? 'selected' : '' }}>Jeudi</option>
            <option value="Friday" {{ old('day_of_week', $schedule->day_of_week) === 'Friday' ? 'selected' : '' }}>Vendredi</option>
            <option value="Saturday" {{ old('day_of_week', $schedule->day_of_week) === 'Saturday' ? 'selected' : '' }}>Samedi</option>
          </select>
          @error('day_of_week')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:20px">
          <div>
            <label class="form-label">Heure de Début *</label>
            <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                   value="{{ old('start_time', $schedule->start_time) }}" required>
            @error('start_time')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
          </div>
          <div>
            <label class="form-label">Heure de Fin *</label>
            <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror"
                   value="{{ old('end_time', $schedule->end_time) }}" required>
            @error('end_time')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
          </div>
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Salle / Classe</label>
          <input type="text" name="room" class="form-control" value="{{ old('room', $schedule->room) }}">
        </div>

        <div style="display:flex;gap:12px">
          <button type="submit" class="btn btn-primary">
            <i data-lucide="save" style="width:14px;height:14px"></i>
            Mettre à jour
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


