@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Ajouter un Tuteur</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('guardians.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('guardians.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Nom Complet</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Téléphone</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" required>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="relationship" class="form-label">Relation avec l'Étudiant</label>
                    <select class="form-control @error('relationship') is-invalid @enderror" id="relationship" name="relationship" required>
                        <option value="">Sélectionner</option>
                        <option value="parent">Parent</option>
                        <option value="grand-parent">Grand-parent</option>
                        <option value="tuteur">Tuteur</option>
                        <option value="oncle">Oncle</option>
                        <option value="tante">Tante</option>
                        <option value="frere">Frère</option>
                        <option value="soeur">Sœur</option>
                        <option value="autre">Autre</option>
                    </select>
                    @error('relationship')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="profession" class="form-label">Profession</label>
                    <input type="text" class="form-control @error('profession') is-invalid @enderror" id="profession" name="profession">
                    @error('profession')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="company" class="form-label">Entreprise</label>
                    <input type="text" class="form-control @error('company') is-invalid @enderror" id="company" name="company">
                    @error('company')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone_professional" class="form-label">Téléphone Professionnel</label>
                    <input type="text" class="form-control @error('phone_professional') is-invalid @enderror" id="phone_professional" name="phone_professional">
                    @error('phone_professional')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_primary_contact" name="is_primary_contact">
                    <label class="form-check-label" for="is_primary_contact">Contact Principal</label>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                    <a href="{{ route('guardians.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
