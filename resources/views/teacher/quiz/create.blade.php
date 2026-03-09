{{-- teacher/quiz/create.blade.php --}}
@extends('layouts.app')
@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Nouveau quiz' : 'New Quiz');
@endphp
@section('title', $pageTitle)
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('teacher.quizzes.index') }}" class="btn btn-light btn-sm">
            <i data-lucide="arrow-left" style="width:14px"></i>
        </a>
        <div class="page-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
            <i data-lucide="file-question"></i>
        </div>
        <h1 class="page-title">{{ $isFr ? 'Nouveau Quiz' : 'New Quiz' }}</h1>
    </div>
</div>

<div class="row">
<div class="col-lg-7">
<div class="card">
<div class="card-body">

<form method="POST" action="{{ route('teacher.quizzes.store') }}">
    @csrf

    @if($errors->any())
    <div class="alert alert-danger mb-4">
        @foreach($errors->all() as $err)<div style="font-size:.83rem">• {{ $err }}</div>@endforeach
    </div>
    @endif

    <div class="mb-3">
        <label class="form-label fw-semibold">{{ $isFr ? 'Titre du quiz' : 'Quiz title' }} <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required
               placeholder="{{ $isFr ? 'Ex: Quiz de Mathématiques — Chapitre 3' : 'E.g: Math Quiz — Chapter 3' }}">
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">{{ $isFr ? 'Matière' : 'Subject' }} <span class="text-danger">*</span></label>
            <select name="subject_id" class="form-select" required>
                <option value="">{{ $isFr ? 'Choisir...' : 'Choose...' }}</option>
                @foreach($subjects as $sub)
                <option value="{{ $sub->id }}" {{ old('subject_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ $isFr ? 'Classe' : 'Class' }} <span class="text-danger">*</span></label>
            <select name="class_id" class="form-select" required>
                <option value="">{{ $isFr ? 'Choisir...' : 'Choose...' }}</option>
                @foreach($classes as $c)
                <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">{{ $isFr ? 'Durée (minutes)' : 'Duration (minutes)' }}</label>
            <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', 30) }}" min="5" max="180">
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ $isFr ? 'Note max' : 'Max score' }}</label>
            <input type="number" name="max_score" class="form-control" value="{{ old('max_score', 20) }}" min="1" max="100">
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ $isFr ? 'Note de passage' : 'Pass mark' }}</label>
            <input type="number" name="pass_score" class="form-control" value="{{ old('pass_score', 10) }}" min="1">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">{{ $isFr ? 'Disponible à partir de' : 'Available from' }}</label>
            <input type="datetime-local" name="available_from" class="form-control" value="{{ old('available_from') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ $isFr ? 'Disponible jusqu\'à' : 'Available until' }}</label>
            <input type="datetime-local" name="available_until" class="form-control" value="{{ old('available_until') }}">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">{{ $isFr ? 'Instructions' : 'Instructions' }}</label>
        <textarea name="instructions" class="form-control" rows="3" style="resize:none"
                  placeholder="{{ $isFr ? 'Instructions pour les élèves...' : 'Instructions for students...' }}">{{ old('instructions') }}</textarea>
    </div>

    <div class="d-flex gap-3 p-3 rounded-3 mb-4" style="background:var(--surface-2)">
        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem">
            <input type="checkbox" name="shuffle_questions" value="1"
                   {{ old('shuffle_questions', 1) ? 'checked' : '' }}
                   style="accent-color:var(--primary)">
            {{ $isFr ? 'Mélanger les questions' : 'Shuffle questions' }}
        </label>
        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem">
            <input type="checkbox" name="show_results_immediately" value="1"
                   {{ old('show_results_immediately', 1) ? 'checked' : '' }}
                   style="accent-color:var(--primary)">
            {{ $isFr ? 'Afficher résultats immédiatement' : 'Show results immediately' }}
        </label>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i data-lucide="save" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Créer et ajouter les questions' : 'Create and add questions' }}
        </button>
        <a href="{{ route('teacher.quizzes.index') }}" class="btn btn-light">{{ $isFr ? 'Annuler' : 'Cancel' }}</a>
    </div>
</form>

</div></div>
</div></div>
@endsection
