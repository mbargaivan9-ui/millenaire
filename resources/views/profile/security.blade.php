@extends('layouts.app')

@section('title', 'Sécurité du Compte')

@section('content')
<div class="container-lg py-5">
    {{-- Header --}}
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="fas fa-shield-alt text-primary me-2"></i>Sécurité du Compte
            </h1>
            <p class="text-muted">Gérez la sécurité et les paramètres de confidentialité de votre compte</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Change Password Section --}}
            <div class="card border-0 shadow-sm rounded-lg mb-4">
                <div class="card-header bg-light border-0 p-4 rounded-top">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-key text-primary me-2"></i>Changer le Mot de Passe
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                    <div class="alert alert-success rounded-3 mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Succès!</strong> — {{ session('success') }}
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger rounded-3 mb-4" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Erreur!</strong> — Veuillez vérifier les erreurs ci-dessous.
                    </div>
                    @endif

                    <form action="{{ route('profile.change-password') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Mot de Passe Actuel</label>
                            <input type="password" name="current_password" 
                                   class="form-control rounded-3 @error('current_password') is-invalid @enderror"
                                   placeholder="Entrez votre mot de passe actuel"
                                   required>
                            @error('current_password')
                                <div class="invalid-feedback d-block">
                                    <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Nouveau Mot de Passe</label>
                            <input type="password" name="password" 
                                   class="form-control rounded-3 @error('password') is-invalid @enderror"
                                   placeholder="Entrez un nouveau mot de passe sécurisé"
                                   required>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Minimum 8 caractères avec au moins une majuscule, une minuscule et un chiffre
                            </small>
                            @error('password')
                                <div class="invalid-feedback d-block">
                                    <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirmer le Nouveau Mot de Passe</label>
                            <input type="password" name="password_confirmation" 
                                   class="form-control rounded-3 @error('password_confirmation') is-invalid @enderror"
                                   placeholder="Confirmez votre nouveau mot de passe"
                                   required>
                            @error('password_confirmation')
                                <div class="invalid-feedback d-block">
                                    <i class="fas fa-times-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary rounded-3">
                                <i class="fas fa-save me-2"></i>Mettre à Jour le Mot de Passe
                            </button>
                            <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary rounded-3">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Active Sessions Section --}}
            @if($sessions && count($sessions) > 0)
            <div class="card border-0 shadow-sm rounded-lg mb-4">
                <div class="card-header bg-light border-0 p-4 rounded-top">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-desktop text-primary me-2"></i>Sessions Actives
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4">
                        Voici un aperçu des sessions et des appareils connectés à votre compte. Vous pouvez vous déconnecter de tous les appareils ci-dessous si vous estimez qu'il y a une session non autorisée.
                    </p>
                    
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="border-bottom">
                                    <th>Appareil</th>
                                    <th>Adresse IP</th>
                                    <th>Dernière Activité</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sessions as $session)
                                <tr class="border-bottom">
                                    <td>
                                        <div>
                                            <i class="fas fa-laptop text-primary me-2"></i>
                                            <strong>{{ $session['agent'] ?? 'Appareil inconnu' }}</strong>
                                            @if($session['is_current'] ?? false)
                                                <span class="badge bg-success ms-2">Actuel</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td><code>{{ $session['ip'] ?? 'N/A' }}</code></td>
                                    <td>
                                        <small class="text-muted">{{ $session['last_active'] ?? 'N/A' }}</small>
                                    </td>
                                    <td class="text-end">
                                        @if(!($session['is_current'] ?? false))
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-2"
                                                onclick="confirm('Déconnecter cet appareil ?') && alert('Fonctionnalité à implémenter')">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <form action="{{ route('profile.logout-all') }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-danger rounded-3" 
                                onclick="return confirm('Vous allez être déconnecté de tous les appareils. Confirmez-vous ?')">
                            <i class="fas fa-sign-out-alt me-2"></i>Déconnecter de Tous les Appareils
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Two-Factor Authentication Section --}}
            <div class="card border-0 shadow-sm rounded-lg mb-4">
                <div class="card-header bg-light border-0 p-4 rounded-top">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-mobile-alt text-primary me-2"></i>Authentification à Deux Facteurs (2FA)
                    </h5>
                </div>
                <div class="card-body p-4">
                    @php
                        $twoFaEnabled = $user->two_factor_enabled ?? false;
                    @endphp
                    @if($twoFaEnabled)
                        <div class="alert alert-success rounded-3 mb-4" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Activée</strong> — Votre compte est protégé par une authentification à deux facteurs
                        </div>
                        <p class="text-muted small mb-3">
                            Vous devrez fournir un code supplémentaire lors de la connexion à votre compte.
                        </p>
                        <form action="{{ route('profile.2fa.disable') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger rounded-3"
                                    onclick="return confirm('Désactiver l\'authentification à deux facteurs ?')">
                                <i class="fas fa-times me-2"></i>Désactiver 2FA
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning rounded-3 mb-4" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Désactivée</strong> — Activez 2FA pour une meilleure sécurité
                        </div>
                        <p class="text-muted small mb-3">
                            L'authentification à deux facteurs ajoute une couche de sécurité supplémentaire à votre compte. En activant 2FA, vous devrez entrer un code de vérification en plus de votre mot de passe lors de la connexion.
                        </p>
                        <form action="{{ route('profile.2fa.enable') }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-success rounded-3">
                                <i class="fas fa-shield-alt me-2"></i>Activer 2FA
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Security Recommendations --}}
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-light border-0 p-4 rounded-top">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-lightbulb text-warning me-2"></i>Recommandations de Sécurité
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="security-checklist">
                        <div class="mb-3 pb-3 border-bottom d-flex align-items-start">
                            <i class="fas fa-check-circle text-success me-3 mt-1" style="font-size: 1.2rem;"></i>
                            <div>
                                <strong class="d-block mb-1">Utilisez un mot de passe fort</strong>
                                <small class="text-muted">
                                    Votre mot de passe doit contenir au moins 8 caractères avec un mélange de lettres majuscules, minuscules et de chiffres.
                                </small>
                            </div>
                        </div>

                        <div class="mb-3 pb-3 border-bottom d-flex align-items-start">
                            <i class="fas fa-check-circle text-success me-3 mt-1" style="font-size: 1.2rem;"></i>
                            <div>
                                <strong class="d-block mb-1">Activez l'authentification à deux facteurs</strong>
                                <small class="text-muted">
                                    2FA offre une protection supplémentaire en exigeant un code de vérification lors de la connexion.
                                </small>
                            </div>
                        </div>

                        <div class="mb-3 pb-3 border-bottom d-flex align-items-start">
                            <i class="fas fa-check-circle text-success me-3 mt-1" style="font-size: 1.2rem;"></i>
                            <div>
                                <strong class="d-block mb-1">Vérifiez vos sessions actives</strong>
                                <small class="text-muted">
                                    Vérifiez régulièrement la liste des appareils connectés et déconnectez-vous des sessions inconnues.
                                </small>
                            </div>
                        </div>

                        <div class="d-flex align-items-start">
                            <i class="fas fa-check-circle text-success me-3 mt-1" style="font-size: 1.2rem;"></i>
                            <div>
                                <strong class="d-block mb-1">Mettez à jour régulièrement votre mot de passe</strong>
                                <small class="text-muted">
                                    Changez votre mot de passe tous les 3 mois pour une sécurité optimale.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-light border-0 p-4 rounded-top">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-history text-primary me-2"></i>Activités Récentes
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="activity-list">
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted d-block mb-1">Mot de passe modifié</small>
                            <strong class="text-dark">Il y a 2 semaines</strong>
                        </div>
                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted d-block mb-1">Connexion</small>
                            <strong class="text-dark">Il y a 5 minutes</strong>
                        </div>
                        <div>
                            <small class="text-muted d-block mb-1">Profil mis à jour</small>
                            <strong class="text-dark">Il y a 1 mois</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .rounded {
        border-radius: 8px;
    }

    .rounded-3 {
        border-radius: 12px;
    }

    .rounded-2 {
        border-radius: 8px;
    }

    .rounded-top {
        border-radius: 12px 12px 0 0;
    }

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
</style>
@endsection
