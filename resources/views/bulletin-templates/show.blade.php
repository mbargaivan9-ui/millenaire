@extends('layouts.app')
@section('title', 'Détail du Modèle de Bulletin')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('bulletin-templates.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="h4 mb-0">{{ $template->name ?? 'Modèle' }}</h1>
    </div>
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header"><h6 class="mb-0">Structure du modèle</h6></div>
                <div class="card-body">
                    @forelse($template->fields ?? [] as $field)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <span><strong>{{ $field->label }}</strong> <span class="badge bg-secondary ms-2">{{ $field->type }}</span></span>
                        <span class="text-muted small">Coeff. {{ $field->coefficient ?? 1 }}</span>
                    </div>
                    @empty
                    <p class="text-muted">Aucun champ défini.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header"><h6 class="mb-0">Actions</h6></div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('bulletin-templates.edit', $template) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Modifier</a>
                    <a href="{{ route('bulletin-templates.preview', $template) }}" class="btn btn-outline-secondary"><i class="bi bi-eye me-1"></i>Aperçu</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
