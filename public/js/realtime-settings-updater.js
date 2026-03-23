/**
 * Real-time Settings Updater
 * Écoute les changements de paramètres et met à jour le DOM en temps réel
 * 
 * Phase 10 — Real-time Settings Updates
 */

(function() {
    'use strict';

    // Initialiser l'écouteur de broadcast
    if (typeof window.Echo !== 'undefined') {
        window.Echo.channel('settings')
            .listen('SettingsUpdated', (event) => {
                console.log('Settings updated in real-time:', event);
                updatePageWithNewSettings(event.settings || event);
            });
    } else {
        console.warn('Laravel Echo not initialized. Real-time updates disabled.');
    }

    /**
     * Met à jour la page avec les nouveaux paramètres
     */
    window.updatePageWithNewSettings = function(settings) {
        // Mettre à jour les éléments du DOM avec data attributes
        updateTextContent(settings);
        updateColors(settings);
        updateLogos(settings);
    };

    /**
     * Met à jour le contenu textuel basé sur data attributes
     */
    function updateTextContent(settings) {
        // Platform name
        if (settings.platform_name) {
            document.querySelectorAll('[data-content="platform_name"]').forEach(el => {
                el.textContent = settings.platform_name;
            });
            document.title = settings.platform_name + (document.title.includes('—') 
                ? ' — ' + document.title.split('—')[1] 
                : '');
        }

        // Hero section
        if (settings.hero_title) {
            const heroTitle = document.querySelector('.hero-content h1');
            if (heroTitle) {
                heroTitle.textContent = settings.hero_title;
                heroTitle.style.animation = 'fadeIn 0.3s ease-in-out';
            }
        }

        if (settings.hero_subtitle) {
            const heroSubtitle = document.querySelector('.hero-content > .container p');
            if (heroSubtitle) {
                heroSubtitle.textContent = settings.hero_subtitle;
                heroSubtitle.style.animation = 'fadeIn 0.3s ease-in-out';
            }
        }

        // CTA Button text
        if (settings.hero_cta_text) {
            document.querySelectorAll('[data-content="hero_cta_text"]').forEach(el => {
                el.textContent = settings.hero_cta_text;
            });
        }

        // About section
        if (settings.about_title) {
            document.querySelectorAll('[data-content="about_title"]').forEach(el => {
                el.textContent = settings.about_title;
            });
        }

        if (settings.about_description) {
            document.querySelectorAll('[data-content="about_description"]').forEach(el => {
                el.textContent = settings.about_description;
            });
        }

        // Contact info
        if (settings.phone) {
            document.querySelectorAll('[data-content="phone"]').forEach(el => {
                el.textContent = settings.phone;
                el.href = 'tel:' + settings.phone.replace(/\s/g, '');
            });
        }

        if (settings.email) {
            document.querySelectorAll('[data-content="email"]').forEach(el => {
                el.textContent = settings.email;
                el.href = 'mailto:' + settings.email;
            });
        }

        if (settings.address) {
            document.querySelectorAll('[data-content="address"]').forEach(el => {
                el.textContent = settings.address;
            });
        }

        // Social links
        if (settings.social_facebook) {
            document.querySelectorAll('[data-content="social_facebook"]').forEach(el => {
                el.href = settings.social_facebook;
            });
        }

        if (settings.social_twitter) {
            document.querySelectorAll('[data-content="social_twitter"]').forEach(el => {
                el.href = settings.social_twitter;
            });
        }

        // Proviseur information
        if (settings.proviseur_name) {
            document.querySelectorAll('[data-content="proviseur_name"]').forEach(el => {
                el.textContent = settings.proviseur_name;
            });
        }

        if (settings.proviseur_title) {
            document.querySelectorAll('[data-content="proviseur_title"]').forEach(el => {
                el.textContent = settings.proviseur_title;
            });
        }

        if (settings.proviseur_bio) {
            document.querySelectorAll('[data-content="proviseur_bio"]').forEach(el => {
                el.textContent = settings.proviseur_bio;
            });
        }
    }

    /**
     * Met à jour les couleurs de la plateforme
     */
    function updateColors(settings) {
        if (settings.primary_color) {
            document.documentElement.style.setProperty('--primary', settings.primary_color);
            
            // Mettre à jour les éléments avec classes Bootstrap
            document.querySelectorAll('.btn-primary, .btn-primary:hover').forEach(el => {
                el.style.backgroundColor = settings.primary_color;
                el.style.borderColor = settings.primary_color;
            });
        }

        if (settings.secondary_color) {
            document.documentElement.style.setProperty('--secondary', settings.secondary_color);
        }
    }

    /**
     * Met à jour les logos et images
     */
    function updateLogos(settings) {
        if (settings.logo_path) {
            document.querySelectorAll('[data-content="logo"]').forEach(el => {
                el.src = '/' + settings.logo_path;
                el.style.animation = 'fadeIn 0.3s ease-in-out';
            });
        }

        if (settings.favicon_path) {
            const favicon = document.querySelector("link[rel='icon']");
            if (favicon) {
                favicon.href = '/' + settings.favicon_path;
            }
        }

        if (settings.hero_image) {
            document.querySelectorAll('[data-content="hero_image"]').forEach(el => {
                el.style.backgroundImage = 'url(/' + settings.hero_image + ')';
                el.style.animation = 'fadeIn 0.3s ease-in-out';
            });
        }

        if (settings.proviseur_photo) {
            document.querySelectorAll('[data-content="proviseur_photo"]').forEach(el => {
                el.src = '/' + settings.proviseur_photo;
                el.style.animation = 'fadeIn 0.3s ease-in-out';
            });
        }

        if (settings.about_image) {
            document.querySelectorAll('[data-content="about_image"]').forEach(el => {
                el.src = '/' + settings.about_image;
                el.style.animation = 'fadeIn 0.3s ease-in-out';
            });
        }
    }

    // Animation fadeIn
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    `;
    document.head.appendChild(style);

})();
