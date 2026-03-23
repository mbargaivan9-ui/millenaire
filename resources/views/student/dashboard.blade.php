{{--
    |--------------------------------------------------------------------------
    | student/dashboard.blade.php — Tableau de Bord Étudiant
    |--------------------------------------------------------------------------
    | Phase 7 — Espace Étudiant
    | Planning, notes, e-learning, chat, bulletins
    --}}

@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Mon Espace' : 'My Space');
@endphp

@section('title', $pageTitle)

@push('styles')
<style>
.schedule-day {
    background: var(--surface-2); border-radius: var(--radius);
    padding: .5rem; min-width: 120px;
    border-top: 3px solid var(--primary);
}
.schedule-slot {
    background: var(--surface); border-radius: 8px; padding: .5rem .75rem;
    margin-bottom: .4rem; border-left: 3px solid var(--primary-light);
    font-size: .78rem;
}
.subject-badge {
    display: inline-block; padding: .2rem .55rem; border-radius: 12px;
    font-size: .72rem; font-weight: 700;
}
.course-card {
    border: 1px solid var(--border); border-radius: var(--radius-lg);
    overflow: hidden; transition: all .2s ease;
}
.course-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
.course-thumb {
    height: 100px; background: linear-gradient(135deg, var(--primary), var(--primary-light));
    display: flex; align-items: center; justify-content: center; font-size: 2.5rem;
}
</style>
@endpush

@section('content')

@php
    $isFr   = app()->getLocale() === 'fr';
    $student = auth()->user()->student;
    $classe  = $student?->classe;
    $marks   = $recentMarks ?? collect();
    $courses = $courses ?? collect();
    $today   = $todaySchedule ?? collect();
    $moyenne = $bulletinData['moyenne'] ?? null;
    $rang    = $bulletinData['rang'] ?? null;
@endphp

{{-- Header --}}
<div class="page-header mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb)">
            <i data-lucide="graduation-cap"></i>
        </div>
        <div>
            <h1 class="page-title">{{ $isFr ? 'Mon Espace Scolaire' : 'My School Space' }}</h1>
            <p class="page-subtitle text-muted">
                {{ $student?->user?->display_name ?? auth()->user()->name }}
                · {{ $classe?->name }}
                @if($classe?->section === 'anglophone') 🇬🇧 @else 🇫🇷 @endif
            </p>
        </div>
    </div>
</div>

