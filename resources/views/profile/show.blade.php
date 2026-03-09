{{--
    | profile/show.blade.php — Mon Profil
    --}}

@extends('layouts.app')
@php
  $pageTitle = $pageTitle ?? (app()->getLocale() === 'fr' ? 'Mon Profil' : 'My Profile');
@endphp
@section('title', $pageTitle)

@section('content')
@php $isFr = app()->getLocale() === 'fr'; @endphp

<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="page-icon" style="background:linear-gradient(135deg,#64748b,#475569)"><i data-lucide="user"></i></div>
        <h1 class="page-title">{{ $isFr ? 'Mon Profil' : 'My Profile' }}</h1>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success mb-4">✅ {{ session('success') }}</div>
@endif

<div class="row gy-4">

    {{-- ─── Profile info form ─────────────────────────────────────────────── --}}
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ $isFr ? 'Informations personnelles' : 'Personal information' }}</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="row gy-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Nom complet' : 'Full name' }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Prénom affiché' : 'Display name' }}</label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" placeholder="{{ $isFr ? 'Optionnel' : 'Optional' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Téléphone' : 'Phone' }}</label>
                            <input type="tel" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="+237 6XX XXX XXX">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ $isFr ? 'Photo de profil' : 'Profile photo' }}</label>
                            <div class="d-flex align-items-center gap-3">
                                <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;font-size:1.3rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                </div>
                                <input type="file" name="avatar" class="form-control" accept="image/*" style="flex:1">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save" style="width:14px" class="me-1"></i>
                            {{ $isFr ? 'Enregistrer les modifications' : 'Save changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Password form --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="card-title mb-0"><i data-lucide="lock" style="width:16px" class="me-2"></i>{{ $isFr ? 'Changer le mot de passe' : 'Change password' }}</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf @method('PUT')
                    <div class="row gy-3">
                        <div class="col-12">
                            <label class="form-label">{{ $isFr ? 'Mot de passe actuel' : 'Current password' }}</label>
                            <input type="password" name="current_password" class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}" required>
                            @error('current_password')<div class="text-danger" style="font-size:.8rem">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Nouveau mot de passe' : 'New password' }}</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ $isFr ? 'Confirmer' : 'Confirm' }}</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-warning">
                            <i data-lucide="key" style="width:14px" class="me-1"></i>
                            {{ $isFr ? 'Changer le mot de passe' : 'Change password' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Language preference --}}
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0">🌐 {{ $isFr ? 'Langue préférée' : 'Preferred language' }}</h6></div>
            <div class="card-body">
                <div class="d-flex gap-3">
                    <a href="{{ route('lang.switch', 'fr') }}" class="btn {{ app()->getLocale() === 'fr' ? 'btn-primary' : 'btn-light' }}" style="min-width:120px;justify-content:center">
                        🇫🇷 Français
                    </a>
                    <a href="{{ route('lang.switch', 'en') }}" class="btn {{ app()->getLocale() === 'en' ? 'btn-primary' : 'btn-light' }}" style="min-width:120px;justify-content:center">
                        🇺🇸 English
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Side panel ──────────────────────────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center py-4">
                <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;font-size:1.8rem;font-weight:900;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </div>
                <div class="fw-bold mb-1">{{ $user->display_name ?? $user->name }}</div>
                <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.5rem">{{ $user->email }}</div>
                <span class="badge bg-primary" style="font-size:.75rem;text-transform:capitalize">{{ $user->role }}</span>

                <hr class="my-3">
                <div style="font-size:.8rem;color:var(--text-muted);text-align:left">
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ $isFr ? 'Membre depuis' : 'Member since' }}</span>
                        <strong>{{ $user->created_at?->format('d/m/Y') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ $isFr ? 'Dernière connexion' : 'Last login' }}</span>
                        <strong>{{ $user->last_login?->diffForHumans() ?? '—' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>{{ $isFr ? 'Statut' : 'Status' }}</span>
                        <strong style="color:#10b981">● {{ $isFr ? 'Actif' : 'Active' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
