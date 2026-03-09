@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Éditer Modèle de Bulletin</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('bulletin-templates.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('bulletin-templates.update', $bulletinTemplate) }}" method="POST">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Nom du Modèle</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ $bulletinTemplate->name }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="template_data" class="form-label">Données du Modèle</label>
                    <textarea class="form-control @error('template_data') is-invalid @enderror" id="template_data" name="template_data" rows="10">{{ json_encode($bulletinTemplate->template_data, JSON_PRETTY_PRINT) }}</textarea>
                    @error('template_data')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" {{ $bulletinTemplate->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Actif</label>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Mettre à jour</button>
                    <a href="{{ route('bulletin-templates.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
