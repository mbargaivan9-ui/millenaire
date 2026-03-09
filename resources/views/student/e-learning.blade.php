{{--
    |--------------------------------------------------------------------------
    | student/e-learning.blade.php — Espace E-Learning Étudiant
    |--------------------------------------------------------------------------
    | Phase 5 — Section 5.2 — Ressources Pédagogiques & Quiz
    | Cours, PDFs, Vidéos YouTube/Vimeo, Quiz interactifs
    --}}

@extends('layouts.app')

@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'E-Learning' : 'E-Learning');
@endphp

@section('title', $pageTitle)

@push('styles')
<style>
/* ─── Subject filter tabs ───────────────────────────────────────────────── */
.subject-tabs { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.5rem; }
.subject-tab {
    padding:.4rem 1rem; border-radius:20px; border:1.5px solid var(--border);
    font-size:.8rem; font-weight:600; cursor:pointer; transition:all .2s ease;
    background:var(--surface); color:var(--text-secondary);
    white-space:nowrap;
}
.subject-tab:hover { border-color:var(--primary); color:var(--primary); }
.subject-tab.active { background:var(--primary); color:#fff; border-color:var(--primary); box-shadow:0 4px 12px rgba(13,148,136,.3); }

/* ─── Material cards ─────────────────────────────────────────────────────── */
.material-card {
    border:1.5px solid var(--border); border-radius:var(--radius-lg);
    padding:1.25rem; transition:all .25s ease;
    display:flex; flex-direction:column; height:100%;
}
.material-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-lg); border-color:var(--primary-light); }

