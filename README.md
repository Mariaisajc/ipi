# 📁 Estructura del Proyecto IPI (Innovation Performance Inndex)

```
IPI/
│
├── index.php                           # Front Controller - Punto de entrada único
├── .htaccess                           # Reescritura de URLs
├── composer.json                       # Dependencias PHP
├── .gitignore                          # Archivos a ignorar en Git
├── .env.example                        # Ejemplo de variables de entorno
│
├── config/                             # ⚙️ Configuraciones
│   ├── app.php                         # Configuración general de la aplicación
│   ├── database.php                    # Conexión a base de datos (PDO)
│   └── routes.php                      # Definición de todas las rutas
│
├── core/                               # 🔧 Núcleo del Framework MVC
│   ├── Router.php                      # Sistema de enrutamiento
│   ├── Controller.php                  # Controlador base
│   ├── Model.php                       # Modelo base con PDO
│   ├── View.php                        # Sistema de renderizado de vistas
│   ├── Auth.php                        # Sistema de autenticación
│   ├── Validator.php                   # Validaciones de datos
│   └── CSRF.php                        # Protección contra CSRF
│
├── controllers/                        # 🎮 Controladores (Lógica de negocio)
│   ├── AuthController.php              # Login, logout, recuperar contraseña
│   │
│   ├── Admin/                          # Controladores del Administrador
│   │   ├── DashboardController.php     # Dashboard principal del admin
│   │   ├── BusinessController.php      # CRUD de empresas
│   │   ├── UserController.php          # CRUD de usuarios
│   │   ├── FormController.php          # CRUD de formularios
│   │   ├── QuestionController.php      # Gestión de preguntas
│   │   ├── ReportController.php        # Visualización de reportes
│   │   └── ExportController.php        # Exportación a Excel/CSV
│   │
│   └── Survey/                         # Controladores del Encuestado
│       ├── DashboardController.php     # Dashboard del encuestado
│       └── FormResponseController.php  # Responder formularios
│
├── models/                             # 📊 Modelos (Acceso a datos)
│   ├── User.php                        # Modelo de usuarios
│   ├── Business.php                    # Modelo de empresas
│   ├── BusinessArea.php                # Modelo de áreas de negocio
│   ├── Form.php                        # Modelo de formularios
│   ├── Question.php                    # Modelo de preguntas
│   ├── QuestionOption.php              # Modelo de opciones de preguntas
│   ├── Response.php                    # Modelo de respuestas (con UUID)
│   ├── Answer.php                      # Modelo de respuestas individuales
│   └── Export.php                      # Modelo de exportaciones
│
├── views/                              # 🎨 Vistas (Presentación HTML/PHP)
│   │
│   ├── layouts/                        # Layouts principales
│   │   ├── admin.php                   # Layout para administrador
│   │   ├── survey.php                  # Layout para encuestado
│   │   │
│   │   └── partials/                   # Componentes reutilizables
│   │       ├── admin/
│   │       │   ├── header.php          # Header del admin
│   │       │   ├── sidebar.php         # Menú lateral del admin
│   │       │   └── footer.php          # Footer del admin
│   │       │
│   │       └── survey/
│   │           ├── header.php          # Header del encuestado
│   │           └── footer.php          # Footer del encuestado
│   │
│   ├── auth/                           # Vistas de autenticación
│   │   └── login.php                   # Página de login
│   │
│   ├── admin/                          # Vistas del administrador
│   │   ├── dashboard.php               # Dashboard principal
│   │   │
│   │   ├── businesses/                 # Gestión de empresas
│   │   │   ├── index.php               # Listar empresas
│   │   │   ├── create.php              # Crear empresa
│   │   │   └── edit.php                # Editar empresa
│   │   │
│   │   ├── users/                      # Gestión de usuarios
│   │   │   ├── index.php               # Listar usuarios
│   │   │   └── create.php              # Crear usuario
│   │   │
│   │   ├── forms/                      # Gestión de formularios
│   │   │   ├── index.php               # Listar formularios
│   │   │   ├── create.php              # Crear formulario
│   │   │   └── builder.php             # Constructor visual de formularios
│   │   │
│   │   └── reports/                    # Reportes y análisis
│   │       ├── index.php               # Dashboard de reportes
│   │       └── export.php              # Opciones de exportación
│   │
│   ├── survey/                         # Vistas del encuestado
│   │   ├── dashboard.php               # Dashboard del encuestado
│   │   ├── form.php                    # Formulario para responder
│   │   └── success.php                 # Confirmación de envío
│   │
│   └── errors/                         # Páginas de error
│       ├── 404.php                     # Página no encontrada
│       └── 403.php                     # Acceso denegado
│
├── services/                           # 📦 Servicios (Lógica de negocio compleja)
│   ├── FormBuilderService.php         # Construcción dinámica de formularios
│   ├── ExportService.php              # Exportación a Excel/CSV (PhpSpreadsheet)
│   └── UUIDService.php                # Generación de identificadores únicos
│
├── middleware/                         # 🛡️ Middleware (Filtros de seguridad)
│   ├── AuthMiddleware.php             # Verificar si el usuario está autenticado
│   ├── AdminMiddleware.php            # Verificar si el usuario es administrador
│   └── SurveyMiddleware.php           # Verificar si el usuario es encuestado
│
├── helpers/                            # 🛠️ Funciones auxiliares
│   └── functions.php                   # Funciones globales (redirect, dd, etc.)
│
├── assets/                             # 🎨 Recursos estáticos (públicos)
│   │
│   ├── css/                            # Hojas de estilo
│   │   ├── global.css                  # Estilos globales
│   │   ├── admin.css                   # Estilos del panel admin
│   │   ├── survey.css                  # Estilos de encuestas
│   │   │
│   │   └── components/                 # Componentes CSS reutilizables
│   │       ├── buttons.css             # Estilos de botones
│   │       ├── forms.css               # Estilos de formularios
│   │       └── tables.css              # Estilos de tablas
│   │
│   ├── js/                             # JavaScript
│   │   ├── main.js                     # JavaScript global
│   │   │
│   │   ├── admin/                      # Scripts del administrador
│   │   │   └── form-builder.js         # Constructor drag-and-drop
│   │   │
│   │   └── survey/                     # Scripts del encuestado
│   │       └── form-renderer.js        # Renderizado dinámico de formularios
│   │
│   └── images/                         # Imágenes
│       └── logo.png                    # Logo del proyecto
│
├── uploads/                            # 📁 Archivos subidos por usuarios
│
├── exports/                            # 📊 Archivos de exportación generados
│   └── temp/                           # Archivos temporales
│
├── storage/                            # 💾 Almacenamiento del sistema
│   ├── logs/                           # Logs de la aplicación
│   └── cache/                          # Caché del sistema
│
├── database/                           # 🗃️ Scripts SQL
│   └── updated_database.sql            # Esquema de base de datos con UUID y exports
│
└── vendor/                             # 📦 Dependencias de Composer (generado)
```

