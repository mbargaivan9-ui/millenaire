{{-- teacher/attendance/history.blade.php --}}
@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Historique des présences' : 'Attendance History');
@endphp

@section('title', $pageTitle)
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('teacher.attendance.index') }}" class="btn btn-light btn-sm">
            <i data-lucide="arrow-left" style="width:14px"></i>
        </a>
        <div class="page-icon" style="background:linear-gradient(135deg,#10b981,#059669)">
            <i data-lucide="history"></i>
        </div>
        <div>
            <h1 class="page-title">{{ $isFr ? 'Historique des présences' : 'Attendance History' }}</h1>
            <p class="page-subtitle text-muted">{{ $class?->name }}</p>
        </div>
    </div>
</div>

{{-- Stats strip --}}
@if($absences->isNotEmpty())
@php
    $total   = $absences->count();
    $present = $absences->where('status', 'present')->count();
    $absent  = $absences->where('status', 'absent')->count();
    $late    = $absences->where('status', 'late')->count();
    $pct     = $total > 0 ? round(($present / $total) * 100) : 0;
@endphp
<div class="row gy-3 mb-4">
    @foreach([
        [$isFr ? 'Présences' : 'Present', $present, '#059669'],
        [$isFr ? 'Absences' : 'Absent',  $absent,  '#dc2626'],
        [$isFr ? 'Retards' : 'Late',      $late,    '#d97706'],
        [$isFr ? 'Taux présence' : 'Rate', $pct.'%', '#3b82f6'],
    ] as [$lbl, $val, $col])
    <div class="col-md-3 col-6">
        <div class="stat-card text-center">
            <div class="stat-value" style="color:{{ $col }}">{{ $val }}</div>
            <div class="stat-label">{{ $lbl }}</div>
        </div>
    </div>
    @endforeach
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ $isFr ? 'Élève' : 'Student' }}</th>
                        <th>{{ $isFr ? 'Date' : 'Date' }}</th>
                        <th style="text-align:center">{{ $isFr ? 'Statut' : 'Status' }}</th>
                        <th>{{ $isFr ? 'Justifié' : 'Justified' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absences as $entry)
                    @php
                    $map = [
                        'present' => ['bg-success', $isFr ? 'Présent' : 'Present'],
                        'absent'  => ['bg-danger',  $isFr ? 'Absent'  : 'Absent'],
                        'late'    => ['bg-warning', $isFr ? 'Retard'  : 'Late'],
                        'excused' => ['bg-info',    $isFr ? 'Excusé'  : 'Excused'],
                    ];
                    [$bg, $label] = $map[$entry->status] ?? ['bg-secondary', $entry->status];
                    @endphp
                    <tr>
                        <td class="fw-semibold" style="font-size:.85rem">
                            {{ $entry->student?->user?->name ?? '—' }}
                        </td>
                        <td style="font-size:.82rem">
                            {{ $entry->date?->locale($isFr ? 'fr' : 'en')->isoFormat('ddd D MMM YYYY') }}
                        </td>
                        <td style="text-align:center">
                            <span class="badge {{ $bg }}" style="font-size:.72rem">{{ $label }}</span>
                        </td>
                        <td style="font-size:.8rem">
                            @if($entry->is_justified)
                            <span style="color:#059669">✓ {{ $isFr ? 'Oui' : 'Yes' }}</span>
                            @else
                            <span style="color:var(--text-muted)">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i data-lucide="calendar-check" style="width:32px;opacity:.3;display:block;margin:0 auto .75rem"></i>
                            {{ $isFr ? 'Aucun historique de présence.' : 'No attendance history.' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($absences, 'links'))
        <div class="card-footer">{{ $absences->links() }}</div>
        @endif
    </div>
</div>

@endsection
