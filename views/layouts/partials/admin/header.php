<?php
/**
 * Header del Panel de Administración
 */

// Obtener usuario autenticado usando la función helper
$user = auth_user();

// Si no hay usuario, obtener datos por defecto
if (!$user) {
    $user = [
        'name' => 'Administrador',
        'email' => 'admin@innovacion.com',
        'role' => 'admin'
    ];
}
?>
<header class="admin-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <!-- Logo y Toggle Sidebar -->
            <div class="col-auto">
                <button class="btn btn-link sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <a href="<?= url('admin/dashboard') ?>" class="header-logo">
                    <span class="logo-text">IPI</span>
                    <span class="logo-subtitle">Admin</span>
                </a>
            </div>
            
            <!-- Search Bar (Centro) -->
            <div class="col d-none d-md-block">
                <form class="header-search" action="<?= url('admin/search') ?>" method="GET">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control border-start-0" 
                               name="q" 
                               placeholder="Buscar empresas, usuarios..."
                               value="<?= e($_GET['q'] ?? '') ?>">
                    </div>
                </form>
            </div>
            
            <!-- Right Section -->
            <div class="col-auto">
                <div class="d-flex align-items-center gap-3">
                    
                    <!-- Notifications -->
                    <div class="dropdown">
                        <button class="btn btn-link position-relative header-icon" 
                                type="button" 
                                id="notificationsDropdown" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <i class="bi bi-bell fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                3
                                <span class="visually-hidden">notificaciones no leídas</span>
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end notifications-dropdown" aria-labelledby="notificationsDropdown">
                            <li class="dropdown-header d-flex justify-content-between align-items-center">
                                <strong>Notificaciones</strong>
                                <span class="badge bg-primary rounded-pill">3</span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item notification-item" href="#">
                                    <div class="d-flex gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="notification-icon bg-success">
                                                <i class="bi bi-check-circle"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-0 small">Nueva respuesta completada</p>
                                            <small class="text-muted">Hace 5 minutos</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item notification-item" href="#">
                                    <div class="d-flex gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="notification-icon bg-info">
                                                <i class="bi bi-person-plus"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-0 small">Nuevo usuario registrado</p>
                                            <small class="text-muted">Hace 1 hora</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item notification-item" href="#">
                                    <div class="d-flex gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="notification-icon bg-warning">
                                                <i class="bi bi-building"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-0 small">Nueva empresa creada</p>
                                            <small class="text-muted">Hace 2 horas</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-center small" href="#">
                                    Ver todas las notificaciones
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-link d-flex align-items-center header-user" 
                                type="button" 
                                id="userDropdown" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false">
                            <div class="user-avatar">
                                <?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?>
                            </div>
                            <div class="user-info d-none d-lg-block ms-2">
                                <div class="user-name"><?= e($user['name'] ?? 'Administrador') ?></div>
                                <div class="user-role">Administrador</div>
                            </div>
                            <i class="bi bi-chevron-down ms-2"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li class="dropdown-header">
                                <strong><?= e($user['name'] ?? 'Administrador') ?></strong>
                                <br>
                                <small class="text-muted"><?= e($user['email'] ?? '') ?></small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?= url('admin/profile') ?>">
                                    <i class="bi bi-person me-2"></i>
                                    Mi Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= url('admin/settings') ?>">
                                    <i class="bi bi-gear me-2"></i>
                                    Configuración
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= url('auth/logout') ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</header>