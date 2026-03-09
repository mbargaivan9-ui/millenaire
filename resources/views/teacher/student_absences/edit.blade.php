@extends('layouts.app')

@section('title', __('absences.edit_absence'))

@section('content')

<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('student-absences.index') }}">{{ __('absences.student_absences') }}</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ __('absences.edit_absence') }}</span>
    </div>
    <h1 class="page-title">{{ __('absences.edit_absence') }}</h1>
    <p class="page-subtitle">{{ __('absences.update_absence_details') }}</p>
  </div>
</div>

<div style="max-width:600px;margin:0 auto">
  <div class="card">
    <form method="POST" action="{{ route('student-absences.update', $absence->id) }}" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      {{-- Student (Read-only) --}}
      <div class="form-group">
        <label class="form-label">
          {{ __('absences.student') }}
        </label>
        <div style="display:flex;align-items:center;gap:12px;padding:12px;background:var(--surface-2);border-radius:var(--radius-sm)">
          @if($absence->student->user->profile_photo)
            <img src="{{ $absence->student->user->avatar_url }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover">
          @else
            <div style="width:40px;height:40px;border-radius:50%;background:var(--primary-bg);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:600">
              {{ substr($absence->student->user->name ?? 'U', 0, 1) }}
            </div>
          @endif
          <span>{{ $absence->student->user->name ?? $absence->student->name }}</span>
        </div>
      </div>

      {{-- Date --}}
      <div class="form-group">
        <label class="form-label" for="date">
          {{ __('absences.date') }}
          <span style="color:var(--danger)">*</span>
        </label>
        <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $absence->date->format('Y-m-d')) }}" required>
        @error('date')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      {{-- Status --}}
      <div class="form-group">
        <label class="form-label" for="status">
          {{ __('absences.status') }}
          <span style="color:var(--danger)">*</span>
        </label>
        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
          <option value="absent" {{ old('status', $absence->status) === 'absent' ? 'selected' : '' }}>{{ __('absences.absent') }}</option>
          <option value="late" {{ old('status', $absence->status) === 'late' ? 'selected' : '' }}>{{ __('absences.late') }}</option>
          <option value="present" {{ old('status', $absence->status) === 'present' ? 'selected' : '' }}>{{ __('absences.present') }}</option>
        </select>
        @error('status')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      {{-- Justification Reason --}}
      <div class="form-group">
        <label class="form-label" for="justification_reason">
          {{ __('absences.justification_reason') }}
        </label>
        <textarea name="justification_reason" id="justification_reason" rows="4" class="form-control @error('justification_reason') is-invalid @enderror" placeholder="{{ __('absences.enter_justification_reason') }}">{{ old('justification_reason', $absence->justification_reason) }}</textarea>
        @error('justification_reason')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      {{-- Justification Document --}}
      <div class="form-group">
        <label class="form-label" for="justification_document">
          {{ __('absences.justification_document') }}
          <span style="font-size:12px;color:var(--text-muted)">{{ __('absences.pdf_max_5mb') }}</span>
        </label>
        
        @if($absence->justification_document)
          <div style="padding:12px;background:var(--surface-2);border-radius:var(--radius-sm);margin-bottom:12px;display:flex;justify-content:space-between;align-items:center">
            <div style="display:flex;align-items:center;gap:8px">
              <i data-lucide="file" style="width:16px;height:16px;color:var(--primary)"></i>
              <span style="font-size:13px">{{ basename($absence->justification_document) }}</span>
            </div>
            <a href="{{ Storage::disk('public')->url($absence->justification_document) }}" target="_blank" class="btn btn-sm btn-outline">
              {{ __('absences.download') }}
            </a>
          </div>
        @endif

        <div class="form-file-input">
          <input type="file" name="justification_document" id="justification_document" accept=".pdf,.jpg,.jpeg,.png" class="form-control @error('justification_document') is-invalid @enderror">
          <small style="color:var(--text-muted);display:block;margin-top:8px">{{ __('absences.supported_formats') }}: PDF, JPG, PNG ({{ __('absences.max_size') }}: 5MB)</small>
        </div>
        @error('justification_document')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      {{-- Notes --}}
      <div class="form-group">
        <label class="form-label" for="notes">
          {{ __('absences.notes') }}
        </label>
        <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" placeholder="{{ __('absences.additional_notes') }}">{{ old('notes', $absence->notes) }}</textarea>
        @error('notes')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      {{-- Metadata --}}
      @if($absence->recorded_by || $absence->recorded_at)
        <div style="padding:12px;background:var(--surface-2);border-radius:var(--radius-sm);margin-top:16px;font-size:12px;color:var(--text-muted)">
          <div><strong>{{ __('absences.recorded_by') }}:</strong> {{ $absence->recorded_by }}</div>
          <div><strong>{{ __('absences.recorded_at') }}:</strong> {{ $absence->recorded_at?->format('d/m/Y H:i') }}</div>
        </div>
      @endif

      {{-- Form Actions --}}
      <div style="display:flex;gap:12px;margin-top:24px;border-top:1px solid var(--border);padding-top:24px">
        <button type="submit" class="btn btn-primary flex-1">
          <i data-lucide="save" style="width:16px;height:16px"></i>
          {{ __('absences.update_absence') }}
        </button>
        <a href="{{ route('student-absences.index') }}" class="btn btn-outline flex-1">
          {{ __('app.cancel') }}
        </a>
      </div>
    </form>
  </div>
</div>

@endsection
