<?php
/**
 * Vista: Crear Usuario
 */
$pageTitle = $title ?? 'Crear Usuario';
?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= $pageTitle ?></h1>
        <p class="text-muted mb-0">Completa la información del nuevo usuario</p>
    </div>
    <a href="<?= url('admin/users') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>
        Volver
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

<form method="POST" action="<?= url('admin/users/store') ?>" id="userForm">
    <?= (new CSRF())->field() ?>
    
    <div class="row">
        <!-- Columna Principal -->
        <div class="col-md-8">
            
            <!-- Credenciales -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-key me-2"></i>Credenciales de Acceso</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="login" class="form-label">Login <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="login" 
                                   name="login" 
                                   pattern="[a-z0-9_]+" 
                                   title="Solo minúsculas, números y guiones bajos"
                                   required>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Solo minúsculas, números y guión bajo (_). Sin espacios ni caracteres especiales
                            </small>
                            <div id="loginFeedback" class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="name" name="name">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <small class="text-muted">Campo obligatorio y debe ser único</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                            <small class="text-muted">Mínimo 6 caracteres</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password_confirm" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                    <i class="bi bi-eye" id="eyeIconConfirm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Información de Encuestado (oculto por defecto) -->
            <div class="card mb-4" id="encuestadoInfo" style="display: none;">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Información de Encuestado</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="business_id" class="form-label">Empresa <span class="text-danger">*</span></label>
                            <select class="form-select" id="business_id" name="business_id">
                                <option value="">Seleccionar empresa...</option>
                                <?php foreach ($businesses as $business): ?>
                                    <option value="<?= $business['id'] ?>"><?= e($business['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" min="<?= date('Y-m-d') ?>">
                            <small class="text-muted">Debe ser igual o posterior a hoy</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">Fecha de Fin <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" min="<?= date('Y-m-d') ?>">
                            <small class="text-muted">Debe ser posterior a la fecha de inicio</small>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            
            <!-- Rol y Estado -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Rol y Estado</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="role" class="form-label">Rol <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Seleccionar rol...</option>
                            <option value="admin">Administrador</option>
                            <option value="encuestado">Encuestado</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Estado <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" selected>Activo</option>
                            <option value="inactive">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Acciones -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-success w-100 mb-2" style="background-color: #5a6c57; border-color: #5a6c57;">
                        <i class="bi bi-check-circle me-1"></i>
                        Guardar Usuario
                    </button>
                    <a href="<?= url('admin/users') ?>" class="btn btn-secondary w-100">
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
    const roleSelect = document.getElementById('role');
    const encuestadoInfo = document.getElementById('encuestadoInfo');
    const businessSelect = document.getElementById('business_id');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    const userForm = document.getElementById('userForm');
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirm');
    const loginInput = document.getElementById('login');
    const loginFeedback = document.getElementById('loginFeedback');
    
    // Validación del login en tiempo real
    loginInput.addEventListener('input', function() {
        let value = this.value;
        
        // Convertir a minúsculas automáticamente
        this.value = value.toLowerCase();
        
        // Validar caracteres permitidos
        const validPattern = /^[a-z0-9_]*$/;
        
        if (!validPattern.test(this.value)) {
            // Eliminar caracteres no válidos
            this.value = this.value.replace(/[^a-z0-9_]/g, '');
            
            // Mostrar feedback
            this.classList.add('is-invalid');
            loginFeedback.textContent = 'Solo se permiten minúsculas, números y guión bajo (_)';
        } else if (this.value.length > 0) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
            loginFeedback.textContent = '';
        } else {
            this.classList.remove('is-invalid', 'is-valid');
            loginFeedback.textContent = '';
        }
    });
    
    // Validación al perder el foco
    loginInput.addEventListener('blur', function() {
        if (this.value.length === 0) {
            this.classList.add('is-invalid');
            loginFeedback.textContent = 'El login es obligatorio';
        } else if (this.value.length < 3) {
            this.classList.add('is-invalid');
            loginFeedback.textContent = 'El login debe tener al menos 3 caracteres';
        }
    });
    
    // Toggle para mostrar/ocultar contraseña
    const togglePassword = document.getElementById('togglePassword');
    const eyeIcon = document.getElementById('eyeIcon');
    
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Cambiar icono
        if (type === 'text') {
            eyeIcon.classList.remove('bi-eye');
            eyeIcon.classList.add('bi-eye-slash');
        } else {
            eyeIcon.classList.remove('bi-eye-slash');
            eyeIcon.classList.add('bi-eye');
        }
    });
    
    // Toggle para confirmar contraseña
    const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
    const eyeIconConfirm = document.getElementById('eyeIconConfirm');
    
    togglePasswordConfirm.addEventListener('click', function() {
        const type = passwordConfirm.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirm.setAttribute('type', type);
        
        // Cambiar icono
        if (type === 'text') {
            eyeIconConfirm.classList.remove('bi-eye');
            eyeIconConfirm.classList.add('bi-eye-slash');
        } else {
            eyeIconConfirm.classList.remove('bi-eye-slash');
            eyeIconConfirm.classList.add('bi-eye');
        }
    });
    
    // Mostrar/ocultar campos de encuestado según rol
    roleSelect.addEventListener('change', function() {
        if (this.value === 'encuestado') {
            encuestadoInfo.style.display = 'block';
            businessSelect.required = true;
            startDate.required = true;
            endDate.required = true;
        } else {
            encuestadoInfo.style.display = 'none';
            businessSelect.required = false;
            startDate.required = false;
            endDate.required = false;
        }
    });
    
    // Validar que las contraseñas coincidan
    userForm.addEventListener('submit', function(e) {
        if (password.value !== passwordConfirm.value) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            passwordConfirm.focus();
            return false;
        }
        
        // Validar fechas si es encuestado
        if (roleSelect.value === 'encuestado') {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            // Validar fecha de inicio (>= hoy)
            if (startDate.value) {
                const startDateValue = new Date(startDate.value + 'T00:00:00');
                if (startDateValue < today) {
                    e.preventDefault();
                    alert('La fecha de inicio debe ser igual o posterior a hoy');
                    startDate.focus();
                    return false;
                }
            }
            
            // Validar fecha de fin (>= hoy)
            if (endDate.value) {
                const endDateValue = new Date(endDate.value + 'T00:00:00');
                if (endDateValue < today) {
                    e.preventDefault();
                    alert('La fecha de fin debe ser igual o posterior a hoy');
                    endDate.focus();
                    return false;
                }
            }
            
            // Validar que fecha fin > fecha inicio
            if (startDate.value && endDate.value) {
                const startDateValue = new Date(startDate.value + 'T00:00:00');
                const endDateValue = new Date(endDate.value + 'T00:00:00');
                
                if (endDateValue <= startDateValue) {
                    e.preventDefault();
                    alert('La fecha de fin debe ser posterior a la fecha de inicio');
                    endDate.focus();
                    return false;
                }
            }
        }
    });
    
    // Actualizar min de end_date cuando cambia start_date
    startDate.addEventListener('change', function() {
        if (this.value) {
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            const minEndDate = nextDay.toISOString().split('T')[0];
            endDate.setAttribute('min', minEndDate);
        }
    });
});
</script>