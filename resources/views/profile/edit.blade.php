@extends('layouts.app')

@section('title', 'Modifier mon profil')

@section('content')
<div class="container-lg py-5">
    {{-- Header --}}
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="fas fa-user-edit text-primary me-2"></i>Modifier mon Profil
            </h1>
            <p class="text-muted">Mettez à jour vos informations personnelles</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Profile Photo Section --}}
                        <div class="mb-5">
                            <div class="text-center mb-3">
                                <div class="avatar-preview mb-4">
                                    <img src="{{ $user->avatar_url ?? asset('images/default-avatar.png') }}"
                                         alt="Avatar" class="rounded-circle"
                                         style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #e9ecef; cursor: pointer;"
                                         onclick="document.getElementById('profile_photo').click()"
                                         id="avatar-display">
                                </div>
                                <input type="file" id="profile_photo" name="profile_photo" class="form-control" 
                                       accept="image/*" onchange="previewImage(this)" style="display:none;">
                                <button type="button" class="btn btn-primary btn-sm mt-2" onclick="document.getElementById('profile_photo').click()">
                                    <i class="fas fa-camera me-1"></i>Changer la photo
                                </button>
                                @error('profile_photo')
                                    <small class="text-danger d-block mt-2">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        {{-- Name Section --}}
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Prénom</label>
                                <input type="text" name="first_name" class="form-control rounded-3 @error('first_name') is-invalid @enderror"
                                       value="{{ old('first_name', $user->first_name) }}" placeholder="Jean">
                                @error('first_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nom</label>
                                <input type="text" name="last_name" class="form-control rounded-3 @error('last_name') is-invalid @enderror"
                                       value="{{ old('last_name', $user->last_name) }}" placeholder="Dupont">
                                @error('last_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Full Name (calculated) --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Nom Complet</label>
                            <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" placeholder="Jean Dupont" required>
                            <small class="text-muted">Ceci sera utilisé comme votre nom d'affichage</small>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Phone & Gender --}}
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Téléphone</label>
                                <input type="tel" name="phoneNumber" class="form-control rounded-3 @error('phoneNumber') is-invalid @enderror"
                                       value="{{ old('phoneNumber', $user->phoneNumber) }}" placeholder="+237 6XX XXX XXX">
                                @error('phoneNumber')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Genre</label>
                                <select name="gender" class="form-select rounded-3 @error('gender') is-invalid @enderror">
                                    <option value="">-- Sélectionner --</option>
                                    <option value="M" {{ old('gender', $user->gender) === 'M' ? 'selected' : '' }}>Masculin</option>
                                    <option value="F" {{ old('gender', $user->gender) === 'F' ? 'selected' : '' }}>Féminin</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Date of Birth --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Date de naissance</label>
                            <input type="date" name="date_of_birth" class="form-control rounded-3 @error('date_of_birth') is-invalid @enderror"
                                   value="{{ old('date_of_birth', $user->date_of_birth) }}">
                            @error('date_of_birth')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Address --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Adresse</label>
                            <input type="text" name="address" class="form-control rounded-3 @error('address') is-invalid @enderror"
                                   value="{{ old('address', $user->address) }}" placeholder="123 Rue de la Paix">
                            @error('address')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- City & Country --}}
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Ville</label>
                                <input type="text" name="city" class="form-control rounded-3 @error('city') is-invalid @enderror"
                                       value="{{ old('city', $user->city) }}" placeholder="Yaoundé">
                                @error('city')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Pays</label>
                                <input type="text" name="country" class="form-control rounded-3 @error('country') is-invalid @enderror"
                                       value="{{ old('country', $user->country) }}" placeholder="Cameroun">
                                @error('country')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Bio --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Biographie</label>
                            <textarea name="bio" class="form-control rounded-3 @error('bio') is-invalid @enderror"
                                      rows="4" placeholder="Dites-nous un peu plus sur vous...">{{ old('bio', $user->bio) }}</textarea>
                            <small class="text-muted">Maximum 1000 caractères</small>
                            @error('bio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Buttons --}}
                        <div class="d-flex gap-2 mt-5">
                            <button type="submit" class="btn btn-primary rounded-3 px-4">
                                <i class="fas fa-save me-2"></i>Enregistrer les modifications
                            </button>
                            <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary rounded-3 px-4">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-info-circle text-primary me-2"></i>Informations
                    </h5>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i>Email: <strong class="text-dark">{{ $user->email }}</strong>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock me-2"></i>Membre depuis <strong class="text-dark">{{ $user->created_at->locale('fr')->format('d F Y') }}</strong>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-user-tag me-2"></i>Rôle: <strong class="text-dark">{{ ucfirst($user->role) }}</strong>
                        </li>
                    </ul>

                    <hr>

                    <div class="mt-3">
                        <a href="{{ route('profile.security') }}" class="btn btn-sm btn-outline-primary w-100 rounded-2">
                            <i class="fas fa-shield-alt me-1"></i>Sécurité du Compte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus, .form-select:focus {
        border-color: #0d9488;
        box-shadow: 0 0 0 0.2rem rgba(13, 148, 136, 0.15);
    }

    .btn-primary {
        background-color: #0d9488;
        border-color: #0d9488;
    }

    .btn-primary:hover {
        background-color: #0a7a67;
        border-color: #0a7a67;
    }

    .rounded-3 {
        border-radius: 12px;
    }
    
    .rounded-2 {
        border-radius: 8px;
    }
</style>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-display').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Auto-refresh page after form submission to refresh avatar
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                // Store timestamp to refresh images
                sessionStorage.setItem('profileUpdated', Date.now());
            });
        }
        
        // Make avatar clickable
        const avatarDisplay = document.getElementById('avatar-display');
        if (avatarDisplay) {
            avatarDisplay.style.cursor = 'pointer';
            avatarDisplay.title = 'Cliquez pour changer la photo';
        }
    });
</script>
@endsection
