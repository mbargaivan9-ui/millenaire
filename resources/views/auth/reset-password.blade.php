@extends('layouts.auth')
@section('title', __('auth.reset_password'))

@section('content')
<div class="auth-simple">

  <div class="auth-simple-logo">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
      <path d="M6 12v5c3 3 9 3 12 0v-5"/>
    </svg>
    {{ config('app.name', 'Millenaire') }}
  </div>

  <div class="auth-simple-card">
    <div class="auth-simple-icon">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
      </svg>
    </div>

    <h2 class="auth-simple-title">{{ __('auth.set_new_password') }}</h2>
    <p class="auth-simple-text">{{ __('auth.set_new_password_desc') }}</p>

    @if($errors->any())
    <div style="background:var(--danger-bg);border:1px solid var(--danger);border-radius:8px;
                padding:10px 14px;margin-bottom:16px;font-size:13px;color:var(--danger);text-align:left">
      @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" style="text-align:left">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

      <div class="form-group">
        <label class="form-label" for="password">{{ __('auth.new_password') }}</label>
        <div class="input-group">
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="{{ __('auth.new_password') }}" required>
          <button type="button" class="input-group-icon" data-toggle-password="password">
            <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
            <svg class="icon-eye-off hidden" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
              <line x1="1" y1="1" x2="23" y2="23"/>
            </svg>
          </button>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password_confirmation">{{ __('auth.confirm_password') }}</label>
        <div class="input-group">
          <input type="password" id="password_confirmation" name="password_confirmation"
                 class="form-control" placeholder="{{ __('auth.confirm_password') }}" required>
          <button type="button" class="input-group-icon" data-toggle-password="password_confirmation">
            <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
            <svg class="icon-eye-off hidden" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
              <line x1="1" y1="1" x2="23" y2="23"/>
            </svg>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full" style="margin-bottom:16px;padding:11px">
        {{ __('auth.reset_password') }}
      </button>
    </form>

    <a href="{{ route('login') }}" style="font-size:13px;color:var(--text-muted);display:flex;align-items:center;justify-content:center;gap:4px">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
      {{ __('auth.back_to_login') }}
    </a>
  </div>

</div>
@endsection
