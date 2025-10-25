<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - IPI</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Flash Messages CSS -->
    <link href="<?= url('assets/css/flash.css') ?>" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #457373 0%, #214247 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo-circle {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .logo-text {
            font-size: 36px;
            font-weight: 700;
            color: #214247;
        }
        
        .brand-title {
            color: white;
            font-size: 28px;
            font-weight: 600;
            margin: 15px 0 5px 0;
            line-height: 1.2;
        }
        
        .login-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
        }
        
        .form-label {
            color: #214247;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-control {
            border: 2px solid #e6dbcb;
            border-radius: 10px;
            padding: 12px 15px 12px 45px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #457373;
            box-shadow: 0 0 0 0.2rem rgba(69, 115, 115, 0.15);
        }
        
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #457373;
            z-index: 10;
            font-size: 18px;
        }
        
        .btn-login {
            background: #214247;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background: #457373;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 66, 71, 0.3);
        }
        
        .help-section {
            margin-top: 20px;
            padding: 15px;
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
            border-radius: 8px;
        }
        
        .help-section p {
            margin: 0;
            font-size: 13px;
            color: #214247;
        }
        
        .help-section a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
        }
        
        .help-section a:hover {
            text-decoration: underline;
        }
        
        .help-title {
            font-weight: 600;
            color: #0d6efd;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .footer-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e6dbcb;
        }
        
        .footer-link a {
            color: #457373;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .footer-link a:hover {
            color: #214247;
            gap: 8px;
        }
        
        .copyright {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 30px;
            font-size: 13px;
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .brand-title {
                font-size: 24px;
            }
            
            .login-form {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <div class="logo-circle">
                <span class="logo-text">IPI</span>
            </div>
            <h1 class="brand-title">
                Innovation<br>Performance<br>Inndex
            </h1>
        </div>
        
        <!-- Formulario -->
        <div class="login-form">
            <!-- Mensaje -->
            <?php 
            $flashData = isset($flash) ? $flash : (isset($_SESSION['flash']) ? $_SESSION['flash'] : null);
            ?>
            <?php if ($flashData): ?>
                <div id="message" class="message <?= $flashData['type'] ?>">
                    <?= htmlspecialchars($flashData['message']) ?>
                </div>
                <?php if (isset($_SESSION['flash'])) unset($_SESSION['flash']); ?>
            <?php endif; ?>
            
            <form method="POST" action="<?= url('auth/do-login') ?>">
                <?= csrf_field() ?>
                
                <!-- Usuario -->
                <div class="mb-3">
                    <label for="login" class="form-label">Usuario o Email</label>
                    <div class="input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="login" 
                            name="login" 
                            placeholder="Ingrese su usuario o email"
                            value="<?= old('login') ?>"
                            required
                            autofocus>
                    </div>
                </div>
                
                <!-- Contraseña -->
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <i class="bi bi-lock input-icon"></i>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            placeholder="Ingrese su contraseña"
                            required>
                    </div>
                </div>
                
                <!-- Botón -->
                <button type="submit" class="btn btn-login">
                    Iniciar Sesión
                </button>
            </form>
            
            <!-- Ayuda -->
            <div class="help-section">
                <p class="help-title">
                    <i class="bi bi-question-circle"></i>
                    ¿Problemas para acceder?
                </p>
                <p>
                    Comunícate con el administrador al correo 
                    <a href="mailto:danieljimenez208573@gmail.com">danieljimenez208573@gmail.com</a>
                </p>
            </div>
            
            <!-- Regresar -->
            <div class="footer-link">
                <a href="<?= url('') ?>">
                    <i class="bi bi-arrow-left"></i>
                    Regresar al inicio
                </a>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="copyright">
            &copy; <?= date('Y') ?> IPI - Innovation Performance Inndex
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Flash Messages JS -->
    <script src="<?= url('assets/js/flash.js') ?>"></script>
</body>
</html>