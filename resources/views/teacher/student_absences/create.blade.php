@extends('layouts.app')

@section('title', __('absences.record_absence'))

@section('content')

<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('student-absences.index') }}">{{ __('absences.student_absences') }}</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ __('absences.record_absence') }}</span>
    </div>
    <h1 class="page-title">{{ __('absences.record_absence') }}</h1>
    <p class="page-subtitle">{{ __('absences.record_student_absence_desc') }}</p>
  </div>
</div>

<div style="max-width:600px;margin:0 auto">
  <div class="card">
    <form method="POST" action="{{ route('student-absences.store') }}" enctype="multipart/form-data">
      @csrf

      {{-- Student Selection --}}
      <div class="form-group">
        <label class="form-label" for="student_id">
          {{ __('absences.student') }}
          <span style="color:var(--danger)">*</span>
        </label>
        <select name="student_id" id="student_id" class="form-control @error('student_id') is-invalid @enderror" required>
          <option value="">{{ __('absences.select_student') }}</option>
          @foreach($students as $student)
            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
              {{ $student->user->name ?? $student->name }}
            </option>
          @endforeach
        </select>
        @error('student_id')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      {{-- Date --}}
      <div class="form-group">
        <label class="form-label" for="date">
          {{ __('absences.date') }}
          <span style="color:var(--danger)">*</span>
        </label>
        <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', now()->format('Y-m-d')) }}" required>
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
          <option value="">{{ __('absences.select_status') }}</option>
          <option value="absent" {{ old('status') === 'absent' ? 'selected' : '' }}>{{ __('absences.absent') }}</option>
          <option value="late" {{ old('status') === 'late' ? 'selected' : '' }}>{{ __('absences.late') }}</option>
          <option value="present" {{ old('status') === 'present' ? 'selected' : '' }}>{{ __('absences.present') }}</option>
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
        <textarea name="justification_reason" id="justification_reason" rows="4" class="form-control @error('justification_reason') is-invalid @enderror" placeholder="{{ __('absences.enter_justification_reason') }}">{{ old('justification_reason') }}</textarea>
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
        <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" placeholder="{{ __('absences.additional_notes') }}">{{ old('notes') }}</textarea>
        @error('notes')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      {{-- Form Actions --}}
      <div style="display:flex;gap:12px;margin-top:24px;border-top:1px solid var(--border);padding-top:24px">
        <button type="submit" class="btn btn-primary flex-1">
          <i data-lucide="save" style="width:16px;height:16px"></i>
          {{ __('absences.record_absence') }}
        </button>
        <a href="{{ route('student-absences.index') }}" class="btn btn-outline flex-1">
          {{ __('app.cancel') }}
        </a>
      </div>
    </form>
  </div>
</div>

@endsection
