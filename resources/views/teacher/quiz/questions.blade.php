{{-- teacher/quiz/questions.blade.php — Question builder --}}
@extends('layouts.app')
@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Questions du quiz' : 'Quiz Questions');
@endphp
@section('title', $pageTitle)

@push('styles')
<style>
.question-card { border: 1.5px solid var(--border); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; background: var(--surface); transition: box-shadow .2s ease; }
.question-card:hover { box-shadow: var(--shadow); }
.option-row { display: flex; align-items: center; gap: .6rem; margin-bottom: .5rem; }
.option-row input[type="text"] { flex: 1; }
.correct-toggle { width: 20px; height: 20px; accent-color: #059669; cursor: pointer; flex-shrink: 0; }
</style>
@endpush

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('teacher.quizzes.index') }}" class="btn btn-light btn-sm">
                <i data-lucide="arrow-left" style="width:14px"></i>
            </a>
            <div class="page-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i data-lucide="list-checks"></i></div>
            <div>
                <h1 class="page-title">{{ $quiz->title }}</h1>
                <p class="page-subtitle text-muted">
                    {{ $quiz->questions->count() }} {{ $isFr ? 'questions' : 'questions' }}
                    · {{ $quiz->subject?->name }}
                </p>
            </div>
        </div>
        <button class="btn btn-primary btn-sm" onclick="addQuestion()">
            <i data-lucide="plus" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Ajouter une question' : 'Add question' }}
        </button>
    </div>
</div>

{{-- Existing questions --}}
<div id="questions-container">
    @foreach($quiz->questions as $i => $q)
    <div class="question-card" id="qcard-{{ $q->id }}">
        <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
            <span class="badge bg-primary" style="flex-shrink:0">Q{{ $i+1 }}</span>
            <div class="fw-semibold flex-grow-1" style="font-size:.9rem">{{ $q->question_text }}</div>
            <span class="badge bg-secondary" style="font-size:.7rem;flex-shrink:0">{{ strtoupper($q->type) }}</span>
        </div>
        @if(in_array($q->type, ['mcq', 'true_false']))
        <div style="padding-left:.5rem">
            @foreach($q->options ?? [] as $opt => $optText)
            <div class="d-flex align-items-center gap-2 mb-1" style="font-size:.82rem">
                @if(($q->correct_answers ?? []) && in_array($opt, (array)$q->correct_answers))
                <span style="color:#059669;font-weight:700">✓</span>
                @else
                <span style="color:var(--text-muted)">○</span>
                @endif
                {{ $optText }}
            </div>
            @endforeach
        </div>
        @endif
        <div class="d-flex gap-1 mt-3">
            <span style="font-size:.72rem;color:var(--text-muted)">{{ $q->points ?? 1 }} {{ $isFr ? 'point(s)' : 'point(s)' }}</span>
        </div>
    </div>
    @endforeach
</div>

{{-- Add question form (hidden by default) --}}
<div class="card" id="add-question-form" style="display:none">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i data-lucide="plus-circle" style="width:15px" class="me-2"></i>
            {{ $isFr ? 'Nouvelle question' : 'New question' }}
        </h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('teacher.quizzes.questions.store', $quiz->id) }}" id="add-q-form">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">{{ $isFr ? 'Type de question' : 'Question type' }}</label>
                <select name="type" class="form-select" id="q-type" onchange="switchType(this.value)" style="max-width:260px">
                    <option value="mcq">{{ $isFr ? 'Choix multiple (MCQ)' : 'Multiple choice (MCQ)' }}</option>
                    <option value="true_false">Vrai / Faux</option>
                    <option value="open">{{ $isFr ? 'Réponse ouverte' : 'Open answer' }}</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">{{ $isFr ? 'Énoncé de la question' : 'Question text' }} <span class="text-danger">*</span></label>
                <textarea name="question_text" class="form-control" rows="2" required
                          placeholder="{{ $isFr ? 'Posez votre question ici...' : 'Type your question here...' }}"></textarea>
            </div>

            {{-- MCQ options --}}
            <div id="mcq-options" class="mb-3">
                <label class="form-label fw-semibold">{{ $isFr ? 'Options (cochez la bonne réponse)' : 'Options (check correct answer)' }}</label>
                @for($i = 0; $i < 4; $i++)
                <div class="option-row">
                    <input type="checkbox" name="correct_answers[]" value="{{ $i }}" class="correct-toggle">
                    <input type="text" name="options[{{ $i }}]" class="form-control form-control-sm"
                           placeholder="{{ $isFr ? 'Option '.($i+1) : 'Option '.($i+1) }}">
                </div>
                @endfor
            </div>

            {{-- True/False --}}
            <div id="tf-options" class="mb-3" style="display:none">
                <label class="form-label fw-semibold">{{ $isFr ? 'Bonne réponse' : 'Correct answer' }}</label>
                <div class="d-flex gap-3">
                    <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer">
                        <input type="radio" name="correct_tf" value="true" style="accent-color:#059669">
                        {{ $isFr ? '✓ Vrai' : '✓ True' }}
                    </label>
                    <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer">
                        <input type="radio" name="correct_tf" value="false" style="accent-color:#dc2626">
                        {{ $isFr ? '✗ Faux' : '✗ False' }}
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ $isFr ? 'Points' : 'Points' }}</label>
                <input type="number" name="points" class="form-control" value="1" min="0.5" step="0.5" style="width:100px">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="save" style="width:13px" class="me-1"></i>
                    {{ $isFr ? 'Ajouter' : 'Add question' }}
                </button>
                <button type="button" class="btn btn-light btn-sm" onclick="cancelAdd()">
                    {{ $isFr ? 'Annuler' : 'Cancel' }}
                </button>
            </div>
        </form>
    </div>
</div>

@if($quiz->questions->isEmpty())
<div class="card text-center py-5">
    <div class="card-body">
        <i data-lucide="help-circle" style="width:48px;opacity:.25;display:block;margin:0 auto 1rem"></i>
        <p class="text-muted">{{ $isFr ? 'Aucune question. Cliquez sur "Ajouter une question" pour commencer.' : 'No questions yet. Click "Add question" to start.' }}</p>
    </div>
</div>
@endif

{{-- Finalize --}}
@if($quiz->questions->isNotEmpty())
<div class="card mt-4">
    <div class="card-body d-flex align-items-center justify-content-between">
        <div>
            <div class="fw-bold">{{ $quiz->questions->count() }} {{ $isFr ? 'questions' : 'questions' }} · {{ $quiz->questions->sum('points') }} {{ $isFr ? 'points' : 'points' }}</div>
            <div style="font-size:.78rem;color:var(--text-muted)">{{ $isFr ? 'Durée:' : 'Duration:' }} {{ $quiz->duration_minutes }} min</div>
        </div>
        <form method="POST" action="{{ route('teacher.quizzes.publish', $quiz->id) }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                ✅ {{ $isFr ? 'Publier le quiz' : 'Publish quiz' }}
            </button>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
window.addQuestion = () => {
    document.getElementById('add-question-form').style.display = '';
    document.getElementById('add-question-form').scrollIntoView({ behavior: 'smooth' });
};
window.cancelAdd = () => {
    document.getElementById('add-question-form').style.display = 'none';
};
window.switchType = (type) => {
    document.getElementById('mcq-options').style.display = type === 'mcq' ? '' : 'none';
    document.getElementById('tf-options').style.display  = type === 'true_false' ? '' : 'none';
};
</script>
@endpush
