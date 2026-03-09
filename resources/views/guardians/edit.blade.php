@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Éditer Tuteur</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('guardians.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('guardians.update', $guardian) }}" method="POST">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Nom Complet</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ $guardian->user->name }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ $guardian->user->email }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Téléphone</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ $guardian->user->phoneNumber }}" required>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="relationship" class="form-label">Relation</label>
                    <select class="form-control @error('relationship') is-invalid @enderror" id="relationship" name="relationship" required>
                        <option value="parent" {{ $guardian->relationship === 'parent' ? 'selected' : '' }}>Parent</option>
                        <option value="grand-parent" {{ $guardian->relationship === 'grand-parent' ? 'selected' : '' }}>Grand-parent</option>
                        <option value="tuteur" {{ $guardian->relationship === 'tuteur' ? 'selected' : '' }}>Tuteur</option>
                        <option value="autre" {{ $guardian->relationship === 'autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                    @error('relationship')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Mettre à jour</button>
                    <a href="{{ route('guardians.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
