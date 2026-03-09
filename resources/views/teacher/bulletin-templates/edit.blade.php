@extends('layouts.app')
@section('title', 'Modifier le Modèle de Bulletin')
@section('content')
<div class="container-fluid py-4" style="max-width:800px">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('teacher.bulletin-templates.index') }}" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
        <h1 class="h4 mb-0">Modifier : {{ $template->name ?? 'Modèle' }}</h1>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('teacher.bulletin-templates.update', $template) }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nom du modèle</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $template->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $template->description) }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label">Structure (JSON)</label>
                    <textarea name="structure" class="form-control font-monospace" rows="10">{{ old('structure', json_encode($template->structure ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Enregistrer</button>
                <a href="{{ route('teacher.bulletin-templates.index') }}" class="btn btn-outline-secondary ms-2">Annuler</a>
            </form>
        </div>
    </div>
</div>
@endsection
