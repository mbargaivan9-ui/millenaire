{{-- admin/teachers/show.blade.php --}}
@extends('layouts.app')
@section('title', $teacher->user->name)
@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.teachers.index') }}" class="btn btn-light btn-sm"><i data-lucide="arrow-left" style="width:14px"></i></a>
            <div>
                <h1 class="page-title">{{ $teacher->user->display_name ?? $teacher->user->name }}</h1>
                <p class="page-subtitle text-muted">{{ $teacher->user->email }}</p>
            </div>
        </div>
        <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn btn-primary btn-sm">
            <i data-lucide="edit-2" style="width:14px" class="me-1"></i>{{ $isFr ? 'Modifier' : 'Edit' }}
        </a>
    </div>
</div>

<div class="row gy-4">
    <div class="col-lg-4">
        <div class="card text-center py-4">
            <div class="card-body">
                <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;font-size:1.8rem;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                    {{ strtoupper(substr($teacher->user->name, 0, 1)) }}
                </div>
                <div class="fw-bold mb-1">{{ $teacher->user->name }}</div>
                @if($teacher->qualification)
                <div style="font-size:.78rem;color:var(--text-muted)">{{ $teacher->qualification }}</div>
                @endif

                @if($teacher->is_prof_principal)
                <div class="mt-2">
                    <span class="badge" style="background:var(--primary-bg);color:var(--primary)">⭐ Prof. Principal — {{ $teacher->headClass?->name }}</span>
                </div>
                @endif

                <div class="mt-3 d-flex flex-wrap gap-1 justify-content-center">
                    @if($teacher->is_active)
                    <span class="badge bg-success">{{ $isFr ? 'Actif' : 'Active' }}</span>
                    @else
                    <span class="badge bg-danger">{{ $isFr ? 'Inactif' : 'Inactive' }}</span>
                    @endif
                    @if($teacher->is_visible_on_site)
                    <span class="badge bg-info">{{ $isFr ? 'Visible site' : 'On website' }}</span>
                    @endif
                </div>

                <div class="mt-3 text-start" style="font-size:.82rem">
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color:var(--text-muted)">{{ $isFr ? 'Classes' : 'Classes' }}</span>
                        <strong>{{ $teacher->classes->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span style="color:var(--text-muted)">{{ $isFr ? 'Matières' : 'Subjects' }}</span>
                        <strong>{{ $teacher->subjects->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h6 class="card-title mb-0">{{ $isFr ? 'Matières enseignées' : 'Subjects' }}</h6></div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @forelse($teacher->subjects as $sub)
                    <span class="badge" style="background:var(--primary-bg);color:var(--primary);font-size:.8rem;padding:.35rem .75rem">{{ $sub->name }}</span>
                    @empty
                    <span class="text-muted" style="font-size:.83rem">{{ $isFr ? 'Aucune matière affectée.' : 'No subjects assigned.' }}</span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h6 class="card-title mb-0">{{ $isFr ? 'Classes' : 'Classes' }}</h6></div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @forelse($teacher->classes as $class)
                    <span class="badge bg-secondary" style="font-size:.8rem;padding:.35rem .75rem">{{ $class->name }}</span>
                    @empty
                    <span class="text-muted" style="font-size:.83rem">{{ $isFr ? 'Aucune classe.' : 'No classes.' }}</span>
                    @endforelse
                </div>
            </div>
        </div>

        @if($teacher->bio_fr || $teacher->bio_en)
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">Bio</h6></div>
            <div class="card-body">
                @if($isFr && $teacher->bio_fr)
                <p style="font-size:.88rem;color:var(--text-secondary)">{{ $teacher->bio_fr }}</p>
                @elseif($teacher->bio_en)
                <p style="font-size:.88rem;color:var(--text-secondary)">{{ $teacher->bio_en }}</p>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection


