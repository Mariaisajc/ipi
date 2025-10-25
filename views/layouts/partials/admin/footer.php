<?php
/**
 * Footer del Panel de Administración
 */
?>
<footer class="admin-footer">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0 text-muted small">
                    &copy; <?= date('Y') ?> <strong>IPI - Innovation Performance Inndex</strong>. 
                    Todos los derechos reservados.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <ul class="footer-links list-inline mb-0">
                    <li class="list-inline-item">
                        <a href="<?= url('admin/help') ?>" class="text-muted small">
                            <i class="bi bi-question-circle me-1"></i>Ayuda
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="<?= url('admin/privacy') ?>" class="text-muted small">
                            <i class="bi bi-shield-check me-1"></i>Privacidad
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="<?= url('admin/terms') ?>" class="text-muted small">
                            <i class="bi bi-file-text me-1"></i>Términos
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <span class="text-muted small">v1.0.0</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>