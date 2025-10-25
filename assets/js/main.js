/**
 * IPI - Innovation Performance Index
 * JavaScript Global
 */

(function() {
    'use strict';

    // Esperar a que el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        
        // Auto-cerrar alerts después de 5 segundos
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });

        // Configurar CSRF token para AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            // Configurar AJAX para incluir CSRF token
            const originalFetch = window.fetch;
            window.fetch = function() {
                let [resource, config] = arguments;
                
                if (!config) {
                    config = {};
                }
                
                if (!config.headers) {
                    config.headers = {};
                }
                
                config.headers['X-CSRF-Token'] = csrfToken.getAttribute('content');
                
                return originalFetch(resource, config);
            };
        }

        // Confirmación antes de eliminar
        const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                const message = this.getAttribute('data-confirm-delete') || '¿Está seguro que desea eliminar este elemento?';
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });

        // Tooltips de Bootstrap
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Popovers de Bootstrap
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    });

})();