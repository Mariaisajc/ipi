<?php
/**
 * Vista: Crear Formulario
 */
$pageTitle = $title ?? 'Crear Formulario';
?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= $pageTitle ?></h1>
        <p class="text-muted mb-0">Completa la información básica del formulario</p>
    </div>
    <a href="<?= url('admin/forms') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>
        Volver
    </a>
</div>

<!-- Flash Message -->
<?php 
$flashData = isset($flash) ? $flash : (isset($_SESSION['flash']) ? $_SESSION['flash'] : null);
if ($flashData): 
    unset($_SESSION['flash']);
?>
    <div class="alert alert-<?= $flashData['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show">
        <?= $flashData['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" action="<?= url('admin/forms/store') ?>" id="formCreate">
    <?= (new CSRF())->field() ?>
    
    <div class="row">
        <!-- Columna Principal -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Información del Formulario
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Título -->
                    <div class="mb-3">
                        <label for="title" class="form-label">
                            Título del Formulario <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="title" 
                               name="title" 
                               maxlength="100"
                               required
                               placeholder="Ej: Evaluación de Capacidades de Innovación">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Máximo 100 caracteres
                        </small>
                    </div>
                    
                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            Descripción
                        </label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="4"
                                  placeholder="Describe el propósito de este formulario..."></textarea>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Opcional. Ayuda a los usuarios a entender el formulario
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Información -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        Información
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Paso 1:</strong> Completa la información básica del formulario.
                    </p>
                    <p class="small mb-2">
                        <strong>Paso 2:</strong> Después podrás agregar preguntas usando el constructor visual.
                    </p>
                    <p class="small mb-0">
                        <strong>Paso 3:</strong> Publica el formulario para asignarlo a usuarios.
                    </p>
                </div>
            </div>
            
            <!-- Estado Inicial -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="bi bi-tag me-2"></i>
                        Estado Inicial
                    </h6>
                    <span class="badge bg-secondary fs-6">
                        <i class="bi bi-file-earmark-text me-1"></i>
                        Borrador
                    </span>
                    <p class="small text-muted mt-2 mb-0">
                        El formulario se creará como borrador. Podrás publicarlo después de agregar preguntas.
                    </p>
                </div>
            </div>
            
            <!-- Botones de Acción -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>
                            Crear y Continuar
                        </button>
                        <a href="<?= url('admin/forms') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>
                            Cancelar
                        </a>
                    </div>
                    <p class="small text-muted mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Al crear, serás redirigido al constructor para agregar preguntas.
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Contador de caracteres para el título
const titleInput = document.getElementById('title');
const maxLength = 100;

titleInput.addEventListener('input', function() {
    const remaining = maxLength - this.value.length;
    const smallText = this.nextElementSibling;
    
    if (remaining < 20) {
        smallText.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i>${remaining} caracteres restantes`;
        smallText.classList.add('text-warning');
    } else {
        smallText.innerHTML = '<i class="bi bi-info-circle me-1"></i>Máximo 100 caracteres';
        smallText.classList.remove('text-warning');
    }
});

// Validación del formulario
document.getElementById('formCreate').addEventListener('submit', function(e) {
    const title = titleInput.value.trim();
    
    if (title.length === 0) {
        e.preventDefault();
        alert('El título es obligatorio');
        titleInput.focus();
        return false;
    }
    
    if (title.length > 100) {
        e.preventDefault();
        alert('El título no puede exceder 100 caracteres');
        titleInput.focus();
        return false;
    }
});
</script>