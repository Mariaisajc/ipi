/**
 * Logout Modal
 * Muestra modal de confirmación antes de cerrar sesión
 */

document.addEventListener('DOMContentLoaded', function() {
    // Buscar el botón de logout
    const logoutButton = document.querySelector('a[href*="auth/logout"]');
    
    if (logoutButton) {
        logoutButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const logoutUrl = this.href;
            
            // Mostrar modal de confirmación
            showLogoutModal(logoutUrl);
        });
    }
});

function showLogoutModal(logoutUrl) {
    // Crear modal si no existe
    let modal = document.getElementById('logoutModal');
    
    if (!modal) {
        modal = createLogoutModal();
        document.body.appendChild(modal);
    }
    
    // Configurar botones
    const confirmBtn = modal.querySelector('#confirmLogout');
    const cancelBtn = modal.querySelector('.btn-cancel');
    const closeBtn = modal.querySelector('.btn-close');
    
    // Botón confirmar
    confirmBtn.onclick = function() {
        window.location.href = logoutUrl;
    };
    
    // Botón cancelar
    cancelBtn.onclick = function() {
        bsModal.hide();
    };
    
    // Botón X
    closeBtn.onclick = function() {
        bsModal.hide();
    };
    
    // Mostrar el modal con Bootstrap
    // backdrop: 'static' = No se cierra al hacer click fuera
    // keyboard: false = No se cierra con ESC
    const bsModal = new bootstrap.Modal(modal, {
        backdrop: 'static',
        keyboard: false
    });
    
    bsModal.show();
}

function createLogoutModal() {
    const modalHTML = `
        <div class="modal fade" id="logoutModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">
                            <i class="bi bi-box-arrow-right text-danger me-2"></i>
                            Cerrar Sesión
                        </h5>
                        <button type="button" class="btn-close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <div class="mb-3">
                            <i class="bi bi-box-arrow-right text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <p class="mb-0 fs-5">¿Estás seguro de cerrar sesión?</p>
                        <p class="text-muted small mt-2">Tendrás que volver a iniciar sesión para acceder al sistema.</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center">
                        <button type="button" class="btn btn-secondary px-4 btn-cancel">
                            <i class="bi bi-x-circle me-1"></i>
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-danger px-4" id="confirmLogout">
                            <i class="bi bi-box-arrow-right me-1"></i>
                            Cerrar Sesión
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const div = document.createElement('div');
    div.innerHTML = modalHTML;
    return div.firstElementChild;
}