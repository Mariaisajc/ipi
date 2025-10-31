<?php
/**
 * Vista: Detalle del Formulario
 */
$pageTitle = $title ?? 'Detalle del Formulario';

// Helper para iconos de tipos de pregunta
function getQuestionIcon($type) {
    $icons = [
        'text' => 'textarea-t', 'textarea' => 'textarea', 'number' => 'hash',
        'email' => 'envelope', 'date' => 'calendar', 'radio' => 'circle',
        'checkbox' => 'check-square', 'select' => 'list', 'scale' => 'bar-chart'
    ];
    return $icons[$type] ?? 'question-circle';
}
?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-eye-fill text-primary me-2"></i>
            <?= e($form['title']) ?>
        </h1>
        <p class="text-muted mb-0">
            Visualizando los detalles completos del formulario.
        </p>
    </div>
    <div>
        <a href="<?= url('admin/forms/edit?id=' . $form['id']) ?>" class="btn btn-success" style="background-color: #5a6c57; border-color: #5a6c57;">
            <i class="bi bi-people me-1"></i>
            Asignar Usuarios
        </a>
        <a href="<?= url('admin/forms/builder?id=' . $form['id']) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-tools me-1"></i>
            Ir al Constructor
        </a>
        <a href="<?= url('admin/forms') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Columna Principal: Preguntas -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-ol me-2"></i>
                    Preguntas del Formulario
                </h5>
                <span class="badge bg-primary">
                    <?= count($questions) ?> pregunta<?= count($questions) !== 1 ? 's' : '' ?>
                </span>
            </div>
            <div class="card-body">
                <?php if (empty($questions)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                        <h6 class="mt-3 text-muted">Este formulario aún no tiene preguntas.</h6>
                        <a href="<?= url('admin/forms/builder?id=' . $form['id']) ?>" class="btn btn-sm btn-primary mt-2">
                            <i class="bi bi-plus-circle me-1"></i>
                            Agregar Preguntas
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($questions as $question): ?>
                        <div class="mb-4 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <p class="fw-semibold mb-1">
                                    <span class="text-muted me-2"><?= $question['order_number'] ?>.</span>
                                    <?= e($question['question_text']) ?>
                                </p>
                                <div>
                                    <span class="badge bg-secondary me-2">
                                        <i class="bi bi-<?= getQuestionIcon($question['type_name']) ?> me-1"></i>
                                        <?= e($question['type_label']) ?>
                                    </span>
                                    <?php if ($question['required']): ?>
                                        <span class="badge bg-danger">Obligatoria</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($question['options'])): ?>
                                <ul class="list-unstyled ps-4 mt-2">
                                    <?php foreach ($question['options'] as $option): ?>
                                        <li class="text-muted small">
                                            <i class="bi bi-dot me-1"></i>
                                            <?= e($option['option_text']) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar: Detalles y Estadísticas -->
    <div class="col-lg-4">
        <!-- Detalles -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Detalles</h5>
            </div>
            <div class="card-body">
                <p><strong>Descripción:</strong></p>
                <p class="text-muted"><?= e($form['description'] ?: 'Sin descripción.') ?></p>
                <hr>
                <p class="mb-1"><strong>Estado:</strong> 
                    <span class="badge bg-<?= $form['status'] === 'draft' ? 'secondary' : ($form['status'] === 'active' ? 'success' : 'danger') ?>">
                        <?= ucfirst($form['status']) ?>
                    </span>
                </p>
                <p class="mb-1"><strong>Creado por:</strong> <?= e($form['creator_name']) ?></p>
                <p class="mb-0"><strong>Fecha de creación:</strong> <?= date('d/m/Y', strtotime($form['created_at'])) ?></p>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Estadísticas</h5>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Usuarios Asignados
                    <span class="badge bg-info rounded-pill"><?= $statistics['total_assignments'] ?? 0 ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Respuestas Iniciadas
                    <span class="badge bg-warning rounded-pill"><?= $statistics['started_responses'] ?? 0 ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Respuestas Completadas
                    <span class="badge bg-success rounded-pill"><?= $statistics['completed_responses'] ?? 0 ?></span>
                </li>
            </ul>
        </div>
    </div>
</div>