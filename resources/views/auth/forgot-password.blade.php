@extends('layouts.auth')
@section('title', __('auth.forgot_password'))

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
        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
      </svg>
    </div>

    <h2 class="auth-simple-title">{{ __('auth.forgot_password') }}</h2>
    <p class="auth-simple-text">{{ __('auth.forgot_password_desc') }}</p>

    @if(session('status'))
    <div style="background:var(--success-bg);border:1px solid var(--success);border-radius:8px;
                padding:10px 14px;margin-bottom:16px;font-size:13px;color:var(--success);text-align:left">
      {{ session('status') }}
    </div>
    @endif

    @if($errors->any())
    <div style="background:var(--danger-bg);border:1px solid var(--danger);border-radius:8px;
                padding:10px 14px;margin-bottom:16px;font-size:13px;color:var(--danger);text-align:left">
      {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" style="text-align:left">
      @csrf
      <div class="form-group">
        <label class="form-label" for="email">{{ __('auth.email') }}</label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="nom@exemple.com" value="{{ old('email') }}" required>
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="margin-bottom:16px;padding:11px">
        {{ __('auth.send_reset_link') }}
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
