<?php
/**
 * Vista: Detalles de Empresa (Solo Lectura)
 */
$pageTitle = $title ?? 'Detalles de Empresa';

// Convertir idiomas de string a array si es necesario
$idiomas_array = [];
if (!empty($business['idiomas_participantes'])) {
    $idiomas_array = explode(',', $business['idiomas_participantes']);
}
?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= e($business['name']) ?></h1>
        <p class="text-muted mb-0">Información detallada de la empresa</p>
    </div>
</div>

<div class="row">
    <!-- Columna Izquierda -->
    <div class="col-md-8">
        
        <!-- 1. Información Básica -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i> Información Básica</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Nombre de la Empresa</label>
                        <p class="fw-bold"><?= e($business['name']) ?></p>
                    </div>
                    
                    <?php if (!empty($business['razon_social'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Razón Social</label>
                        <p class="fw-bold"><?= e($business['razon_social']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($business['nit'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">NIT</label>
                        <p class="fw-bold"><?= e($business['nit']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($business['sector'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Sector</label>
                        <p class="fw-bold">
                            <span class="badge bg-secondary"><?= e($business['sector']) ?></span>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($business['subsector'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Subsector</label>
                        <p class="fw-bold"><?= e($business['subsector']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 2. Ubicación -->
        <?php if (!empty($business['address']) || !empty($business['country'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i> Ubicación</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (!empty($business['address'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Dirección</label>
                        <p class="fw-bold"><?= e($business['address']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($business['country'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">País</label>
                        <p class="fw-bold"><?= e($business['country']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- 3. Información Laboral -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i> Información Laboral</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">Total Empleados</label>
                        <p class="fw-bold fs-4">
                            <?= !empty($business['total_empleados']) ? number_format($business['total_empleados']) : 'No especificado' ?>
                        </p>
                    </div>
                    
                    <?php if (!empty($business['area_name'])): ?>
                    <div class="col-md-8 mb-3">
                        <label class="form-label text-muted">Área Principal</label>
                        <p class="fw-bold"><?= e($business['area_name']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Empleados del Área</label>
                        <p class="fw-bold">
                            <?= !empty($business['area_empleados']) ? number_format($business['area_empleados']) : 'No especificado' ?>
                        </p>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Empleados Invitados</label>
                        <p class="fw-bold">
                            <?= !empty($business['empleados_invitados']) ? number_format($business['empleados_invitados']) : 'No especificado' ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 4. Áreas de Negocio -->
        <?php if (!empty($areas)): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i> Áreas de Negocio</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach ($areas as $area): ?>
                    <div class="list-group-item">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <h6 class="mb-1"><?= e($area['name']) ?></h6>
                        </div>
                        <?php if (!empty($area['description'])): ?>
                        <p class="mb-0 text-muted"><?= e($area['description']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- 5. Innovación -->
        <?php if (!empty($business['tiene_departamento_innovacion'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-lightbulb me-2"></i> Departamento de Innovación</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">¿Tiene Departamento?</label>
                        <p class="fw-bold">
                            <span class="badge bg-success">Sí</span>
                        </p>
                    </div>
                    
                    <?php if (!empty($business['nivel_departamento_innovacion'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Nivel</label>
                        <p class="fw-bold"><?= e($business['nivel_departamento_innovacion']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- 6. Idiomas y Configuración -->
        <?php if (!empty($idiomas_array) || !empty($business['idioma_informe'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-translate me-2"></i> Idiomas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (!empty($idiomas_array)): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Idiomas de Participantes</label>
                        <p class="fw-bold">
                            <?php foreach ($idiomas_array as $idioma): ?>
                                <span class="badge bg-primary me-1"><?= e($idioma) ?></span>
                            <?php endforeach; ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($business['idioma_informe'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Idioma del Informe</label>
                        <p class="fw-bold">
                            <span class="badge bg-secondary"><?= e($business['idioma_informe']) ?></span>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- 7. Período de Evaluación -->
        <?php if (!empty($business['start_date']) || !empty($business['end_date'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-calendar-range me-2"></i> Período de Evaluación</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (!empty($business['start_date'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Fecha de Inicio</label>
                        <p class="fw-bold">
                            <i class="bi bi-calendar-check me-1"></i>
                            <?= date('d/m/Y', strtotime($business['start_date'])) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($business['end_date'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Fecha de Fin</label>
                        <p class="fw-bold">
                            <i class="bi bi-calendar-x me-1"></i>
                            <?= date('d/m/Y', strtotime($business['end_date'])) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- 8. Administrador -->
        <?php if (!empty($business['administrador_nombre']) || !empty($business['administrador_email'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i> Administrador de la Empresa</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (!empty($business['administrador_nombre'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Nombre</label>
                        <p class="fw-bold"><?= e($business['administrador_nombre']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($business['administrador_email'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Email</label>
                        <p class="fw-bold">
                            <a href="mailto:<?= e($business['administrador_email']) ?>">
                                <?= e($business['administrador_email']) ?>
                            </a>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Columna Derecha (Sidebar) -->
    <div class="col-md-4">
        
        <!-- Estado -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Estado</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">Estado Actual</label>
                    <p class="mb-0">
                        <?php if ($business['status'] === 'active'): ?>
                            <span class="badge bg-success fs-6">
                                <i class="bi bi-check-circle me-1"></i>
                                Activa
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger fs-6">
                                <i class="bi bi-x-circle me-1"></i>
                                Inactiva
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Fecha de Creación</label>
                    <p class="mb-0">
                        <i class="bi bi-calendar-plus me-1"></i>
                        <?= date('d/m/Y H:i', strtotime($business['created_at'])) ?>
                    </p>
                </div>
                
                <?php if ($business['updated_at'] != $business['created_at']): ?>
                <div class="mb-0">
                    <label class="form-label text-muted">Última Actualización</label>
                    <p class="mb-0">
                        <i class="bi bi-clock-history me-1"></i>
                        <?= date('d/m/Y H:i', strtotime($business['updated_at'])) ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Estadísticas Rápidas -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i> Resumen</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Total Empleados</span>
                    <span class="badge bg-primary fs-6">
                        <?= !empty($business['total_empleados']) ? number_format($business['total_empleados']) : '0' ?>
                    </span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Áreas de Negocio</span>
                    <span class="badge bg-success fs-6"><?= count($areas) ?></span>
                </div>
                
                <?php if (!empty($business['area_empleados'])): ?>
                <div class="d-flex justify-content-between align-items-center mb-0">
                    <span class="text-muted">Empleados del Área</span>
                    <span class="badge bg-info fs-6"><?= number_format($business['area_empleados']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Acciones -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i> Acciones</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= url('admin/businesses/edit?id=' . $business['id']) ?>" class="btn btn-success" style="background-color: #5a6c57; border-color: #5a6c57;">
                        <i class="bi bi-pencil me-1"></i>
                        Editar Empresa
                    </a>
                    
                    <a href="<?= url('admin/businesses') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Volver al Listado
                    </a>
                </div>
            </div>
        </div>
        
    </div>
</div>