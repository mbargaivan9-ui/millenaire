{{--
    |--------------------------------------------------------------------------
    | student/quiz-results.blade.php — Historique Résultats Quiz
    |--------------------------------------------------------------------------
    | Phase 5 — Section 5.2.2 — Résultats Quiz
    | Liste de tous les quiz complétés et leurs résultats
--}}

@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Historique des Résultats' : 'Quiz History');
@endphp

@section('title', $pageTitle)

@push('styles')
<style>
.results-table { 
    border-collapse: collapse; 
    width: 100%; 
    margin: 20px 0;
}

.results-table thead {
    background: var(--surface-2);
    border-bottom: 2px solid var(--border);
}

.results-table th {
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.results-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    font-size: 0.9rem;
}

.results-table tbody tr:hover {
    background: var(--surface-2);
    transition: background 0.15s ease;
}

.score-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    font-weight: 700;
    font-size: 1.1rem;
}

.score-badge.passed {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.score-badge.failed {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.passed {
    background: #ecfdf5;
    color: #059669;
}

.status-badge.failed {
    background: #fef2f2;
    color: #dc2626;
}

.status-badge.pending {
    background: #fffbeb;
    color: #d97706;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.action-buttons a {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s ease;
}

.action-buttons a.btn-view {
    background: var(--primary-light);
    color: var(--primary);
}

.action-buttons a.btn-view:hover {
    background: var(--primary);
    color: white;
}
</style>
@endpush

@section('content')

@php
    $isFr = app()->getLocale() === 'fr';
@endphp

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
    <div>
        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 8px;">
            {{ __('nav.learning') ?? 'Apprentissage' }} / {{ $pageTitle }}
        </div>
        <h1 style="font-size: 2rem; font-weight: 700; margin: 0; color: var(--text-primary);">
            {{ $pageTitle }}
        </h1>
        <p style="color: var(--text-secondary); margin: 8px 0 0 0; font-size: 0.95rem;">
            {{ $isFr ? 'Consultez l\'historique de vos résultats de quiz' : 'View your quiz completion history' }}
        </p>
    </div>
</div>

@if($quizSubmissions->isEmpty())
    <div style="text-align: center; padding: 60px 20px; background: var(--surface); border-radius: 12px; border: 1.5px dashed var(--border);">
        <div style="font-size: 3rem; opacity: 0.3; margin-bottom: 16px;">📝</div>
        <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 8px;">{{ $isFr ? 'Aucun résultat pour le moment' : 'No results yet' }}</h3>
        <p style="color: var(--text-secondary); margin-bottom: 24px;">
            {{ $isFr ? 'Vous n\'avez pas encore complété de quiz' : 'You haven\'t completed any quizzes yet' }}
        </p>
        <a href="{{ route('student.quiz-take.index') }}" style="display: inline-block; padding: 10px 24px; background: var(--primary); color: white; border-radius: 8px; font-weight: 600; text-decoration: none;">
            {{ $isFr ? 'Commencer un quiz' : 'Take a Quiz' }}
        </a>
    </div>
@else
    <div style="background: white; border-radius: 12px; border: 1.5px solid var(--border); overflow: hidden;">
        <table class="results-table">
            <thead>
                <tr>
                    <th>{{ $isFr ? 'Quiz' : 'Quiz' }}</th>
                    <th>{{ $isFr ? 'Matière' : 'Subject' }}</th>
                    <th style="text-align: center;">{{ $isFr ? 'Score' : 'Score' }}</th>
                    <th>{{ $isFr ? 'Résultat' : 'Result' }}</th>
                    <th>{{ $isFr ? 'Date' : 'Date' }}</th>
                    <th style="text-align: center;">{{ $isFr ? 'Actions' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quizSubmissions as $submission)
                    @php
                        $quiz = $submission->quiz;
                        $score = $submission->total_points > 0 ? round($submission->score / $submission->total_points * 100) : 0;
                        $passed = $score >= ($quiz->pass_score ?? 50);
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: var(--text-primary);">{{ $quiz->title }}</div>
                        </td>
                        <td>
                            <span style="display: inline-block; padding: 4px 12px; background: var(--primary-light); color: var(--primary); border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                {{ $quiz->subject?->name ?? '—' }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div class="score-badge {{ $passed ? 'passed' : 'failed' }}">
                                {{ $score }}%
                            </div>
                        </td>
                        <td>
                            <span class="status-badge {{ $passed ? 'passed' : 'failed' }}">
                                {{ $passed ? ($isFr ? 'Admis' : 'Passed') : ($isFr ? 'Échec' : 'Failed') }}
                            </span>
                        </td>
                        <td>
                            <span style="color: var(--text-secondary); font-size: 0.9rem;">
                                {{ $submission->completed_at ? $submission->completed_at->format('d/m/Y H:i') : '—' }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div class="action-buttons">
                                <a href="{{ route('student.quiz-result.show', $submission->id) }}" class="btn-view">
                                    {{ $isFr ? 'Voir' : 'View' }}
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 32px; display: flex; justify-content: center;">
        {{ $quizSubmissions->links() }}
    </div>
@endif

@endsection
