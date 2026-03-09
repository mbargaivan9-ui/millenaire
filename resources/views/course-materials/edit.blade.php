@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Éditer Matériau de Cours</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('course-materials.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('course-materials.update', $courseMaterial) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label for="title" class="form-label">Titre</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ $courseMaterial->title }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ $courseMaterial->description }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="pdf" {{ $courseMaterial->type === 'pdf' ? 'selected' : '' }}>PDF</option>
                        <option value="video" {{ $courseMaterial->type === 'video' ? 'selected' : '' }}>Vidéo</option>
                        <option value="document" {{ $courseMaterial->type === 'document' ? 'selected' : '' }}>Document</option>
                        <option value="presentation" {{ $courseMaterial->type === 'presentation' ? 'selected' : '' }}>Présentation</option>
                        <option value="exercise" {{ $courseMaterial->type === 'exercise' ? 'selected' : '' }}>Exercice</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="file" class="form-label">Remplacer le fichier (optionnel)</label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Mettre à jour</button>
                    <a href="{{ route('course-materials.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
