<?php
/**
 * Vista: Constructor de Formularios
 * Interface drag-and-drop para construir formularios
 */
$pageTitle = $title ?? 'Constructor de Formulario';
$formId = $form['id'] ?? 0;

// Helper para iconos de tipos de pregunta
function getQuestionIcon($type) {
    $icons = [
        'text' => 'textarea-t',
        'textarea' => 'textarea',
        'number' => 'hash',
        'email' => 'envelope',
        'date' => 'calendar',
        'radio' => 'circle',
        'checkbox' => 'check-square',
        'select' => 'list',
        'scale' => 'bar-chart'
    ];
    return $icons[$type] ?? 'question-circle';
}
?>

<!-- CSS del builder -->
<link rel="stylesheet" href="<?= asset('css/form-builder.css') ?>">

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-tools text-primary me-2"></i>
            <?= $pageTitle ?>
        </h1>
        <p class="text-muted mb-0">
            <strong><?= e($form['title']) ?></strong>
            <span class="badge bg-<?= $form['status'] === 'draft' ? 'secondary' : ($form['status'] === 'active' ? 'success' : 'danger') ?> ms-2">
                <?= $form['status'] === 'draft' ? 'Borrador' : ($form['status'] === 'active' ? 'Activo' : 'Cerrado') ?>
            </span>
        </p>
    </div>
    <div>
        <a href="<?= url('admin/forms/view?id=' . $formId) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-eye me-1"></i>
            Vista Previa
        </a>
        <?php if ($form['status'] === 'draft'): ?>
            <button type="button" class="btn btn-success" onclick="publishForm()" style="background-color: #5a6c57; border-color: #5a6c57;">
                <i class="bi bi-check-circle me-1"></i>
                Publicar
            </button>
        <?php elseif ($form['status'] === 'active'): ?>
            <button type="button" class="btn btn-danger" onclick="closeForm()">
                <i class="bi bi-x-circle me-1"></i>
                Cerrar
            </button>
        <?php endif; ?>
        <a href="<?= url('admin/forms') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Volver
        </a>
    </div>
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

