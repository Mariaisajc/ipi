<?php
/**
 * Vista: Listado de Usuarios
 */
$pageTitle = $title ?? 'Usuarios';
?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= $pageTitle ?></h1>
        <p class="text-muted mb-0">Gestiona los usuarios del sistema</p>
    </div>
    <a href="<?= url('admin/users/create') ?>" class="btn btn-success" style="background-color: #5a6c57; border-color: #5a6c57;">
        <i class="bi bi-plus-circle me-1"></i>
        Nuevo Usuario
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

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('admin/users') ?>" id="filterForm">
            <div class="row g-3">
                <!-- Buscador -->
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               class="form-control border-start-0" 
                               placeholder="Buscar por login, nombre o email..." 
                               value="<?= e($search ?? '') ?>"
                               id="searchInput">
                    </div>
                </div>
                
                <!-- Filtro por Rol -->
                <div class="col-md-3">
                    <select name="role" class="form-select" id="roleFilter">
                        <option value="">Todos los roles</option>
                        <option value="admin" <?= ($role ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        <option value="encuestado" <?= ($role ?? '') === 'encuestado' ? 'selected' : '' ?>>Encuestado</option>
                    </select>
                </div>
                
                <!-- Filtro por Estado -->
                <div class="col-md-3">
                    <select name="status" class="form-select" id="statusFilter">
                        <option value="">Todos los estados</option>
                        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-submit cuando cambian los filtros
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('filterForm');
    const selects = form.querySelectorAll('select');
    const searchInput = document.getElementById('searchInput');
    
    // Submit al cambiar selectores
    selects.forEach(select => {
        select.addEventListener('change', function() {
            form.submit();
        });
    });
    
    // Submit al escribir en el buscador (con delay)
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            form.submit();
        }, 500);
    });
});
</script>

<!-- Tabla de Usuarios -->
<div class="card">
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No hay usuarios registrados</p>
                <a href="<?= url('admin/users/create') ?>" class="btn btn-success" style="background-color: #5a6c57; border-color: #5a6c57;">
                    Crear usuario
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Empresa</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= e($user['login']) ?></div>
                                <small class="text-muted">
                                    <i class="bi bi-calendar me-1"></i>
                                    <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                </small>
                            </td>
                            <td>
                                <?php if (!empty($user['name'])): ?>
                                    <?= e($user['name']) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($user['email'])): ?>
                                    <small><?= e($user['email']) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge bg-primary">
                                        <i class="bi bi-shield-check me-1"></i>Administrador
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">
                                        <i class="bi bi-person me-1"></i>Encuestado
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['role'] === 'encuestado' && !empty($user['business_name'])): ?>
                                    <div><?= e($user['business_name']) ?></div>
                                    <?php if (!empty($user['start_date']) && !empty($user['end_date'])): ?>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($user['start_date'])) ?> - 
                                            <?= date('d/m/Y', strtotime($user['end_date'])) ?>
                                        </small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                // Calcular estado real según fechas
                                $displayStatus = $user['status'];
                                $statusClass = 'secondary';
                                $statusIcon = 'x-circle';
                                $statusText = 'Inactivo';
                                
                                if ($user['role'] === 'encuestado' && !empty($user['start_date']) && !empty($user['end_date'])) {
                                    $today = date('Y-m-d');
                                    $startDate = $user['start_date'];
                                    $endDate = $user['end_date'];
                                    
                                    if ($today < $startDate) {
                                        // Pendiente (aún no empieza)
                                        $statusClass = 'warning';
                                        $statusIcon = 'clock';
                                        $statusText = 'Pendiente';
                                    } elseif ($today > $endDate) {
                                        // Expirado
                                        $statusClass = 'danger';
                                        $statusIcon = 'x-octagon';
                                        $statusText = 'Expirado';
                                    } elseif ($user['status'] === 'active') {
                                        // Activo (en período válido)
                                        $statusClass = 'success';
                                        $statusIcon = 'check-circle';
                                        $statusText = 'Activo';
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
                                
                                <?php if ($user['role'] === 'encuestado' && !empty($user['start_date']) && !empty($user['end_date'])): ?>
                                    <?php
                                    $today = date('Y-m-d');
                                    if ($today < $user['start_date']) {
                                        $diff = (new DateTime($user['start_date']))->diff(new DateTime($today));
                                        echo '<br><small class="text-muted">Inicia en ' . $diff->days . ' días</small>';
                                    } elseif ($today <= $user['end_date']) {
                                        $diff = (new DateTime($today))->diff(new DateTime($user['end_date']));
                                        echo '<br><small class="text-muted">' . $diff->days . ' días restantes</small>';
                                    }
                                    ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?= url('admin/users/show?id=' . $user['id']) ?>" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= url('admin/users/edit?id=' . $user['id']) ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($user['can_delete_data']['can_delete']): ?>
                                        <button onclick="confirmDelete(<?= $user['id'] ?>, '<?= e($user['login']) ?>')" 
                                                class="btn btn-sm btn-outline-danger" 
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary" 
                                                disabled
                                                title="<?= e($user['can_delete_data']['reason']) ?>">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php 
                        $params = [];
                        if ($search) $params[] = 'search=' . urlencode($search);
                        if ($role) $params[] = 'role=' . urlencode($role);
                        if ($status) $params[] = 'status=' . urlencode($status);
                        $params[] = 'page=' . $i;
                        $queryString = implode('&', $params);
                        ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('admin/users?' . $queryString) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete(id, login) {
    if (confirm('¿Estás seguro de eliminar el usuario "' + login + '"?\n\nEsta acción no se puede deshacer.')) {
        deleteUser(id);
    }
}

function deleteUser(id) {
    fetch('<?= url('admin/users/destroy') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: 'id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el usuario');
    });
}
</script>

<style>
/* Paginación con colores corporativos */
.pagination .page-link {
    color: #5a6c57;
    border-color: #dee2e6;
}

.pagination .page-link:hover {
    color: #4a5d4a;
    background-color: #e8f5e9;
    border-color: #5a6c57;
}

.pagination .page-item.active .page-link {
    background-color: #5a6c57;
    border-color: #5a6c57;
    color: white;
}

.pagination .page-item.active .page-link:hover {
    background-color: #4a5d4a;
    border-color: #4a5d4a;
}
</style>