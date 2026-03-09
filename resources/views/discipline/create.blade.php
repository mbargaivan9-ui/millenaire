@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Signaler une Discipline</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('discipline.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('discipline.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="student_id" class="form-label">Étudiant</label>
                    <select class="form-control @error('student_id') is-invalid @enderror" id="student_id" name="student_id" required>
                        <option value="">Sélectionner un étudiant</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->user->name }}</option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="type" class="form-label">Type de Discipline</label>
                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="">Sélectionner le type</option>
                        <option value="warning">Avertissement</option>
                        <option value="detention">Retenue</option>
                        <option value="suspension">Suspension</option>
                        <option value="expulsion">Expulsion</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Raison</label>
                    <input type="text" class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" required>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description Détaillée</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4"></textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="incident_date" class="form-label">Date de l'Incident</label>
                    <input type="date" class="form-control @error('incident_date') is-invalid @enderror" id="incident_date" name="incident_date" required>
                    @error('incident_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="start_date" class="form-label">Date de Début</label>
                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" required>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="end_date" class="form-label">Date de Fin (optionnel)</label>
                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date">
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Statut</label>
                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="pending">En Attente</option>
                        <option value="active">Actif</option>
                        <option value="resolved">Résolu</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                    <a href="{{ route('discipline.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
