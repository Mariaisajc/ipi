<?php
/**
 * Vista: Listado de Empresas
 */
$pageTitle = $title ?? 'Empresas';
?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= $pageTitle ?></h1>
        <p class="text-muted mb-0">Gestiona las empresas del sistema</p>
    </div>
    <a href="<?= url('admin/businesses/create') ?>" class="btn btn-success" style="background-color: #5a6c57; border-color: #5a6c57;">
        <i class="bi bi-plus-circle me-1"></i>
        Nueva Empresa
    </a>
</div>

<!-- Flash Message -->
<?php 
$flashData = isset($flash) ? $flash : (isset($_SESSION['flash']) ? $_SESSION['flash'] : null);
if ($flashData): 
    unset($_SESSION['flash']);
?>
    <div class="alert alert-<?= $flashData['type'] === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show">
        <?= htmlspecialchars($flashData['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('admin/businesses') ?>" id="filterForm">
            <div class="row g-3">
                <!-- Buscador -->
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               class="form-control border-start-0" 
                               placeholder="Buscar por nombre, NIT o descripción..." 
                               value="<?= e($search ?? '') ?>"
                               id="searchInput">
                    </div>
                </div>
                
                <!-- Filtro por Estado -->
                <div class="col-md-3">
                    <select name="status" class="form-select" id="statusFilter">
                        <option value="">Todos los estados</option>
                        <option value="borrador" <?= ($status ?? '') === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Activa</option>
                        <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactiva</option>
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
    const statusSelect = document.getElementById('statusFilter');
    const searchInput = document.getElementById('searchInput');
    
    // Submit al cambiar selector de estado
    statusSelect.addEventListener('change', function() {
        form.submit();
    });
    
    // Submit al escribir en el buscador (con delay)
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            form.submit();
        }, 500); // Espera 500ms después de dejar de escribir
    });
});
</script>

<!-- Tabla de Empresas -->
<div class="card">
    <div class="card-body">
        <?php if (empty($businesses)): ?>
            <div class="text-center py-5">
                <i class="bi bi-building" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No hay empresas registradas</p>
                <a href="<?= url('admin/businesses/create') ?>" class="btn btn-success" style="background-color: #5a6c57; border-color: #5a6c57;">
                    Crear empresa
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Fecha de Creación</th>
                            <th>Usuarios</th>
                            <th>Áreas</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($businesses as $business): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= e($business['name']) ?></div>
                                <?php if (!empty($business['sector'])): ?>
                                    <small class="text-muted">
                                        <i class="bi bi-tag me-1"></i><?= e($business['sector']) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= date('d/m/Y', strtotime($business['created_at'])) ?></div>
                                <small class="text-muted"><?= date('H:i', strtotime($business['created_at'])) ?></small>
                            </td>
                            <td>
                                <?php
                                $usersCount = (int)($business['users_count'] ?? 0);
                                ?>
                                <?php if ($usersCount > 0): ?>
                                    <a href="<?= url('admin/businesses/show?id=' . $business['id'] . '#users') ?>" 
                                       class="text-decoration-none">
                                        <span class="badge bg-primary rounded-pill">
                                            <i class="bi bi-people-fill me-1"></i>
                                            <span class="fw-bold"><?= $usersCount ?></span>
                                        </span>
                                        <small class="text-muted ms-2">
                                            usuario<?= $usersCount > 1 ? 's' : '' ?>
                                        </small>
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill">
                                        <i class="bi bi-people me-1"></i>
                                        <span class="fw-bold">0</span>
                                    </span>
                                    <small class="text-muted ms-2">Sin usuarios</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $areas = $businessAreaModel->getByBusiness($business['id']);
                                $areaCount = count($areas);
                                ?>
                                <?php if ($areaCount > 0): ?>
                                    <span class="badge bg-info text-dark">
                                        <i class="bi bi-diagram-3 me-1"></i>
                                        <?= $areaCount ?> área<?= $areaCount > 1 ? 's' : '' ?>
                                    </span>
                                <?php else: ?>
                                    <small class="text-muted">Sin áreas</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($business['status'] === 'active'): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Activa
                                    </span>
                                <?php elseif ($business['status'] === 'borrador'): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-pencil-square me-1"></i>Borrador
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Inactiva
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?= url('admin/businesses/show?id=' . $business['id']) ?>" 
                                       class="btn btn-sm btn-outline-success" 
                                       style="--bs-btn-color: #5a6c57; --bs-btn-border-color: #5a6c57; --bs-btn-hover-bg: #5a6c57; --bs-btn-hover-border-color: #5a6c57; --bs-btn-active-bg: #5a6c57; --bs-btn-active-border-color: #5a6c57;"
                                       title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= url('admin/businesses/edit?id=' . $business['id']) ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($business['status'] === 'borrador'): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger btn-delete" 
                                                data-id="<?= $business['id'] ?>" 
                                                data-name="<?= e($business['name']) ?>"
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-secondary" 
                                                disabled
                                                title="Solo se pueden eliminar empresas en estado Borrador">
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
            
            <!-- Paginación -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php 
                        $params = [];
                        if ($search) $params[] = 'search=' . urlencode($search);
                        if ($status) $params[] = 'status=' . urlencode($status);
                        $params[] = 'page=' . $i;
                        $queryString = implode('&', $params);
                        ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('admin/businesses?' . $queryString) ?>">
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
// Eliminar empresa
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.btn-delete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            
            if (typeof showConfirmModal === 'function') {
                showConfirmModal({
                    title: 'Eliminar Empresa',
                    message: `¿Estás seguro de eliminar la empresa "${name}"?<br><small class="text-muted">Esta acción no se puede deshacer.</small>`,
                    icon: 'trash',
                    iconColor: 'danger',
                    confirmText: 'Eliminar',
                    confirmColor: 'danger'
                }).then(confirmed => {
                    if (confirmed) {
                        deleteBusiness(id);
                    }
                });
            } else {
                if (confirm(`¿Estás seguro de eliminar la empresa "${name}"? Esta acción no se puede deshacer.`)) {
                    deleteBusiness(id);
                }
            }
        });
    });
});

function deleteBusiness(id) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch('<?= url('admin/businesses/delete') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&csrf_token=${csrfToken}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al eliminar la empresa');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar la empresa');
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