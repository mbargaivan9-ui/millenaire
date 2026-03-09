@extends('layouts.app')
@section('title', 'Détail du Cours')
@section('content')
<div class="container-fluid py-4" style="max-width:800px">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="h4 mb-0">{{ $material->title ?? 'Support de Cours' }}</h1>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Matière :</strong> {{ $material->subject->name ?? '—' }}</p>
                    <p><strong>Classe :</strong> {{ $material->classe->name ?? '—' }}</p>
                    <p><strong>Type :</strong> <span class="badge bg-info">{{ $material->type ?? 'document' }}</span></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Enseignant :</strong> {{ $material->teacher->user->name ?? '—' }}</p>
                    <p><strong>Publié :</strong> {{ $material->is_published ? 'Oui' : 'Non' }}</p>
                    <p><strong>Téléchargements :</strong> {{ $material->download_count ?? 0 }}</p>
                </div>
            </div>
            @if($material->description)
            <div class="alert alert-light">{{ $material->description }}</div>
            @endif
            @if($material->file_path)
            <a href="{{ asset($material->file_path) }}" download class="btn btn-primary"><i class="bi bi-download me-1"></i>Télécharger</a>
            @elseif($material->url)
            <a href="{{ $material->url }}" target="_blank" class="btn btn-primary"><i class="bi bi-link-45deg me-1"></i>Accéder</a>
            @endif
        </div>
    </div>
</div>
@endsection
