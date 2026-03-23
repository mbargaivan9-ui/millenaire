{{--
    |--------------------------------------------------------------------------
    | instructors.blade.php — Page Enseignants Publique
    |--------------------------------------------------------------------------
    | Phase 2 — Section 3.8 Instructors Page
    --}}

@extends('layouts.public')

@php
  $pageTitle = $pageTitle ?? __('public.teachers_title');
@endphp

@section('title', $pageTitle)
@section('body_class', 'instructors-page')

@section('content')

@endphp

<div class="page-title" style="background:linear-gradient(135deg,#0d9488,#0f766e);padding:3rem 0;text-align:center;color:#fff;">
    <div class="container">
        <h1 style="font-family:'Raleway',sans-serif;font-weight:800;font-size:2.2rem;margin-bottom:.5rem">
            {{ __('public.teachers_title') }}
        </h1>
        <p style="opacity:.85">{{ __('public.instructors_qualified_team') }}</p>
    </div>
</div>

<section class="section" style="padding:80px 0;">
    <div class="container">

        <div class="row gy-4">
            @forelse($teachers ?? [] as $i => $teacher)
            <div class="col-xl-3 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ ($i % 4) * 50 }}">
                <div class="instructor-card">
                    <div class="instructor-image">
                        @if($teacher->user->avatar_url)
                            <img src="{{ asset($teacher->user->avatar_url) }}" class="img-fluid"
                                 alt="{{ $teacher->user->display_name ?? $teacher->user->name }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center"
                                 style="height:220px;background:linear-gradient(135deg,#f0fdfa,#ccfbf1)">
                                <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#0d9488,#14b8a6);display:flex;align-items:center;justify-content:center;">
                                    <span style="font-size:2.2rem;color:white;font-weight:700">
                                        {{ strtoupper(substr($teacher->user->name ?? 'T', 0, 1)) }}
                                    </span>
                                </div>
                            </div>
                        @endif
                        <div class="overlay-content">
                            <div class="course-count">
                                <i class="bi bi-book me-1"></i>
                                {{ $teacher->subjects->count() }} {{ __('public.teacher_subjects_count') }}
                            </div>
                        </div>
                    </div>
                    <div class="instructor-info">
                        <h5>{{ $teacher->user->display_name ?? $teacher->user->name }}</h5>
                        <p class="specialty">
                            {{ $teacher->subjects->pluck('name')->take(2)->implode(', ') ?: __('public.teacher_label') }}
                        </p>
                        @if($teacher->is_prof_principal)
                        <p class="mb-2">
                            <span class="badge" style="background:#0d9488;font-size:.7rem;padding:.35rem .75rem;border-radius:20px;color:white">
                                <i class="bi bi-star-fill me-1"></i>
                                {{ __('public.teacher_head') }}
                            </span>
                        </p>
                        @endif
                        @if($teacher->classes->isNotEmpty())
                        <p style="font-size:.8rem;color:#64748b">
                            <i class="bi bi-building me-1"></i>
                            {{ $teacher->classes->pluck('name')->take(3)->implode(', ') }}
                        </p>
                        @endif
                        <div class="action-buttons">
                            <a href="{{ route('public.teacher.profile', $teacher->id) }}" class="btn-view">
                                {{ __('public.teacher_view_profile') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-people" style="font-size:4rem;color:#0d9488;opacity:.3"></i>
                <p class="mt-3 text-muted">{{ __('public.no_teachers_available') }}</p>
            </div>
            @endforelse
        </div>

    </div>
</section>

@endsection
