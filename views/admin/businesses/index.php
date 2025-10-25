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
    <a href="<?= url('admin/businesses/create') ?>" class="btn btn-primary">
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
        <form method="GET" action="<?= url('admin/businesses') ?>" class="row g-3">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" 
                       placeholder="Buscar por nombre, email o ciudad..." 
                       value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Buscar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Empresas -->
<div class="card">
    <div class="card-body">
        <?php if (empty($businesses)): ?>
            <div class="text-center py-5">
                <i class="bi bi-building" style="font-size: 3rem; color: #ccc;"></i>
                <p class="text-muted mt-3">No hay empresas registradas</p>
                <a href="<?= url('admin/businesses/create') ?>" class="btn btn-primary">
                    Crear primera empresa
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Contacto</th>
                            <th>Ubicación</th>
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
                                <?php if (!empty($business['razon_social'])): ?>
                                    <small class="text-muted"><?= e($business['razon_social']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($business['administrador_nombre'])): ?>
                                    <div><?= e($business['administrador_nombre']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($business['administrador_email'])): ?>
                                    <small class="text-muted"><?= e($business['administrador_email']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($business['country'])): ?>
                                    <?= e($business['country']) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $business['areas_count'] ?> áreas</span>
                            </td>
                            <td>
                                <?php if ($business['status'] === 'active'): ?>
                                    <span class="badge bg-success">Activa</span>
                                <?php elseif ($business['status'] === 'borrador'): ?>
                                    <span class="badge bg-warning text-dark">Borrador</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
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
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('admin/businesses?page=' . $i . ($search ? '&search=' . urlencode($search) : '')) ?>">
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