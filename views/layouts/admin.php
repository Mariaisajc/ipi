<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="IPI - Innovation Performance Inndex - Panel de Administración">
    <title><?= $title ?? 'Panel Admin' ?> - IPI</title>
    
    <!-- CSRF Token para AJAX -->
    <?= (new CSRF())->metaTag() ?>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Global -->
    <link rel="stylesheet" href="<?= asset('css/global.css') ?>">
    
    <!-- CSS Admin -->
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    
    <!-- Modals CSS -->
    <link rel="stylesheet" href="<?= asset('css/modals.css') ?>">
    
    <!-- CSS Adicionales (opcional) -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= asset($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="admin-body">
    
    <!-- Header -->
    <?php require VIEWS_PATH . '/layouts/partials/admin/header.php'; ?>
    
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php require VIEWS_PATH . '/layouts/partials/admin/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="container-fluid">
                <!-- Breadcrumb (opcional) -->
                <?php if (isset($breadcrumb)): ?>
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <?php foreach ($breadcrumb as $item): ?>
                                <?php if (isset($item['url'])): ?>
                                    <li class="breadcrumb-item">
                                        <a href="<?= url($item['url']) ?>"><?= e($item['label']) ?></a>
                                    </li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        <?= e($item['label']) ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                <?php endif; ?>
                
                <!-- Flash Messages -->
                <?php $flash = get_flash(); ?>
                <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'x-circle' : 'info-circle') ?>"></i>
                        <?= e($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Page Content -->
                <?= $content ?>
            </div>
        </main>
    </div>
    
    <!-- Footer -->
    <?php require VIEWS_PATH . '/layouts/partials/admin/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (opcional, solo si es necesario) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- JS Global -->
    <script src="<?= asset('js/main.js') ?>"></script>
    
    <!-- Modal Utilities (debe cargarse primero) -->
    <script src="<?= asset('js/modal-utils.js') ?>"></script>
    
    <!-- Logout Modal JS -->
    <script src="<?= asset('js/logout.js') ?>"></script>
    
    <!-- JS Adicionales (opcional) -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= asset($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline Scripts (opcional) -->
    <?php if (isset($inlineScript)): ?>
        <script><?= $inlineScript ?></script>
    <?php endif; ?>
</body>
</html>