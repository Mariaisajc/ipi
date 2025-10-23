-- BASE DE DATOS: innovacion_db
-- =====================================================

CREATE DATABASE IF NOT EXISTS innovacion_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE innovacion_db;

-- =====================================================
-- 1. TABLA: businesses (Empresas)
-- =====================================================
CREATE TABLE businesses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    razon_social VARCHAR(255) NULL,
    nit VARCHAR(50) NULL,
    address VARCHAR(255) NULL,
    country VARCHAR(100) NULL,
    sector VARCHAR(100) NULL,
    subsector VARCHAR(100) NULL,
    total_empleados INT NULL,
    area_name VARCHAR(255) NULL,
    area_empleados INT NULL,
    empleados_invitados INT NULL,
    tiene_departamento_innovacion ENUM('Si','No') NULL,
    nivel_departamento_innovacion ENUM(
        'Primer nivel (presidencia)',
        'Segundo nivel (vicepresidencia)',
        'Tercer nivel',
        'Cuarto nivel',
        'N/A'
    ) NULL,
    idiomas_participantes SET('Español','Inglés','Francés') NULL,
    idioma_informe ENUM('Español','Inglés','Francés') NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    administrador_nombre VARCHAR(255) NULL,
    administrador_email VARCHAR(255) NULL,
    status ENUM('borrador', 'active', 'inactive') DEFAULT 'active',
    
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_created_by (created_by)
);

-- =====================================================
-- 2. TABLA: business_areas (Áreas o divisiones del negocio)
-- =====================================================
CREATE TABLE business_areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES businesses(created_by) ON DELETE SET NULL,
    
    INDEX idx_business (business_id)
);

-- =====================================================
-- 3. TABLA: users (Usuarios)
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100) NULL,
    email VARCHAR(100) NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'encuestado') NOT NULL DEFAULT 'encuestado',
    
    business_id INT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,

    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_business_id (business_id),
    INDEX idx_role_status (role, status),
    INDEX idx_login (login)
);

-- =====================================================
-- 4. TABLA: forms (Formularios)
-- =====================================================
CREATE TABLE forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NULL,
    status ENUM('draft', 'active', 'closed') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_status (status),
    INDEX idx_created_by (created_by)
);

-- =====================================================
-- 5. TABLA: user_forms (Asignación de formularios a usuarios)
-- =====================================================
CREATE TABLE user_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    form_id INT NOT NULL,
    assigned_by INT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,

    UNIQUE KEY unique_user_form (user_id, form_id),
    INDEX idx_user (user_id),
    INDEX idx_form (form_id)
);

-- =====================================================
-- 6. TABLA: question_types (Tipos de preguntas)
-- =====================================================
CREATE TABLE question_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 7. TABLA: questions (Preguntas del formulario)
-- =====================================================
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT NOT NULL,
    question_text TEXT NOT NULL,
    type_id INT NOT NULL,
    required BOOLEAN DEFAULT FALSE,
    order_number INT DEFAULT 0,
    placeholder VARCHAR(255) NULL,
    help_text TEXT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE,
    FOREIGN KEY (type_id) REFERENCES question_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_form_order (form_id, order_number)
);

-- =====================================================
-- 8. TABLA: question_options (Opciones de preguntas)
-- =====================================================
CREATE TABLE question_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    value VARCHAR(255) NULL,
    order_number INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    
    INDEX idx_question (question_id)
);

-- =====================================================
-- 9. TABLA: question_children (Subpreguntas condicionales)
-- =====================================================
CREATE TABLE question_children (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_option_id INT NOT NULL,
    child_question_id INT NOT NULL,
    
    FOREIGN KEY (parent_option_id) REFERENCES question_options(id) ON DELETE CASCADE,
    FOREIGN KEY (child_question_id) REFERENCES questions(id) ON DELETE CASCADE,
    
    INDEX idx_parent (parent_option_id),
    INDEX idx_child (child_question_id)
);

-- =====================================================
-- 10. TABLA: responses (Respuestas de los formularios)
-- =====================================================
CREATE TABLE responses (
    id VARCHAR(36) PRIMARY KEY,
    user_id INT NOT NULL,
    form_id INT NOT NULL,
    business_area_id INT NULL,
    status ENUM('started', 'in_progress', 'completed') DEFAULT 'started',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    submitted_at TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE RESTRICT,
    FOREIGN KEY (business_area_id) REFERENCES business_areas(id) ON DELETE SET NULL,
    
    INDEX idx_user_form (user_id, form_id),
    INDEX idx_status (status),
    INDEX idx_form_status (form_id, status)
);

-- =====================================================
-- 11. TABLA: answers (Respuestas individuales)
-- =====================================================
CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    response_id VARCHAR(36) NOT NULL,
    question_id INT NOT NULL,
    answer_text TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (response_id) REFERENCES responses(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE RESTRICT,

    UNIQUE KEY unique_response_question (response_id, question_id),
    INDEX idx_response (response_id),
    INDEX idx_question (question_id)
);

-- =====================================================
-- 12. TABLA: exports (Historial de exportaciones) ✨ NUEVA
-- =====================================================
CREATE TABLE exports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    form_id INT NOT NULL,
    business_id INT NULL,
    exported_by INT NOT NULL,
    file_format ENUM('excel', 'csv') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    filters JSON NULL COMMENT 'Filtros aplicados en la exportación (fechas, áreas, etc.)',
    record_count INT NULL COMMENT 'Número de respuestas exportadas',
    file_size INT NULL COMMENT 'Tamaño del archivo en bytes',
    exported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL,
    FOREIGN KEY (exported_by) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_form_date (form_id, exported_at),
    INDEX idx_business (business_id),
    INDEX idx_exported_by (exported_by)
);

-- =====================================================
-- DATOS INICIALES
-- =====================================================

-- Tipos de preguntas
INSERT INTO question_types (name, description) VALUES
('text', 'Texto corto'),
('textarea', 'Texto largo'),
('number', 'Número'),
('email', 'Correo electrónico'),
('date', 'Fecha'),
('radio', 'Selección única'),
('checkbox', 'Selección múltiple'),
('select', 'Lista desplegable'),
('scale', 'Escala numérica');

-- Usuario administrador por defecto
-- Password: admin123 (encriptado con bcrypt)
INSERT INTO users (login, name, email, password, role, status) VALUES
('admin', 'Administrador Principal', 'admin@innovacion.com', 
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');