@extends('layouts.app')

@section('title', 'Générer Tokens d\'Accès — ' . ($class->name ?? ''))

@section('content')

<div class="page-header mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('teacher.parent-management.index', $class->id) }}" class="btn btn-light btn-sm">
            <i data-lucide="arrow-left" style="width:14px" class="me-1"></i>
            Retour
        </a>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,#10b981,#34d399)">
            <i data-lucide="key"></i>
        </div>
        <div>
            <h1 class="page-title">Générer Tokens d'Accès</h1>
            <p class="text-muted mb-0">Classe: {{ $class->name }}</p>
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

<form action="{{ route('teacher.parent-management.store-tokens', $class->id) }}" method="POST" class="card">
    @csrf

    <div class="card-body">
        <div class="alert alert-info">
            <i data-lucide="info" style="width:18px" class="me-2"></i>
            <strong>Tokens d'accès</strong> permettent aux parents de se connecter sans mot de passe. 
            Chaque token expirp après 30 jours.
        </div>

        <h6 class="fw-bold mb-3">
            <i data-lucide="users" style="width:18px" class="me-2"></i>
            Sélectionner les parents
        </h6>

        <div class="list-group border rounded" style="max-height:400px;overflow-y:auto">
            @forelse($parents as $parent)
            <label class="list-group-item">
                <input type="checkbox" name="parent_ids[]" value="{{ $parent->id }}">
                <strong>{{ $parent->name }}</strong>
                <small class="d-block text-muted">{{ $parent->email }} • {{ $parent->students()->count() }} enfant(s)</small>
            </label>
            @empty
            <div class="text-muted p-3">Aucun parent disponible</div>
            @endforelse
        </div>

        @error('parent_ids')
        <div class="text-danger mt-2">{{ $message }}</div>
        @enderror

        <h6 class="fw-bold mt-4 mb-3">
            <i data-lucide="calendar" style="width:18px" class="me-2"></i>
            Validité des Tokens
        </h6>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Durée de validité (jours) *</label>
                <input type="number" name="validity_days" class="form-control" value="30" min="1" max="365" required>
                <small class="text-muted">Après ce délai, le token expire</small>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nombre d'utilisations maximales</label>
                <input type="number" name="max_uses" class="form-control" placeholder="Illimité" min="1">
                <small class="text-muted">Laissez vide pour illimité</small>
            </div>
        </div>
    </div>

    <div class="card-footer bg-light d-flex gap-2">
        <button type="submit" class="btn btn-success">
            <i data-lucide="check" style="width:16px" class="me-2"></i>
            Générer les Tokens
        </button>
        <a href="{{ route('teacher.parent-management.index', $class->id) }}" class="btn btn-secondary">
            Annuler
        </a>
    </div>
</form>

@endsection
