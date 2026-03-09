@extends('layouts.app')

@section('title', __('absences.absence_report'))

@section('content')

<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <a href="{{ route('student-absences.index') }}">{{ __('absences.student_absences') }}</a>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ __('absences.absence_report') }}</span>
    </div>
    <h1 class="page-title">{{ __('absences.absence_report') }}</h1>
    <p class="page-subtitle">{{ __('absences.class_student_absence_statistics', ['class' => $classe->name]) }}</p>
  </div>
  <button class="btn btn-outline" onclick="window.print()">
    <i data-lucide="printer" style="width:16px;height:16px"></i>
    {{ __('absences.print_report') }}
  </button>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
  <div class="card" style="text-align:center;padding:20px">
    <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:8px">{{ __('absences.total_students') }}</div>
    <div style="font-size:28px;font-weight:700;color:var(--primary)">{{ $stats['total_students'] }}</div>
  </div>
  <div class="card" style="text-align:center;padding:20px">
    <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:8px">{{ __('absences.total_absence_records') }}</div>
    <div style="font-size:28px;font-weight:700;color:var(--info)">{{ $stats['total_records'] }}</div>
  </div>
  <div class="card" style="text-align:center;padding:20px">
    <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:8px">{{ __('absences.justified_absences') }}</div>
    <div style="font-size:28px;font-weight:700;color:var(--success)">{{ $stats['justified'] }}</div>
  </div>
  <div class="card" style="text-align:center;padding:20px">
    <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-bottom:8px">{{ __('absences.unjustified_absences') }}</div>
    <div style="font-size:28px;font-weight:700;color:var(--danger)">{{ $stats['unjustified'] }}</div>
  </div>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:24px">
  <div class="card-body">
    <form method="GET" class="row g-3">
      <div class="col-md-3">
        <label class="form-label">{{ __('absences.start_date') }}</label>
        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">{{ __('absences.end_date') }}</label>
        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">{{ __('absences.sort_by') }}</label>
        <select name="sort_by" class="form-control">
          <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>{{ __('absences.student_name') }}</option>
          <option value="absences" {{ request('sort_by') === 'absences' ? 'selected' : '' }}>{{ __('absences.most_absences') }}</option>
          <option value="justified" {{ request('sort_by') === 'justified' ? 'selected' : '' }}>{{ __('absences.most_justified') }}</option>
        </select>
      </div>
      <div class="col-md-3" style="display:flex;align-items:flex-end">
        <button type="submit" class="btn btn-primary w-100">
          <i data-lucide="filter" style="width:16px;height:16px"></i>
          {{ __('absences.filter') }}
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Student Absence Report Table --}}
<div class="card">
  <div class="card-header">
    <i data-lucide="table" style="width:16px;height:16px"></i>
    <span>{{ __('absences.student_absence_summary') }}</span>
  </div>
  <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse">
      <thead>
        <tr style="border-bottom:1px solid var(--border);background:var(--surface-2)">
          <th style="padding:12px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary)">#</th>
          <th style="padding:12px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.student') }}</th>
          <th style="padding:12px;text-align:center;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.total_absences') }}</th>
          <th style="padding:12px;text-align:center;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.late_arrivals') }}</th>
          <th style="padding:12px;text-align:center;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.justified') }}</th>
          <th style="padding:12px;text-align:center;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.unjustified') }}</th>
          <th style="padding:12px;text-align:center;font-size:12px;font-weight:600;color:var(--text-secondary)">{{ __('absences.percentage') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($studentReports as $index => $report)
          <tr style="border-bottom:1px solid var(--border-light)" onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='transparent'">
            <td style="padding:12px;font-size:13px;color:var(--text-muted)">{{ $index + 1 }}</td>
            <td style="padding:12px;font-size:13px">
              <div style="display:flex;align-items:center;gap:8px">
                @if($report['student']->user->profile_photo)
                  <img src="{{ $report['student']->user->avatar_url }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover">
                @else
                  <div style="width:32px;height:32px;border-radius:50%;background:var(--primary-bg);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600">
                    {{ substr($report['student']->user->name ?? 'U', 0, 1) }}
                  </div>
                @endif
                <div>
                  <div style="font-weight:500">{{ $report['student']->user->name ?? $report['student']->name }}</div>
                  <div style="font-size:11px;color:var(--text-muted)">{{ $report['student']->student_number ?? '-' }}</div>
                </div>
              </div>
            </td>
            <td style="padding:12px;text-align:center;font-size:13px;font-weight:600">
              <span style="padding:4px 8px;border-radius:4px;background:var(--info-bg);color:var(--info)">{{ $report['total_absences'] }}</span>
            </td>
            <td style="padding:12px;text-align:center;font-size:13px;font-weight:600">
              <span style="padding:4px 8px;border-radius:4px;background:var(--warning-bg);color:var(--warning)">{{ $report['late_arrivals'] }}</span>
            </td>
            <td style="padding:12px;text-align:center;font-size:13px;font-weight:600">
              <span style="padding:4px 8px;border-radius:4px;background:var(--success-bg);color:var(--success)">{{ $report['justified'] }}</span>
            </td>
            <td style="padding:12px;text-align:center;font-size:13px;font-weight:600">
              <span style="padding:4px 8px;border-radius:4px;background:var(--danger-bg);color:var(--danger)">{{ $report['unjustified'] }}</span>
            </td>
            <td style="padding:12px;text-align:center;font-size:13px">
              <div style="display:flex;align-items:center;justify-content:center;gap:8px">
                <div style="width:60px;height:6px;background:var(--border);border-radius:3px;overflow:hidden">
                  <div style="height:100%;background:var(--success);width:{{ $report['percentage'] }}%;transition:width 0.3s"></div>
                </div>
                <span>{{ number_format($report['percentage'], 1) }}%</span>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" style="padding:32px;text-align:center;color:var(--text-muted)">
              {{ __('absences.no_data_available') }}
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Print Styles --}}
<style>
@media print {
  .page-header,
  .card-body form {
    display: none;
  }

  .card {
    page-break-inside: avoid;
    box-shadow: none;
    border: 1px solid #ddd;
  }

  table {
    page-break-inside: avoid;
  }

  tr {
    page-break-inside: avoid;
  }

  body {
    background: white;
    color: black;
  }

  h1, h2, h3 {
    page-break-after: avoid;
  }
}
</style>

@endsection
