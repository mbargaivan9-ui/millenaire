@extends('layouts.app')

@section('title', __('absences.absence_details'))

@section('content')

<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('student-absences.index') }}">{{ __('absences.student_absences') }}</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ __('absences.absence_details') }}</span>
    </div>
    <h1 class="page-title">{{ __('absences.absence_details') }}</h1>
  </div>
  <div style="display:flex;gap:8px">
    <a href="{{ route('student-absences.edit', $absence->id) }}" class="btn btn-outline">
      <i data-lucide="edit" style="width:16px;height:16px"></i>
      {{ __('absences.edit') }}
    </a>
    <form method="POST" action="{{ route('student-absences.destroy', $absence->id) }}" style="display:inline" onsubmit="return confirm('{{ __('absences.confirm_delete') }}')">
      @csrf
      @method('DELETE')
      <button type="submit" class="btn btn-outline" style="color:var(--danger)">
        <i data-lucide="trash-2" style="width:16px;height:16px"></i>
        {{ __('absences.delete') }}
      </button>
    </form>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
  {{-- Student Information Card --}}
  <div class="card">
    <div class="card-header">
      <i data-lucide="user" style="width:16px;height:16px"></i>
      <span>{{ __('absences.student_information') }}</span>
    </div>
    <div class="card-body">
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px">
        @if($absence->student->user->profile_photo)
          <img src="{{ $absence->student->user->avatar_url }}" style="width:60px;height:60px;border-radius:50%;object-fit:cover">
        @else
          <div style="width:60px;height:60px;border-radius:50%;background:var(--primary-bg);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:600;font-size:20px">
            {{ substr($absence->student->user->name ?? 'U', 0, 1) }}
          </div>
        @endif
        <div>
          <div style="font-size:16px;font-weight:600">{{ $absence->student->user->name ?? $absence->student->name }}</div>
          <div style="font-size:13px;color:var(--text-muted)">{{ $absence->student->student_number ?? __('absences.no_number') }}</div>
        </div>
      </div>

      @php
        $age = now()->diffInYears($absence->student->user->date_of_birth);
      @endphp

      <div style="display:grid;gap:12px">
        <div>
          <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:4px">{{ __('absences.matric_number') }}</div>
          <div style="font-size:13px">{{ $absence->student->student_number ?? '-' }}</div>
        </div>
        <div>
          <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:4px">{{ __('absences.date_of_birth') }}</div>
          <div style="font-size:13px">{{ $absence->student->user->date_of_birth?->format('d/m/Y') ?? '-' }} ({{ $age }} {{ __('absences.years_old') }})</div>
        </div>
        <div>
          <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:4px">{{ __('absences.email') }}</div>
          <div style="font-size:13px">{{ $absence->student->user->email ?? '-' }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Absence Details Card --}}
  <div class="card">
    <div class="card-header">
      <i data-lucide="calendar" style="width:16px;height:16px"></i>
      <span>{{ __('absences.absence_details') }}</span>
    </div>
    <div class="card-body">
      <div style="display:grid;gap:16px">
        {{-- Date --}}
        <div>
          <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:8px">{{ __('absences.date') }}</div>
          <div style="font-size:13px">{{ $absence->date->format('l, d F Y') }}</div>
        </div>

        {{-- Status Badge --}}
        <div>
          <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:8px">{{ __('absences.status') }}</div>
          <div>
            @if($absence->status === 'absent')
              <span style="padding:6px 12px;border-radius:6px;background:var(--danger-bg);color:var(--danger);font-size:12px;font-weight:600;display:inline-block">
                <i data-lucide="x-circle" style="width:14px;height:14px;display:inline;margin-right:4px"></i>
                {{ __('absences.absent') }}
              </span>
            @elseif($absence->status === 'late')
              <span style="padding:6px 12px;border-radius:6px;background:var(--warning-bg);color:var(--warning);font-size:12px;font-weight:600;display:inline-block">
                <i data-lucide="clock" style="width:14px;height:14px;display:inline;margin-right:4px"></i>
                {{ __('absences.late') }}
              </span>
            @else
              <span style="padding:6px 12px;border-radius:6px;background:var(--success-bg);color:var(--success);font-size:12px;font-weight:600;display:inline-block">
                <i data-lucide="check-circle" style="width:14px;height:14px;display:inline;margin-right:4px"></i>
                {{ __('absences.present') }}
              </span>
            @endif
          </div>
        </div>

        {{-- Justification Status --}}
        <div>
          <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:8px">{{ __('absences.justification_status') }}</div>
          <div>
            @if($absence->justification_reason)
              <span style="padding:6px 12px;border-radius:6px;background:var(--success-bg);color:var(--success);font-size:12px;font-weight:600;display:inline-block">
                <i data-lucide="check-circle" style="width:14px;height:14px;display:inline;margin-right:4px"></i>
                {{ __('absences.justified') }}
              </span>
            @else
              <span style="padding:6px 12px;border-radius:6px;background:rgba(255,193,7,0.1);color:#ffc107;font-size:12px;font-weight:600;display:inline-block">
                <i data-lucide="alert-circle" style="width:14px;height:14px;display:inline;margin-right:4px"></i>
                {{ __('absences.not_justified') }}
              </span>
            @endif
          </div>
        </div>

        {{-- Metadata --}}
        <div style="border-top:1px solid var(--border-light);padding-top:12px">
          <div style="font-size:12px;color:var(--text-muted)">
            <div><strong>{{ __('absences.recorded_by') }}:</strong> {{ $absence->recorded_by }}</div>
            <div><strong>{{ __('absences.recorded_at') }}:</strong> {{ $absence->recorded_at?->format('d/m/Y H:i') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Justification Section --}}
