{{--
    | teacher/quiz/index.blade.php — Gestion des Quiz
    --}}

@extends('layouts.app')
@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Mes Quiz' : 'My Quizzes');
@endphp
@section('title', $pageTitle)

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5)"><i data-lucide="help-circle"></i></div>
            <div>
                <h1 class="page-title">{{ $isFr ? 'Mes Quiz' : 'My Quizzes' }}</h1>
                <p class="page-subtitle text-muted">{{ $quizzes->total() ?? 0 }} quiz</p>
            </div>
        </div>
        <a href="{{ route('teacher.quizzes.create') }}" class="btn btn-primary btn-sm">
            <i data-lucide="plus" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Nouveau quiz' : 'New quiz' }}
        </a>
    </div>
</div>

@if($quizzes->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <i data-lucide="help-circle" style="width:48px;opacity:.25;display:block;margin:0 auto 1rem"></i>
        <h5 class="text-muted">{{ $isFr ? 'Aucun quiz créé.' : 'No quizzes yet.' }}</h5>
        <a href="{{ route('teacher.quizzes.create') }}" class="btn btn-primary mt-2">
            <i data-lucide="plus" style="width:14px" class="me-1"></i>{{ $isFr ? 'Créer un quiz' : 'Create a quiz' }}
        </a>
    </div>
</div>
@else
<div class="row gy-3">
    @foreach($quizzes as $quiz)
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <span class="badge" style="background:#6366f115;color:#6366f1;font-size:.7rem">{{ $quiz->subject?->name }}</span>
                    @if($quiz->is_published)
                        <span class="badge bg-success" style="font-size:.65rem">{{ $isFr ? 'Publié' : 'Published' }}</span>
                    @else
                        <span class="badge bg-warning" style="font-size:.65rem">{{ $isFr ? 'Brouillon' : 'Draft' }}</span>
                    @endif
                </div>
                <div class="fw-bold mb-1">{{ $quiz->title }}</div>
                <div style="font-size:.78rem;color:var(--text-muted)">
                    {{ $quiz->questions_count ?? $quiz->questions->count() }} {{ $isFr ? 'questions' : 'questions' }}
                    · {{ $quiz->time_limit ?? 0 }} min
                    · {{ $isFr ? 'Passage:' : 'Pass:' }} {{ $quiz->pass_score }}%
                </div>
                <div style="font-size:.73rem;color:var(--text-muted);margin-top:.5rem">
                    {{ $quiz->submissions_count ?? 0 }} {{ $isFr ? 'soumissions' : 'submissions' }}
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="{{ route('teacher.quizzes.results', $quiz->id) }}" class="btn btn-sm btn-light" style="flex:1;justify-content:center">
                    <i data-lucide="bar-chart-2" style="width:13px" class="me-1"></i>{{ $isFr ? 'Résultats' : 'Results' }}
                </a>
                <a href="{{ route('teacher.quizzes.edit', $quiz->id) }}" class="btn btn-sm btn-primary" style="flex:1;justify-content:center">
                    <i data-lucide="edit-2" style="width:13px" class="me-1"></i>{{ $isFr ? 'Modifier' : 'Edit' }}
                </a>
                <form method="POST" action="{{ route('teacher.quizzes.destroy', $quiz->id) }}" onsubmit="return confirm('{{ $isFr ? 'Supprimer ce quiz ?' : 'Delete this quiz?' }}')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger"><i data-lucide="trash-2" style="width:13px"></i></button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-3">{{ $quizzes->links() }}</div>
@endif

@endsection
