{{--
    |--------------------------------------------------------------------------
    | teacher/dashboard.blade.php — Tableau de Bord Enseignant
    |--------------------------------------------------------------------------
    | Phase 3 — Espace Enseignant
    | Classes, présences, notes récentes, tâches à faire
    --}}

@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Tableau de Bord Enseignant' : 'Teacher Dashboard');
@endphp

@section('title', $pageTitle)

@section('content')

@php
    $isFr    = app()->getLocale() === 'fr';
    $teacher = auth()->user()->teacher;
    $classes = $classes ?? collect();
    $today   = now();
@endphp

{{-- ─── Header ──────────────────────────────────────────────────────────────── --}}
<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb)">
                <i data-lucide="layout-dashboard"></i>
            </div>
            <div>
                <h1 class="page-title">
                    {{ $isFr ? 'Bonjour,' : 'Hello,' }}
                    {{ auth()->user()->first_name ?? explode(' ', auth()->user()->name)[0] }} 👋
                </h1>
                <p class="page-subtitle text-muted">
                    {{ $today->locale($isFr ? 'fr' : 'en')->isoFormat('dddd D MMMM YYYY') }}
                    @if($teacher?->is_prof_principal)
                    · <span style="color:var(--primary);font-weight:700">⭐ {{ $isFr ? 'Prof. Principal' : 'Head Teacher' }} — {{ $teacher->headClass?->name }}</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('teacher.attendance.index') }}" class="btn btn-primary btn-sm">
                <i data-lucide="calendar-check" style="width:14px" class="me-1"></i>
                {{ $isFr ? 'Appel du jour' : "Today's roll call" }}
            </a>
        </div>
    </div>
</div>