.material-type-icon {
    width:48px; height:48px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.5rem; margin-bottom:.75rem; flex-shrink:0;
}
.material-type-icon.pdf   { background:#fef2f2; color:#ef4444; }
.material-type-icon.video { background:#fdf4ff; color:#9333ea; }
.material-type-icon.ppt   { background:#fff7ed; color:#f97316; }
.material-type-icon.link  { background:#eff6ff; color:#3b82f6; }

.material-subject-badge {
    display:inline-block; padding:.2rem .6rem;
    border-radius:20px; font-size:.7rem; font-weight:700;
    background:var(--primary-bg); color:var(--primary);
    margin-bottom:.5rem;
}
.material-title { font-weight:700; font-size:.92rem; margin-bottom:.35rem; color:var(--text-primary); }
.material-desc  { font-size:.8rem; color:var(--text-muted); flex:1; }
.material-meta  { display:flex; align-items:center; gap:.75rem; margin-top:.75rem; font-size:.72rem; color:var(--text-muted); }

/* ─── Quiz cards ─────────────────────────────────────────────────────────── */
.quiz-card {
    border:1.5px solid var(--border); border-radius:var(--radius-lg);
    padding:1.25rem; transition:all .25s ease;
    position:relative; overflow:hidden;
}
.quiz-card:hover { transform:translateY(-3px); box-shadow:var(--shadow-lg); }
.quiz-card.completed { border-color:#10b981; background:#f0fdf4; }
.quiz-card.available { border-color:var(--primary); }
.quiz-card.locked    { opacity:.6; }
.quiz-score-badge {
    position:absolute; top:1rem; right:1rem;
    padding:.3rem .8rem; border-radius:20px;
    font-size:.78rem; font-weight:800;
}

/* ─── Video embed ────────────────────────────────────────────────────────── */
.video-thumb {
    width:100%; aspect-ratio:16/9; border-radius:10px;
    object-fit:cover; cursor:pointer;
    position:relative; overflow:hidden;
}
.play-overlay {
    position:absolute; inset:0;
    display:flex; align-items:center; justify-content:center;
    background:rgba(0,0,0,.35);
    transition:background .2s ease;
}
.play-overlay:hover { background:rgba(0,0,0,.5); }
.play-btn {
    width:56px; height:56px; border-radius:50%;
    background:rgba(255,255,255,.9);
    display:flex; align-items:center; justify-content:center;
}
</style>
@endpush

@section('content')

@php
    $isFr      = app()->getLocale() === 'fr';
    $student   = auth()->user()->student;
    $subjects  = $subjects ?? collect();
    $materials = $materials ?? collect();
    $quizzes   = $quizzes ?? collect();
@endphp

{{-- ─── Header ──────────────────────────────────────────────────────────────── --}}
<div class="page-header mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
            <i data-lucide="book-open"></i>
        </div>
        <div>
            <h1 class="page-title">{{ $isFr ? 'Ressources E-Learning' : 'E-Learning Resources' }}</h1>
            <p class="page-subtitle text-muted">{{ $student?->classe?->name }}</p>
        </div>
        <div class="ms-auto">
            <input type="text" class="form-control" id="search-material"
                   placeholder="{{ $isFr ? 'Rechercher un cours...' : 'Search a course...' }}"
                   oninput="filterMaterials(this.value)"
                   style="width:220px">
        </div>
    </div>
</div>

{{-- ─── Subject filter tabs ──────────────────────────────────────────────────── --}}
<div class="subject-tabs" id="subject-tabs">
    <div class="subject-tab active" data-subject="all" onclick="filterBySubject('all', this)">
        {{ $isFr ? 'Tout' : 'All' }} ({{ $materials->count() + $quizzes->count() }})
    </div>
    @foreach($subjects as $subject)
    @php
        $cnt = $materials->where('subject_id', $subject->id)->count() + $quizzes->where('subject_id', $subject->id)->count();
    @endphp
    @if($cnt > 0)
    <div class="subject-tab" data-subject="{{ $subject->id }}" onclick="filterBySubject('{{ $subject->id }}', this)">
        {{ $subject->name }} ({{ $cnt }})
    </div>
    @endif
    @endforeach
</div>

{{-- ─── Course Materials ─────────────────────────────────────────────────────── --}}
@if($materials->isNotEmpty())
<h5 class="fw-bold mb-3">
    <i data-lucide="folder-open" style="width:18px" class="me-2"></i>
    {{ $isFr ? 'Cours & Ressources' : 'Courses & Resources' }}
    <span class="badge bg-secondary ms-2">{{ $materials->count() }}</span>
</h5>
<div class="row gy-3 mb-5" id="materials-grid">
    @foreach($materials as $mat)
    @php
        $typeMap  = ['pdf'=>'pdf','video'=>'video','powerpoint'=>'ppt','link'=>'link'];
        $iconMap  = ['pdf'=>'📄','video'=>'🎬','powerpoint'=>'📊','link'=>'🔗'];
        $typeKey  = $typeMap[$mat->type] ?? 'link';
        $icon     = $iconMap[$mat->type] ?? '📎';
    @endphp
    <div class="col-md-4 col-sm-6 material-item"
         data-subject="{{ $mat->subject_id }}"
         data-search="{{ strtolower($mat->title . ' ' . ($mat->description ?? '')) }}">
        <div class="material-card">
            <div class="material-type-icon {{ $typeKey }}">{{ $icon }}</div>
            <span class="material-subject-badge">{{ $mat->subject?->name }}</span>
            <div class="material-title">{{ $mat->title }}</div>
            @if($mat->description)
            <div class="material-desc">{{ Str::limit($mat->description, 80) }}</div>
            @endif
            <div class="material-meta">
                <span>
                    <i data-lucide="user" style="width:11px" class="me-1"></i>
                    {{ $mat->teacher?->user?->name }}
                </span>
                <span>
                    <i data-lucide="calendar" style="width:11px" class="me-1"></i>
                    {{ $mat->created_at?->format('d/m/Y') }}
                </span>
                @if($mat->type === 'pdf' && $mat->file_size)
                <span>{{ round($mat->file_size / 1024) }} KB</span>
                @endif
            </div>
            <div class="mt-auto pt-3">
                @if($mat->type === 'pdf')
                <a href="{{ route('student.material.download', $mat->id) }}" class="btn btn-primary btn-sm w-100" target="_blank">
                    <i data-lucide="download" style="width:14px" class="me-1"></i>
                    {{ $isFr ? 'Télécharger PDF' : 'Download PDF' }}
                </a>
                @elseif($mat->type === 'video')
                <button class="btn btn-primary btn-sm w-100" onclick="openVideo('{{ $mat->video_url }}', '{{ addslashes($mat->title) }}')">
                    <i data-lucide="play-circle" style="width:14px" class="me-1"></i>
                    {{ $isFr ? 'Voir la vidéo' : 'Watch video' }}
                </button>
                @else
                <a href="{{ $mat->external_url ?? '#' }}" class="btn btn-light btn-sm w-100" target="_blank">
                    <i data-lucide="external-link" style="width:14px" class="me-1"></i>
                    {{ $isFr ? 'Ouvrir' : 'Open' }}
                </a>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ─── Quizzes ──────────────────────────────────────────────────────────────── --}}
@if($quizzes->isNotEmpty())
<h5 class="fw-bold mb-3">
    <i data-lucide="help-circle" style="width:18px" class="me-2"></i>
    {{ $isFr ? 'Quiz & Évaluations' : 'Quizzes & Assessments' }}
    <span class="badge bg-secondary ms-2">{{ $quizzes->count() }}</span>
</h5>
<div class="row gy-3" id="quizzes-grid">
    @foreach($quizzes as $quiz)
    @php
        $submission = $quiz->submissions?->where('student_id', $student?->id)->first();
        $isCompleted = $submission !== null;
        $isAvailable = $quiz->is_published && now()->between($quiz->available_from ?? now()->subYear(), $quiz->available_until ?? now()->addYear());
        $statusClass = $isCompleted ? 'completed' : ($isAvailable ? 'available' : 'locked');
        $scoreColor  = $submission ? ($submission->score >= $quiz->pass_score ? '#10b981' : '#ef4444') : null;
    @endphp
    <div class="col-md-4 col-sm-6 material-item" data-subject="{{ $quiz->subject_id }}" data-search="{{ strtolower($quiz->title) }}">
        <div class="quiz-card {{ $statusClass }}">
            @if($isCompleted && $submission)
            <span class="quiz-score-badge" style="background:{{ $scoreColor }}22;color:{{ $scoreColor }}">
                {{ $submission->score }}/{{ $submission->total_points }}
            </span>
            @elseif($isAvailable)
            <span class="quiz-score-badge" style="background:var(--primary-bg);color:var(--primary)">
                {{ $isFr ? 'Disponible' : 'Available' }}
            </span>
            @else
            <span class="quiz-score-badge" style="background:var(--surface-2);color:var(--text-muted)">
                <i data-lucide="lock" style="width:12px"></i>
            </span>
            @endif

            <div class="d-flex align-items-start gap-3 mb-3">
                <div style="width:44px;height:44px;border-radius:12px;background:{{ $isCompleted ? '#f0fdf4' : 'var(--primary-bg)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i data-lucide="{{ $isCompleted ? 'check-circle' : 'help-circle' }}" style="width:22px;color:{{ $isCompleted ? '#10b981' : 'var(--primary)' }}"></i>
                </div>
                <div>
                    <div class="fw-bold" style="font-size:.9rem">{{ $quiz->title }}</div>
                    <div style="font-size:.75rem;color:var(--text-muted)">{{ $quiz->subject?->name }}</div>
                </div>
            </div>

            @if($quiz->description)
            <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem">{{ Str::limit($quiz->description, 70) }}</p>
            @endif

            <div class="d-flex gap-3 mb-3" style="font-size:.75rem;color:var(--text-muted)">
                <span><i data-lucide="clock" style="width:12px" class="me-1"></i>{{ $quiz->time_limit_minutes }} min</span>
                <span><i data-lucide="list" style="width:12px" class="me-1"></i>{{ $quiz->questions_count ?? $quiz->questions?->count() ?? '?' }} {{ $isFr ? 'questions' : 'questions' }}</span>
                @if($quiz->pass_score)
                <span><i data-lucide="target" style="width:12px" class="me-1"></i>{{ $isFr ? 'Seuil' : 'Pass' }}: {{ $quiz->pass_score }}%</span>
                @endif
            </div>

            @if($isCompleted)
            <a href="{{ route('student.quiz.result', $submission->id) }}" class="btn btn-sm w-100" style="background:#ecfdf5;color:#059669;border-color:#86efac">
                <i data-lucide="bar-chart-2" style="width:14px" class="me-1"></i>
                {{ $isFr ? 'Voir mes résultats' : 'View my results' }}
            </a>
            @elseif($isAvailable)
            <a href="{{ route('student.quiz.start', $quiz->id) }}" class="btn btn-primary btn-sm w-100">
                <i data-lucide="play" style="width:14px" class="me-1"></i>
                {{ $isFr ? 'Commencer le Quiz' : 'Start Quiz' }}
            </a>
            @else
            <button class="btn btn-secondary btn-sm w-100" disabled>
                <i data-lucide="lock" style="width:14px" class="me-1"></i>
                {{ $isFr ? 'Non disponible' : 'Not available' }}
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

@if($materials->isEmpty() && $quizzes->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <i data-lucide="book-open" style="width:48px;opacity:.25;color:var(--text-muted)"></i>
        <h5 class="mt-3 text-muted">{{ $isFr ? 'Aucune ressource disponible.' : 'No resources available yet.' }}</h5>
        <p class="text-muted" style="font-size:.85rem">{{ $isFr ? 'Vos enseignants n\'ont pas encore publié de cours.' : 'Your teachers haven\'t published any courses yet.' }}</p>
    </div>
</div>
@endif

{{-- ─── Video Modal ──────────────────────────────────────────────────────────── --}}
<div class="modal fade" id="videoModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="video-modal-title">Vidéo</h5>
                <button class="btn-close" data-bs-dismiss="modal" onclick="stopVideo()"></button>
            </div>
            <div class="modal-body p-0">
                <div style="aspect-ratio:16/9;width:100%">
                    <iframe id="video-iframe" src="" width="100%" height="100%"
                            frameborder="0" allowfullscreen
                            allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture"
                            style="display:block;border-radius:0 0 var(--radius) var(--radius)"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ─── Filter by subject ────────────────────────────────────────────────────────
window.filterBySubject = function(subjectId, tab) {
    document.querySelectorAll('.subject-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    document.querySelectorAll('.material-item').forEach(item => {
        const match = subjectId === 'all' || item.dataset.subject === subjectId;
        item.closest('.col-md-4, .col-sm-6')?.classList.toggle('d-none', !match);
        item.classList.toggle('d-none', !match);
    });
};

// ─── Text search ──────────────────────────────────────────────────────────────
window.filterMaterials = function(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.material-item').forEach(item => {
        const match = !q || (item.dataset.search ?? '').includes(q);
        item.classList.toggle('d-none', !match);
    });
};

// ─── Open video modal ─────────────────────────────────────────────────────────
window.openVideo = function(url, title) {
    // Convert YouTube/Vimeo URLs to embed
    let embedUrl = url;
    const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/);
    const vmMatch = url.match(/vimeo\.com\/(\d+)/);
    if (ytMatch) embedUrl = `https://www.youtube.com/embed/${ytMatch[1]}?autoplay=1`;
    if (vmMatch) embedUrl = `https://player.vimeo.com/video/${vmMatch[1]}?autoplay=1`;

    document.getElementById('video-modal-title').textContent = title;
    document.getElementById('video-iframe').src = embedUrl;
    new bootstrap.Modal(document.getElementById('videoModal')).show();
};

window.stopVideo = function() {
    document.getElementById('video-iframe').src = '';
};

document.getElementById('videoModal')?.addEventListener('hidden.bs.modal', stopVideo);
</script>
@endpush
