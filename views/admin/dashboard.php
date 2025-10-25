<?php
/**
 * Vista: Dashboard Admin
 */
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Bienvenido al panel de administración de IPI</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-ipi-primary" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>Imprimir
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <!-- Total Empresas -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="bi bi-building"></i>
            </div>
            <div class="stat-value"><?= format_number($stats['total_businesses']) ?></div>
            <div class="stat-label">Empresas Activas</div>
            <a href="<?= url('admin/businesses') ?>" class="btn btn-sm btn-link p-0 mt-2">
                Ver todas <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Total Usuarios -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-value"><?= format_number($stats['total_users']) ?></div>
            <div class="stat-label">Usuarios Activos</div>
            <small class="text-muted">
                <?= $stats['admin_users'] ?> Admins, <?= $stats['encuestado_users'] ?> Encuestados
            </small>
        </div>
    </div>
    
    <!-- Total Formularios -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="bi bi-file-text"></i>
            </div>
            <div class="stat-value"><?= format_number($stats['total_forms']) ?></div>
            <div class="stat-label">Formularios Activos</div>
            <a href="<?= url('admin/forms') ?>" class="btn btn-sm btn-link p-0 mt-2">
                Ver todos <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Total Respuestas -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value"><?= format_number($stats['total_responses']) ?></div>
            <div class="stat-label">Respuestas Completadas</div>
            <small class="text-muted">
                <?= $stats['pending_responses'] ?> en progreso
            </small>
        </div>
    </div>
</div>

<div class="row">
    <!-- Actividad Reciente -->
    <div class="col-xl-8 mb-4">
        <div class="content-card">
            <div class="content-card-header d-flex justify-content-between align-items-center">
                <h5 class="content-card-title">Actividad Reciente</h5>
                <button class="btn btn-sm btn-link">Ver todo</button>
            </div>
            <div class="content-card-body">
                <?php if (empty($recentActivity)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                        <p>No hay actividad reciente</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentActivity as $activity): ?>
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 me-3">
                                        <?php
                                        $iconClass = [
                                            'user' => 'bi-person-plus text-info',
                                            'business' => 'bi-building text-primary',
                                            'form' => 'bi-file-text text-warning',
                                            'response' => 'bi-check-circle text-success'
                                        ];
                                        ?>
                                        <i class="bi <?= $iconClass[$activity['type']] ?? 'bi-circle' ?> fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1">
                                            <strong><?= e($activity['action']) ?>:</strong>
                                            <?= e($activity['description']) ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= time_ago($activity['date']) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Respuestas por Formulario -->
    <div class="col-xl-4 mb-4">
        <div class="content-card">
            <div class="content-card-header">
                <h5 class="content-card-title">Respuestas por Formulario</h5>
            </div>
            <div class="content-card-body">
                <?php if (empty($responsesByForm)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-bar-chart fs-1 d-block mb-3"></i>
                        <p>No hay datos disponibles</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($responsesByForm as $formStat): ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-medium"><?= truncate(e($formStat['title']), 30) ?></span>
                                <span class="badge bg-primary"><?= $formStat['total_responses'] ?></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <?php 
                                $total = $formStat['total_responses'];
                                $completedPercent = $total > 0 ? ($formStat['completed'] / $total * 100) : 0;
                                $inProgressPercent = $total > 0 ? ($formStat['in_progress'] / $total * 100) : 0;
                                ?>
                                <div class="progress-bar bg-success" 
                                     style="width: <?= $completedPercent ?>%"
                                     title="Completadas: <?= $formStat['completed'] ?>">
                                </div>
                                <div class="progress-bar bg-warning" 
                                     style="width: <?= $inProgressPercent ?>%"
                                     title="En progreso: <?= $formStat['in_progress'] ?>">
                                </div>
                            </div>
                            <small class="text-muted">
                                <?= $formStat['completed'] ?> completadas, 
                                <?= $formStat['in_progress'] ?> en progreso
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Empresas Recientes -->
    <div class="col-xl-6 mb-4">
        <div class="content-card">
            <div class="content-card-header d-flex justify-content-between align-items-center">
                <h5 class="content-card-title">Empresas Recientes</h5>
                <a href="<?= url('admin/businesses') ?>" class="btn btn-sm btn-link">Ver todas</a>
            </div>
            <div class="content-card-body">
                <?php if (empty($recentBusinesses)): ?>
                    <div class="text-center py-4 text-muted">
                        <p>No hay empresas registradas</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentBusinesses as $business): ?>
                            <a href="<?= url('admin/businesses/view/' . $business['id']) ?>" 
                               class="list-group-item list-group-item-action border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="user-avatar" style="width: 45px; height: 45px;">
                                            <?= strtoupper(substr($business['name'], 0, 1)) ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0"><?= e($business['name']) ?></h6>
                                        <small class="text-muted">
                                            <?= e($business['sector'] ?? 'Sin sector') ?> • 
                                            <?= format_date($business['created_at']) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-success">Activa</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Formularios Activos -->
    <div class="col-xl-6 mb-4">
        <div class="content-card">
            <div class="content-card-header d-flex justify-content-between align-items-center">
                <h5 class="content-card-title">Formularios Activos</h5>
                <a href="<?= url('admin/forms') ?>" class="btn btn-sm btn-link">Ver todos</a>
            </div>
            <div class="content-card-body">
                <?php if (empty($activeForms)): ?>
                    <div class="text-center py-4 text-muted">
                        <p>No hay formularios activos</p>
                        <a href="<?= url('admin/forms/create') ?>" class="btn btn-sm btn-ipi-primary mt-2">
                            <i class="bi bi-plus-circle me-2"></i>Crear Formulario
                        </a>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($activeForms as $form): ?>
                            <a href="<?= url('admin/forms/builder/' . $form['id']) ?>" 
                               class="list-group-item list-group-item-action border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="bi bi-file-text fs-3 text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0"><?= e($form['title']) ?></h6>
                                        <small class="text-muted">
                                            Creado el <?= format_date($form['created_at']) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-success">Activo</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>