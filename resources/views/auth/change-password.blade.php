<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ app()->getLocale() === 'fr' ? 'Modifier le mot de passe' : 'Change password' }} — {{ \App\Models\EstablishmentSetting::getInstance()->platform_name ?? 'Millénaire Connect' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <style>
    :root {
        --primary: {{ \App\Models\EstablishmentSetting::getInstance()->primary_color ?? '#0d9488' }};
        --primary-dark: {{ \App\Models\EstablishmentSetting::getInstance()->secondary_color ?? '#0f766e' }};
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Inter', system-ui, sans-serif;
        min-height: 100vh;
        display: flex;
        background: #f1f5f9;
    }

    /* ─── Left panel ─────────────────────────────────────────────────────── */
    .auth-left {
        flex: 1;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        display: flex; flex-direction: column;
        justify-content: center; align-items: center;
        padding: 3rem;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .auth-left::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .auth-logo {
        width: 80px; height: 80px; border-radius: 20px;
        background: rgba(255,255,255,.15);
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(8px);
    }
    .auth-left h1 { font-size: 1.8rem; font-weight: 900; margin-bottom: .5rem; position: relative; }
    .auth-left p  { opacity: .8; font-size: .9rem; position: relative; max-width: 320px; text-align: center; }

    .auth-features { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; margin-top: 2rem; width: 100%; max-width: 380px; position: relative; }
    .auth-feature {
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 12px; padding: .9rem;
        backdrop-filter: blur(4px);
    }
    .auth-feature-icon { font-size: 1.5rem; margin-bottom: .4rem; }
    .auth-feature-label { font-size: .75rem; font-weight: 600; opacity: .9; }

    /* ─── Right panel ────────────────────────────────────────────────────── */
    .auth-right {
        width: 480px;
        background: #fff;
        display: flex; flex-direction: column;
        justify-content: center;
        padding: 3rem;
    }

    .auth-form-title { font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-bottom: .4rem; }
    .auth-form-sub   { font-size: .85rem; color: #94a3b8; margin-bottom: 2rem; }

    .form-group { margin-bottom: 1.1rem; }
    .form-label { display: block; font-size: .82rem; font-weight: 600; color: #475569; margin-bottom: .4rem; }
    .form-control {
        width: 100%; padding: .65rem .9rem;
        border: 1.5px solid #e2e8f0; border-radius: 10px;
        font-size: .88rem; color: #0f172a; background: #fff;
        transition: all .15s ease;
    }
    .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
    .form-control.is-invalid { border-color: #ef4444; }

    .input-wrap { position: relative; }
    .input-icon { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .input-wrap .form-control { padding-left: 2.5rem; }

    .btn-auth {
        width: 100%; padding: .75rem; border-radius: 10px;
        background: var(--primary); color: #fff;
        border: none; font-size: .9rem; font-weight: 700;
        cursor: pointer; transition: all .2s ease;
        display: flex; align-items: center; justify-content: center; gap: .5rem;
    }
    .btn-auth:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 4px 15px rgba(13,148,136,.3); }

    .info-badge { background:#e0f2fe; border:1px solid #7dd3fc; color:#0369a1; padding:.75rem 1rem; border-radius:10px; font-size:.82rem; margin-bottom:1.5rem; }
    .success-badge { background:#dcfce7; border:1px solid #86efac; color:#16a34a; padding:.75rem 1rem; border-radius:10px; font-size:.82rem; margin-bottom:1.5rem; }
    .error-badge { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; padding:.75rem 1rem; border-radius:10px; font-size:.82rem; margin-bottom:1.5rem; }

    .invalid-feedback { display:block; font-size:.75rem; color:#ef4444; margin-top:.3rem; }

    .password-requirements {
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
        padding: 1rem; margin-top: 1rem; font-size: .8rem; color: #475569;
    }
    .password-requirements li { margin-bottom: .4rem; }
    .password-requirements .check { color: #16a34a; font-weight: 600; }
    .password-requirements .uncheck { color: #94a3b8; }

    .auth-footer { text-align: center; margin-top: 1.5rem; font-size: .82rem; color: #94a3b8; }
    .auth-footer a { color: var(--primary); font-weight: 600; text-decoration: none; }

    @media (max-width: 768px) {
        .auth-left { display: none; }
        .auth-right { width: 100%; padding: 2rem; }
    }
    </style>
</head>
<body>

<div class="auth-left">
    <div class="auth-logo">
        @php $settings = \App\Models\EstablishmentSetting::getInstance(); @endphp
        @if($settings->logo_path)
            <img src="{{ asset($settings->logo_path) }}" style="width:50px;height:50px;object-fit:contain" alt="Logo">
        @else
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <path d="M12 1l9 5v6c0 5.55-3.84 10.74-9 12-5.16-1.26-9-6.45-9-12V6l9-5z"/><path d="M10 17l4-4 4 4M10 13l4 4 4-4"/>
            </svg>
        @endif
    </div>
    <h1>{{ app()->getLocale() === 'fr' ? 'Bienvenue!' : 'Welcome!' }}</h1>
    <p>{{ app()->getLocale() === 'fr' ? 'Veuillez créer un nouveau mot de passe pour sécuriser votre compte.' : 'Please create a new password to secure your account.' }}</p>

    <div class="auth-features">
        <div class="auth-feature"><div class="auth-feature-icon">🔒</div><div class="auth-feature-label">{{ app()->getLocale() === 'fr' ? 'Sécurité renforcée' : 'Enhanced security' }}</div></div>
        <div class="auth-feature"><div class="auth-feature-icon">✅</div><div class="auth-feature-label">{{ app()->getLocale() === 'fr' ? 'Validation forte' : 'Strong validation' }}</div></div>
        <div class="auth-feature"><div class="auth-feature-icon">⚡</div><div class="auth-feature-label">{{ app()->getLocale() === 'fr' ? 'Accès rapide' : 'Quick access' }}</div></div>
        <div class="auth-feature"><div class="auth-feature-icon">🛡️</div><div class="auth-feature-label">{{ app()->getLocale() === 'fr' ? 'Protection complète' : 'Full protection' }}</div></div>
    </div>
</div>

<div class="auth-right">

    <div class="auth-form-title">
        {{ auth()->user()->must_change_password 
            ? (app()->getLocale() === 'fr' ? 'Sécurisez votre compte' : 'Secure your account')
            : (app()->getLocale() === 'fr' ? 'Modifier le mot de passe' : 'Change password')
        }}
    </div>
    <div class="auth-form-sub">
        {{ auth()->user()->must_change_password 
            ? (app()->getLocale() === 'fr' 
                ? 'Créez un mot de passe sécurisé pour accéder à votre espace.' 
                : 'Create a secure password to access your space.'
              )
            : (app()->getLocale() === 'fr' 
                ? 'Modifiez votre mot de passe actuel' 
                : 'Update your current password'
              )
        }}
    </div>

    @if($errors->any())
        <div class="error-badge">
            <strong>{{ app()->getLocale() === 'fr' ? 'Erreur' : 'Error' }}:</strong>
            {{ $errors->first() }}
        </div>
    @endif

    @if(session('info'))
        <div class="info-badge">
            {{ session('info') }}
        </div>
    @endif

    @if(session('success'))
        <div class="success-badge">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('auth.update-password') }}" novalidate>
        @csrf

        @if(auth()->user()->must_change_password)
            {{-- First login: no need to verify current password --}}
            <input type="hidden" name="skip_current" value="1">
        @else
            {{-- Regular password change: verify current password --}}
            <div class="form-group">
                <label class="form-label">
                    {{ app()->getLocale() === 'fr' ? 'Mot de passe actuel' : 'Current password' }}
                    <span style="color:#ef4444">*</span>
                </label>
                <div class="input-wrap">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <input type="password" 
                           name="current_password" 
                           class="form-control @error('current_password') is-invalid @enderror" 
                           placeholder="••••••••"
                           required>
                </div>
                @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        @endif

        <div class="form-group">
            <label class="form-label">
                {{ app()->getLocale() === 'fr' ? 'Nouveau mot de passe' : 'New password' }}
                <span style="color:#ef4444">*</span>
            </label>
            <div class="input-wrap">
                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1l9 5v6c0 5.55-3.84 10.74-9 12-5.16-1.26-9-6.45-9-12V6l9-5z"/><path d="M10 17l4-4 4 4M10 13l4 4 4-4"/></svg>
                <input type="password" 
                       name="password" 
                       id="password"
                       class="form-control @error('password') is-invalid @enderror" 
                       placeholder="••••••••"
                       required>
            </div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">
                {{ app()->getLocale() === 'fr' ? 'Confirmer le mot de passe' : 'Confirm password' }}
                <span style="color:#ef4444">*</span>
            </label>
            <div class="input-wrap">
                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1l9 5v6c0 5.55-3.84 10.74-9 12-5.16-1.26-9-6.45-9-12V6l9-5z"/><path d="M9 12l2 2 4-4"/></svg>
                <input type="password" 
                       name="password_confirmation" 
                       id="password_confirmation"
                       class="form-control" 
                       placeholder="••••••••"
                       required>
            </div>
        </div>

        <div class="password-requirements">
            <strong style="display:block;margin-bottom:.6rem;color:#0f172a">{{ app()->getLocale() === 'fr' ? 'Exigences du mot de passe:' : 'Password requirements:' }}</strong>
            <ul style="list-style:none;padding:0;margin:0">
                <li><span class="uncheck" id="req-length">○</span> {{ app()->getLocale() === 'fr' ? 'Au moins 8 caractères' : 'At least 8 characters' }}</li>
                <li><span class="uncheck" id="req-upper">○</span> {{ app()->getLocale() === 'fr' ? 'Au moins 1 lettre majuscule' : 'At least 1 uppercase letter' }}</li>
                <li><span class="uncheck" id="req-lower">○</span> {{ app()->getLocale() === 'fr' ? 'Au moins 1 lettre minuscule' : 'At least 1 lowercase letter' }}</li>
                <li><span class="uncheck" id="req-number">○</span> {{ app()->getLocale() === 'fr' ? 'Au moins 1 chiffre' : 'At least 1 number' }}</li>
                <li><span class="uncheck" id="req-symbol">○</span> {{ app()->getLocale() === 'fr' ? 'Au moins 1 caractère spécial' : 'At least 1 special character' }}</li>
            </ul>
        </div>

        <button type="submit" class="btn-auth mt-4">
            {{ app()->getLocale() === 'fr' ? 'Mettre à jour le mot de passe' : 'Update password' }}
        </button>

        @if(!auth()->user()->must_change_password)
            <div class="auth-footer">
                <a href="javascript:history.back()">
                    {{ app()->getLocale() === 'fr' ? '← Retour' : '← Back' }}
                </a>
            </div>
        @endif
    </form>
</div>

<script>
const passwordInput = document.getElementById('password');
const requirements = {
    length: document.getElementById('req-length'),
    upper: document.getElementById('req-upper'),
    lower: document.getElementById('req-lower'),
    number: document.getElementById('req-number'),
    symbol: document.getElementById('req-symbol'),
};

function updateRequirements() {
    const pwd = passwordInput.value;
    
    const checks = {
        length: pwd.length >= 8,
        upper: /[A-Z]/.test(pwd),
        lower: /[a-z]/.test(pwd),
        number: /[0-9]/.test(pwd),
        symbol: /[!@#$%^&*()_+\-=\[\]{};:'",.<>?\/\\|`~]/.test(pwd),
    };

    Object.keys(requirements).forEach(key => {
        if (checks[key]) {
            requirements[key].classList.remove('uncheck');
            requirements[key].classList.add('check');
            requirements[key].textContent = '✓';
        } else {
            requirements[key].classList.remove('check');
            requirements[key].classList.add('uncheck');
            requirements[key].textContent = '○';
        }
    });
}

passwordInput.addEventListener('input', updateRequirements);
updateRequirements(); // Initial check
</script>
</body>
</html>