<div class="row">
    <!-- Columna Principal - Lista de Preguntas -->
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
            <div class="card-body" id="questions-container">
                <?php if (empty($questions)): ?>
                    <!-- Estado vacío -->
                    <div class="empty-state" id="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h5 class="mt-3 text-muted">No hay preguntas</h5>
                        <p class="text-muted mb-0">
                            Comienza agregando preguntas desde el panel lateral
                        </p>
                    </div>
                <?php else: ?>
                    <!-- Lista de preguntas -->
                    <div id="questions-list">
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="question-item" data-question-id="<?= $question['id'] ?>" data-order="<?= $question['order_number'] ?>">
                                <div class="d-flex align-items-start">
                                    <!-- Handle para drag -->
                                    <div class="drag-handle">
                                        <i class="bi bi-grip-vertical fs-4"></i>
                                    </div>
                                    
                                    <!-- Contenido de la pregunta -->
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge bg-secondary question-type-badge me-2">
                                                    <i class="bi bi-<?= getQuestionIcon($question['type_name']) ?> me-1"></i>
                                                    <?= e($question['type_label']) ?>
                                                </span>
                                                <?php if ($question['required']): ?>
                                                    <span class="badge bg-danger question-type-badge">Obligatoria</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" 
                                                        class="btn btn-outline-primary btn-sm"
                                                        onclick="editQuestion(<?= $question['id'] ?>)"
                                                        title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm"
                                                        onclick="deleteQuestion(<?= $question['id'] ?>)"
                                                        title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <p class="mb-1 fw-semibold"><?= e($question['question_text']) ?></p>
                                        
                                        <?php if ($question['help_text']): ?>
                                            <p class="text-muted small mb-1">
                                                <i class="bi bi-info-circle me-1"></i>
                                                <?= e($question['help_text']) ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php if ($question['placeholder']): ?>
                                            <p class="text-muted small mb-1">
                                                <i class="bi bi-cursor-text me-1"></i>
                                                Placeholder: <?= e($question['placeholder']) ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <!-- Mostrar opciones si las tiene -->
                                        <?php if (!empty($question['options'])): ?>
                                            <div class="question-options">
                                                <?php foreach ($question['options'] as $option): ?>
                                                    <div class="option-item">
                                                        <i class="bi bi-circle me-2" style="font-size: 0.6rem;"></i>
                                                        <?= e($option['option_text']) ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Sidebar - Tipos de Preguntas -->
    <div class="col-lg-4">
        <div class="builder-sidebar">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle me-2"></i>
                        Agregar Pregunta
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Selecciona el tipo de pregunta que deseas agregar:
                    </p>
                    
                    <div class="row g-3">
                        <?php foreach ($questionTypes as $type): ?>
                            <div class="col-6">
                                <div class="type-selector" 
                                     onclick="openAddQuestionModal('<?= $type['name'] ?>', <?= $type['id'] ?>, '<?= e($type['description']) ?>', '')">
                                    <i class="bi bi-<?= getQuestionIcon($type['name']) ?> d-block"></i>
                                    <span class="type-name"><?= e($type['description']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Información -->
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Información
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Arrastra las preguntas para reordenarlas
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Los cambios se guardan automáticamente
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            Publica el formulario cuando esté listo
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Agregar/Editar Pregunta -->
<div class="modal fade" id="questionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionModalTitle">Agregar Pregunta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="questionForm">
                    <input type="hidden" id="question_id" name="question_id">
                    <input type="hidden" id="form_id" name="form_id" value="<?= $formId ?>">
                    <input type="hidden" id="type_id" name="type_id">
                    
                    <!-- Texto de la pregunta -->
                    <div class="mb-3">
                        <label for="question_text" class="form-label">
                            Pregunta <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" 
                                  id="question_text" 
                                  name="question_text" 
                                  rows="3" 
                                  required
                                  placeholder="Escribe tu pregunta aquí..."></textarea>
                    </div>
                    
                    <!-- Texto de ayuda -->
                    <div class="mb-3">
                        <label for="help_text" class="form-label">Texto de ayuda (opcional)</label>
                        <input type="text" 
                               class="form-control" 
                               id="help_text" 
                               name="help_text"
                               placeholder="Texto opcional para ayudar al usuario">
                    </div>
                    
                    <!-- Placeholder -->
                    <div class="mb-3" id="placeholder_container">
                        <label for="placeholder" class="form-label">Placeholder (opcional)</label>
                        <input type="text" 
                               class="form-control" 
                               id="placeholder" 
                               name="placeholder"
                               placeholder="Ejemplo: Escribe aquí tu respuesta...">
                    </div>
                    
                    <!-- Obligatoria -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="required" 
                               name="required"
                               value="1">
                        <label class="form-check-label" for="required">
                            Pregunta obligatoria
                        </label>
                    </div>
                    
                    <!-- Opciones (solo para radio, checkbox, select) -->
                    <div id="options_container" style="display: none;">
                        <hr>
                        <label class="form-label fw-bold">Opciones de respuesta</label>
                        <p class="text-muted small">Agrega las opciones que el usuario podrá seleccionar:</p>
                        <div id="options_list"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addOption()">
                            <i class="bi bi-plus-circle me-1"></i>
                            Agregar Opción
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveQuestion()" style="background-color: #5a6c57; border-color: #5a6c57;">
                    <i class="bi bi-check-circle me-1"></i>
                    Guardar Pregunta
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SortableJS para drag-and-drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<!-- JavaScript del builder -->
<script>
    // Definir BASE_URL para el JavaScript
    const BASE_URL = '<?= rtrim($config['url'] ?? '', '/') ?>';
</script>
<script src="<?= asset('js/form-builder.js') ?>"></script>