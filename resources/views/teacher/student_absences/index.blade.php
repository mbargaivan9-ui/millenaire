@extends('layouts.app')

@section('title', __('absences.student_absences'))

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ __('absences.student_absences') }}</span>
    </div>
    <h1 class="page-title">{{ __('absences.manage_student_absences') }}</h1>
    <p class="page-subtitle">{{ __('absences.manage_class_student_absences', ['class' => $classe->name]) }}</p>
  </div>
  <a href="{{ route('student-absences.create') }}" class="btn btn-primary">
    <i data-lucide="plus" style="width:16px;height:16px"></i>
    {{ __('absences.record_absence') }}
  </a>
</div>

{{-- Statistics Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
  <div class="card" style="text-align:center;padding:20px">
    <div style="font-size:28px;font-weight:700;color:var(--primary)">{{ $stats['total_records'] }}</div>
    <div style="font-size:12px;color:var(--text-muted);margin-top:8px">{{ __('absences.total_records') }}</div>
  </div>
  <div class="card" style="text-align:center;padding:20px">
    <div style="font-size:28px;font-weight:700;color:var(--danger)">{{ $stats['absences'] }}</div>
    <div style="font-size:12px;color:var(--text-muted);margin-top:8px">{{ __('absences.total_absences') }}</div>
  </div>
  <div class="card" style="text-align:center;padding:20px">
    <div style="font-size:28px;font-weight:700;color:var(--warning)">{{ $stats['late'] }}</div>
    <div style="font-size:12px;color:var(--text-muted);margin-top:8px">{{ __('absences.late_arrivals') }}</div>
  </div>
  <div class="card" style="text-align:center;padding:20px">
    <div style="font-size:28px;font-weight:700;color:var(--success)">{{ $stats['justified'] }}</div>
    <div style="font-size:12px;color:var(--text-muted);margin-top:8px">{{ __('absences.justified_absences') }}</div>
  </div>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:24px">
  <div class="card-body">
    <form method="GET" class="row g-3">
      <div class="col-md-3">
        <label class="form-label">{{ __('absences.student') }}</label>
        <select name="student_id" class="form-control">
          <option value="">{{ __('absences.all_students') }}</option>
          @foreach($students as $student)
            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
              {{ $student->user->name ?? $student->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">{{ __('absences.status') }}</label>
        <select name="status" class="form-control">
          <option value="">{{ __('absences.all_statuses') }}</option>
          <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>{{ __('absences.absent') }}</option>
          <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>{{ __('absences.late') }}</option>
          <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>{{ __('absences.present') }}</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">{{ __('absences.start_date') }}</label>
        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
      </div>
      <div class="col-md-2">
        <label class="form-label">{{ __('absences.end_date') }}</label>
        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
      </div>
      <div class="col-md-2" style="display:flex;align-items:flex-end;gap:8px">
        <button type="submit" class="btn btn-primary w-100">
          <i data-lucide="filter" style="width:16px;height:16px"></i>
          {{ __('absences.filter') }}
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Quick Actions --}}
<div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap">
  <a href="{{ route('student-absences.bulk-create-form') }}" class="btn btn-outline">
    <i data-lucide="list" style="width:16px;height:16px"></i>
    {{ __('absences.bulk_record') }}
  </a>
  <a href="{{ route('student-absences.report') }}" class="btn btn-outline">
    <i data-lucide="download" style="width:16px;height:16px"></i>
    {{ __('absences.generate_report') }}
  </a>
</div>

{{-- Absences Table --}}
<div class="card">
  <div class="card-header">
    <i data-lucide="users" style="width:16px;height:16px"></i>
    <span>{{ __('absences.student_absence_records') }}</span>
  </div>
  <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse">
      <thead>
        <tr style="border-bottom:1px solid var(--border)">
          <th style="padding:12px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.date') }}</th>
          <th style="padding:12px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.student') }}</th>
          <th style="padding:12px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.status') }}</th>
          <th style="padding:12px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.justification') }}</th>
          <th style="padding:12px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.recorded_by') }}</th>
          <th style="padding:12px;text-align:center;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($absences as $absence)
          <tr style="border-bottom:1px solid var(--border-light);transition:var(--transition)" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='transparent'">
            <td style="padding:12px;font-size:13px">
              {{ $absence->date->format('d/m/Y') }}
            </td>
            <td style="padding:12px;font-size:13px">
              <div style="display:flex;align-items:center;gap:8px">
                @if($absence->student->user->profile_photo)
                  <img src="{{ $absence->student->user->avatar_url }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover">
                @else
                  <div style="width:32px;height:32px;border-radius:50%;background:var(--primary-bg);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600">
                    {{ substr($absence->student->user->name ?? 'U', 0, 1) }}
                  </div>
                @endif
                <span>{{ $absence->student->user->name ?? $absence->student->name }}</span>
              </div>
            </td>
            <td style="padding:12px;font-size:13px">
              @if($absence->status === 'absent')
                <span style="padding:4px 8px;border-radius:4px;background:var(--danger-bg);color:var(--danger);font-size:11px;font-weight:600">{{ __('absences.absent') }}</span>
              @elseif($absence->status === 'late')
                <span style="padding:4px 8px;border-radius:4px;background:var(--warning-bg);color:var(--warning);font-size:11px;font-weight:600">{{ __('absences.late') }}</span>
              @else
                <span style="padding:4px 8px;border-radius:4px;background:var(--success-bg);color:var(--success);font-size:11px;font-weight:600">{{ __('absences.present') }}</span>
              @endif
            </td>
            <td style="padding:12px;font-size:13px">
              @if($absence->justification_reason)
                <span style="padding:4px 8px;border-radius:4px;background:var(--success-bg);color:var(--success);font-size:11px;font-weight:600">
                  <i data-lucide="check-circle" style="width:14px;height:14px;display:inline"></i>
                  {{ __('absences.justified') }}
                </span>
              @else
                <span style="color:var(--text-muted);font-size:12px">-</span>
              @endif
            </td>
            <td style="padding:12px;font-size:13px;color:var(--text-muted)">
              {{ $absence->recorded_by }}
            </td>
            <td style="padding:12px;text-align:center;font-size:13px">
              <div style="display:flex;gap:8px;justify-content:center">
                <a href="{{ route('student-absences.show', $absence->id) }}" class="btn-icon" title="{{ __('absences.view') }}">
                  <i data-lucide="eye" style="width:16px;height:16px"></i>
                </a>
                <a href="{{ route('student-absences.edit', $absence->id) }}" class="btn-icon" title="{{ __('absences.edit') }}">
                  <i data-lucide="edit" style="width:16px;height:16px"></i>
                </a>
                <form method="POST" action="{{ route('student-absences.destroy', $absence->id) }}" style="display:inline" onsubmit="return confirm('{{ __('absences.confirm_delete') }}')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn-icon" title="{{ __('absences.delete') }}" style="color:var(--danger)">
                    <i data-lucide="trash-2" style="width:16px;height:16px"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="padding:32px;text-align:center;color:var(--text-muted)">
              {{ __('absences.no_records_found') }}
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Pagination --}}
<div style="margin-top:24px">
  {{ $absences->links() }}
</div>

<style>
.btn-icon {
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width:36px;
  height:36px;
  border-radius:var(--radius-sm);
  color:var(--text-secondary);
  transition:var(--transition);
  cursor:pointer;
  border:none;
  background:transparent;
}

.btn-icon:hover {
  background:var(--surface-2);
  color:var(--text-primary);
}
</style>

@endsection
