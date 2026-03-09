<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ app()->getLocale() === 'fr' ? 'Connexion' : 'Login' }} — {{ \App\Models\EstablishmentSetting::getInstance()->platform_name ?? 'Millénaire Connect' }}</title>
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
    .toggle-pw { position: absolute; right: .85rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8; background: none; border: none; }

    .btn-auth {
        width: 100%; padding: .75rem; border-radius: 10px;
        background: var(--primary); color: #fff;
        border: none; font-size: .9rem; font-weight: 700;
        cursor: pointer; transition: all .2s ease;
        display: flex; align-items: center; justify-content: center; gap: .5rem;
    }
    .btn-auth:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 4px 15px rgba(13,148,136,.3); }

    .role-pills { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .role-pill {
        padding: .3rem .85rem; border-radius: 20px;
        border: 1.5px solid #e2e8f0; cursor: pointer;
        font-size: .78rem; font-weight: 600; color: #64748b;
        transition: all .15s ease;
    }
    .role-pill:hover, .role-pill.active { border-color: var(--primary); background: rgba(13,148,136,.06); color: var(--primary); }

    .auth-footer { text-align: center; margin-top: 1.5rem; font-size: .82rem; color: #94a3b8; }
    .auth-footer a { color: var(--primary); font-weight: 600; text-decoration: none; }

    @media (max-width: 768px) {
        .auth-left { display: none; }
        .auth-right { width: 100%; }
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
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
        @endif
    </div>
    <h1>{{ $settings->platform_name ?? 'Millénaire Connect' }}</h1>
    <p>{{ $settings->slogan ?? (app()->getLocale() === 'fr' ? 'La plateforme digitale du Collège Millénaire Bilingue' : 'The digital platform of Collège Millénaire Bilingue') }}</p>

    <div class="auth-features">
        <div class="auth-feature"><div class="auth-feature-icon">📊</div><div class="auth-feature-label">{{ app()->getLocale() === 'fr' ? 'Bulletins numériques' : 'Digital report cards' }}</div></div>
        <div class="auth-feature"><div class="auth-feature-icon">📅</div><div class="auth-feature-label">{{ app()->getLocale() === 'fr' ? 'Emplois du temps' : 'Schedules' }}</div></div>
        <div class="auth-feature"><div class="auth-feature-icon">💬</div><div class="auth-feature-label">{{ app()->getLocale() === 'fr' ? 'Messagerie temps réel' : 'Real-time messaging' }}</div></div>
        <div class="auth-feature"><div class="auth-feature-icon">💰</div><div class="auth-feature-label">{{ app()->getLocale() === 'fr' ? 'Paiements Mobile Money' : 'Mobile Money payments' }}</div></div>
    </div>
</div>

<div class="auth-right">

    {{-- Language switcher --}}
    <div style="text-align:right;margin-bottom:1.5rem">
        <a href="{{ route('lang.switch', 'fr') }}" style="font-size:.78rem;font-weight:700;color:{{ app()->getLocale()==='fr' ? 'var(--primary)' : '#94a3b8' }};text-decoration:none;margin-right:.75rem">🇫🇷 FR</a>
        <a href="{{ route('lang.switch', 'en') }}" style="font-size:.78rem;font-weight:700;color:{{ app()->getLocale()==='en' ? 'var(--primary)' : '#94a3b8' }};text-decoration:none">🇺🇸 EN</a>
    </div>

    <div class="auth-form-title">{{ app()->getLocale() === 'fr' ? 'Connexion' : 'Sign in' }}</div>
    <div class="auth-form-sub">{{ app()->getLocale() === 'fr' ? 'Bienvenue ! Connectez-vous pour accéder à votre espace.' : 'Welcome back! Sign in to access your space.' }}</div>

    {{-- Errors --}}
    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.83rem;color:#dc2626">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Adresse email' : 'Email address' }}</label>
            <div class="input-wrap">
                <svg class="input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       value="{{ old('email') }}"
                       placeholder="vous@exemple.com" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center" style="margin-bottom:.4rem">
                <label class="form-label mb-0">{{ app()->getLocale() === 'fr' ? 'Mot de passe' : 'Password' }}</label>
                <a href="{{ route('password.request') }}" style="font-size:.78rem;color:var(--primary);text-decoration:none">
                    {{ app()->getLocale() === 'fr' ? 'Mot de passe oublié ?' : 'Forgot password?' }}
                </a>
            </div>
            <div class="input-wrap">
                <svg class="input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input type="password" name="password" id="pw-input" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                       placeholder="••••••••" required>
                <button type="button" class="toggle-pw" onclick="togglePw()" title="{{ app()->getLocale() === 'fr' ? 'Afficher/masquer' : 'Show/hide' }}">
                    <svg id="eye-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.5rem">
            <input type="checkbox" name="remember" id="remember" style="accent-color:var(--primary);width:16px;height:16px">
            <label for="remember" style="font-size:.83rem;color:#64748b;cursor:pointer">
                {{ app()->getLocale() === 'fr' ? 'Se souvenir de moi' : 'Remember me' }}
            </label>
        </div>

        <button type="submit" class="btn-auth">
            {{ app()->getLocale() === 'fr' ? 'Se connecter' : 'Sign in' }}
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
    </form>

    <div class="auth-footer">
        <a href="{{ route('home') }}">← {{ app()->getLocale() === 'fr' ? 'Retour au site' : 'Back to website' }}</a>
    </div>
</div>

<script>
function togglePw() {
    const inp  = document.getElementById('pw-input');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
