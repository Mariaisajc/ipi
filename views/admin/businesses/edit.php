<?php
/**
 * Vista: Editar Empresa
 */
$pageTitle = $title ?? 'Editar Empresa';

// Convertir idiomas de string a array si es necesario
$idiomas_array = [];
if (!empty($business['idiomas_participantes'])) {
    $idiomas_array = explode(',', $business['idiomas_participantes']);
}
?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= $pageTitle ?></h1>
        <p class="text-muted mb-0">Actualiza la información de la empresa</p>
    </div>
    <a href="<?= url('admin/businesses') ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i>
        Volver
    </a>
</div>

<!-- Errores -->
<?php if (isset($_SESSION['errors'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Errores:</strong>
        <ul class="mb-0">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<!-- Formulario -->
<form method="POST" action="<?= url('admin/businesses/update') ?>" id="businessForm">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $business['id'] ?>">
    
    <div class="row">
        <!-- Columna Izquierda -->
        <div class="col-md-8">
            
            <!-- 1. Información Básica -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Información Básica</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">
                                Nombre de la Empresa <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= e($business['name']) ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="razon_social" class="form-label">Razón Social</label>
                            <input type="text" class="form-control" id="razon_social" name="razon_social" 
                                   value="<?= e($business['razon_social'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="nit" class="form-label">NIT</label>
                            <input type="text" class="form-control" id="nit" name="nit" 
                                   value="<?= e($business['nit'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="sector" class="form-label">Sector</label>
                            <select class="form-select" id="sector" name="sector">
                                <option value="">Seleccione...</option>
                                <option value="Tecnología" <?= ($business['sector'] ?? '') == 'Tecnología' ? 'selected' : '' ?>>Tecnología</option>
                                <option value="Manufactura" <?= ($business['sector'] ?? '') == 'Manufactura' ? 'selected' : '' ?>>Manufactura</option>
                                <option value="Servicios" <?= ($business['sector'] ?? '') == 'Servicios' ? 'selected' : '' ?>>Servicios</option>
                                <option value="Comercio" <?= ($business['sector'] ?? '') == 'Comercio' ? 'selected' : '' ?>>Comercio</option>
                                <option value="Salud" <?= ($business['sector'] ?? '') == 'Salud' ? 'selected' : '' ?>>Salud</option>
                                <option value="Educación" <?= ($business['sector'] ?? '') == 'Educación' ? 'selected' : '' ?>>Educación</option>
                                <option value="Financiero" <?= ($business['sector'] ?? '') == 'Financiero' ? 'selected' : '' ?>>Financiero</option>
                                <option value="Otro" <?= ($business['sector'] ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="subsector" class="form-label">Subsector</label>
                            <input type="text" class="form-control" id="subsector" name="subsector" 
                                   value="<?= e($business['subsector'] ?? '') ?>" placeholder="Ej: Software, Retail">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 2. Ubicación -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Ubicación</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="address" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?= e($business['address'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">País</label>
                            <input type="text" class="form-control" id="country" name="country" 
                                   value="<?= e($business['country'] ?? '') ?>" placeholder="Colombia">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 3. Información Laboral -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Información Laboral</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="total_empleados" class="form-label">Total Empleados</label>
                            <input type="number" class="form-control" id="total_empleados" name="total_empleados" 
                                   value="<?= e($business['total_empleados'] ?? '') ?>" min="0" placeholder="0">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="area_empleados" class="form-label">Empleados del Área</label>
                            <input type="number" class="form-control" id="area_empleados" name="area_empleados" 
                                   value="<?= e($business['area_empleados'] ?? '') ?>" min="0" placeholder="0">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="empleados_invitados" class="form-label">Empleados Invitados</label>
                            <input type="number" class="form-control" id="empleados_invitados" name="empleados_invitados" 
                                   value="<?= e($business['empleados_invitados'] ?? '') ?>" min="0" placeholder="0">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="area_name" class="form-label">Nombre del Área Principal</label>
                            <input type="text" class="form-control" id="area_name" name="area_name" 
                                   value="<?= e($business['area_name'] ?? '') ?>" placeholder="Ej: Innovación y Desarrollo (Opcional)">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 4. Departamento de Innovación -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Departamento de Innovación</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tiene_departamento_innovacion" class="form-label">
                                ¿Tiene Departamento de Innovación?
                            </label>
                            <select class="form-select" id="tiene_departamento_innovacion" name="tiene_departamento_innovacion">
                                <option value="">Seleccione...</option>
                                <option value="Si" <?= ($business['tiene_departamento_innovacion'] ?? '') == 'Si' ? 'selected' : '' ?>>Sí</option>
                                <option value="No" <?= ($business['tiene_departamento_innovacion'] ?? '') == 'No' ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="nivel_departamento_innovacion" class="form-label">Nivel del Departamento</label>
                            <select class="form-select" id="nivel_departamento_innovacion" name="nivel_departamento_innovacion">
                                <option value="">Seleccione...</option>
                                <option value="Primer nivel (presidencia)" <?= ($business['nivel_departamento_innovacion'] ?? '') == 'Primer nivel (presidencia)' ? 'selected' : '' ?>>Primer nivel (presidencia)</option>
                                <option value="Segundo nivel (vicepresidencia)" <?= ($business['nivel_departamento_innovacion'] ?? '') == 'Segundo nivel (vicepresidencia)' ? 'selected' : '' ?>>Segundo nivel (vicepresidencia)</option>
                                <option value="Tercer nivel" <?= ($business['nivel_departamento_innovacion'] ?? '') == 'Tercer nivel' ? 'selected' : '' ?>>Tercer nivel</option>
                                <option value="Cuarto nivel" <?= ($business['nivel_departamento_innovacion'] ?? '') == 'Cuarto nivel' ? 'selected' : '' ?>>Cuarto nivel</option>
                                <option value="N/A" <?= ($business['nivel_departamento_innovacion'] ?? '') == 'N/A' ? 'selected' : '' ?>>N/A</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 5. Configuración de Idiomas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-translate me-2"></i>Configuración de Idiomas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Idiomas de los Participantes</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="idiomas_participantes[]" 
                                       value="Español" id="idioma1" <?= in_array('Español', $idiomas_array) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="idioma1">Español</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="idiomas_participantes[]" 
                                       value="Inglés" id="idioma2" <?= in_array('Inglés', $idiomas_array) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="idioma2">Inglés</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="idiomas_participantes[]" 
                                       value="Francés" id="idioma3" <?= in_array('Francés', $idiomas_array) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="idioma3">Francés</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="idioma_informe" class="form-label">Idioma del Informe</label>
                            <select class="form-select" id="idioma_informe" name="idioma_informe">
                                <option value="">Seleccione...</option>
                                <option value="Español" <?= ($business['idioma_informe'] ?? '') == 'Español' ? 'selected' : '' ?>>Español</option>
                                <option value="Inglés" <?= ($business['idioma_informe'] ?? '') == 'Inglés' ? 'selected' : '' ?>>Inglés</option>
                                <option value="Francés" <?= ($business['idioma_informe'] ?? '') == 'Francés' ? 'selected' : '' ?>>Francés</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 6. Fechas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calendar-range me-2"></i>Fechas de Evaluación</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= e($business['start_date'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= e($business['end_date'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 7. Áreas de Negocio -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Áreas de Negocio Adicionales</h5>
                    <button type="button" class="btn btn-sm btn-primary" id="addAreaBtn">
                        <i class="bi bi-plus-circle me-1"></i>Agregar Área
                    </button>
                </div>
                <div class="card-body">
                    <div id="areasContainer">
                        <?php if (!empty($areas)): ?>
                            <?php foreach ($areas as $index => $area): ?>
                                <div class="area-item mb-3">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control" name="areas[<?= $index ?>][name]" 
                                                   value="<?= e($area['name']) ?>" placeholder="Nombre del área">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="areas[<?= $index ?>][description]" 
                                                   value="<?= e($area['description'] ?? '') ?>" placeholder="Descripción (opcional)">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-outline-danger remove-area w-100">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="area-item mb-3">
                                <div class="row">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="areas[0][name]" 
                                               placeholder="Nombre del área">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="areas[0][description]" 
                                               placeholder="Descripción (opcional)">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger remove-area w-100" disabled>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Áreas de negocio para organizar las encuestas
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Columna Derecha -->
        <div class="col-md-4">
            
            <!-- Estadísticas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Estadísticas</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stat-box">
                                <i class="bi bi-diagram-3 text-primary" style="font-size: 2rem;"></i>
                                <h4 class="mt-2 mb-0"><?= $business['areas_count'] ?? 0 ?></h4>
                                <p class="text-muted mb-0 small">Áreas</p>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-box">
                                <i class="bi bi-calendar text-info" style="font-size: 2rem;"></i>
                                <h4 class="mt-2 mb-0 small"><?= date('d/m/Y', strtotime($business['created_at'])) ?></h4>
                                <p class="text-muted mb-0 small">Registro</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Administrador -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Administrador</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="administrador_nombre" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="administrador_nombre" name="administrador_nombre" 
                               value="<?= e($business['administrador_nombre'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="administrador_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="administrador_email" name="administrador_email" 
                               value="<?= e($business['administrador_email'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <!-- Estado -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-toggle-on me-2"></i>Estado</h5>
                </div>
                <div class="card-body">
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?= ($business['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Activa</option>
                        <option value="inactive" <?= ($business['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactiva</option>
                        <option value="borrador" <?= ($business['status'] ?? '') === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                    </select>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Las empresas inactivas no pueden crear encuestas
                    </small>
                </div>
            </div>
            
            <!-- Acciones -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-check-circle me-1"></i>
                        Actualizar Empresa
                    </button>
                    <a href="<?= url('admin/businesses') ?>" class="btn btn-secondary w-100">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const areasContainer = document.getElementById('areasContainer');
    const addAreaBtn = document.getElementById('addAreaBtn');
    let areaIndex = areasContainer.children.length;
    
    // Agregar nueva área
    addAreaBtn.addEventListener('click', function() {
        const areaItem = document.createElement('div');
        areaItem.className = 'area-item mb-3';
        areaItem.innerHTML = `
            <div class="row">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="areas[${areaIndex}][name]" 
                           placeholder="Nombre del área">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="areas[${areaIndex}][description]" 
                           placeholder="Descripción (opcional)">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger remove-area w-100">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        areasContainer.appendChild(areaItem);
        areaIndex++;
        updateRemoveButtons();
    });
    
    // Eliminar área
    areasContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-area')) {
            const areaItem = e.target.closest('.area-item');
            if (areasContainer.children.length > 1) {
                areaItem.remove();
                updateRemoveButtons();
            }
        }
    });
    
    // Actualizar botones
    function updateRemoveButtons() {
        const removeButtons = areasContainer.querySelectorAll('.remove-area');
        removeButtons.forEach(btn => {
            btn.disabled = areasContainer.children.length === 1;
        });
    }
    
    updateRemoveButtons();
    
    // Validación
    document.getElementById('businessForm').addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        if (!name) {
            e.preventDefault();
            alert('El nombre de la empresa es requerido');
            document.getElementById('name').focus();
            return false;
        }
    });
});
</script>