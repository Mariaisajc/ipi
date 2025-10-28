<?php
/**
 * Vista: Listado de Formularios
 */
$pageTitle = $title ?? 'Formularios';
?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="bi bi-clipboard-check text-primary me-2"></i>
            <?= $pageTitle ?>
        </h1>
        <p class="text-muted mb-0">Gestiona los formularios de evaluación</p>
    </div>
    <a href="<?= url('admin/forms/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>
        Crear Formulario
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

<!-- Filtros y Búsqueda -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('admin/forms') ?>" class="row g-3">
            <!-- Búsqueda -->
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           placeholder="Buscar por título..." 
                           value="<?= e($searchTerm ?? '') ?>">
                </div>
            </div>
            
            <!-- Filtro por estado -->
            <div class="col-md-4">
                <select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="draft" <?= ($currentStatus ?? '') === 'draft' ? 'selected' : '' ?>>
                        Borradores
                    </option>
                    <option value="active" <?= ($currentStatus ?? '') === 'active' ? 'selected' : '' ?>>
                        Activos
                    </option>
                    <option value="closed" <?= ($currentStatus ?? '') === 'closed' ? 'selected' : '' ?>>
                        Cerrados
                    </option>
                </select>
            </div>
            
            <!-- Botones -->
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-funnel me-1"></i>
                    Filtrar
                </button>
                <?php if ($searchTerm || $currentStatus): ?>
                    <a href="<?= url('admin/forms') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>
                        Limpiar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Formularios -->
<div class="card">
    <div class="card-body">
        <?php if (empty($forms)): ?>
            <!-- Estado vacío -->
            <div class="text-center py-5">
                <i class="bi bi-clipboard-x" style="font-size: 4rem; color: #dee2e6;"></i>
                <h5 class="mt-3 text-muted">No hay formularios</h5>
                <p class="text-muted">
                    <?php if ($searchTerm || $currentStatus): ?>
                        No se encontraron formularios con los filtros aplicados.
                    <?php else: ?>
                        Comienza creando tu primer formulario de evaluación.
                    <?php endif; ?>
                </p>
                <?php if (!$searchTerm && !$currentStatus): ?>
                    <a href="<?= url('admin/forms/create') ?>" class="btn btn-primary mt-2">
                        <i class="bi bi-plus-circle me-1"></i>
                        Crear Primer Formulario
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">ID</th>
                            <th style="width: 30%;">Título</th>
                            <th style="width: 10%;" class="text-center">Estado</th>
                            <th style="width: 10%;" class="text-center">Preguntas</th>
                            <th style="width: 10%;" class="text-center">Asignaciones</th>
                            <th style="width: 10%;" class="text-center">Respuestas</th>
                            <th style="width: 15%;">Creado por</th>
                            <th style="width: 10%;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forms as $form): ?>
                            <tr>
                                <!-- ID -->
                                <td class="fw-bold text-muted">#<?= $form['id'] ?></td>
                                
                                <!-- Título -->
                                <td>
                                    <a href="<?= url('admin/forms/view?id=' . $form['id']) ?>" 
                                       class="text-decoration-none fw-semibold">
                                        <?= e($form['title']) ?>
                                    </a>
                                    <?php if ($form['description']): ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= e(substr($form['description'], 0, 60)) ?>
                                            <?= strlen($form['description']) > 60 ? '...' : '' ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Estado -->
                                <td class="text-center">
                                    <?php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'active' => 'success',
                                        'closed' => 'danger'
                                    ];
                                    $statusNames = [
                                        'draft' => 'Borrador',
                                        'active' => 'Activo',
                                        'closed' => 'Cerrado'
                                    ];
                                    $color = $statusColors[$form['status']] ?? 'secondary';
                                    $name = $statusNames[$form['status']] ?? $form['status'];
                                    ?>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= $name ?>
                                    </span>
                                </td>
                                
                                <!-- Preguntas -->
                                <td class="text-center">
                                    <?php if ($form['question_count'] > 0): ?>
                                        <span class="badge bg-primary">
                                            <?= $form['question_count'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">0</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Asignaciones -->
                                <td class="text-center">
                                    <?php if ($form['assignment_count'] > 0): ?>
                                        <span class="badge bg-info">
                                            <?= $form['assignment_count'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Respuestas -->
                                <td class="text-center">
                                    <?php if ($form['response_count'] > 0): ?>
                                        <span class="badge bg-success">
                                            <?= $form['response_count'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Creado por -->
                                <td>
                                    <small class="text-muted">
                                        <?= e($form['creator_login'] ?? 'N/A') ?>
                                    </small>
                                </td>
                                
                                <!-- Acciones -->
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Ver -->
                                        <a href="<?= url('admin/forms/view?id=' . $form['id']) ?>" 
                                           class="btn btn-outline-primary"
                                           title="Ver detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        
                                        <!-- Constructor -->
                                        <a href="<?= url('admin/forms/builder?id=' . $form['id']) ?>" 
                                           class="btn btn-outline-secondary"
                                           title="Constructor">
                                            <i class="bi bi-tools"></i>
                                        </a>
                                        
                                        <!-- Editar -->
                                        <a href="<?= url('admin/forms/edit?id=' . $form['id']) ?>" 
                                           class="btn btn-outline-warning"
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <!-- Duplicar -->
                                        <button type="button" 
                                                class="btn btn-outline-info"
                                                onclick="duplicateForm(<?= $form['id'] ?>)"
                                                title="Duplicar">
                                            <i class="bi bi-files"></i>
                                        </button>
                                        
                                        <!-- Eliminar -->
                                        <?php if ($form['response_count'] == 0): ?>
                                            <button type="button" 
                                                    class="btn btn-outline-danger"
                                                    onclick="deleteForm(<?= $form['id'] ?>, '<?= e($form['title']) ?>')"
                                                    title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">¿Está seguro que desea eliminar el formulario?</p>
                <p class="fw-bold text-danger mb-2" id="formToDelete"></p>
                <p class="text-muted small mb-0">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="bi bi-trash me-1"></i>
                    Sí, Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

// Variable para almacenar el ID a eliminar
let formIdToDelete = null;

/**
 * Duplicar formulario
 */
function duplicateForm(formId) {
    if (!confirm('¿Desea duplicar este formulario?')) {
        return;
    }
    
    fetch('<?= url('admin/forms/duplicate') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: formId,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Redirigir al constructor del nuevo formulario
            window.location.href = '<?= url('admin/forms/builder?id=') ?>' + data.form_id;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al duplicar el formulario');
    });
}

/**
 * Eliminar formulario
 */
function deleteForm(formId, formTitle) {
    formIdToDelete = formId;
    document.getElementById('formToDelete').textContent = formTitle;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Confirmar eliminación
document.getElementById('confirmDelete').addEventListener('click', function() {
    if (!formIdToDelete) return;
    
    fetch('<?= url('admin/forms/destroy') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: formIdToDelete,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el formulario');
    })
    .finally(() => {
        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
    });
});
</script>