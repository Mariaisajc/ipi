<?php
/**
 * Vista: Asignar Usuarios a Formulario
 */
$pageTitle = $title ?? 'Asignar Usuarios';
?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><?= $pageTitle ?></h1>
        <p class="text-muted mb-0">Asigna "<strong><?= e($form['title']) ?></strong>" a los usuarios.</p>
    </div>
    <a href="<?= url('admin/forms/show?id=' . $form['id']) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>
        Volver al Detalle
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

<form method="POST" action="<?= url('admin/forms/update') ?>">
    <?= (new CSRF())->field() ?>
    <input type="hidden" name="form_id" value="<?= $form['id'] ?>">

    <!-- NUEVO: Card para editar información básica -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información Básica</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="title" class="form-label">Título del Formulario <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control" 
                       id="title" 
                       name="title" 
                       value="<?= e($form['title']) ?>" 
                       required>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-people me-2"></i>Lista de Usuarios (Encuestados)</h5>
        </div>
        <div class="card-body">
            <?php if (empty($availableUsers)): ?>
                <p class="text-muted text-center">No hay usuarios con el rol "Encuestado" para asignar.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th style="width: 45%;">Nombre</th>
                                <th style="width: 10%;" class="text-center">Estado</th>
                                <th style="width: 40%;">Correo Electrónico</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($availableUsers as $user): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" 
                                               class="form-check-input user-checkbox" 
                                               name="user_ids[]" 
                                               value="<?= $user['id'] ?>"
                                               <?= in_array($user['id'], $assignedUserIds) ? 'checked' : '' ?>>
                                    </td>
                                    <td><?= e($user['name']) ?></td>
                                    <!-- NUEVO: Mostrar estado del usuario -->
                                    <td class="text-center">
                                        <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($user['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= e($user['email']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-success" style="background-color: #5a6c57; border-color: #5a6c57;">
                <i class="bi bi-check-circle me-1"></i>
                Guardar Asignaciones
            </button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    });
});
</script>