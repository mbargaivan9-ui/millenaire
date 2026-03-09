@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Ajouter un Matériau de Cours</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('course-materials.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('course-materials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="class_subject_teacher_id" class="form-label">Classe/Matière/Enseignant</label>
                    <select class="form-control @error('class_subject_teacher_id') is-invalid @enderror" id="class_subject_teacher_id" name="class_subject_teacher_id" required>
                        <option value="">Sélectionner</option>
                        @foreach($classSubjectTeachers as $cst)
                            <option value="{{ $cst->id }}">
                                {{ $cst->classe->name }} - {{ $cst->subject->name }} - {{ $cst->teacher->user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_subject_teacher_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Titre</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3"></textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="pdf">PDF</option>
                        <option value="video">Vidéo</option>
                        <option value="document">Document</option>
                        <option value="presentation">Présentation</option>
                        <option value="exercise">Exercice</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="file" class="form-label">Fichier (optionnel)</label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="external_link" class="form-label">Lien Externe (optionnel)</label>
                    <input type="url" class="form-control @error('external_link') is-invalid @enderror" id="external_link" name="external_link">
                    @error('external_link')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="upload_date" class="form-label">Date d'Upload</label>
                    <input type="date" class="form-control @error('upload_date') is-invalid @enderror" id="upload_date" name="upload_date" required>
                    @error('upload_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_visible" name="is_visible">
                    <label class="form-check-label" for="is_visible">Visible aux élèves</label>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                    <a href="{{ route('course-materials.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
