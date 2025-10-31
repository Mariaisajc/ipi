<?php
// filepath: c:\xampp\htdocs\ipi\views\layouts\partials\admin\info_modal.php
/**
 * Modal genérico para mostrar información (errores, advertencias, etc.)
 */
?>
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0" id="infoModalHeader">
                <h5 class="modal-title" id="infoModalLabel">
                    <i class="bi me-2" id="infoModalIcon"></i>
                    <span id="infoModalTitle"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="infoModalMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="background-color: #5a6c57; border-color: #5a6c57;">Aceptar</button>
            </div>
        </div>
    </div>
</div>