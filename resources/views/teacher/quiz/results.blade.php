{{-- teacher/quiz/results.blade.php --}}
@extends('layouts.app')
@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Résultats du quiz' : 'Quiz Results');
@endphp
@section('title', $pageTitle)
@section('content')
@php
    $isFr    = app()->getLocale() === 'fr';
    $scores  = $submissions->pluck('score')->filter();
    $avg     = $scores->isNotEmpty() ? round($scores->avg(), 2) : null;
    $highest = $scores->max();
    $lowest  = $scores->min();
    $passed  = $submissions->where('score', '>=', $quiz->pass_score ?? 10)->count();
@endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('teacher.quizzes.index') }}" class="btn btn-light btn-sm">
            <i data-lucide="arrow-left" style="width:14px"></i>
        </a>
        <div class="page-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i data-lucide="bar-chart-2"></i></div>
        <div>
            <h1 class="page-title">{{ $quiz->title }}</h1>
            <p class="page-subtitle text-muted">{{ $isFr ? 'Résultats' : 'Results' }} · {{ $submissions->count() }} {{ $isFr ? 'soumissions' : 'submissions' }}</p>
        </div>
    </div>
</div>

<div class="row gy-3 mb-4">
    @foreach([
        [$isFr ? 'Moyenne' : 'Average',    $avg ?? '—', '#3b82f6'],
        [$isFr ? 'Note max' : 'Highest',   $highest ?? '—', '#059669'],
        [$isFr ? 'Note min' : 'Lowest',    $lowest ?? '—', '#ef4444'],
        [$isFr ? 'Admis' : 'Passed',       $passed.'/'.$submissions->count(), '#10b981'],
    ] as [$lbl, $val, $col])
    <div class="col-md-3 col-6">
        <div class="stat-card text-center">
            <div class="stat-value" style="color:{{ $col }}">{{ $val }}</div>
            <div class="stat-label">{{ $lbl }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ $isFr ? 'Élève' : 'Student' }}</th>
                    <th style="text-align:center">{{ $isFr ? 'Score' : 'Score' }}</th>
                    <th style="text-align:center">{{ $isFr ? 'Résultat' : 'Result' }}</th>
                    <th>{{ $isFr ? 'Terminé à' : 'Completed at' }}</th>
                    <th>{{ $isFr ? 'Durée' : 'Duration' }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions->sortByDesc('score') as $sub)
                @php
                    $pct = $quiz->max_score > 0 ? round(($sub->score / $quiz->max_score) * 100) : 0;
                    $col = $pct >= 80 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
                    $pass = $sub->score >= ($quiz->pass_score ?? 10);
                @endphp
                <tr>
                    <td class="fw-semibold" style="font-size:.85rem">{{ $sub->student?->user?->name ?? '—' }}</td>
                    <td style="text-align:center">
                        <span style="background:{{ $col }}22;color:{{ $col }};padding:.2rem .6rem;border-radius:12px;font-size:.82rem;font-weight:700">
                            {{ number_format($sub->score ?? 0, 1) }}/{{ $quiz->max_score ?? 20 }}
                        </span>
                    </td>
                    <td style="text-align:center">
                        @if($pass)
                        <span class="badge bg-success">{{ $isFr ? 'Admis' : 'Passed' }}</span>
                        @else
                        <span class="badge bg-danger">{{ $isFr ? 'Échec' : 'Failed' }}</span>
                        @endif
                    </td>
                    <td style="font-size:.78rem;color:var(--text-muted)">
                        {{ $sub->completed_at?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                    <td style="font-size:.78rem;color:var(--text-muted)">
                        @if($sub->started_at && $sub->completed_at)
                        {{ $sub->started_at->diffInMinutes($sub->completed_at) }} min
                        @else —
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        {{ $isFr ? 'Aucune soumission pour ce quiz.' : 'No submissions yet.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
