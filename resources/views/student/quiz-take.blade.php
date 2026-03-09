{{--
    |--------------------------------------------------------------------------
    | student/quiz-take.blade.php — Interface de Passation de Quiz
    |--------------------------------------------------------------------------
    | Phase 5 — Section 5.2.2 — Quiz Interactifs
    | Timer, MCQ, Vrai/Faux, Réponse courte, Soumission AJAX
    --}}

@extends('layouts.app')

@section('title', $quiz->title)

@push('styles')
<style>
/* ─── Quiz Container ─────────────────────────────────────────────────────── */
.quiz-container { max-width:800px; margin:0 auto; }

/* ─── Timer ──────────────────────────────────────────────────────────────── */
.quiz-timer {
    display:flex; align-items:center; gap:.75rem;
    padding:.75rem 1.25rem; border-radius:var(--radius-lg);
    background:var(--surface); border:2px solid var(--border);
    font-size:1.2rem; font-weight:900; letter-spacing:1px;
    font-variant-numeric:tabular-nums;
    transition:all .3s ease;
}
.quiz-timer.warning  { border-color:#f59e0b; color:#d97706; background:#fffbeb; }
.quiz-timer.critical { border-color:#ef4444; color:#dc2626; background:#fef2f2; animation:pulse .5s infinite; }
@keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.6; } }

/* ─── Progress bar ───────────────────────────────────────────────────────── */
.quiz-progress-bar { height:6px; background:var(--border); border-radius:3px; overflow:hidden; margin-bottom:1.5rem; }
.quiz-progress-fill { height:100%; background:linear-gradient(90deg,var(--primary),var(--primary-light)); border-radius:3px; transition:width .3s ease; }

/* ─── Question card ──────────────────────────────────────────────────────── */
.question-card {
    background:var(--surface); border:1.5px solid var(--border);
    border-radius:var(--radius-lg); padding:2rem;
    transition:box-shadow .2s ease;
}
.question-number { font-size:.75rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.6px;margin-bottom:.75rem; }
.question-text { font-size:1.05rem;font-weight:600;color:var(--text-primary);line-height:1.6;margin-bottom:1.5rem; }

