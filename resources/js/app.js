/**
 * MILLENAIRE PLATFORM — Main JavaScript
 * Dashboard Admin + Authentication System
 */

import { createApp } from 'vue';
import OCRWizard from './components/OCRWizard.vue';

// ─── Theme Manager ──────────────────────────────────────────
const ThemeManager = {
  STORAGE_KEY: 'millenaire_theme',

  init() {
    const saved = localStorage.getItem(this.STORAGE_KEY) || 'light';
    this.apply(saved);
  },

  apply(theme) {
    const root = document.documentElement;
    if (theme === 'dark') {
      root.setAttribute('data-theme', 'dark');
    } else if (theme === 'system') {
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      root.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
    } else {
      root.removeAttribute('data-theme');
    }
    localStorage.setItem(this.STORAGE_KEY, theme);
    document.querySelectorAll('.theme-option').forEach(el => {
      el.classList.toggle('active', el.dataset.theme === theme);
    });
  },

  toggle() {
    const current = localStorage.getItem(this.STORAGE_KEY) || 'light';
    this.apply(current === 'dark' ? 'light' : 'dark');
  }
};

// ─── Sidebar Manager ────────────────────────────────────────
const SidebarManager = {
  STORAGE_KEY: 'millenaire_sidebar',
  sidebar: null,
  topbar: null,
  mainContent: null,

  init() {
    this.sidebar = document.querySelector('.sidebar');
    this.topbar = document.querySelector('.topbar');
    this.mainContent = document.querySelector('.main-content');
    if (!this.sidebar) return;

    const collapsed = localStorage.getItem(this.STORAGE_KEY) === 'collapsed';
    if (collapsed) this.collapse(false);

    // Submenu toggles
    document.querySelectorAll('.sidebar-item[data-toggle]').forEach(item => {
      item.addEventListener('click', () => this.toggleSubmenu(item));
    });

    // Mobile overlay
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 768 &&
          !this.sidebar.contains(e.target) &&
          !e.target.closest('.topbar-toggle')) {
        this.sidebar.classList.remove('mobile-open');
      }
    });
  },

  toggle() {
    if (this.sidebar.classList.contains('collapsed')) {
      this.expand();
    } else {
      this.collapse();
    }
  },

  collapse(save = true) {
    this.sidebar.classList.add('collapsed');
    this.topbar?.classList.add('sidebar-collapsed');
    this.mainContent?.classList.add('sidebar-collapsed');
    if (save) localStorage.setItem(this.STORAGE_KEY, 'collapsed');
  },

  expand(save = true) {
    this.sidebar.classList.remove('collapsed');
    this.topbar?.classList.remove('sidebar-collapsed');
    this.mainContent?.classList.remove('sidebar-collapsed');
    if (save) localStorage.setItem(this.STORAGE_KEY, 'expanded');
  },

  toggleMobile() {
    this.sidebar.classList.toggle('mobile-open');
  },

  toggleSubmenu(item) {
    const targetId = item.dataset.toggle;
    const submenu = document.getElementById(targetId);
    if (!submenu) return;

    item.classList.toggle('open');
    submenu.classList.toggle('open');
  }
};

// ─── Dropdown Manager ───────────────────────────────────────
const DropdownManager = {
  init() {
    document.addEventListener('click', (e) => {
      // Close all dropdowns on outside click
      const trigger = e.target.closest('[data-dropdown]');
      if (trigger) {
        const menuId = trigger.dataset.dropdown;
        const menu = document.getElementById(menuId);
        if (menu) {
          const isOpen = menu.classList.contains('open');
          this.closeAll();
          if (!isOpen) menu.classList.add('open');
        }
      } else if (!e.target.closest('.dropdown-menu')) {
        this.closeAll();
      }
    });
  },

  closeAll() {
    document.querySelectorAll('.dropdown-menu.open').forEach(m => m.classList.remove('open'));
  }
};

// ─── Language Manager ───────────────────────────────────────
const LangManager = {
  STORAGE_KEY: 'millenaire_lang',

  init() {
    const saved = localStorage.getItem(this.STORAGE_KEY) || document.documentElement.lang || 'fr';
    this.setActive(saved);
  },

  setActive(lang) {
    localStorage.setItem(this.STORAGE_KEY, lang);
    document.querySelectorAll('[data-lang]').forEach(el => {
      el.classList.toggle('active', el.dataset.lang === lang);
    });
  },

  switch(lang) {
    // Submit to server to change locale
    const form = document.getElementById('lang-form');
    if (form) {
      form.querySelector('[name="lang"]').value = lang;
      form.submit();
    } else {
      // Fallback: redirect with lang param
      const url = new URL(window.location);
      url.searchParams.set('lang', lang);
      window.location = url;
    }
  }
};

