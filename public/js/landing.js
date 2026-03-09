/**
 * landing.js — Scripts Page Publique Millénaire Connect
 * Phase 2 — Interface Publique
 * Adapté du template Learner avec Alpine.js et Laravel Echo
 */

(function () {
  'use strict';

  /* ─── Preloader ──────────────────────────────────────────── */
  const preloader = document.getElementById('preloader');
  if (preloader) {
    window.addEventListener('load', () => {
      preloader.style.opacity = '0';
      preloader.style.visibility = 'hidden';
      setTimeout(() => preloader.remove(), 400);
    });
  }

  /* ─── Scroll-Top Button ───────────────────────────────────── */
  const scrollTop = document.getElementById('scroll-top');
  if (scrollTop) {
    const toggleScrollTop = () => {
      scrollTop.classList.toggle('active', window.scrollY > 200);
    };
    window.addEventListener('scroll', toggleScrollTop, { passive: true });
    scrollTop.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    toggleScrollTop();
  }

  /* ─── Header Shadow on Scroll ─────────────────────────────── */
  const header = document.getElementById('header');
  if (header) {
    window.addEventListener('scroll', () => {
      header.classList.toggle('scrolled', window.scrollY > 50);
    }, { passive: true });
  }

  /* ─── Mobile Nav Toggle ───────────────────────────────────── */
  const navToggle = document.querySelector('.mobile-nav-toggle');
  const navMenu   = document.querySelector('.navmenu');
  if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
      navMenu.classList.toggle('active');
      navToggle.classList.toggle('bi-list');
      navToggle.classList.toggle('bi-x');
    });
    // Close on outside click
    document.addEventListener('click', (e) => {
      if (!navMenu.contains(e.target) && !navToggle.contains(e.target)) {
        navMenu.classList.remove('active');
        navToggle.classList.add('bi-list');
        navToggle.classList.remove('bi-x');
      }
    });
  }

  /* ─── AOS Init ────────────────────────────────────────────── */
  if (typeof AOS !== 'undefined') {
    AOS.init({
      duration: 600,
      easing: 'ease-in-out',
      once: true,
      offset: 50,
    });
  }

  /* ─── PureCounter Init ────────────────────────────────────── */
  if (typeof PureCounter !== 'undefined') {
    new PureCounter();
  }

  /* ─── Swiper Init ─────────────────────────────────────────── */
  document.querySelectorAll('.init-swiper').forEach((el) => {
    let config = {};
    const configEl = el.querySelector('.swiper-config');
    if (configEl) {
      try { config = JSON.parse(configEl.textContent); } catch (err) { /* noop */ }
    }
    if (typeof Swiper !== 'undefined') {
      new Swiper(el, config);
    }
  });

  /* ─── Smooth Anchor Scroll ────────────────────────────────── */
  document.querySelectorAll('a[href^="#"]').forEach((a) => {
    a.addEventListener('click', (e) => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) {
        e.preventDefault();
        const headerH = header ? header.offsetHeight : 70;
        const top = target.getBoundingClientRect().top + window.scrollY - headerH - 10;
        window.scrollTo({ top, behavior: 'smooth' });
      }
    });
  });

  /* ─── Active Nav Link on Scroll ──────────────────────────── */
  const navLinks = document.querySelectorAll('.navmenu a');
  const sections = document.querySelectorAll('section[id]');

  const onScroll = () => {
    const scrollPos = window.scrollY + (header ? header.offsetHeight : 70) + 10;
    sections.forEach((section) => {
      if (
        section.offsetTop <= scrollPos &&
        section.offsetTop + section.offsetHeight > scrollPos
      ) {
        navLinks.forEach((link) => {
          link.classList.toggle('active', link.getAttribute('href') === `#${section.id}`);
        });
      }
    });
  };
  window.addEventListener('scroll', onScroll, { passive: true });

  /* ─── Flash / Toast messages ──────────────────────────────── */
  const flashData = document.getElementById('flash-data');
  if (flashData) {
    const type = flashData.dataset.type;
    const message = flashData.dataset.message;
    if (message) showToast(type, message);
  }

  function showToast(type, message) {
    const colors = {
      success: '#10b981',
      error:   '#ef4444',
      warning: '#f59e0b',
      info:    '#3b82f6',
    };
    const toast = document.createElement('div');
    toast.style.cssText = `
      position:fixed; top:20px; right:20px; z-index:9999;
      background:${colors[type] || colors.info};
      color:#fff; padding:1rem 1.5rem;
      border-radius:10px; box-shadow:0 8px 30px rgba(0,0,0,.15);
      font-weight:600; font-size:.9rem; max-width:320px;
      animation: slideInRight .3s ease;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
      toast.style.animation = 'slideOutRight .3s ease forwards';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }

  /* ─── Announcement Real-Time (via window.Echo) ───────────── */
  if (typeof window.Echo !== 'undefined') {
    window.Echo.channel('announcements')
      .listen('AnnouncementPublished', (data) => {
        const container = document.querySelector('#announcements-container .row');
        if (!container || !data.announcement) return;
        const a = data.announcement;
        const card = document.createElement('div');
        card.className = 'col-lg-4 col-md-6';
        card.style.animation = 'fadeIn .4s ease';
        card.innerHTML = `
          <div class="course-card announcement-card">
            <div class="course-image">
              <div class="d-flex align-items-center justify-content-center"
                   style="height:200px;background:linear-gradient(135deg,#f0fdfa,#ccfbf1)">
                <i class="bi bi-megaphone" style="font-size:3rem;color:#0d9488"></i>
              </div>
              <div class="badge new">Nouveau</div>
            </div>
            <div class="course-content">
              <div class="course-meta">
                <span class="level"><i class="bi bi-calendar3 me-1"></i>${a.date ?? ''}</span>
                <span class="duration">Annonce</span>
              </div>
              <h3><a href="/announcements/${a.slug || a.id}">${a.title}</a></h3>
              <p>${a.excerpt ?? ''}</p>
              <a href="/announcements/${a.slug || a.id}" class="btn-course">Lire la suite</a>
            </div>
          </div>`;
        container.prepend(card);
      });
  }

  /* ─── CSS keyframes injection ─────────────────────────────── */
  const style = document.createElement('style');
  style.textContent = `
    @keyframes fadeIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    @keyframes slideInRight { from { opacity:0; transform:translateX(100%); } to { opacity:1; transform:translateX(0); } }
    @keyframes slideOutRight { from { opacity:1; transform:translateX(0); } to { opacity:0; transform:translateX(100%); } }
  `;
  document.head.appendChild(style);

})();