/* ─── MCQ Options ────────────────────────────────────────────────────────── */
.option-label {
    display:flex; align-items:center; gap:.75rem;
    padding:.85rem 1.1rem; border-radius:var(--radius-md);
    border:1.5px solid var(--border); cursor:pointer;
    transition:all .15s ease; margin-bottom:.6rem;
    background:var(--surface);
}
.option-label:hover { border-color:var(--primary); background:var(--primary-bg); }
.option-label input[type="radio"] { accent-color:var(--primary); width:18px; height:18px; flex-shrink:0; }
.option-label.selected { border-color:var(--primary); background:var(--primary-bg); font-weight:600; }
.option-key {
    width:28px; height:28px; border-radius:8px;
    background:var(--surface-2); display:flex; align-items:center; justify-content:center;
    font-size:.78rem; font-weight:800; flex-shrink:0;
}
.option-label.selected .option-key { background:var(--primary); color:#fff; }

/* ─── Navigation dots ────────────────────────────────────────────────────── */
.nav-dots { display:flex; flex-wrap:wrap; gap:.4rem; justify-content:center; margin:1.5rem 0; }
.nav-dot {
    width:32px; height:32px; border-radius:50%;
    border:1.5px solid var(--border); background:var(--surface);
    font-size:.75rem; font-weight:700; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:all .15s ease;
}
.nav-dot:hover   { border-color:var(--primary); }
.nav-dot.current { border-color:var(--primary); background:var(--primary); color:#fff; }
.nav-dot.answered { border-color:#10b981; background:#ecfdf5; color:#059669; }
</style>
@endpush

@section('content')

@php
    $isFr = app()->getLocale() === 'fr';
    $questions = $quiz->questions->sortBy('sort_order')->values();
    $totalQuestions = $questions->count();
    $timeLimitSeconds = ($quiz->time_limit_minutes ?? 30) * 60;
@endphp

<div class="quiz-container">

    {{-- ─── Header ─────────────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-0">{{ $quiz->title }}</h2>
            <p class="text-muted mb-0" style="font-size:.85rem">{{ $quiz->subject?->name }} · {{ $totalQuestions }} {{ $isFr ? 'questions' : 'questions' }}</p>
        </div>
        <div class="quiz-timer" id="timer">
            <i data-lucide="clock" style="width:20px;color:var(--primary)"></i>
            <span id="timer-display">{{ gmdate('i:s', $timeLimitSeconds) }}</span>
        </div>
    </div>

    {{-- Progress bar --}}
    <div class="quiz-progress-bar">
        <div class="quiz-progress-fill" id="progress-fill" style="width:0%"></div>
    </div>

    {{-- Navigation dots --}}
    <div class="nav-dots" id="nav-dots">
        @foreach($questions as $i => $q)
        <div class="nav-dot {{ $i === 0 ? 'current' : '' }}" id="dot-{{ $i }}" onclick="goToQuestion({{ $i }})">
            {{ $i + 1 }}
        </div>
        @endforeach
    </div>

    {{-- ─── Questions ───────────────────────────────────────────────────────── --}}
    <div id="questions-container">
        @foreach($questions as $i => $question)
        <div class="question-card" id="q-card-{{ $i }}" style="{{ $i === 0 ? '' : 'display:none' }}">
            <div class="question-number">{{ $isFr ? 'Question' : 'Question' }} {{ $i + 1 }} / {{ $totalQuestions }}</div>
            <div class="question-text">{{ $question->question }}</div>

            @if($question->type === 'multiple_choice')
            @php $options = is_array($question->options) ? $question->options : json_decode($question->options ?? '[]', true); @endphp
            <div class="options-list">
                @foreach($options as $j => $opt)
                <label class="option-label" id="opt-{{ $i }}-{{ $j }}">
                    <input type="radio" name="answer[{{ $question->id }}]" value="{{ $j }}"
                           onchange="onAnswer({{ $i }}, {{ $question->id }}, '{{ $j }}', this.closest('label'))">
                    <div class="option-key">{{ chr(65 + $j) }}</div>
                    <span>{{ $opt }}</span>
                </label>
                @endforeach
            </div>

            @elseif($question->type === 'true_false')
            <div class="options-list">
                @foreach([['val' => '1', 'label_fr' => 'Vrai', 'label_en' => 'True'], ['val' => '0', 'label_fr' => 'Faux', 'label_en' => 'False']] as $j => $opt)
                <label class="option-label" id="opt-{{ $i }}-{{ $j }}">
                    <input type="radio" name="answer[{{ $question->id }}]" value="{{ $opt['val'] }}"
                           onchange="onAnswer({{ $i }}, {{ $question->id }}, '{{ $opt['val'] }}', this.closest('label'))">
                    <div class="option-key">{{ chr(65 + $j) }}</div>
                    <span>{{ $isFr ? $opt['label_fr'] : $opt['label_en'] }}</span>
                </label>
                @endforeach
            </div>

            @else
            {{-- Short answer --}}
            <textarea name="answer[{{ $question->id }}]"
                      class="form-control" rows="4"
                      placeholder="{{ $isFr ? 'Votre réponse...' : 'Your answer...' }}"
                      oninput="onTextAnswer({{ $i }}, {{ $question->id }}, this.value)"
                      style="resize:none"></textarea>
            @endif
        </div>
        @endforeach
    </div>

    {{-- ─── Navigation buttons ──────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mt-4">
        <button class="btn btn-light" id="btn-prev" onclick="navigate(-1)" disabled>
            <i data-lucide="chevron-left" style="width:16px" class="me-1"></i>
            {{ $isFr ? 'Précédent' : 'Previous' }}
        </button>
        <span class="text-muted" style="font-size:.82rem">
            <span id="answered-count">0</span>/{{ $totalQuestions }} {{ $isFr ? 'répondues' : 'answered' }}
        </span>
        <div class="d-flex gap-2">
            <button class="btn btn-light" id="btn-next" onclick="navigate(1)">
                {{ $isFr ? 'Suivant' : 'Next' }}
                <i data-lucide="chevron-right" style="width:16px" class="ms-1"></i>
            </button>
            <button class="btn btn-primary" id="btn-submit" onclick="submitQuiz()" style="display:none">
                <i data-lucide="check" style="width:16px" class="me-1"></i>
                {{ $isFr ? 'Soumettre' : 'Submit' }}
            </button>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
(function() {
'use strict';

const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const TOTAL       = {{ $totalQuestions }};
const QUIZ_ID     = {{ $quiz->id }};
const TIME_LIMIT  = {{ $timeLimitSeconds }};
const isFr        = {{ app()->getLocale() === 'fr' ? 'true' : 'false' }};

let currentQ  = 0;
let timeLeft  = TIME_LIMIT;
let answers   = {};
let timerInterval;

// ─── Timer ────────────────────────────────────────────────────────────────────
timerInterval = setInterval(() => {
    timeLeft--;
    const m = Math.floor(timeLeft / 60).toString().padStart(2, '0');
    const s = (timeLeft % 60).toString().padStart(2, '0');
    document.getElementById('timer-display').textContent = `${m}:${s}`;

    const timer = document.getElementById('timer');
    if (timeLeft <= 30)       timer.className = 'quiz-timer critical';
    else if (timeLeft <= 120) timer.className = 'quiz-timer warning';

    if (timeLeft <= 0) { clearInterval(timerInterval); submitQuiz(true); }
}, 1000);

// ─── Navigation ───────────────────────────────────────────────────────────────
window.goToQuestion = function(idx) {
    document.getElementById(`q-card-${currentQ}`)?.style.setProperty('display', 'none');
    document.getElementById(`dot-${currentQ}`)?.classList.remove('current');
    currentQ = idx;
    document.getElementById(`q-card-${currentQ}`)?.style.setProperty('display', '');
    document.getElementById(`dot-${currentQ}`)?.classList.add('current');
    document.getElementById('btn-prev').disabled = currentQ === 0;
    const isLast = currentQ === TOTAL - 1;
    document.getElementById('btn-next').style.display  = isLast ? 'none' : '';
    document.getElementById('btn-submit').style.display = isLast ? '' : 'none';
    updateProgress();
};

window.navigate = function(dir) { goToQuestion(currentQ + dir); };

// ─── Record answer ────────────────────────────────────────────────────────────
window.onAnswer = function(qIdx, qId, val, label) {
    answers[qId] = val;
    // Deselect siblings
    label.closest('.options-list')?.querySelectorAll('.option-label').forEach(l => l.classList.remove('selected'));
    label.classList.add('selected');
    document.getElementById(`dot-${qIdx}`)?.classList.add('answered');
    updateAnsweredCount();
};

window.onTextAnswer = function(qIdx, qId, val) {
    answers[qId] = val;
    if (val.trim()) document.getElementById(`dot-${qIdx}`)?.classList.add('answered');
    updateAnsweredCount();
};

function updateAnsweredCount() {
    document.getElementById('answered-count').textContent = Object.keys(answers).length;
}

function updateProgress() {
    const pct = (currentQ + 1) / TOTAL * 100;
    document.getElementById('progress-fill').style.width = pct + '%';
}

// ─── Submit ───────────────────────────────────────────────────────────────────
window.submitQuiz = async function(autoSubmit = false) {
    clearInterval(timerInterval);
    if (!autoSubmit) {
        const unanswered = TOTAL - Object.keys(answers).length;
        if (unanswered > 0) {
            const confirmed = confirm(`${unanswered} question(s) sans réponse. ${isFr ? 'Soumettre quand même ?' : 'Submit anyway?'}`);
            if (!confirmed) { timerInterval = setInterval(arguments.callee, 1000); return; }
        }
    }

    const btn = document.getElementById('btn-submit');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + (isFr ? 'Envoi...' : 'Submitting...'); }

    try {
        const res  = await fetch(`/student/quizzes/${QUIZ_ID}/submit`, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
            body: JSON.stringify({ answers }),
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = `/student/quizzes/${data.submission_id}/result`;
        } else {
            alert(data.message ?? 'Erreur');
            if (btn) { btn.disabled = false; btn.textContent = isFr ? 'Réessayer' : 'Retry'; }
        }
    } catch(err) {
        console.error(err);
        if (btn) { btn.disabled = false; }
    }
};

// ─── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
    updateProgress();
});

})();
</script>
@endpush
