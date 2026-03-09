@extends('layouts.app')

@section('title', 'Éditer Bulletin — ' . $reportCard->student->user->last_name)

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit"></i> Éditer Bulletin
                    </h5>
                </div>

                <div class="card-body">
                    <div class="mb-4 p-3 bg-light rounded">
                        <h6 class="text-muted mb-2">Informations de l'étudiant</h6>
                        <p class="mb-0">
                            <strong>{{ $reportCard->student->user->last_name }} {{ $reportCard->student->user->first_name }}</strong>
                        </p>
                        <p class="mb-0 text-muted small">
                            Classe : {{ $reportCard->student->classe->name }} | 
                            Trimestre {{ $reportCard->term }} - Séquence {{ $reportCard->sequence }}
                        </p>
                    </div>

                    <form action="{{ route('teacher.report-cards.update', $reportCard) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="appreciation" class="form-label">
                                <i class="fas fa-comment-dots"></i> Appréciation
                            </label>
                            <textarea 
                                class="form-control @error('appreciation') is-invalid @enderror" 
                                id="appreciation" 
                                name="appreciation" 
                                rows="4"
                                placeholder="Entrez l'appréciation générale..."
                            >{{ old('appreciation', $reportCard->appreciation) }}</textarea>
                            @error('appreciation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Maximum 500 caractères</small>
                        </div>

                        <div class="mb-3">
                            <label for="behavior_comment" class="form-label">
                                <i class="fas fa-heart"></i> Commentaire sur le comportement
                            </label>
                            <textarea 
                                class="form-control @error('behavior_comment') is-invalid @enderror" 
                                id="behavior_comment" 
                                name="behavior_comment" 
                                rows="4"
                                placeholder="Entrez le commentaire sur le comportement..."
                            >{{ old('behavior_comment', $reportCard->behavior_comment) }}</textarea>
                            @error('behavior_comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Maximum 500 caractères</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                            <a href="{{ route('teacher.report-cards.show', $reportCard) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
