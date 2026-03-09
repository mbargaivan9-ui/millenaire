@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Éditer Discipline</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('discipline.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('discipline.update', $discipline) }}" method="POST">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label for="type" class="form-label">Type de Discipline</label>
                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="warning" {{ $discipline->type === 'warning' ? 'selected' : '' }}>Avertissement</option>
                        <option value="detention" {{ $discipline->type === 'detention' ? 'selected' : '' }}>Retenue</option>
                        <option value="suspension" {{ $discipline->type === 'suspension' ? 'selected' : '' }}>Suspension</option>
                        <option value="expulsion" {{ $discipline->type === 'expulsion' ? 'selected' : '' }}>Expulsion</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="pending" {{ $discipline->status === 'pending' ? 'selected' : '' }}>En Attente</option>
                        <option value="active" {{ $discipline->status === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="resolved" {{ $discipline->status === 'resolved' ? 'selected' : '' }}>Résolu</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="resolution" class="form-label">Résolution</label>
                    <textarea class="form-control @error('resolution') is-invalid @enderror" id="resolution" name="resolution" rows="3">{{ $discipline->resolution }}</textarea>
                    @error('resolution')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Mettre à jour</button>
                    <a href="{{ route('discipline.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
