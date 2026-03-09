@extends('layouts.app')

@section('title', 'Créer Parent — ' . ($class->name ?? ''))

@section('content')

<div class="page-header mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('teacher.parent-management.index', $class->id) }}" class="btn btn-light btn-sm">
            <i data-lucide="arrow-left" style="width:14px" class="me-1"></i>
            Retour
        </a>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,#ec4899,#f472b6)">
            <i data-lucide="user-plus"></i>
        </div>
        <div>
            <h1 class="page-title">Créer Compte Parent</h1>
            <p class="text-muted mb-0">Classe: {{ $class->name }}</p>
        </div>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Erreurs de validation:</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('teacher.parent-management.store', $class->id) }}" method="POST" class="card">
    @csrf

    <div class="card-body">
        <div class="row g-3">
            <!-- Informations Personnelles -->
            <div class="col-12">
                <h6 class="fw-bold mb-3">
                    <i data-lucide="user" style="width:18px" class="me-2"></i>
                    Informations Personnelles
                </h6>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nom Complet *</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                       value="{{ old('name') }}" placeholder="Ex: Jean Dupont" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                       value="{{ old('email') }}" placeholder="email@example.com" required>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Téléphone</label>
                <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                       value="{{ old('phone') }}" placeholder="+237 6XX XXX XXX">
                @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Relation à l'élève *</label>
                <select name="relationship" class="form-select @error('relationship') is-invalid @enderror" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="father" {{ old('relationship') === 'father' ? 'selected' : '' }}>Père</option>
                    <option value="mother" {{ old('relationship') === 'mother' ? 'selected' : '' }}>Mère</option>
                    <option value="guardian" {{ old('relationship') === 'guardian' ? 'selected' : '' }}>Tuteur</option>
                    <option value="relatives" {{ old('relationship') === 'relatives' ? 'selected' : '' }}>Autres proches</option>
                </select>
                @error('relationship')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Élèves -->
            <div class="col-12">
                <h6 class="fw-bold mb-3 mt-3">
                    <i data-lucide="book" style="width:18px" class="me-2"></i>
                    Lier aux Élèves
                </h6>
            </div>

            <div class="col-12">
                <label class="form-label">Sélectionner les élèves de cette classe *</label>
                <div class="list-group border rounded" style="max-height:300px;overflow-y:auto">
                    @forelse($students as $student)
                    <label class="list-group-item">
                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                               {{ in_array($student->id, old('student_ids', [])) ? 'checked' : '' }}>
                        <strong>{{ $student->user->display_name ?? $student->user->name }}</strong>
                        <small class="d-block text-muted">{{ $student->matricule }}</small>
                    </label>
                    @empty
                    <div class="text-muted p-3">Aucun élève dans cette classe</div>
                    @endforelse
                </div>
                @error('student_ids')
                <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <!-- Paramètres d'Accès -->
            <div class="col-12">
                <h6 class="fw-bold mb-3 mt-3">
                    <i data-lucide="lock" style="width:18px" class="me-2"></i>
                    Paramètres d'Accès
                </h6>
            </div>

            <div class="col-md-6">
                <label class="form-label">Mot de passe *</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                       placeholder="Minimum 8 caractères" required>
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Confirmer le mot de passe *</label>
                <input type="password" name="password_confirmation" class="form-control" 
                       placeholder="Confirmer le mot de passe" required>
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" name="is_active" id="isActive" class="form-check-input" value="1" checked>
                    <label class="form-check-label" for="isActive">
                        Compte actif (l'utilisateur peut se connecter)
                    </label>
                </div>
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" name="generate_token" id="generateToken" class="form-check-input" value="1" checked>
                    <label class="form-check-label" for="generateToken">
                        Générer immédiatement un token d'accès
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer bg-light d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i data-lucide="check" style="width:16px" class="me-2"></i>
            Créer le Parent
        </button>
        <a href="{{ route('teacher.parent-management.index', $class->id) }}" class="btn btn-secondary">
            Annuler
        </a>
    </div>
</form>

@endsection