{{-- KPI --}}
<div class="row g-3 mb-4">
    {{-- Overall Average --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ $isFr ? 'Moyenne Générale' : 'Overall Average' }}</p>
                        <h2 class="fw-bold mb-0">{{ $moyenne !== null ? number_format((float)$moyenne, 2) : '—' }}<small style="font-size:0.6em">/20</small></h2>
                    </div>
                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary">📊</div>
                </div>
                <div class="mt-2 small text-muted">{{ $isFr ? 'Performance' : 'Performance' }}</div>
            </div>
        </div>
    </div>

    {{-- Rank --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ $isFr ? 'Rang' : 'Rank' }}</p>
                        <h2 class="fw-bold mb-0">{{ $rang ? $rang . 'e' : '—' }}</h2>
                    </div>
                    <div class="kpi-icon bg-warning bg-opacity-10 text-warning">🏆</div>
                </div>
                <div class="mt-2 small text-muted">{{ $isFr ? 'Classement' : 'Ranking' }}</div>
            </div>
        </div>
    </div>

    {{-- Available Courses --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ $isFr ? 'Cours disponibles' : 'Available courses' }}</p>
                        <h2 class="fw-bold mb-0">{{ $courses->count() }}</h2>
                    </div>
                    <div class="kpi-icon bg-info bg-opacity-10 text-info">📚</div>
                </div>
                <div class="mt-2 small text-muted">{{ $isFr ? 'À étudier' : 'To study' }}</div>
            </div>
        </div>
    </div>

    {{-- Quizzes Passed --}}
    <div class="col-6 col-lg-3">
        <div class="card kpi-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">{{ $isFr ? 'Quiz réussis' : 'Quizzes passed' }}</p>
                        <h2 class="fw-bold mb-0">{{ $quizzesPassed ?? 0 }}</h2>
                    </div>
                    <div class="kpi-icon bg-success bg-opacity-10 text-success">✓</div>
                </div>
                <div class="mt-2 small text-muted">{{ $isFr ? 'Réussis' : 'Passed' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row gy-4">
    {{-- Left --}}
    <div class="col-lg-8">

        {{-- Today's schedule --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">
                    <i data-lucide="calendar" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Emploi du Temps — Aujourd\'hui' : 'Today\'s Schedule' }}
                    <span class="text-muted fw-normal ms-2" style="font-size:.8rem">{{ now()->locale($isFr ? 'fr' : 'en')->isoFormat('dddd D MMMM') }}</span>
                </h6>
                <a href="{{ route('student.schedule') }}" class="btn btn-sm btn-light">{{ $isFr ? 'Voir tout' : 'View all' }}</a>
            </div>
            <div class="card-body">
                @if($today->isEmpty())
                    <p class="text-center text-muted py-2 mb-0" style="font-size:.85rem">
                        🎉 {{ $isFr ? 'Aucun cours aujourd\'hui !' : 'No classes today!' }}
                    </p>
                @else
                    @foreach($today as $slot)
                    <div class="schedule-slot mb-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold" style="font-size:.82rem">{{ $slot->start_time }} – {{ $slot->end_time }}</span>
                            <span class="subject-badge" style="background:var(--primary-bg);color:var(--primary)">{{ $slot->subject?->name }}</span>
                        </div>
                        <div class="text-muted" style="font-size:.72rem">{{ $slot->teacher?->user?->name }} · {{ $slot->room ?? ($isFr ? 'Salle non définie' : 'Room TBD') }}</div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- E-Learning courses --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">
                    <i data-lucide="play-circle" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Mes Cours en Ligne' : 'My Online Courses' }}
                </h6>
                <a href="{{ route('student.courses.index') }}" class="btn btn-sm btn-light">{{ $isFr ? 'Tous les cours' : 'All courses' }}</a>
            </div>
            <div class="card-body">
                @if($courses->isEmpty())
                    <p class="text-center text-muted py-2 mb-0" style="font-size:.85rem">{{ $isFr ? 'Aucun cours disponible.' : 'No courses available.' }}</p>
                @else
                <div class="row gy-3">
                    @foreach($courses->take(4) as $course)
                    <div class="col-md-6">
                        <div class="course-card">
                            <div class="course-thumb">
                                @if($course->type === 'video') 🎬
                                @elseif($course->type === 'pdf') 📄
                                @else 📋
                                @endif
                            </div>
                            <div class="p-3">
                                <div class="fw-bold mb-1" style="font-size:.85rem">{{ Str::limit($course->title, 40) }}</div>
                                <div class="text-muted mb-2" style="font-size:.75rem">{{ $course->subject?->name }} · {{ $course->teacher?->user?->name }}</div>
                                <a href="{{ route('student.courses.show', $course->id) }}" class="btn btn-primary btn-sm w-100">
                                    <i data-lucide="play" style="width:12px" class="me-1"></i>
                                    {{ $isFr ? 'Accéder' : 'Open' }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Right --}}
    <div class="col-lg-4">

        {{-- Recent grades --}}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-lucide="bar-chart-2" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Dernières Notes' : 'Recent Grades' }}
                </h6>
            </div>
            <div class="card-body p-0">
                @forelse($marks->take(8) as $mark)
                @php $s = (float)$mark->score; $c = $s>=16?'#10b981':($s>=13?'#3b82f6':($s>=10?'#f59e0b':'#ef4444')); @endphp
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <div>
                        <div class="fw-semibold" style="font-size:.82rem">{{ Str::limit($mark->subject?->name, 20) }}</div>
                        <div class="text-muted" style="font-size:.72rem">{{ $mark->updated_at?->format('d/m') }}</div>
                    </div>
                    <span class="subject-badge" style="background:{{ $c }}22;color:{{ $c }}">
                        {{ number_format($s, 2) }}/20
                    </span>
                </div>
                @empty
                <p class="text-center text-muted py-3 mb-0" style="font-size:.83rem">{{ $isFr ? 'Aucune note.' : 'No grades.' }}</p>
                @endforelse
                <div class="p-2">
                    <a href="{{ route('student.bulletins.index') }}" class="btn btn-light btn-sm w-100">
                        {{ $isFr ? 'Voir le bulletin' : 'View report card' }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Pending quizzes --}}
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-lucide="clipboard-list" style="width:16px" class="me-2"></i>
                    {{ $isFr ? 'Quiz en Attente' : 'Pending Quizzes' }}
                </h6>
            </div>
            <div class="card-body">
                @forelse($pendingQuizzes ?? [] as $quiz)
                <div class="d-flex align-items-center justify-content-between mb-2 p-2 rounded-3" style="background:var(--surface-2)">
                    <div>
                        <div class="fw-semibold" style="font-size:.82rem">{{ Str::limit($quiz->title, 28) }}</div>
                        <div class="text-muted" style="font-size:.72rem">
                            {{ $quiz->subject?->name }}
                            @if($quiz->available_until)
                            · {{ $isFr ? 'Expire' : 'Expires' }} {{ $quiz->available_until->format('d/m H:i') }}
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('student.quiz.show', $quiz->id) }}" class="btn btn-warning btn-sm">
                        {{ $isFr ? 'Faire' : 'Start' }}
                    </a>
                </div>
                @empty
                <p class="text-center text-muted mb-0" style="font-size:.83rem">
                    ✅ {{ $isFr ? 'Aucun quiz en attente.' : 'No pending quizzes.' }}
                </p>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection
