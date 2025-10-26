<?php
/**
 * Vista: Detalle de Usuario
 */
$pageTitle = $title ?? 'Detalle de Usuario';
$userName = !empty($user['name']) ? $user['name'] : $user['login'];
?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= e($userName) ?></h1>
        <p class="text-muted mb-0">Información detallada del usuario</p>
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
    <!-- Columna Principal -->
    <div class="col-md-8">
        
        <!-- Información Personal -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Información Personal</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Login</label>
                        <div class="fw-bold"><?= e($user['login']) ?></div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Nombre Completo</label>
                        <div><?= !empty($user['name']) ? e($user['name']) : '<span class="text-muted">-</span>' ?></div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Email</label>
                        <div><?= !empty($user['email']) ? e($user['email']) : '<span class="text-muted">-</span>' ?></div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Rol</label>
                        <div>
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge bg-primary">
                                    <i class="bi bi-shield-check me-1"></i>Administrador
                                </span>
                            <?php else: ?>
                                <span class="badge bg-info text-dark">
                                    <i class="bi bi-person me-1"></i>Encuestado
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Estado</label>
                        <div>
                            <?php 
                            // Calcular estado real según fechas
                            $displayStatus = $user['status'];
                            $statusClass = 'secondary';
                            $statusIcon = 'x-circle';
                            $statusText = 'Inactivo';
                            $statusDetail = '';
                            
                            if ($user['role'] === 'encuestado' && !empty($user['start_date']) && !empty($user['end_date'])) {
                                $today = date('Y-m-d');
                                $startDate = $user['start_date'];
                                $endDate = $user['end_date'];
                                
                                if ($today < $startDate) {
                                    // Pendiente (aún no empieza)
                                    $statusClass = 'warning text-dark';
                                    $statusIcon = 'clock';
                                    $statusText = 'Pendiente';
                                    $diff = (new DateTime($startDate))->diff(new DateTime($today));
                                    $statusDetail = 'Inicia en ' . $diff->days . ' días';
                                } elseif ($today > $endDate) {
                                    // Expirado
                                    $statusClass = 'danger';
                                    $statusIcon = 'x-octagon';
                                    $statusText = 'Expirado';
                                    $diff = (new DateTime($today))->diff(new DateTime($endDate));
                                    $statusDetail = 'Finalizó hace ' . $diff->days . ' días';
                                } elseif ($user['status'] === 'active') {
                                    // Activo (en período válido)
                                    $statusClass = 'success';
                                    $statusIcon = 'check-circle';
                                    $statusText = 'Activo';
                                    $diff = (new DateTime($today))->diff(new DateTime($endDate));
                                    $statusDetail = $diff->days . ' días restantes';
                                }
                            } elseif ($user['status'] === 'active') {
                                $statusClass = 'success';
                                $statusIcon = 'check-circle';
                                $statusText = 'Activo';
                            }
                            ?>
                            <span class="badge bg-<?= $statusClass ?>">
                                <i class="bi bi-<?= $statusIcon ?> me-1"></i><?= $statusText ?>
                            </span>
                            <?php if ($statusDetail): ?>
                                <br><small class="text-muted mt-1 d-block"><?= $statusDetail ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Empresa (si es encuestado) -->
        <?php if ($user['role'] === 'encuestado'): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i>Información de Empresa</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="text-muted small">Empresa Asignada</label>
                        <div class="fw-bold">
                            <?= !empty($user['business_name']) ? e($user['business_name']) : '<span class="text-muted">Sin empresa asignada</span>' ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Fecha de Inicio</label>
                        <div>
                            <?php if (!empty($user['start_date'])): ?>
                                <i class="bi bi-calendar me-1"></i>
                                <?= date('d/m/Y', strtotime($user['start_date'])) ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Fecha de Fin</label>
                        <div>
                            <?php if (!empty($user['end_date'])): ?>
                                <i class="bi bi-calendar me-1"></i>
                                <?= date('d/m/Y', strtotime($user['end_date'])) ?>
                                
                                <?php 
                                $today = date('Y-m-d');
                                $endDate = $user['end_date'];
                                if ($endDate < $today): 
                                ?>
                                    <span class="badge bg-danger ms-2">Expirado</span>
                                <?php elseif ($endDate == $today): ?>
                                    <span class="badge bg-warning text-dark ms-2">Expira hoy</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Información Adicional -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información Adicional</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Creado por</label>
                        <div>
                            <?= !empty($user['created_by_name']) ? e($user['created_by_name']) : '<span class="text-muted">Sistema</span>' ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Fecha de Creación</label>
                        <div>
                            <i class="bi bi-calendar me-1"></i>
                            <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Última Actualización</label>
                        <div>
                            <i class="bi bi-clock me-1"></i>
                            <?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Último Acceso</label>
                        <div>
                            <?php if (!empty($user['last_login'])): ?>
                                <i class="bi bi-box-arrow-in-right me-1"></i>
                                <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?>
                            <?php else: ?>
                                <span class="text-muted">Nunca ha iniciado sesión</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Sidebar -->
    <div class="col-md-4">
        
        <!-- Acciones -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Acciones</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= url('admin/users/edit?id=' . $user['id']) ?>" 
                       class="btn btn-success"
                       style="background-color: #5a6c57; border-color: #5a6c57;">
                        <i class="bi bi-pencil me-1"></i>
                        Editar Usuario
                    </a>
                    
                    <a href="<?= url('admin/users') ?>" 
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Volver al Listado
                    </a>
                    
                    
                    <!-- Botón Eliminar con validación -->
                    <?php if ($canDeleteData['can_delete']): ?>
                        <button onclick="openDeleteModal()" 
                                class="btn btn-outline-danger">
                            <i class="bi bi-trash me-1"></i>
                            Eliminar Usuario
                        </button>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary" 
                                disabled
                                title="<?= e($canDeleteData['reason']) ?>">
                            <i class="bi bi-lock me-1"></i>
                            No se puede eliminar
                        </button>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            <?= e($canDeleteData['reason']) ?>
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas rápidas -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Resumen</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Días en el sistema</label>
                    <div class="fw-bold">
                        <?php 
                        $createdDate = new DateTime($user['created_at']);
                        $now = new DateTime();
                        $days = $now->diff($createdDate)->days;
                        echo $days . ' días';
                        ?>
                    </div>
                </div>
                
                <?php if ($user['role'] === 'encuestado' && !empty($user['end_date'])): ?>
                <div class="mb-3">
                    <label class="text-muted small">Días restantes</label>
                    <div class="fw-bold">
                        <?php 
                        $endDate = new DateTime($user['end_date']);
                        $now = new DateTime();
                        $remaining = $now->diff($endDate)->days;
                        
                        if ($user['end_date'] < date('Y-m-d')) {
                            echo '<span class="text-danger">Expirado</span>';
                        } elseif ($remaining == 0) {
                            echo '<span class="text-warning">Expira hoy</span>';
                        } else {
                            echo $remaining . ' días';
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<script>
/**
 * Abrir modal de confirmación de eliminación
 */
function openDeleteModal() {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

/**
 * Eliminar usuario
 */
function deleteUser() {
    const btn = document.getElementById('confirmDeleteBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Eliminando...';
    
    fetch('<?= url('admin/users/destroy') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: 'id=<?= $user['id'] ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            
            // Mostrar mensaje y redirigir
            alert(data.message);
            window.location.href = '<?= url('admin/users') ?>';
        } else {
            alert('Error: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash me-1"></i>Confirmar Eliminación';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el usuario');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-trash me-1"></i>Confirmar Eliminación';
    });
}
</script>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Información del usuario -->
                <div class="text-center mb-4">
                    <i class="bi bi-exclamation-circle text-danger" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 mb-2">¿Desea realmente eliminar por completo este usuario?</h5>
                    <p class="text-muted mb-0">Esta acción no se puede deshacer</p>
                </div>
                
                <div class="bg-light p-3 rounded text-center">
                    <strong>Usuario:</strong> 
                    <span class="text-danger fw-bold"><?= e($user['login']) ?></span>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="deleteUser()">
                    <i class="bi bi-trash me-1"></i>
                    Sí, Eliminar
                </button>
            </div>
        </div>
    </div>
</div>