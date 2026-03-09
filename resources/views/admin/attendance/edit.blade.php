@extends('layouts.app')
@section('title', 'Éditer Absence Enseignant')

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('admin.attendance.index') }}" style="color:var(--primary);text-decoration:none">Absences Enseignants</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">Éditer</span>
    </div>
    <h1 class="page-title">Éditer Absence Enseignant</h1>
    <p class="page-subtitle">Modifier l'absence d'un enseignant</p>
  </div>
</div>

<div style="max-width:700px">
  <div class="card">
    <div class="card-header">
      <i data-lucide="edit" style="width:16px;height:16px"></i>
      <span>Éditer Absence Enseignant</span>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.attendance.update', $attendance) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div style="margin-bottom:20px">
          <label class="form-label">Enseignant</label>
          <div style="padding:10px;background:var(--text-bg);border-radius:4px;display:flex;align-items:center;gap:10px">
            <i data-lucide="user" style="width:16px;height:16px;color:var(--text-muted)"></i>
            <span>{{ $attendance->teacher->user->name }} - {{ $attendance->teacher->specialization ?? '-' }}</span>
          </div>
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Date</label>
          <div style="padding:10px;background:var(--text-bg);border-radius:4px;display:flex;align-items:center;gap:10px">
            <i data-lucide="calendar" style="width:16px;height:16px;color:var(--text-muted)"></i>
            <span>{{ $attendance->date->format('d/m/Y') }}</span>
          </div>
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Statut *</label>
          <select name="status" class="form-control @error('status') is-invalid @enderror" required>
            <option value="present" {{ old('status', $attendance->status) === 'present' ? 'selected' : '' }}>Présent</option>
            <option value="absent" {{ old('status', $attendance->status) === 'absent' ? 'selected' : '' }}>Absent</option>
            <option value="late" {{ old('status', $attendance->status) === 'late' ? 'selected' : '' }}>En retard</option>
            <option value="justified" {{ old('status', $attendance->status) === 'justified' ? 'selected' : '' }}>Justifié</option>
            <option value="medical_leave" {{ old('status', $attendance->status) === 'medical_leave' ? 'selected' : '' }}>Congé maladie</option>
            <option value="authorized_leave" {{ old('status', $attendance->status) === 'authorized_leave' ? 'selected' : '' }}>Congé autorisé</option>
          </select>
          @error('status')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Raison/Motif</label>
          <textarea name="reason" class="form-control" rows="3" maxlength="500">{{ old('reason', $attendance->reason) }}</textarea>
          <small style="color:var(--text-muted);font-size:12px">500 caractères max</small>
        </div>

        <div style="margin-bottom:20px">
          <label class="form-label">Document Justificatif</label>
          @if($attendance->justification_document)
            <div style="padding:10px;background:var(--text-bg);border-radius:4px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center">
              <span style="font-size:13px">
                <i data-lucide="file" style="width:14px;height:14px;display:inline;margin-right:8px"></i>
                {{ basename($attendance->justification_document) }}
              </span>
              <a href="{{ Storage::disk('public')->url($attendance->justification_document) }}" target="_blank" class="btn btn-sm" style="background:var(--primary-bg);color:var(--primary)">Voir</a>
            </div>
          @endif
          <input type="file" name="justification_document" class="form-control @error('justification_document') is-invalid @enderror" 
                 accept=".pdf,.jpg,.jpeg,.png">
          <small style="color:var(--text-muted);font-size:12px">Formats acceptés: PDF, JPG, PNG (Max: 5MB)</small>
          @error('justification_document')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:20px;padding:12px;background:var(--text-bg);border-radius:4px;border-left:3px solid var(--info)">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
            <input type="checkbox" name="is_approved" value="1" {{ $attendance->is_approved ? 'checked' : '' }} id="approved" style="width:18px;height:18px;cursor:pointer">
            <label for="approved" style="cursor:pointer;margin:0">
              <strong>Approuvé</strong>
            </label>
          </div>
          <small style="color:var(--text-muted);font-size:12px;display:block;margin-left:28px">
            L'absence a-t-elle été approuvée par l'administration ?
          </small>

        <div style="display:flex;gap:12px">
          <button type="submit" class="btn btn-primary">
            <i data-lucide="save" style="width:14px;height:14px"></i>
            Mettre à jour
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