// ─── OTP Input Handler ──────────────────────────────────────
const OTPManager = {
  init() {
    const inputs = document.querySelectorAll('.otp-input');
    if (!inputs.length) return;

    inputs.forEach((input, i) => {
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !input.value && i > 0) {
          inputs[i - 1].focus();
        }
      });

      input.addEventListener('input', (e) => {
        const val = e.target.value.replace(/\D/g, '');
        input.value = val.slice(-1);
        if (val && i < inputs.length - 1) {
          inputs[i + 1].focus();
        }
      });

      input.addEventListener('paste', (e) => {
        e.preventDefault();
        const paste = e.clipboardData.getData('text').replace(/\D/g, '');
        paste.split('').slice(0, inputs.length).forEach((char, j) => {
          if (inputs[j]) inputs[j].value = char;
        });
        const next = Math.min(paste.length, inputs.length - 1);
        inputs[next].focus();
      });
    });
  }
};

// ─── Password Toggle ─────────────────────────────────────────
const PasswordManager = {
  init() {
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
      btn.addEventListener('click', () => {
        const targetId = btn.dataset.togglePassword;
        const input = document.getElementById(targetId);
        if (!input) return;
        const isText = input.type === 'text';
        input.type = isText ? 'password' : 'text';
        btn.querySelector('.icon-eye')?.classList.toggle('hidden', !isText);
        btn.querySelector('.icon-eye-off')?.classList.toggle('hidden', isText);
      });
    });
  }
};

// ─── Toast Notifications ────────────────────────────────────
const Toast = {
  container: null,

  init() {
    this.container = document.getElementById('toast-container');
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.id = 'toast-container';
      this.container.style.cssText = `
        position: fixed; bottom: 24px; right: 24px; z-index: 9999;
        display: flex; flex-direction: column; gap: 8px; pointer-events: none;
      `;
      document.body.appendChild(this.container);
    }
  },

  show(message, type = 'info', duration = 4000) {
    const icons = {
      success: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
      error:   '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
      warning: '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
      info:    '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'
    };
    const colors = {
      success: '#10b981', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6'
    };

    const toast = document.createElement('div');
    toast.style.cssText = `
      display: flex; align-items: center; gap: 10px; padding: 12px 16px;
      background: white; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
      border-left: 3px solid ${colors[type]}; pointer-events: all;
      font-size: 13.5px; font-weight: 500; color: #0f172a; min-width: 280px;
      animation: slideInRight 0.25s ease;
    `;
    toast.innerHTML = `
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
        stroke="${colors[type]}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        ${icons[type]}
      </svg>
      <span style="flex:1">${message}</span>
      <button onclick="this.parentElement.remove()" style="color:#94a3b8;font-size:16px;line-height:1">×</button>
    `;

    this.container.appendChild(toast);
    setTimeout(() => {
      toast.style.animation = 'slideOutRight 0.25s ease forwards';
      setTimeout(() => toast.remove(), 250);
    }, duration);
  }
};

// Add toast animation CSS
const toastStyle = document.createElement('style');
toastStyle.textContent = `
  @keyframes slideInRight {
    from { opacity: 0; transform: translateX(100%); }
    to   { opacity: 1; transform: translateX(0); }
  }
  @keyframes slideOutRight {
    from { opacity: 1; transform: translateX(0); }
    to   { opacity: 0; transform: translateX(100%); }
  }
`;
document.head.appendChild(toastStyle);