@if($absence->justification_reason)
  <div class="card" style="margin-bottom:24px">
    <div class="card-header">
      <i data-lucide="check-circle" style="width:16px;height:16px;color:var(--success)"></i>
      <span>{{ __('absences.justification') }}</span>
    </div>
    <div class="card-body">
      <div style="margin-bottom:16px">
        <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:8px">{{ __('absences.reason') }}</div>
        <div style="font-size:13px;line-height:1.6;white-space:pre-wrap;word-break:break-word">{{ $absence->justification_reason }}</div>
      </div>

      @if($absence->justification_document)
        <div style="border-top:1px solid var(--border-light);padding-top:16px">
          <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:12px">{{ __('absences.supporting_documents') }}</div>
          <div style="padding:12px;background:var(--surface-2);border-radius:var(--radius-sm);display:flex;justify-content:space-between;align-items:center">
            <div style="display:flex;align-items:center;gap:8px">
              <i data-lucide="file" style="width:18px;height:18px;color:var(--primary)"></i>
              <div>
                <div style="font-size:13px;font-weight:500">{{ basename($absence->justification_document) }}</div>
                <div style="font-size:11px;color:var(--text-muted)">{{ __('absences.size') }}: {{ formatFileSize(Storage::disk('public')->size($absence->justification_document)) }}</div>
              </div>
            </div>
            <a href="{{ Storage::disk('public')->url($absence->justification_document) }}" target="_blank" class="btn btn-sm btn-primary">
              <i data-lucide="download" style="width:14px;height:14px"></i>
              {{ __('absences.download') }}
            </a>
          </div>
        </div>
      @endif
    </div>
  </div>
@else
  {{-- Add Justification Section --}}
  <div class="card">
    <div class="card-header">
      <i data-lucide="alert-circle" style="width:16px;height:16px;color:var(--warning)"></i>
      <span>{{ __('absences.add_justification') }}</span>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('student-absences.justify', $absence->id) }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
          <label class="form-label" for="justification_reason">
            {{ __('absences.justification_reason') }}
            <span style="color:var(--danger)">*</span>
          </label>
          <textarea name="justification_reason" id="justification_reason" rows="4" class="form-control @error('justification_reason') is-invalid @enderror" placeholder="{{ __('absences.enter_justification_reason') }}" required>{{ old('justification_reason') }}</textarea>
          @error('justification_reason')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="form-group">
          <label class="form-label" for="justification_document">
            {{ __('absences.supporting_document') }}
          </label>
          <input type="file" name="justification_document" id="justification_document" accept=".pdf,.jpg,.jpeg,.png" class="form-control @error('justification_document') is-invalid @enderror">
          <small style="color:var(--text-muted);display:block;margin-top:8px">{{ __('absences.supported_formats') }}: PDF, JPG, PNG ({{ __('absences.max_size') }}: 5MB)</small>
          @error('justification_document')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div style="display:flex;gap:12px">
          <button type="submit" class="btn btn-primary">
            <i data-lucide="save" style="width:16px;height:16px"></i>
            {{ __('absences.add_justification') }}
          </button>
        </div>
      </form>
    </div>
  </div>
@endif

@endsection
