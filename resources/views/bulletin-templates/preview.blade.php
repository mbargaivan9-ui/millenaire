@extends('layouts.app')
@section('title', 'Aperçu du Modèle')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('bulletin-templates.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="h4 mb-0">Aperçu : {{ $template->name ?? 'Modèle' }}</h1>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="border rounded p-3 bg-light">
                <h5>{{ $template->name ?? 'Modèle de Bulletin' }}</h5>
                <p class="text-muted">{{ $template->description ?? 'Aucune description' }}</p>
                <hr>
                <pre class="bg-white p-3 rounded small">{{ json_encode($template->structure ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    </div>
</div>
@endsection
