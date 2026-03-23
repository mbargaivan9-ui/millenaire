<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="{{ Cookie::get('theme', 'light') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title') — {{ config('app.name', 'Millenaire') }}</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

  <style>
    :root {
      --primary: #0d9488;
      --primary-dark: #0f766e;
      --success: #16a34a;
      --danger: #dc2626;
      --danger-bg: #fef2f2;
      --warning: #ea580c;
      --info: #0284c7;
      --text: #0f172a;
      --text-secondary: #64748b;
      --border: #cbd5e1;
      --bg-light: #f8fafc;
      --bg-lighter: #f1f5f9;
      --surface: #fff;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      width: 100%;
      overflow-x: hidden;
    }

    body {
      font-family: 'Inter', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
      font-size: 16px;
      line-height: 1.6;
      color: #0f172a;
      background: #f1f5f9;
    }

    /* Language Switcher */
    .lang-switcher-wrapper {
      position: fixed;
      top: 16px;
      right: 16px;
      z-index: 1050;
      max-width: 400px;
    }

    @media (max-width: 768px) {
      .lang-switcher-wrapper {
        top: 12px;
        right: 12px;
      }
    }

    @media (max-width: 480px) {
      .lang-switcher-wrapper {
        top: 8px;
        right: 8px;
      }
    }

    /* Content Area */
    main, [role="main"] {
      position: relative;
      z-index: 1;
      width: 100%;
    }

    body > :not(.lang-switcher-wrapper) {
      position: relative;
      z-index: 1;
    }

    /* Auth Layout */
    .auth-wrapper {
      display: flex;
      min-height: 100vh;
      width: 100%;
    }

    .auth-panel {
      flex: 1;
      background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
      color: white;
      padding: 3rem 2rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      position: relative;
      overflow: hidden;
      min-width: 350px;
    }

    .auth-panel::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
      pointer-events: none;
    }

    .auth-brand {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 1rem;
      margin-bottom: 2rem;
      position: relative;
      z-index: 1;
    }

    .auth-brand-logo {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      background: rgba(255,255,255,0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 28px;
    }

    .auth-brand-name {
      font-size: 1.2rem;
      font-weight: 700;
      display: block;
    }

    .auth-brand-tag {
      font-size: 0.8rem;
      opacity: 0.8;
      display: block;
    }

    .auth-panel-title {
      font-size: 1.8rem;
      font-weight: 900;
      margin-bottom: 0.5rem;
      position: relative;
      z-index: 1;
    }

    .auth-panel-desc {
      font-size: 0.95rem;
      opacity: 0.85;
      max-width: 320px;
      margin-bottom: 2rem;
      position: relative;
      z-index: 1;
      line-height: 1.6;
    }

    .auth-panel-features {
      list-style: none;
      position: relative;
      z-index: 1;
      margin-bottom: 2rem;
    }

    .auth-panel-features li {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      font-size: 0.9rem;
      margin-bottom: 0.75rem;
      opacity: 0.9;
    }

    .auth-panel-features i {
      width: 18px;
      height: 18px;
    }

    .auth-meta {
      position: absolute;
      bottom: 1.5rem;
      font-size: 0.8rem;
      opacity: 0.7;
    }

    .auth-form-area {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      overflow-y: auto;
    }

    .auth-card {
      width: 100%;
      max-width: 480px;
      background: white;
      border-radius: 16px;
      padding: 2.5rem;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .auth-title {
      font-size: 1.5rem;
      font-weight: 800;
      color: #0f172a;
      margin-bottom: 0.5rem;
    }

    .auth-subtitle {
      font-size: 0.9rem;
      color: #64748b;
      margin-bottom: 1.5rem;
    }

    /* Form Elements */
    .form-group {
      margin-bottom: 1.25rem;
    }

    .form-label {
      display: block;
      font-size: 0.85rem;
      font-weight: 600;
      color: #334155;
      margin-bottom: 0.5rem;
      text-transform: none;
    }

    .form-control,
    .form-select,
    textarea {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1.5px solid #cbd5e1;
      border-radius: 10px;
      font-size: 0.95rem;
      font-family: inherit;
      color: #0f172a;
      background: #fff;
      transition: all 0.2s ease;
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
    }

    .form-control::placeholder {
      color: #a0aec0;
      opacity: 0.8;
    }

    .form-control:focus,
    .form-select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(13,148,136,0.15);
      background: #fafef9;
    }

    .form-control:hover:not(:focus) {
      border-color: #a0aec0;
    }

    .form-control.is-invalid {
      border-color: #ef4444;
      background: #fef2f2;
    }

    .form-control.is-invalid:focus {
      box-shadow: 0 0 0 3px rgba(239,68,68,0.15);
    }

    .form-hint {
      font-size: 0.8rem;
      color: #64748b;
      margin-top: 0.4rem;
      display: block;
    }

    .input-group {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-group .form-control {
      padding-right: 2.5rem;
    }

    .input-group-icon {
      position: absolute;
      right: 1rem;
      background: none;
      border: none;
      cursor: pointer;
      color: #94a3b8;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 20px;
      height: 20px;
      transition: color 0.2s ease;
      padding: 0;
    }

    .input-group-icon:hover {
      color: var(--primary);
    }

    .input-group-icon:focus {
      outline: 2px solid var(--primary);
      outline-offset: 2px;
    }

    .hidden {
      display: none !important;
    }

    /* Grid */
    .grid {
      display: grid;
    }

    .grid-2 {
      grid-template-columns: 1fr 1fr;
    }

    /* Buttons */
    .btn,
    button[type="submit"],
    button[type="button"] {
      font-family: inherit;
      border-radius: 10px;
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.25s ease;
      -webkit-appearance: none;
      appearance: none;
    }

    .btn-primary,
    button[type="submit"] {
      width: 100%;
      padding: 0.9rem 1rem;
      background: var(--primary);
      color: white;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .btn-primary:hover,
    button[type="submit"]:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(13,148,136,0.25);
    }

    .btn-primary:active,
    button[type="submit"]:active {
      transform: translateY(0);
      box-shadow: 0 2px 8px rgba(13,148,136,0.15);
    }

    .btn-primary:focus,
    button[type="submit"]:focus {
      outline: 2px solid var(--primary);
      outline-offset: 2px;
    }

    .btn-link {
      background: none;
      color: var(--primary);
      text-decoration: none;
      padding: 0;
      font-weight: 600;
    }

    .btn-link:hover {
      text-decoration: underline;
    }

    /* Links */
    a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s ease;
    }

    a:hover {
      opacity: 0.8;
    }

    /* Spacing Utilities */
    .mt-12 {
      margin-top: 1.2rem;
    }

    .mb-0 {
      margin-bottom: 0 !important;
    }

    .text-center {
      text-align: center;
    }

    .text-muted {
      color: #64748b;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .auth-wrapper {
        flex-direction: column;
      }

      .auth-panel {
        min-height: 250px;
        padding: 2rem;
      }

      .auth-form-area {
        padding: 1.5rem;
      }

      .auth-card {
        padding: 2rem;
      }

      .grid-2 {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 480px) {
      body {
        font-size: 14px;
      }

      .auth-panel {
        min-height: 200px;
        padding: 1.5rem;
      }

      .auth-panel-title {
        font-size: 1.4rem;
      }

      .auth-form-area {
        padding: 1rem;
      }

      .auth-card {
        padding: 1.5rem;
      }

      .auth-title {
        font-size: 1.2rem;
      }

      .form-control,
      .form-select {
        padding: 0.65rem 0.75rem;
        font-size: 0.85rem;
        border-radius: 8px;
      }

      .btn-primary,
      button[type="submit"] {
        padding: 0.75rem;
        font-size: 0.85rem;
      }

      .input-group-icon {
        right: 0.75rem;
      }

      .input-group .form-control {
        padding-right: 2.2rem;
      }
    }
  </style>

  @stack('styles')
</head>
<body>

{{-- Language Switcher (Floating Button for Auth Pages) --}}
<div class="lang-switcher-wrapper">
  <x-language-switcher />
</div>

@yield('content')

<script src="{{ asset('js/app.js') }}"></script>
<script>lucide.createIcons();</script>
<script>
  // Toggle password visibility
  document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('[data-toggle-password]');
    toggleButtons.forEach(button => {
      button.addEventListener('click', function() {
        const fieldId = this.getAttribute('data-toggle-password');
        const field = document.getElementById(fieldId);
        const eyeIcon = this.querySelector('.icon-eye');
        const eyeOffIcon = this.querySelector('.icon-eye-off');
        
        if (field.type === 'password') {
          field.type = 'text';
          eyeIcon?.classList.add('hidden');
          eyeOffIcon?.classList.remove('hidden');
        } else {
          field.type = 'password';
          eyeIcon?.classList.remove('hidden');
          eyeOffIcon?.classList.add('hidden');
        }
      });
    });
  });
</script>
@stack('scripts')

</body>
</html>
