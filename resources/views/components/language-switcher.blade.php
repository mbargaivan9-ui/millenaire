<div class="lang-switcher-premium">
  {{-- Current Language Display --}}
  <button class="lang-trigger" data-bs-toggle="dropdown" aria-expanded="false" 
          title="{{ __('app.select_language') ?? 'Select Language' }}">
    <div class="lang-flag">
      @if(app()->getLocale() === 'fr')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 600" class="flag-icon">
          <rect width="900" height="600" fill="#000084"/>
          <rect x="300" width="600" height="600" fill="#fff"/>
          <rect x="600" width="300" height="600" fill="#ff0000"/>
        </svg>
      @else
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 7410 3900" class="flag-icon">
          <rect width="7410" height="3900" fill="#3c3b6b"/>
          <path d="M0,0 L7410,3900 M7410,0 L0,3900" stroke="#fff" stroke-width="300"/>
          <path d="M0,0 L7410,3900 M7410,0 L0,3900" stroke="#c01c28" stroke-width="200" stroke-dasharray="400,250"/>
          <rect width="7410" height="3900" fill="none" stroke="#fff" stroke-width="600"/>
        </svg>
      @endif
    </div>
    <div class="lang-info">
      <span class="lang-code">
        @if(app()->getLocale() === 'fr')
          FR
        @else
          EN
        @endif
      </span>
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
           stroke-width="2" class="lang-chevron">
        <polyline points="6 9 12 15 18 9"></polyline>
      </svg>
    </div>
  </button>

  {{-- Dropdown Menu --}}
  <div class="lang-dropdown dropdown-menu dropdown-menu-end">
    {{-- French --}}
    <a class="lang-option {{ app()->getLocale() === 'fr' ? 'active' : '' }}" 
       href="{{ route('lang.switch', 'fr') }}" data-lang="fr">
      <div class="lang-option-flag">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 600">
          <rect width="900" height="600" fill="#000084"/>
          <rect x="300" width="600" height="600" fill="#fff"/>
          <rect x="600" width="300" height="600" fill="#ff0000"/>
        </svg>
      </div>
      <div class="lang-option-text">
        <div class="lang-option-name">Français</div>
        <div class="lang-option-code">FR - France</div>
      </div>
      @if(app()->getLocale() === 'fr')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" 
             stroke="currentColor" stroke-width="2.5" class="lang-check">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
      @endif
    </a>

    {{-- English --}}
    <a class="lang-option {{ app()->getLocale() === 'en' ? 'active' : '' }}" 
       href="{{ route('lang.switch', 'en') }}" data-lang="en">
      <div class="lang-option-flag">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 7410 3900">
          <rect width="7410" height="3900" fill="#3c3b6b"/>
          <path d="M0,0 L7410,3900 M7410,0 L0,3900" stroke="#fff" stroke-width="300"/>
          <path d="M0,0 L7410,3900 M7410,0 L0,3900" stroke="#c01c28" stroke-width="200" stroke-dasharray="400,250"/>
          <rect width="7410" height="3900" fill="none" stroke="#fff" stroke-width="600"/>
        </svg>
      </div>
      <div class="lang-option-text">
        <div class="lang-option-name">English</div>
        <div class="lang-option-code">EN - United States</div>
      </div>
      @if(app()->getLocale() === 'en')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" 
             stroke="currentColor" stroke-width="2.5" class="lang-check">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
      @endif
    </a>
  </div>
</div>