// ─── Confirmation Modal ─────────────────────────────────────
const Modal = {
  show(options = {}) {
    const existing = document.getElementById('confirm-modal');
    if (existing) existing.remove();

    const {
      title = 'Confirmation',
      message = 'Êtes-vous sûr ?',
      confirmText = 'Confirmer',
      cancelText = 'Annuler',
      type = 'danger',
      onConfirm = () => {}
    } = options;

    const colors = { danger: '#ef4444', primary: '#0d9488', warning: '#f59e0b' };

    const modal = document.createElement('div');
    modal.id = 'confirm-modal';
    modal.style.cssText = `
      position: fixed; inset: 0; z-index: 9000;
      display: flex; align-items: center; justify-content: center;
      background: rgba(0,0,0,0.4); backdrop-filter: blur(4px);
      animation: fadeIn 0.2s ease;
    `;
    modal.innerHTML = `
      <div style="background:var(--surface);border-radius:16px;padding:28px;width:100%;max-width:380px;
        box-shadow:0 20px 40px rgba(0,0,0,0.2);animation:scaleIn 0.2s ease;border:1px solid var(--border)">
        <h3 style="font-size:17px;font-weight:700;color:var(--text-primary);margin-bottom:8px">${title}</h3>
        <p style="font-size:13.5px;color:var(--text-secondary);line-height:1.6;margin-bottom:24px">${message}</p>
        <div style="display:flex;gap:10px;justify-content:flex-end">
          <button onclick="document.getElementById('confirm-modal').remove()"
            style="padding:8px 18px;border:1px solid var(--border);border-radius:8px;font-size:13px;font-weight:600;
            color:var(--text-secondary);background:var(--surface);cursor:pointer">
            ${cancelText}
          </button>
          <button id="confirm-btn"
            style="padding:8px 18px;border:none;border-radius:8px;font-size:13px;font-weight:600;
            color:white;background:${colors[type] || colors.primary};cursor:pointer">
            ${confirmText}
          </button>
        </div>
      </div>
    `;

    document.body.appendChild(modal);
    document.getElementById('confirm-btn').addEventListener('click', () => {
      modal.remove();
      onConfirm();
    });
    modal.addEventListener('click', (e) => { if (e.target === modal) modal.remove(); });
  }
};

// Modal animation CSS
const modalStyle = document.createElement('style');
modalStyle.textContent = `
  @keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
  @keyframes scaleIn { from { opacity: 0; transform: scale(0.92); } to { opacity: 1; transform: scale(1); } }
`;
document.head.appendChild(modalStyle);

// ─── Active nav item highlight ───────────────────────────────
function initActiveNav() {
  const current = window.location.pathname;
  document.querySelectorAll('.sidebar-item[href], .sidebar-subitem[href]').forEach(el => {
    if (el.getAttribute('href') === current) {
      el.classList.add('active');
      // Open parent submenu
      const submenu = el.closest('.sidebar-submenu');
      if (submenu) {
        submenu.classList.add('open');
        const toggle = document.querySelector(`[data-toggle="${submenu.id}"]`);
        toggle?.classList.add('open');
      }
    }
  });
}

// ─── Init all on DOM ready ───────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  ThemeManager.init();
  SidebarManager.init();
  DropdownManager.init();
  LangManager.init();
  OTPManager.init();
  PasswordManager.init();
  Toast.init();
  initActiveNav();

  // Theme toggle btn
  document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
    btn.addEventListener('click', () => ThemeManager.toggle());
  });

  // Theme option selectors
  document.querySelectorAll('.theme-option').forEach(opt => {
    opt.addEventListener('click', () => ThemeManager.apply(opt.dataset.theme));
  });

  // Sidebar toggle
  document.querySelectorAll('.topbar-toggle, [data-sidebar-toggle]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (window.innerWidth <= 768) {
        SidebarManager.toggleMobile();
      } else {
        SidebarManager.toggle();
      }
    });
  });

  // Lang switcher
  document.querySelectorAll('[data-lang]').forEach(el => {
    el.addEventListener('click', () => LangManager.switch(el.dataset.lang));
  });

  // Auto-flash messages from Laravel session
  const flashEl = document.getElementById('flash-data');
  if (flashEl) {
    const { type, message } = flashEl.dataset;
    if (message) Toast.show(message, type || 'info');
  }
});

// ─── Export for global use ───────────────────────────────────
window.Millenaire = { ThemeManager, SidebarManager, Toast, Modal };

// ─── Vue.js App Registration ────────────────────────────────
// Initialize Vue 3 app for dynamic components
document.addEventListener('DOMContentLoaded', () => {
  const appElement = document.getElementById('app');
  if (appElement) {
    const app = createApp({});
    
    // Register OCR Components
    app.component('OCRWizard', OCRWizard);
    
    app.mount(appElement);
    console.log('[Vue] Application initialized with OCR components');
  }
});

