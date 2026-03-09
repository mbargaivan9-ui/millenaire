{{--
    | admin/teachers/index.blade.php — Gestion des Enseignants
    --}}

@extends('layouts.app')
@section('title', app()->getLocale() === 'fr' ? 'Gestion des Enseignants' : 'Teacher Management')

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb)"><i data-lucide="user-check"></i></div>
            <div>
                <h1 class="page-title">{{ $isFr ? 'Gestion des Enseignants' : 'Teacher Management' }}</h1>
                <p class="page-subtitle text-muted">{{ $teachers->total() ?? 0 }} {{ $isFr ? 'enseignants' : 'teachers' }}</p>
            </div>
        </div>
        <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary btn-sm">
            <i data-lucide="user-plus" style="width:14px" class="me-1"></i>
            {{ $isFr ? 'Ajouter un enseignant' : 'Add teacher' }}
        </a>
    </div>
</div>

{{-- Search --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="d-flex gap-3 flex-wrap align-items-end">
            <div style="flex:1;min-width:200px">
                <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                       placeholder="{{ $isFr ? 'Rechercher par nom, matière...' : 'Search by name, subject...' }}">
            </div>
            <div style="min-width:160px">
                <select name="subject_id" class="form-select">
                    <option value="">{{ $isFr ? 'Toutes les matières' : 'All subjects' }}</option>
                    @foreach($subjects ?? [] as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="search" style="width:14px" class="me-1"></i>{{ $isFr ? 'Filtrer' : 'Filter' }}
            </button>
        </form>
    </div>
</div>

<div class="row gy-3">
    @forelse($teachers as $teacher)
    <div class="col-md-4 col-lg-3">
        <div class="card h-100" style="transition:transform .2s ease" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div class="card-body text-center py-4">
                <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#2563eb);color:#fff;font-size:1.5rem;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem">
                    {{ strtoupper(substr($teacher->user->name ?? 'T', 0, 1)) }}
                </div>
                <div class="fw-bold mb-1" style="font-size:.9rem">{{ $teacher->user->display_name ?? $teacher->user->name }}</div>

                @if($teacher->is_prof_principal)
                <span class="badge" style="background:var(--primary-bg);color:var(--primary);margin-bottom:.5rem">
                    ⭐ {{ $isFr ? 'Prof. Principal' : 'Head Teacher' }} — {{ $teacher->headClass?->name }}
                </span>
                @endif

                <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.75rem">
                    {{ $teacher->subjects->pluck('name')->implode(', ') ?: ($isFr ? 'Aucune matière' : 'No subjects') }}
                </div>

                <div class="d-flex gap-1 justify-content-center flex-wrap">
                    @foreach($teacher->classes->take(3) as $class)
                    <span class="badge bg-secondary" style="font-size:.65rem">{{ $class->name }}</span>
                    @endforeach
                    @if($teacher->classes->count() > 3)
                    <span class="badge bg-secondary" style="font-size:.65rem">+{{ $teacher->classes->count() - 3 }}</span>
                    @endif
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="{{ route('admin.teachers.show', $teacher->id) }}" class="btn btn-sm btn-light" style="flex:1;justify-content:center">
                    <i data-lucide="eye" style="width:13px" class="me-1"></i>{{ $isFr ? 'Voir' : 'View' }}
                </a>
                <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn btn-sm btn-primary" style="flex:1;justify-content:center">
                    <i data-lucide="edit-2" style="width:13px" class="me-1"></i>{{ $isFr ? 'Modifier' : 'Edit' }}
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card"><div class="card-body text-center py-5 text-muted">
            <i data-lucide="user-x" style="width:32px;opacity:.3;display:block;margin:0 auto .75rem"></i>
            {{ $isFr ? 'Aucun enseignant trouvé.' : 'No teachers found.' }}
        </div></div>
    </div>
    @endforelse
</div>

<div class="mt-3">{{ $teachers->withQueryString()->links() }}</div>

@endsection


