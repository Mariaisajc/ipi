<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPI - Innovation Performance Inndex</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }
        
        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(135deg, #457373 0%, #214247 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            padding: 80px 0;
        }
        
        .logo-large {
            width: 150px;
            height: 150px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .logo-large .logo-text {
            font-size: 60px;
            font-weight: 800;
            color: #214247;
        }
        
        h1 {
            font-size: 56px;
            font-weight: 700;
            margin: 30px 0;
            line-height: 1.2;
        }
        
        .subtitle {
            font-size: 22px;
            font-weight: 300;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        
        .btn-primary-custom {
            background: white;
            color: #214247;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 18px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            color: #214247;
        }
        
        .btn-secondary-custom {
            background: transparent;
            color: white;
            padding: 15px 40px;
            border: 2px solid white;
            border-radius: 50px;
            font-weight: 600;
            font-size: 18px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin-left: 20px;
        }
        
        .btn-secondary-custom:hover {
            background: white;
            color: #214247;
        }
        
        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: white;
        }
        
        .section-title {
            font-size: 42px;
            font-weight: 700;
            color: #214247;
            margin-bottom: 60px;
            text-align: center;
        }
        
        .feature-card {
            padding: 40px;
            border-radius: 20px;
            background: #f8f9fa;
            border: 2px solid #e6dbcb;
            transition: all 0.3s ease;
            height: 100%;
            text-align: center;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-color: #457373;
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #457373, #214247);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
        }
        
        .feature-title {
            font-size: 22px;
            font-weight: 600;
            color: #214247;
            margin-bottom: 15px;
        }
        
        .feature-text {
            color: #666;
            line-height: 1.6;
        }
        
        /* Footer */
        .footer {
            background: #214247;
            color: white;
            padding: 40px 0;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 36px;
            }
            
            .subtitle {
                font-size: 18px;
            }
            
            .btn-secondary-custom {
                margin-left: 0;
                margin-top: 15px;
            }
            
            .section-title {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="hero-content text-center">
                        <div class="logo-large">
                            <span class="logo-text">IPI</span>
                        </div>
                        <h1>Innovation Performance Inndex</h1>
                        <p class="subtitle">
                            Evalúa y potencia el sistema de innovación de tu empresa
                        </p>
                        <div class="mt-4">
                            <a href="<?= url('auth/login') ?>" class="btn-primary-custom">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Iniciar Sesión
                            </a>
                            <a href="#features" class="btn-secondary-custom">
                                Conocer más
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <h2 class="section-title">¿Por qué elegir nuestra medición?</h2>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-clipboard-data"></i>
                        </div>
                        <h3 class="feature-title">Evaluación Inteligente</h3>
                        <p class="feature-text">
                            Sistema de preguntas que se adapta a las necesidades de tu empresa
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h3 class="feature-title">Análisis Completo</h3>
                        <p class="feature-text">
                            Evaluación integral de capacidades de innovación empresarial
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3 class="feature-title">Datos Seguros</h3>
                        <p class="feature-text">
                            Máxima seguridad y confidencialidad de la información
                        </p>
                    </div>
                </div>
            </div>
            
                
                
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="mb-2">&copy; <?= date('Y') ?> IPI - Innovation Performance Inndex</p>
            <p class="mb-0">
                <small>Todos los derechos reservados</small>
            </p>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Smooth Scroll -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>