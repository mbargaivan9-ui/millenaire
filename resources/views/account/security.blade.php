@extends('layouts.app')

@section('title', 'Sécurité du Compte')

@section('content')

{{-- Page Header --}}
<div class="page-header mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon" style="background:linear-gradient(135deg,#EF4444,#DC2626)">
                <i data-lucide="shield-alert"></i>
            </div>
            <div>
                <h1 class="page-title">Sécurité du Compte</h1>
                <p class="page-subtitle text-muted">Gérez votre mot de passe et vos paramètres de sécurité</p>
            </div>
        </div>
    </div>
</div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Change Password --}}
            <x-ui.card title="Changer le Mot de Passe" class="mb-4">
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Mot de Passe Actuel</label>
                        <input type="password" class="form-control rounded-3 @error('current_password') is-invalid @enderror" 
                               name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Nouveau Mot de Passe</label>
                        <input type="password" class="form-control rounded-3 @error('password') is-invalid @enderror" 
                               name="password" required>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-2">
                            Minimum 8 caractères, incluant majuscules et chiffres recommandés
                        </small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirmer le Mot de Passe</label>
                        <input type="password" class="form-control rounded-3" 
                               name="password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-3">
                        <i class="fas fa-save me-2"></i>Mettre à Jour
                    </button>
                </form>
            </x-ui.card>

            {{-- Two Factor Authentication --}}
            <x-ui.card title="Authentification à Deux Facteurs" class="mb-4">
                @if(Auth::user()->two_factor_enabled)
                <div class="alert alert-success rounded-3 mb-4">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Activée</strong> - Votre compte est protégé par une authentification à deux facteurs
                </div>

                <form action="{{ route('profile.2fa.disable') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger rounded-3" 
                            onclick="return confirm('Désactiver 2FA ?')">
                        <i class="fas fa-times me-2"></i>Désactiver
                    </button>
                </form>
                @else
                <div class="alert alert-warning rounded-3 mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Désactivée</strong> - Activez 2FA pour sécuriser votre compte
                </div>

                <form action="{{ route('profile.2fa.enable') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success rounded-3">
                        <i class="fas fa-shield-check me-2"></i>Activer 2FA
                    </button>
                </form>
                @endif
            </x-ui.card>

            {{-- Active Sessions --}}
            <x-ui.card title="Sessions Actives">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="bg-light">
                                <th>Appareil</th>
                                <th>IP Adresse</th>
                                <th>Localisation</th>
                                <th>Dernière Activité</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions ?? [] as $session)
                            <tr>
                                <td>
                                    <i class="fas fa-laptop me-2"></i>{{ $session->device ?? 'Unknown' }}
                                </td>
                                <td class="text-monospace">{{ $session->ip_address }}</td>
                                <td>{{ $session->location ?? 'N/A' }}</td>
                                <td>{{ $session->last_activity?->diffForHumans() }}</td>
                                <td>
                                    @if($session->is_current)
                                    <span class="badge bg-success">Actuelle</span>
                                    @else
                                    <form action="{{ route('profile.session.destroy', $session) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    Aucune session supplémentaire
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <x-ui.card title="Conseils Sécurité">
                <ul class="list-unstyled small text-muted">
                    <li class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Mot de passe fort:</strong> 12+ caractères
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Unique:</strong> Ne réutilisez pas
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>2FA:</strong> Couche supplémentaire
                    </li>
                    <li>
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Sessions:</strong> Déconnectez autres appareils
                    </li>
                </ul>
            </x-ui.card>

            <x-ui.card title="Danger Zone" class="mt-4 border-danger">
                <div class="alert alert-danger rounded-3 mb-3">
                    <small>
                        <i class="fas fa-warning me-2"></i>
                        Cette action est irréversible
                    </small>
                </div>

                <form action="{{ route('profile.delete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger rounded-3 w-100" 
                            onclick="return confirm('Êtes-vous sûr ? Cela supprimera votre compte définitivement.')">
                        <i class="fas fa-trash me-2"></i>Supprimer le Compte
                    </button>
                </form>
            </x-ui.card>
        </div>
    </div>

@endsection
