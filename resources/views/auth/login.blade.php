@extends('layouts.auth')
@section('title', 'Connexion')

@section('content')
<div class="auth-wrapper">

  {{-- Left Panel --}}
  <div class="auth-panel">
    <div class="auth-brand">
      <div class="auth-brand-logo">
        @php 
          $logoUrl = \App\Helpers\SettingsHelper::logoUrl();
          $settings = \App\Models\EstablishmentSetting::getInstance();
        @endphp
        @if($logoUrl)
          <img src="{{ $logoUrl }}" alt="Logo de l'établissement" style="width: 48px; height: 48px; object-fit: contain; max-width: 100%;" loading="lazy">
        @else
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 48px; height: 48px; color: #0d9488;">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
          </svg>
        @endif
      </div>
      <div>
        <span class="auth-brand-name">{{ $settings->platform_name ?? 'Millénaire Connect' }}</span>
        <span class="auth-brand-tag">{{ $settings->platform_tagline ?? 'La plateforme de gestion scolaire complète pour l\'éducation moderne' }}</span>
      </div>
    </div>

    <h1 class="auth-panel-title">Bienvenue sur Millénaire Connect</h1>
    <p class="auth-panel-desc">Bienvenue ! Connectez-vous pour accéder à votre espace.</p>

    <ul class="auth-panel-features">
      <li><i data-lucide="bar-chart-3"></i><span>Bulletins Scolaires</span></li>
      <li><i data-lucide="calendar"></i><span>Emploi du Temps</span></li>
      <li><i data-lucide="message-circle"></i><span>Messagerie Sécurisée</span></li>
      <li><i data-lucide="credit-card"></i><span>Paiements Mobile Money</span></li>
    </ul>

    <div class="auth-meta">© {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</div>
  </div>

  {{-- Form --}}
  <div class="auth-form-area">
    <div class="auth-card">
      <div style="margin-bottom: 32px;">
        <h2 class="auth-title" style="font-size: 28px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Connexion</h2>
        <p class="auth-subtitle" style="font-size: 14px; color: #64748b; margin: 0;">Connectez-vous à votre compte Millénaire</p>
      </div>

      @if($errors->any())
      <div style="background-color: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: #991b1b; font-weight: 500; display: flex; gap: 10px; align-items: flex-start;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
          <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <div>
          <strong>Erreur :</strong> {{ $errors->first() }}
        </div>
      </div>
      @endif

      <form method="POST" action="{{ route('login.post') }}" autocomplete="on" novalidate>
        @csrf

        <div class="form-group" style="margin-bottom: 18px;">
          <label class="form-label" for="email" style="display: block; font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">Adresse e-mail</label>
          <div style="position: relative;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" 
                 stroke="currentColor" stroke-width="2" style="position: absolute; left: 14px; top: 50%; 
                 transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
            <input type="email" id="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                   value="{{ old('email') }}"
                   style="padding-left: 44px; height: 44px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 14px; transition: all 0.3s ease;"
                   placeholder="votre@email.com" required autofocus autocomplete="email">
          </div>
          @if($errors->has('email'))
            <small style="color: #dc2626; font-size: 12px; margin-top: 6px; display: block;">{{ $errors->first('email') }}</small>
          @endif
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; gap: 8px;">
            <label class="form-label" for="password" style="font-size: 14px; font-weight: 600; color: #1e293b; margin: 0;\" >Mot de passe</label>
            <a href="{{ route('password.request') }}" style="font-size: 13px; color: #0d9488; text-decoration: none; font-weight: 600; hover: text-decoration: underline;">
              Mot de passe oublié ?
            </a>
          </div>
          <div style="position: relative;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" style="position: absolute; left: 14px; top: 50%;
                 transform: translateY(-50%); color: #94a3b8; pointer-events: none;">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input type="password" id="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                   style="padding-left: 44px; padding-right: 44px; height: 44px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 14px; transition: all 0.3s ease;"
                   placeholder="••••••••" required autocomplete="current-password">
            <button type="button" class="input-group-icon" data-toggle-password="password" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 4px; cursor: pointer; color: #64748b; display: flex; align-items: center; justify-content: center;" title="Afficher/masquer le mot de passe">
              <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
              <svg class="icon-eye-off hidden" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
          @if($errors->has('password'))
            <small style="color: #dc2626; font-size: 12px; margin-top: 6px; display: block;">{{ $errors->first('password') }}</small>
          @endif
        </div>

        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 24px; margin-top: 18px;">
          <input type="checkbox" id="remember" name="remember" value="1" @if(old('remember')) checked @endif 
                 style="width: 18px; height: 18px; accent-color: #0d9488; border-radius: 4px; cursor: pointer; border: 1.5px solid #cbd5e1;">
          <label for="remember" style="font-size: 13px; color: #475569; cursor: pointer; margin: 0; user-select: none; font-weight: 500;">
            Se souvenir de moi
          </label>
        </div>

        <button type="submit" style="width: 100%; height: 44px; background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3); hover: box-shadow: 0 6px 16px rgba(13, 148, 136, 0.4);">
          Se connecter
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </button>
      </form>

      <div style="text-align: center; margin-top: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0;">
        <p style="font-size: 13px; color: #64748b; margin: 0;">
          Vous n'avez pas de compte ?
          <a href="{{ route('register') }}" style="color: #0d9488; font-weight: 600; text-decoration: none;">
            En créer un
          </a>
        </p>
      </div>

      <div style="text-align: center; margin-top: 16px; font-size: 12px;">
        <a href="{{ route('home') }}" style="color: #0d9488; font-weight: 600; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 6px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
          </svg>
          Retour au site
        </a>
      </div>
    </div>
  </div>

</div>

<style>
  .input-group-icon:hover {
    color: #334155 !important;
  }

  button[type="submit"]:hover {
    transform: translateY(-2px);
  }

  button[type="submit"]:active {
    transform: translateY(0);
  }

  .form-control:focus {
    border-color: #0d9488;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
  }

  .form-control.is-invalid {
    border-color: #dc2626;
  }

  .form-control.is-invalid:focus {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Password Toggle
    const toggleButtons = document.querySelectorAll('[data-toggle-password]');
    toggleButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.dataset.togglePassword;
        const input = document.getElementById(targetId);
        const iconEye = this.querySelector('.icon-eye');
        const iconEyeOff = this.querySelector('.icon-eye-off');
        
        if (input.type === 'password') {
          input.type = 'text';
          iconEye.classList.add('hidden');
          iconEyeOff.classList.remove('hidden');
        } else {
          input.type = 'password';
          iconEye.classList.remove('hidden');
          iconEyeOff.classList.add('hidden');
        }
      });
    });

    // Load lucide icons
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  });
</script>
@endsection
