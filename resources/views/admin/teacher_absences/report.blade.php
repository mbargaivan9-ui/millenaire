@extends('layouts.app')

@section('title', __('teacher_absences.report_title'))

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>@lang('teacher_absences.report_title')</h2>
                <a href="{{ route('admin.teacher_absences.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> @lang('teacher_absences.back_to_list')
                </a>
            </div>

            {{-- Filter Card --}}
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="form-inline gap-3">
                        <div class="form-group">
                            <label for="start_date" class="mr-2">@lang('teacher_absences.start_date')</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ $startDate->format('Y-m-d') }}">
                        </div>

                        <div class="form-group">
                            <label for="end_date" class="mr-2">@lang('teacher_absences.end_date')</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ $endDate->format('Y-m-d') }}">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync"></i> @lang('teacher_absences.generate_report')
                        </button>
                    </form>
                </div>
            </div>

            {{-- Summary Statistics --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> @lang('teacher_absences.summary_by_teacher')
                        <span class="float-right small">
                            {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
                        </span>
                    </h5>
                </div>

                @if(count($summary) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>@lang('teacher_absences.teacher')</th>
                                    <th class="text-center">@lang('teacher_absences.total_days')</th>
                                    <th class="text-center">@lang('teacher_absences.unjustified_absences')</th>
                                    <th class="text-center">@lang('teacher_absences.justified_absences')</th>
                                    <th class="text-center">@lang('teacher_absences.absence_rate')</th>
                                    <th class="text-center">@lang('teacher_absences.attendance_rate')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summary as $item)
                                    @php
                                        $attendanceRate = 100 - $item['rate'];
                                        $rateColor = $item['rate'] > 10 ? 'danger' : ($item['rate'] > 5 ? 'warning' : 'success');
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $item['teacher']->user->avatar_url }}" 
                                                     alt="{{ $item['teacher']->user->name }}"
                                                     class="rounded-circle me-2" style="width: 32px; height: 32px;">
                                                <strong>{{ $item['teacher']->user->name }}</strong>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">{{ $item['total_days'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-danger">{{ $item['absences'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-warning">{{ $item['justified'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-{{ $rateColor }}">
                                                {{ $item['rate'] }}%
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-{{ $rateColor === 'success' ? 'success' : ($rateColor === 'warning' ? 'warning' : 'danger') }}">
                                                {{ $attendanceRate }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Summary Statistics Cards --}}
                    <div class="card-footer bg-light">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="mb-0">
                                    <h6 class="text-muted">@lang('teacher_absences.total_records')</h6>
                                    <h4>{{ array_sum(array_column($summary, 'total_days')) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-0">
                                    <h6 class="text-muted">@lang('teacher_absences.total_absences')</h6>
                                    <h4>{{ array_sum(array_column($summary, 'absences')) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-0">
                                    <h6 class="text-muted">@lang('teacher_absences.total_justified')</h6>
                                    <h4>{{ array_sum(array_column($summary, 'justified')) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <p>@lang('teacher_absences.no_records')</p>
                    </div>
                @endif
            </div>

            {{-- Export or Print Options --}}
            <div class="row">
                <div class="col-12">
                    <button type="button" class="btn btn-success" onclick="window.print()">
                        <i class="fas fa-print"></i> @lang('forms.print')
                    </button>
                    <button type="button" class="btn btn-info" onclick="exportToCSV()">
                        <i class="fas fa-download"></i> @lang('forms.export')
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportToCSV() {
    const table = document.querySelector('table');
    let csv = [];
    
    // Add headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => {
        headers.push(th.textContent.trim());
    });
    csv.push(headers.join(','));
    
    // Add rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            row.push('"' + td.textContent.trim().replace(/"/g, '""') + '"');
        });
        csv.push(row.join(','));
    });
    
    // Create download link
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', 'teacher_absences_report.csv');
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endsection