{{-- ─── KPIs ──────────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    {{-- Total Students --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ $isFr ? 'Élèves au total' : 'Total students' }}</p>
                        <h2 class="fw-bold mb-0">{{ $totalStudents ?? 0 }}</h2>
                    </div>
                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary">👥</div>
                </div>
                <div class="mt-2 small text-muted">{{ $classes->count() }} {{ $isFr ? 'classe(s)' : 'class(es)' }}</div>
            </div>
        </div>
    </div>

    {{-- Classes --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ $isFr ? 'Classes' : 'Classes' }}</p>
                        <h2 class="fw-bold mb-0">{{ $classes->count() }}</h2>
                    </div>
                    <div class="kpi-icon bg-info bg-opacity-10 text-info">📚</div>
                </div>
                <div class="mt-2 small text-muted">{{ $isFr ? 'Gérées' : 'Managed' }}</div>
            </div>
        </div>
    </div>

    {{-- Attendance Today --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ $isFr ? "Présence aujourd'hui" : "Today's attendance" }}</p>
                        <h2 class="fw-bold mb-0">{{ $attendanceToday ?? 0 }}<small style="font-size:0.6em">%</small></h2>
                    </div>
                    <div class="kpi-icon bg-success bg-opacity-10 text-success">✓</div>
                </div>
                <div class="mt-2 small text-muted">{{ $isFr ? 'Présents' : 'Present' }}</div>
            </div>
        </div>
    </div>

    {{-- Pending Grades --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ $isFr ? 'Notes à saisir' : 'Grades to enter' }}</p>
                        <h2 class="fw-bold mb-0">{{ $pendingGrades ?? 0 }}</h2>
                    </div>
                    <div class="kpi-icon bg-warning bg-opacity-10 text-warning">📝</div>
                </div>
                <div class="mt-2 small text-muted">{{ $isFr ? 'En attente' : 'Pending' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row gy-4">

    {{-- My Classes --}}
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0"><i data-lucide="grid" style="width:16px" class="me-2"></i>{{ $isFr ? 'Mes Classes' : 'My Classes' }}</h6>
            </div>
            <div class="card-body">
                <div class="row gy-3">
                    @forelse($classes as $class)
                    @php
                        $section = $class->section === 'anglophone' ? '🇬🇧' : '🇫🇷';
                        $count   = $class->students_count ?? $class->students->count();
                    @endphp
                    <div class="col-md-6">
                        <div style="border:1.5px solid var(--border);border-radius:var(--radius-lg);padding:1rem;transition:all .2s ease"
                             onmouseover="this.style.borderColor='var(--primary)';this.style.background='var(--primary-bg)'"
                             onmouseout="this.style.borderColor='var(--border)';this.style.background='transparent'">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span style="font-size:1.2rem">{{ $section }}</span>
                                <span class="fw-bold">{{ $class->name }}</span>
                                <span class="badge bg-secondary ms-auto">{{ $count }} {{ $isFr ? 'élèves' : 'students' }}</span>
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                <a href="{{ route('teacher.attendance.index', ['class_id' => $class->id]) }}" class="btn btn-sm btn-light" style="flex:1;justify-content:center">
                                    <i data-lucide="calendar" style="width:12px" class="me-1"></i>{{ $isFr ? 'Appel' : 'Roll call' }}
                                </a>
                                <a href="{{ route('teacher.bulletin.dashboard', $class->id) }}" class="btn btn-sm btn-primary" style="flex:1;justify-content:center">
                                    <i data-lucide="table-2" style="width:12px" class="me-1"></i>{{ $isFr ? 'Notes' : 'Grades' }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center text-muted py-3">
                        {{ $isFr ? 'Aucune classe affectée.' : 'No classes assigned.' }}
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent grades entered --}}
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0"><i data-lucide="clock" style="width:16px" class="me-2"></i>{{ $isFr ? 'Notes récemment saisies' : 'Recently entered grades' }}</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ $isFr ? 'Élève' : 'Student' }}</th>
                                <th>{{ $isFr ? 'Matière' : 'Subject' }}</th>
                                <th style="text-align:center">{{ $isFr ? 'Note' : 'Grade' }}</th>
                                <th>{{ $isFr ? 'Date' : 'Date' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentGrades ?? [] as $mark)
                            @php $score = (float)$mark->score; $c = $score>=16?'#10b981':($score>=13?'#3b82f6':($score>=10?'#f59e0b':'#ef4444')); @endphp
                            <tr>
                                <td class="fw-semibold" style="font-size:.83rem">{{ $mark->student?->user?->name }}</td>
                                <td style="font-size:.8rem;color:var(--text-muted)">{{ $mark->subject?->name }}</td>
                                <td style="text-align:center"><span style="background:{{ $c }}22;color:{{ $c }};padding:.18rem .55rem;border-radius:12px;font-size:.76rem;font-weight:700">{{ number_format($score,2) }}/20</span></td>
                                <td style="font-size:.75rem;color:var(--text-muted)">{{ $mark->updated_at?->format('d/m H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted">{{ $isFr ? 'Aucune note saisie récemment.' : 'No grades entered recently.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Right sidebar --}}
    <div class="col-lg-5">

        {{-- Today's schedule --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="card-title mb-0"><i data-lucide="clock" style="width:16px" class="me-2"></i>{{ $isFr ? "Programme d'aujourd'hui" : "Today's schedule" }}</h6></div>
            <div class="card-body">
                @forelse($todaySchedule ?? [] as $slot)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="text-align:center;min-width:52px">
                        <div style="font-size:.7rem;font-weight:700;color:var(--text-muted)">{{ $slot->start_time }}</div>
                        <div style="font-size:.7rem;color:var(--text-muted)">{{ $slot->end_time }}</div>
                    </div>
                    <div style="width:3px;height:42px;background:var(--primary);border-radius:2px;flex-shrink:0"></div>
                    <div>
                        <div class="fw-bold" style="font-size:.85rem">{{ $slot->subject?->name }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted)">{{ $slot->class?->name }} · {{ $slot->room ?? ($isFr ? 'Salle TBD' : 'Room TBD') }}</div>
                    </div>
                </div>
                @empty
                <p class="text-muted mb-0" style="font-size:.83rem">{{ $isFr ? "Aucun cours aujourd'hui." : 'No classes today.' }}</p>
                @endforelse
            </div>
        </div>

        {{-- To-do: Bulletins to submit --}}
        @if(($teacher?->is_prof_principal) && ($pendingBulletins ?? 0) > 0)
        <div class="card mb-4" style="border-color:#f59e0b">
            <div class="card-body" style="background:#fffbeb">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i data-lucide="alert-triangle" style="width:18px;color:#d97706"></i>
                    <span class="fw-bold" style="color:#92400e">{{ $isFr ? 'Bulletins à soumettre' : 'Bulletins to submit' }}</span>
                </div>
                <p style="font-size:.82rem;color:#92400e;margin-bottom:.75rem">
                    {{ $pendingBulletins }} {{ $isFr ? 'bulletins non soumis pour validation.' : 'bulletins pending submission.' }}
                </p>
                <a href="{{ route('teacher.bulletin.dashboard', $teacher->head_class_id) }}" class="btn btn-warning btn-sm">
                    {{ $isFr ? 'Saisir les notes' : 'Enter grades' }} →
                </a>
            </div>
        </div>
        @endif

        {{-- Quick access: OCR Digitizer --}}
        @if($teacher?->is_prof_principal && $teacher?->head_class_id)
        <div class="card">
            <div class="card-body text-center py-4">
                <i data-lucide="scan" style="width:32px;color:var(--primary);display:block;margin:0 auto .75rem"></i>
                <div class="fw-bold mb-1">{{ $isFr ? 'Digitaliseur de Bulletin' : 'Bulletin Digitizer' }}</div>
                <p class="text-muted mb-3" style="font-size:.8rem">{{ $isFr ? 'Numérisez un bulletin papier en quelques clics.' : 'Digitize a paper bulletin in a few clicks.' }}</p>
                
                    <i data-lucide="scan" style="width:13px" class="me-1"></i>{{ $isFr ? 'Ouvrir le digitaliseur' : 'Open digitizer' }}
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