---

## 📋 Descripción de Carpetas

| Carpeta | Descripción | Acceso Web |
|---------|-------------|------------|
| **config/** | Archivos de configuración (BD, rutas, app) | ❌ Protegida |
| **core/** | Clases base del framework MVC | ❌ Protegida |
| **controllers/** | Lógica de control de la aplicación | ❌ Protegida |
| **models/** | Interacción con la base de datos | ❌ Protegida |
| **views/** | Templates HTML/PHP | ❌ Protegida |
| **services/** | Lógica de negocio compleja | ❌ Protegida |
| **middleware/** | Filtros de autenticación y autorización | ❌ Protegida |
| **helpers/** | Funciones auxiliares globales | ❌ Protegida |
| **assets/** | CSS, JavaScript, imágenes | ✅ Pública |
| **uploads/** | Archivos subidos por usuarios | ✅ Pública* |
| **exports/** | Archivos Excel/CSV generados | ⚠️ Acceso controlado |
| **storage/** | Logs y caché del sistema | ❌ Protegida |
| **database/** | Scripts SQL | ❌ Protegida |
| **vendor/** | Librerías de Composer | ❌ Protegida |

\* *En producción se protegerá con `.htaccess`*

---

## 🎯 Características de la Arquitectura

- ✅ **Patrón MVC**: Model-View-Controller
- ✅ **Front Controller**: Un solo punto de entrada (`index.php`)
- ✅ **Enrutamiento**: Sistema de rutas amigables
- ✅ **Separación de roles**: Admin y Encuestado mediante namespaces
- ✅ **PSR-4 Autoloading**: Carga automática de clases con Composer
- ✅ **Middleware**: Sistema de filtros para autenticación
- ✅ **Services**: Lógica de negocio separada
- ✅ **UUID**: Identificadores únicos para respuestas
- ✅ **Seguridad**: CSRF, validaciones, middleware

---

## 🚀 Tecnologías

- **Backend**: PHP 7.4+
- **Base de datos**: MySQL 8.0+
- **Dependencias**: 
  - `phpoffice/phpspreadsheet` - Exportación Excel/CSV
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Servidor local**: XAMPP/WAMP (Apache + MySQL)
- **Producción**: Hostinger (shared hosting)

---

## 📦 Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/tu-usuario/IPI.git

# 2. Instalar dependencias
composer install

# 3. Configurar variables de entorno
cp .env.example .env
# Editar .env con tus credenciales de BD

# 4. Importar base de datos
mysql -u root -p innovacion_db < database/updated_database.sql

# 5. Configurar Apache
# RewriteBase en .htaccess debe ser /IPI/ (o tu carpeta)

# 6. Acceder a la aplicación
http://localhost/IPI/
```

---

## 🔐 Credenciales por Defecto

**Administrador:**
- Usuario: `admin`
- Contraseña: `admin123`

---

## 📝 Próximos Pasos

1. ✅ Crear estructura de carpetas
2. ⚙️ Configurar archivos básicos (composer.json, .env, .htaccess)
3. 🔧 Implementar clases del core/
4. 🎮 Crear controladores base
5. 📊 Implementar modelos
6. 🎨 Diseñar vistas
7. 🔐 Sistema de autenticación
8. 📝 Constructor de formularios
9. 📊 Sistema de reportes y exportación

---

## 📌 Sobre el Proyecto

**IPI - Innovation Performance Inndex** es un sistema de construcción y respuesta de formularios diseñado para evaluar el desempeño de innovación en empresas. Permite a los administradores crear formularios personalizados con preguntas dinámicas (padre-hijo) y a los encuestados responderlos de manera intuitiva. El sistema genera reportes y permite exportar los resultados a Excel/CSV para análisis.

### Funcionalidades Principales

#### Para Administradores:
- ✅ Gestión de empresas y áreas de negocio
- ✅ Creación de usuarios (administradores y encuestados)
- ✅ Constructor visual de formularios con drag-and-drop
- ✅ Preguntas dinámicas (padre-hijo/condicionales)
- ✅ Asignación de formularios a usuarios
- ✅ Dashboard de reportes con estadísticas
- ✅ Exportación de resultados a Excel/CSV
- ✅ Historial de exportaciones

#### Para Encuestados:
- ✅ Dashboard con formularios asignados
- ✅ Interfaz intuitiva para responder formularios
- ✅ Guardado automático de progreso
- ✅ Visualización de formularios completados
- ✅ Acceso con credenciales genéricas (500+ usuarios)

### Tipos de Preguntas Soportados:
- 📝 Texto corto
- 📄 Texto largo (textarea)
- 🔢 Número
- 📧 Email
- 📅 Fecha
- ⭕ Selección única (radio)
- ☑️ Selección múltiple (checkbox)
- 📋 Lista desplegable (select)
- 📊 Escala numérica

---

## 👥 Equipo

Proyecto desarrollado por **Asesorias Jimenez**

---

## 📞 Soporte

Para preguntas o soporte técnico, contacta a: **danieljimenez208573@gmail.com**