{{--
    |--------------------------------------------------------------------------
    | student/quiz-result.blade.php — Résultats du Quiz
    |--------------------------------------------------------------------------
    | Phase 5 — Section 5.2.2 — Résultats Quiz
    | Score, corrigé, explications
    --}}

@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Résultats du Quiz' : 'Quiz Results');
@endphp

@section('title', $pageTitle)

@push('styles')
<style>
.result-hero {
    background:linear-gradient(135deg,var(--primary),var(--primary-light));
    border-radius:var(--radius-xl); padding:2.5rem; color:#fff; text-align:center; margin-bottom:2rem;
}
.result-score { font-size:4rem; font-weight:900; line-height:1; margin-bottom:.5rem; }
.result-label { font-size:1.1rem; opacity:.85; }

.question-result { border:1.5px solid var(--border); border-radius:var(--radius-lg); padding:1.25rem; margin-bottom:1rem; }
.question-result.correct { border-color:#10b981; background:#f0fdf4; }
.question-result.wrong   { border-color:#ef4444; background:#fef2f2; }
.question-result.pending { border-color:#f59e0b; background:#fffbeb; }

.answer-chip {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.3rem .75rem; border-radius:12px; font-size:.8rem; font-weight:700; margin-top:.5rem;
}
.answer-chip.correct { background:#ecfdf5; color:#059669; }
.answer-chip.wrong   { background:#fef2f2; color:#dc2626; }
.answer-chip.pending { background:#fffbeb; color:#d97706; }
</style>
@endpush

@section('content')

@php
    $isFr       = app()->getLocale() === 'fr';
    $quiz       = $submission->quiz;
    $pct        = $submission->total_points > 0 ? round($submission->score / $submission->total_points * 100) : 0;
    $passed     = $pct >= ($quiz->pass_score ?? 50);
    $answers    = is_array($submission->answers) ? $submission->answers : json_decode($submission->answers ?? '[]', true);
@endphp

<div style="max-width:800px; margin:0 auto">

    {{-- ─── Hero Score ───────────────────────────────────────────────────────── --}}
    <div class="result-hero">
        <div class="result-score">{{ $pct }}%</div>
        <div class="result-label">
            {{ $submission->score }} / {{ $submission->total_points }} {{ $isFr ? 'points' : 'points' }}
        </div>
        <div class="mt-3">
            @if(!$submission->is_graded)
            <span style="background:rgba(255,255,255,.2);padding:.4rem 1rem;border-radius:20px;font-size:.85rem">
                ⏳ {{ $isFr ? 'En attente de correction (questions ouvertes)' : 'Pending manual grading (open questions)' }}
            </span>
            @elseif($passed)
            <span style="background:rgba(255,255,255,.25);padding:.4rem 1rem;border-radius:20px;font-size:.9rem;font-weight:700">
                🎉 {{ $isFr ? 'Réussi !' : 'Passed!' }}
            </span>
            @else
            <span style="background:rgba(0,0,0,.15);padding:.4rem 1rem;border-radius:20px;font-size:.9rem;font-weight:700">
                ❌ {{ $isFr ? 'Non réussi' : 'Failed' }} ({{ $isFr ? 'Seuil' : 'Pass mark' }}: {{ $quiz->pass_score ?? 50 }}%)
            </span>
            @endif
        </div>
    </div>

    {{-- ─── Answers review ──────────────────────────────────────────────────── --}}
    <h5 class="fw-bold mb-3">{{ $isFr ? 'Corrigé détaillé' : 'Detailed review' }}</h5>

    @foreach($quiz->questions->sortBy('sort_order')->values() as $i => $q)
    @php
        $qAns      = $answers[$q->id] ?? null;
        $isCorrect = false;
        $state     = 'pending';
        if ($q->type === 'short_answer') {
            $state = 'pending';
        } elseif ((string)$qAns === (string)$q->correct_answer) {
            $isCorrect = true;
            $state     = 'correct';
        } else {
            $state = 'wrong';
        }
        $options = is_array($q->options) ? $q->options : json_decode($q->options ?? '[]', true);
    @endphp
    <div class="question-result {{ $state }}">
        <div class="d-flex align-items-start gap-3">
            <div style="width:28px;height:28px;border-radius:8px;background:{{ $state === 'correct' ? '#10b981' : ($state === 'wrong' ? '#ef4444' : '#f59e0b') }}22;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px">
                @if($state === 'correct') ✅
                @elseif($state === 'wrong') ❌
                @else ⏳
                @endif
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold mb-2">{{ $i + 1 }}. {{ $q->question }}</div>

                @if($q->type !== 'short_answer')
                <div class="answer-chip {{ $state }}">
                    {{ $isFr ? 'Votre réponse' : 'Your answer' }}:
                    @if($options && isset($options[$qAns]))
                        {{ $options[$qAns] }}
                    @elseif($q->type === 'true_false')
                        {{ $qAns == '1' ? ($isFr ? 'Vrai' : 'True') : ($isFr ? 'Faux' : 'False') }}
                    @else
                        {{ $qAns ?? ($isFr ? 'Sans réponse' : 'No answer') }}
                    @endif
                </div>

                @if($state === 'wrong')
                <div class="answer-chip correct ms-2">
                    {{ $isFr ? 'Bonne réponse' : 'Correct answer' }}:
                    @if($options && isset($options[$q->correct_answer]))
                        {{ $options[$q->correct_answer] }}
                    @elseif($q->type === 'true_false')
                        {{ $q->correct_answer == '1' ? ($isFr ? 'Vrai' : 'True') : ($isFr ? 'Faux' : 'False') }}
                    @else
                        {{ $q->correct_answer }}
                    @endif
                </div>
                @endif

                @else
                {{-- Short answer --}}
                <div class="mt-2 p-2 rounded-3" style="background:rgba(0,0,0,.04);font-size:.85rem">
                    <strong>{{ $isFr ? 'Votre réponse:' : 'Your answer:' }}</strong>
                    {{ $qAns ?? ($isFr ? '(sans réponse)' : '(no answer)') }}
                </div>
                @endif
            </div>
            <div style="font-size:.8rem;font-weight:700;flex-shrink:0;color:{{ $state === 'correct' ? '#059669' : ($state === 'wrong' ? '#dc2626' : '#d97706') }}">
                {{ $state === 'correct' ? '+' . ($q->points ?? 1) : '0' }}/{{ $q->points ?? 1 }}
            </div>
        </div>
    </div>
    @endforeach

    <div class="d-flex gap-2 mt-4">
        <a href="{{ route('student.courses') }}" class="btn btn-light">
            <i data-lucide="arrow-left" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Retour aux cours' : 'Back to courses' }}
        </a>
        <a href="{{ route('student.dashboard') }}" class="btn btn-primary">
            {{ $isFr ? 'Tableau de bord' : 'Dashboard' }}
        </a>
    </div>
</div>

@endsection
