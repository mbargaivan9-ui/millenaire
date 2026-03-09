// ─── Lucide Icons Manager ───────────────────────────────────
const IconManager = {
  init() {
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  },

  refresh() {
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  }
};

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

    // Submenu toggles - attach to both sidebar-item divs with data-toggle
    document.querySelectorAll('.sidebar-item[data-toggle]').forEach(item => {
      item.style.cursor = 'pointer'; // Ensure cursor is pointer
      item.addEventListener('click', (e) => {
        // Prevent event bubbling for nested links
        if (e.target.closest('a')) return;
        this.toggleSubmenu(item);
      });
    });

    // Mobile overlay
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 768 &&
          !this.sidebar.contains(e.target) &&
          !e.target.closest('.topbar-toggle')) {
        this.sidebar.classList.remove('mobile-open');
      }
    });

    console.log('SidebarManager initialized, found', document.querySelectorAll('.sidebar-item[data-toggle]').length, 'submenu toggles');
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
    const form = document.getElementById('lang-form');
    if (form) {
      form.querySelector('[name="lang"]').value = lang;
      form.submit();
    } else {
      const url = new URL(window.location);
      url.searchParams.set('lang', lang);
      window.location = url;
    }
  }
};

// ─── Password Visibility Toggle ────────────────────────────
const PasswordToggle = {
  init() {
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const inputId = btn.dataset.togglePassword;
        const input = document.getElementById(inputId);
        if (!input) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        
        const eyeIcon = btn.querySelector('.icon-eye');
        const eyeOffIcon = btn.querySelector('.icon-eye-off');
        if (eyeIcon) eyeIcon.classList.toggle('hidden');
        if (eyeOffIcon) eyeOffIcon.classList.toggle('hidden');
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

// ─── Active nav item highlight ───────────────────────────────
function initActiveNav() {
  const current = window.location.pathname;
  document.querySelectorAll('.sidebar-item[href], .sidebar-subitem[href]').forEach(el => {
    if (el.getAttribute('href') === current) {
      el.classList.add('active');
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
  Toast.init();
  PasswordToggle.init();
  initActiveNav();
  IconManager.init(); // Initialize Lucide icons

  // Theme toggle btn
  document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
    btn.addEventListener('click', () => ThemeManager.toggle());
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

  // Auto-flash messages from Laravel session
  const flashEl = document.getElementById('flash-data');
  if (flashEl) {
    const { type, message } = flashEl.dataset;
    if (message) Toast.show(message, type || 'info');
  }
});

// Re-initialize icons when content is dynamically loaded (AJAX)
const originalAjax = window.XMLHttpRequest.prototype.open;
window.XMLHttpRequest.prototype.open = function(method, url, ...args) {
  this.addEventListener('loadend', () => {
    IconManager.refresh();
  });
  return originalAjax.call(this, method, url, ...args);
};

// ─── Export for global use ───────────────────────────────────
window.Millenaire = { ThemeManager, SidebarManager, Toast, IconManager };
