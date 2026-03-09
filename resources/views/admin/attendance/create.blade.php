@extends('layouts.app')
@section('title', 'Ajouter Absence Enseignant')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('admin.attendance.index') }}" style="color:var(--primary);text-decoration:none">Absences Enseignants</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">Ajouter</span>
    </div>
    <h1 class="page-title">Ajouter une Absence Enseignant</h1>
    <p class="page-subtitle">Enregistrer absence d'un membre du personnel enseignant</p>
  </div>
</div>

<div style="max-width:700px">
  <div class="card">
    <div class="card-header">
      <i data-lucide="plus-circle" style="width:16px;height:16px"></i>
      <span>Nouvelle Absence Enseignant</span>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.attendance.store') }}" enctype="multipart/form-data">
        @csrf

        <div style="margin-bottom:20px">
          <label class="form-label">Enseignant *</label>
          <select name="teacher_id" class="form-control @error('teacher_id') is-invalid @enderror" required>
            <option value="">-- Sélectionnez un enseignant --</option>
            @foreach($teachers ?? [] as $teacher)
              <option value="{{ $teacher->id }}">{{ $teacher->user?->name ?? $teacher->id }} - {{ $teacher->specialization ?? '-' }}</option>
            @endforeach
          </select>
          @error('teacher_id')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Date *</label>
          <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" required value="{{ old('date', date('Y-m-d')) }}">
          @error('date')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Statut *</label>
          <select name="status" class="form-control @error('status') is-invalid @enderror" required>
            <option value="">-- Sélectionnez --</option>
            <option value="present">Présent</option>
            <option value="absent">Absent</option>
            <option value="late">En retard</option>
            <option value="justified">Justifié</option>
            <option value="medical_leave">Congé maladie</option>
            <option value="authorized_leave">Congé autorisé</option>
          </select>
          @error('status')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Raison/Motif</label>
          <textarea name="reason" class="form-control" rows="3" placeholder="Raison de l'absence..." maxlength="500">{{ old('reason') }}</textarea>
          <small style="color:var(--text-muted);font-size:12px">500 caractères max</small>
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Document Justificatif (Optionnel)</label>
          <input type="file" name="justification_document" class="form-control @error('justification_document') is-invalid @enderror" 
                 accept=".pdf,.jpg,.jpeg,.png">
          <small style="color:var(--text-muted);font-size:12px">Formats acceptés: PDF, JPG, PNG (Max: 5MB)</small>
          @error('justification_document')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="display:flex;gap:12px">
          <button type="submit" class="btn btn-primary">
            <i data-lucide="save" style="width:14px;height:14px"></i>
            Enregistrer
          </button>
          <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">
            Annuler
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection


