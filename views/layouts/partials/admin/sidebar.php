<?php
/**
 * Sidebar del Panel de Administración
 */
$currentUrl = $_SERVER['REQUEST_URI'] ?? '';
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-content">
        
        <!-- Dashboard -->
        <div class="sidebar-section">
            <a href="<?= url('admin/dashboard') ?>" class="sidebar-item <?= active_class('admin/dashboard') ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        <!-- Gestión -->
        <div class="sidebar-section">
            <div class="sidebar-header">Gestión</div>
            
            <a href="<?= url('admin/businesses') ?>" class="sidebar-item <?= active_class('admin/businesses') ?>">
                <i class="bi bi-building"></i>
                <span>Empresas</span>
            </a>
            
            <a href="<?= url('admin/users') ?>" class="sidebar-item <?= active_class('admin/users') ?>">
                <i class="bi bi-people"></i>
                <span>Usuarios</span>
            </a>
            
            <a href="<?= url('admin/forms') ?>" class="sidebar-item <?= active_class('admin/forms') ?>">
                <i class="bi bi-file-text"></i>
                <span>Formularios</span>
            </a>
        </div>
        
        <!-- Reportes y Análisis -->
        <div class="sidebar-section">
            <div class="sidebar-header">Reportes</div>
            
            <a href="<?= url('admin/reports') ?>" class="sidebar-item <?= active_class('admin/reports') ?>">
                <i class="bi bi-bar-chart"></i>
                <span>Análisis</span>
            </a>
            
            <a href="<?= url('admin/export/history') ?>" class="sidebar-item <?= active_class('admin/export') ?>">
                <i class="bi bi-download"></i>
                <span>Exportaciones</span>
            </a>
        </div>
        
        <!-- Configuración -->
        <div class="sidebar-section">
            <div class="sidebar-header">Sistema</div>
            
            <a href="<?= url('admin/settings') ?>" class="sidebar-item <?= active_class('admin/settings') ?>">
                <i class="bi bi-gear"></i>
                <span>Configuración</span>
            </a>
            
            <a href="<?= url('admin/logs') ?>" class="sidebar-item <?= active_class('admin/logs') ?>">
                <i class="bi bi-journal-text"></i>
                <span>Logs</span>
            </a>
        </div>
        
        <!-- Ayuda -->
        <div class="sidebar-section mt-auto">
            <a href="<?= url('admin/help') ?>" class="sidebar-item">
                <i class="bi bi-question-circle"></i>
                <span>Ayuda</span>
            </a>
        </div>
        
    </div>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="sidebar-footer-content">
            <small class="text-muted d-block">IPI v1.0.0</small>
            <small class="text-muted">Innovation Performance Inndex</small>
        </div>
    </div>
</aside>

<!-- Overlay para cerrar sidebar en móvil -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
// Toggle Sidebar
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const body = document.body;
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            body.classList.toggle('sidebar-open');
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            body.classList.remove('sidebar-open');
        });
    }
    
    // Cerrar sidebar al hacer clic en un enlace (en móvil)
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    sidebarItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth < 992) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                body.classList.remove('sidebar-open');
            }
        });
    });
});
</script>