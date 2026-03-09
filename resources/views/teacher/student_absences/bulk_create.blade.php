@extends('layouts.app')

@section('title', __('absences.bulk_record_absences'))

@section('content')

<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('student-absences.index') }}">{{ __('absences.student_absences') }}</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ __('absences.bulk_record_absences') }}</span>
    </div>
    <h1 class="page-title">{{ __('absences.bulk_record_absences') }}</h1>
    <p class="page-subtitle">{{ __('absences.record_multiple_absences_at_once') }}</p>
  </div>
</div>

<div class="card">
    <form method="POST" action="{{ route('student-absences.bulk-create') }}" id="bulk-form">
    @csrf

    {{-- Bulk Options Header --}}
    <div class="card-header">
      <span>{{ __('absences.record_options') }}</span>
    </div>

    <div style="padding:20px;border-bottom:1px solid var(--border);display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
      {{-- Date --}}
      <div>
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
      <div>
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

      {{-- Select All Students --}}
      <div style="display:flex;align-items:flex-end">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:500">
          <input type="checkbox" id="select-all" style="width:18px;height:18px;cursor:pointer">
          {{ __('absences.select_all_students') }}
        </label>
      </div>
    </div>

    {{-- Students Table --}}
    <div style="overflow-x:auto;max-height:600px;overflow-y:auto">
      <table style="width:100%;border-collapse:collapse">
        <thead style="position:sticky;top:0;background:var(--surface-2);z-index:10">
          <tr style="border-bottom:1px solid var(--border)">
            <th style="padding:12px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary);width:50px">
              <input type="checkbox" id="header-checkbox" style="width:18px;height:18px;cursor:pointer">
            </th>
            <th style="padding:12px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.student') }}</th>
            <th style="padding:12px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.matric_number') }}</th>
            <th style="padding:12px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.notes') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($students as $student)
            <tr style="border-bottom:1px solid var(--border-light);transition:var(--transition)" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='transparent'">
              <td style="padding:12px;text-align:center;width:50px">
                <input type="checkbox" name="students[]" value="{{ $student->id }}" class="student-checkbox" style="width:18px;height:18px;cursor:pointer" {{ in_array($student->id, old('students', [])) ? 'checked' : '' }}>
              </td>
              <td style="padding:12px">
                <div style="display:flex;align-items:center;gap:8px">
                  @if($student->user->profile_photo)
                    <img src="{{ $student->user->avatar_url }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover">
                  @else
                    <div style="width:32px;height:32px;border-radius:50%;background:var(--primary-bg);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600">
                      {{ substr($student->user->name ?? 'U', 0, 1) }}
                    </div>
                  @endif
                  <div>
                    <div style="font-size:13px;font-weight:500">{{ $student->user->name ?? $student->name }}</div>
                    <div style="font-size:11px;color:var(--text-muted)">{{ $student->user->email ?? '-' }}</div>
                  </div>
                </div>
              </td>
              <td style="padding:12px;font-size:13px">{{ $student->student_number ?? '-' }}</td>
              <td style="padding:12px;font-size:13px">
                <input type="text" name="notes[{{ $student->id }}]" placeholder="{{ __('absences.optional_notes') }}" class="form-control" value="{{ old('notes.' . $student->id) }}" style="font-size:12px">
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" style="padding:32px;text-align:center;color:var(--text-muted)">
                {{ __('absences.no_students_found') }}
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Form Actions --}}
    <div style="padding:20px;border-top:1px solid var(--border);display:flex;gap:12px;background:var(--surface-2)">
      <button type="submit" class="btn btn-primary">
        <i data-lucide="save" style="width:16px;height:16px"></i>
        {{ __('absences.record_selected_absences') }}
      </button>
      <a href="{{ route('student-absences.index') }}" class="btn btn-outline">
        {{ __('app.cancel') }}
      </a>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const headerCheckbox = document.getElementById('header-checkbox');
  const selectAllCheckbox = document.getElementById('select-all');
  const studentCheckboxes = document.querySelectorAll('.student-checkbox');

  // Select All functionality
  selectAllCheckbox.addEventListener('change', function() {
    studentCheckboxes.forEach(checkbox => {
      checkbox.checked = this.checked;
    });
    updateHeaderCheckbox();
  });

  // Individual checkbox change
  studentCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
      updateHeaderCheckbox();
    });
  });

  // Header checkbox
  headerCheckbox.addEventListener('change', function() {
    studentCheckboxes.forEach(checkbox => {
      checkbox.checked = this.checked;
    });
    selectAllCheckbox.checked = this.checked;
  });

  function updateHeaderCheckbox() {
    const checked = Array.from(studentCheckboxes).filter(cb => cb.checked).length;
    headerCheckbox.checked = checked === studentCheckboxes.length && studentCheckboxes.length > 0;
    headerCheckbox.indeterminate = checked > 0 && checked < studentCheckboxes.length;
  }

  // Form submission
  document.getElementById('bulk-form').addEventListener('submit', function(e) {
    const checkedCount = Array.from(studentCheckboxes).filter(cb => cb.checked).length;
    if (checkedCount === 0) {
      e.preventDefault();
      alert('{{ __('absences.please_select_at_least_one_student') }}');
      return false;
    }
  });
});
</script>

@endsection
