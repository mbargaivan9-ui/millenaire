<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ app()->getLocale() === 'fr' ? 'Nouveau mot de passe' : 'Reset password' }} — {{ \App\Models\EstablishmentSetting::getInstance()->platform_name ?? 'Millénaire Connect' }}</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
<style>
:root { --primary: {{ \App\Models\EstablishmentSetting::getInstance()->primary_color ?? '#0d9488' }}; }
body { font-family:'Inter',system-ui,sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; background:#f1f5f9; }
.auth-card { width:100%; max-width:440px; background:#fff; border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,.1); padding:2.5rem; }
.form-group { margin-bottom:1rem; }
.form-label { display:block; font-size:.82rem; font-weight:600; color:#475569; margin-bottom:.4rem; }
.form-control { width:100%; border:1.5px solid #e2e8f0; border-radius:10px; padding:.65rem .9rem; font-size:.88rem; }
.form-control:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(13,148,136,.1); }
.btn-auth { width:100%; padding:.75rem; border-radius:10px; background:var(--primary); color:#fff; border:none; font-weight:700; font-size:.9rem; cursor:pointer; }
</style>
</head>
<body>
<div class="auth-card">
    <div style="text-align:center;margin-bottom:2rem">
        <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,var(--primary),#14b8a6);display:flex;align-items:center;justify-content:center;margin:0 auto .75rem">
            <svg width="28" height="28" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <h1 style="font-size:1.3rem;font-weight:800;margin-bottom:.35rem">{{ app()->getLocale() === 'fr' ? 'Nouveau mot de passe' : 'Reset your password' }}</h1>
    </div>

    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.83rem;color:#dc2626">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $email ?? '') }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Nouveau mot de passe' : 'New password' }}</label>
            <input type="password" name="password" class="form-control" required minlength="8" placeholder="••••••••">
        </div>
        <div class="form-group">
            <label class="form-label">{{ app()->getLocale() === 'fr' ? 'Confirmer le mot de passe' : 'Confirm password' }}</label>
            <input type="password" name="password_confirmation" class="form-control" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn-auth">
            {{ app()->getLocale() === 'fr' ? 'Réinitialiser le mot de passe' : 'Reset password' }}
        </button>
    </form>
</div>
</body>
</html>
