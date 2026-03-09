@extends('layouts.app')

@section('title', 'Éditer Parent — ' . $parent->name)

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
            <i data-lucide="user-check"></i>
        </div>
        <div>
            <h1 class="page-title">Éditer Parent</h1>
            <p class="text-muted mb-0">{{ $parent->name }}</p>
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

<form action="{{ route('teacher.parent-management.update', $parent->id) }}" method="POST" class="card">
    @csrf
    @method('PUT')

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
                       value="{{ old('name', $parent->name) }}" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                       value="{{ old('email', $parent->email) }}" required>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Téléphone</label>
                <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                       value="{{ old('phone', $parent->phone) }}">
                @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Relation à l'élève *</label>
                <select name="relationship" class="form-select @error('relationship') is-invalid @enderror" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="father" {{ old('relationship', $parentStudent->relationship ?? '') === 'father' ? 'selected' : '' }}>Père</option>
                    <option value="mother" {{ old('relationship', $parentStudent->relationship ?? '') === 'mother' ? 'selected' : '' }}>Mère</option>
                    <option value="guardian" {{ old('relationship', $parentStudent->relationship ?? '') === 'guardian' ? 'selected' : '' }}>Tuteur</option>
                    <option value="relatives" {{ old('relationship', $parentStudent->relationship ?? '') === 'relatives' ? 'selected' : '' }}>Autres proches</option>
                </select>
                @error('relationship')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Élèves -->
            <div class="col-12">
                <h6 class="fw-bold mb-3 mt-3">
                    <i data-lucide="book" style="width:18px" class="me-2"></i>
                    Élèves Liés
                </h6>
            </div>

            <div class="col-12">
                <div class="list-group border rounded" style="max-height:300px;overflow-y:auto">
                    @forelse($students as $student)
                    <label class="list-group-item">
                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                               {{ $parent->students->contains($student->id) ? 'checked' : '' }}>
                        <strong>{{ $student->user->display_name ?? $student->user->name }}</strong>
                        <small class="d-block text-muted">{{ $student->matricule }}</small>
                    </label>
                    @empty
                    <div class="text-muted p-3">Aucun élève disponible</div>
                    @endforelse
                </div>
                @error('student_ids')
                <div class="text-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <!-- Paramètres -->
            <div class="col-12">
                <h6 class="fw-bold mb-3 mt-3">
                    <i data-lucide="settings" style="width:18px" class="me-2"></i>
                    Paramètres
                </h6>
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" name="is_active" id="isActive" class="form-check-input" value="1"
                           {{ old('is_active', $parent->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="isActive">
                        Compte actif
                    </label>
                </div>
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" name="change_password" id="changePassword" class="form-check-input" value="1">
                    <label class="form-check-label" for="changePassword">
                        Changer le mot de passe
                    </label>
                </div>
            </div>

            <div class="col-md-6" id="passwordFields" style="display:none">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6" id="passwordConfirmFields" style="display:none">
                <label class="form-label">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
        </div>

        <!-- Stats tokens -->
        <div class="mt-4 pt-4 border-top">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-center">
                        <h5 class="text-primary fw-bold">{{ $parent->accessTokens()->active()->count() }}</h5>
                        <small class="text-muted">Tokens actifs</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h5 class="text-warning fw-bold">{{ $parent->accessTokens()->expired()->count() }}</h5>
                        <small class="text-muted">Tokens expirés</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h5 class="text-secondary fw-bold">{{ $parent->students()->count() }}</h5>
                        <small class="text-muted">Enfants liés</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer bg-light d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i data-lucide="check" style="width:16px" class="me-2"></i>
            Enregistrer
        </button>
        <a href="{{ route('teacher.parent-management.index', $class->id) }}" class="btn btn-secondary">
            Annuler
        </a>
    </div>
</form>

<script>
document.getElementById('changePassword').addEventListener('change', function() {
    document.getElementById('passwordFields').style.display = this.checked ? 'block' : 'none';
    document.getElementById('passwordConfirmFields').style.display = this.checked ? 'block' : 'none';
});
</script>

@endsection
