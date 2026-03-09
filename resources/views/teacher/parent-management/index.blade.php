@extends('layouts.app')

@section('title', 'Gestion Parents — ' . ($class->name ?? 'Tous les parents'))

@push('styles')
<style>
.parent-card {
    transition: all 0.2s ease;
}
.parent-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}
.status-active {
    background: #d1fae5;
    color: #065f46;
}
.status-inactive {
    background: #fee2e2;
    color: #7f1d1d;
}
</style>
@endpush

@section('content')

<div class="page-header mb-4">
    <div class="d-flex align-items-center flex-wrap gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,#ec4899,#f472b6)">
            <i data-lucide="users"></i>
        </div>
        <div>
            <h1 class="page-title">
                Gestion Parents
                @if($class)
                <span class="fw-normal text-muted" style="font-size:1rem">— {{ $class->name }}</span>
                @endif
            </h1>
            <p class="text-muted mb-0">Créer des comptes parents, générer tokens d'accès, lier aux élèves</p>
        </div>
        <div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
            <a href="{{ route('teacher.parent-management.create', $class->id) }}" class="btn btn-primary btn-sm">
                <i data-lucide="plus" style="width:14px" class="me-1"></i>
                Nouveau Parent
            </a>
            <a href="{{ route('teacher.parent-management.generate-tokens', $class->id) }}" class="btn btn-success btn-sm">
                <i data-lucide="key" style="width:14px" class="me-1"></i>
                Générer Tokens
            </a>
            <a href="{{ route('teacher.parent-management.tokens', $class->id) }}" class="btn btn-info btn-sm">
                <i data-lucide="lock" style="width:14px" class="me-1"></i>
                Tokens Actifs
            </a>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i data-lucide="check-circle" style="width:18px" class="me-2"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i data-lucide="alert-circle" style="width:18px" class="me-2"></i>
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-3">
    @forelse($parents as $parent)
    <div class="col-md-6 col-lg-4">
        <div class="card parent-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <h6 class="card-title mb-0">{{ $parent->name }}</h6>
                        <small class="text-muted">{{ $parent->email }}</small>
                    </div>
                    <span class="status-badge {{ $parent->is_active ? 'status-active' : 'status-inactive' }}">
                        <i data-lucide="{{ $parent->is_active ? 'check' : 'x' }}" style="width:12px"></i>
                        {{ $parent->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>

                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted d-block">Enfants liés</small>
                    <div class="mt-2">
                        @php
                        $students = $parent->students()->limit(3)->get();
                        @endphp
                        @forelse($students as $student)
                        <span class="badge bg-light text-dark me-1 mb-1">
                            {{ $student->user->display_name ?? $student->user->name }}
                        </span>
                        @empty
                        <span class="text-muted small">Aucun enfant lié</span>
                        @endforelse
                        @if($parent->students()->count() > 3)
                        <span class="badge bg-secondary">+{{ $parent->students()->count() - 3 }}</span>
                        @endif
                    </div>
                </div>

                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted">Tokens actifs: <strong>{{ $parent->accessTokens()->active()->count() }}</strong></small>
                </div>
            </div>

            <div class="card-footer bg-transparent d-flex gap-2">
                <a href="{{ route('teacher.parent-management.edit', $parent->id) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                    <i data-lucide="edit-2" style="width:14px" class="me-1"></i>
                    Éditer
                </a>
                <form action="{{ route('teacher.parent-management.destroy', $parent->id) }}" method="POST" class="flex-grow-1" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Confirmez la suppression?')">
                        <i data-lucide="trash-2" style="width:14px" class="me-1"></i>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center py-5">
            <i data-lucide="inbox" style="width:32px" class="d-block mb-2"></i>
            <strong>Aucun parent créé</strong>
            <p class="text-muted mb-0">Commencez par créer un nouveau compte parent</p>
        </div>
    </div>
    @endforelse
</div>

@if($parents->hasPages())
<nav aria-label="Page navigation" class="mt-4">
    {{ $parents->links() }}
</nav>
@endif

@endsection
