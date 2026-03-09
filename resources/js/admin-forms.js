/**
 * Millénaire Connect - Admin Form Handler
 * Gère les validations, soumissions et interactions des formulaires admin
 */

(function() {
    'use strict';

    // Configuration
    const config = {
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
        apiBaseUrl: '/api/v1',
        animationDuration: 300,
    };

    /**
     * Validation de formulaire côté client
     */
    const FormValidator = {
        rules: {
            email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
            phoneNumber: (value) => /^[0-9+\-\s().]*$/.test(value) && value.length >= 8,
            matricule: (value) => value.length >= 3,
            name: (value) => value.trim().length >= 2,
            amount: (value) => !isNaN(parseFloat(value)) && parseFloat(value) > 0,
        },

        validate(form) {
            const fields = form.querySelectorAll('[data-validate]');
            let isValid = true;

            fields.forEach(field => {
                const rule = field.dataset.validate;
                const value = field.value.trim();

                if (this.rules[rule]) {
                    const valid = this.rules[rule](value);
                    this.setFieldStatus(field, valid);
                    if (!valid) isValid = false;
                } else if (field.hasAttribute('required') && !value) {
                    this.setFieldStatus(field, false);
                    isValid = false;
                } else {
                    this.setFieldStatus(field, true);
                }
            });

            return isValid;
        },
    }
})