<style>
  /* ═══════════════════════════════════════════════════════════════════ */
  /* Premium Language Switcher Styles */
  /* ═══════════════════════════════════════════════════════════════════ */

  .lang-switcher-premium {
    position: relative;
    display: inline-block;
  }

  /* ─── Trigger Button ─────────────────────────────────────────────── */
  .lang-switcher-premium .lang-trigger {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    background: var(--surface);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    font-family: 'Raleway', sans-serif;
    position: relative;
    overflow: hidden;
  }

  .lang-trigger::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    transition: left 0.6s ease;
  }

  .lang-trigger:hover::before {
    left: 100%;
  }

  .lang-trigger:hover {
    border-color: var(--primary);
    background: var(--primary-bg);
    box-shadow: 0 4px 12px rgba(13, 148, 136, 0.15);
    color: var(--primary);
  }

  .lang-trigger:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
  }

  /* ─── Flag Icon ──────────────────────────────────────────────────── */
  .lang-flag {
    width: 20px;
    height: 14px;
    border-radius: 2px;
    overflow: hidden;
    flex-shrink: 0;
    border: 0.5px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .flag-icon {
    width: 100%;
    height: 100%;
  }

  /* ─── Language Code ──────────────────────────────────────────────── */
  .lang-code {
    font-weight: 700;
    letter-spacing: 0.5px;
  }

  /* ─── Chevron Icon ───────────────────────────────────────────────── */
  .lang-chevron {
    width: 16px;
    height: 16px;
    transition: transform 0.3s ease;
    margin-left: 2px;
  }

  .lang-trigger[aria-expanded="true"] .lang-chevron {
    transform: rotate(180deg);
  }

  /* ─── Dropdown Menu ──────────────────────────────────────────────── */
  .lang-dropdown {
    min-width: 220px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    padding: 8px !important;
    margin-top: 4px !important;
    overflow: hidden;
    backdrop-filter: blur(10px);
    animation: slideFadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  @keyframes slideFadeIn {
    from {
      opacity: 0;
      transform: translateY(-8px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* ─── Language Option ────────────────────────────────────────────── */
  .lang-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: var(--text-primary);
    text-decoration: none;
    position: relative;
    overflow: hidden;
  }

  .lang-option::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(13, 148, 136, 0.1), transparent);
    transition: left 0.5s ease;
  }

  .lang-option:hover {
    background: var(--surface-2);
    padding-left: 16px;
  }

  .lang-option:hover::before {
    left: 100%;
  }

  .lang-option.active {
    background: linear-gradient(135deg, rgba(13, 148, 136, 0.1), rgba(13, 148, 136, 0.05));
    border-left: 3px solid var(--primary);
    padding-left: 9px;
  }

  /* ─── Option Flag ────────────────────────────────────────────────── */
  .lang-option-flag {
    width: 28px;
    height: 20px;
    border-radius: 4px;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .lang-option-flag svg {
    width: 100%;
    height: 100%;
  }

  /* ─── Option Text ────────────────────────────────────────────────── */
  .lang-option-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
  }

  .lang-option-name {
    font-weight: 600;
    font-size: 13px;
    color: var(--text-primary);
  }

  .lang-option-code {
    font-size: 11px;
    color: var(--text-secondary);
    font-weight: 500;
    letter-spacing: 0.3px;
  }

  /* ─── Check Mark ─────────────────────────────────────────────────── */
  .lang-check {
    width: 18px;
    height: 18px;
    color: var(--primary);
    flex-shrink: 0;
    animation: checkPop 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  @keyframes checkPop {
    0% {
      transform: scale(0.5);
      opacity: 0;
    }
    100% {
      transform: scale(1);
      opacity: 1;
    }
  }

  /* ─── Dark Mode Support ──────────────────────────────────────────── */
  @media (prefers-color-scheme: dark) {
    .lang-switcher-premium .lang-trigger {
      border-color: rgba(255, 255, 255, 0.1);
    }

    .lang-flag,
    .lang-option-flag {
      border-color: rgba(255, 255, 255, 0.15);
    }
  }

  /* ─── Mobile Responsive ──────────────────────────────────────────── */
  @media (max-width: 768px) {
    .lang-switcher-premium .lang-trigger {
      padding: 5px 8px;
      gap: 6px;
    }

    .lang-flag {
      width: 18px;
      height: 12px;
    }

    .lang-code {
      font-size: 12px;
    }

    .lang-chevron {
      width: 14px;
      height: 14px;
    }

    .lang-dropdown {
      min-width: 200px;
    }

    .lang-option {
      padding: 10px 10px;
    }

    .lang-option-flag {
      width: 24px;
      height: 18px;
    }

    .lang-option-name {
      font-size: 12px;
    }

    .lang-option-code {
      font-size: 10px;
    }
  }
</style>
