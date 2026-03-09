@extends('layouts.auth')
@section('title', __('auth.create_account'))

@section('content')
<div class="auth-wrapper">

  {{-- Left Panel --}}
  <div class="auth-panel">
    <div class="auth-brand">
      <div class="auth-brand-logo">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
          <path d="M6 12v5c3 3 9 3 12 0v-5"/>
        </svg>
      </div>
      <div>
        <span class="auth-brand-name">{{ config('app.name', 'Millenaire') }}</span>
        <span class="auth-brand-tag">{{ __('auth.launch_faster') }}</span>
      </div>
    </div>

    <h1 class="auth-panel-title">{{ __('auth.register_panel_title') }}</h1>
    <p class="auth-panel-desc">{{ __('auth.register_panel_desc') }}</p>

    <ul class="auth-panel-features">
      <li><i data-lucide="users"></i>{{ __('auth.feature_collaborators') }}</li>
      <li><i data-lucide="kanban-square"></i>{{ __('auth.feature_projects') }}</li>
      <li><i data-lucide="bell"></i>{{ __('auth.feature_notifications') }}</li>
      <li><i data-lucide="bar-chart-2"></i>{{ __('auth.feature_reporting') }}</li>
    </ul>

    <div class="auth-meta">© {{ date('Y') }} {{ config('app.name') }}. {{ __('auth.rights_reserved') }}</div>
  </div>

  {{-- Form --}}
  <div class="auth-form-area">
    <div class="auth-card">
      <h2 class="auth-title">{{ __('auth.create_account') }}</h2>
      <p class="auth-subtitle">{{ __('auth.register_subtitle') }}</p>

      @if($errors->any())
      <div style="background:var(--danger-bg);border:1px solid var(--danger);border-radius:8px;
                  padding:10px 14px;margin-bottom:16px;font-size:13px;color:var(--danger)">
        <ul style="list-style:none;margin:0">
          @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
      </div>
      @endif

      <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="grid grid-2" style="gap:12px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" for="first_name">{{ __('auth.first_name') }}</label>
            <input type="text" id="first_name" name="first_name" class="form-control"
                   placeholder="{{ __('auth.first_name_placeholder') }}"
                   value="{{ old('first_name') }}" required>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" for="last_name">{{ __('auth.last_name') }}</label>
            <input type="text" id="last_name" name="last_name" class="form-control"
                   placeholder="{{ __('auth.last_name_placeholder') }}"
                   value="{{ old('last_name') }}" required>
          </div>
        </div>

        <div class="form-group mt-12">
          <label class="form-label" for="email">{{ __('auth.email') }}</label>
          <input type="email" id="email" name="email" class="form-control"
                 placeholder="nom@exemple.com" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">{{ __('auth.password') }}</label>
          <div class="input-group">
            <input type="password" id="password" name="password" class="form-control"
                   placeholder="{{ __('auth.create_password') }}" required>
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
          <p class="form-hint">{{ __('auth.password_hint') }}</p>
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

        <div style="margin-bottom:20px">
          <label class="form-check">
            <input type="checkbox" name="terms" required {{ old('terms') ? 'checked' : '' }}>
            <span class="form-check-label">
              {{ __('auth.agree_to') }}
              <a href="#" style="color:var(--primary);font-weight:600">{{ __('auth.terms') }}</a>
              {{ __('auth.and') }}
              <a href="#" style="color:var(--primary);font-weight:600">{{ __('auth.privacy_policy') }}</a>.
            </span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg">
          {{ __('auth.create_account_btn') }}
        </button>

        <div class="auth-footer">
          {{ __('auth.have_account') }}
          <a href="{{ route('login') }}">{{ __('auth.sign_in') }}</a>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection
