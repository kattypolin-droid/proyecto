<?php
// htmlspecialchars protege el título contra caracteres especiales en HTML.
$pageTitle = $pageTitle ?? 'Sistema de Calzado';
// basename conserva solo el nombre del archivo solicitado y sirve para activar la navegación.
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/styles.css?v=20261124-site-polish2" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <!-- Marca institucional enlazada a la página principal. -->
        <a class="navbar-brand fw-bold" href="index.php" aria-label="Catálogo Bata">
            <img src="assets/logo-bata.png" alt="Catálogo Bata" class="brand-logo">
            <span class="brand-tagline">
                <strong>Calzados</strong>
                <small>para cada paso</small>
                <span class="store-status" id="storeStatus"><span class="status-dot"></span><span class="status-text">Calculando...</span></span>
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <!-- active y aria-current identifican la sección visible. -->
                <li class="nav-item"><a class="nav-link <?= $paginaActual === 'index.php' ? 'active' : '' ?>" <?= $paginaActual === 'index.php' ? 'aria-current="page"' : '' ?> href="index.php">Calzados</a></li>
                <li class="nav-item"><a class="nav-link <?= $paginaActual === 'reportes.php' ? 'active' : '' ?>" <?= $paginaActual === 'reportes.php' ? 'aria-current="page"' : '' ?> href="reportes.php">Reportes</a></li>
            </ul>
            <div class="dropdown admin-dropdown">
                <!-- Información de la sesión actual mostrada al usuario. -->
                <button class="admin-profile dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="admin-avatar-iniciales"><?= htmlspecialchars(strtoupper(substr($_SESSION['usuario'] ?? 'A', 0, 1))) ?></span>
                    <span class="admin-copy"><strong><?= htmlspecialchars($_SESSION['usuario'] ?? 'Administrador') ?></strong><small>Sesión activa</small></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end admin-menu">
                    <li>
                        <?php if (basename($_SERVER['PHP_SELF']) === 'index.php'): ?>
                            <button class="dropdown-item" type="button" id="abrirInventario"><span class="menu-icon" aria-hidden="true">▦</span> Inventario</button>
                        <?php else: ?>
                            <a class="dropdown-item" href="index.php?ver_inventario=1"><span class="menu-icon" aria-hidden="true">▦</span> Inventario</a>
                        <?php endif; ?>
                    </li>
                    <li><a class="dropdown-item" href="agregar.php"><span class="menu-icon" aria-hidden="true">＋</span> Nuevo calzado</a></li>
                    <li>
                        <?php if (basename($_SERVER['PHP_SELF']) === 'index.php'): ?>
                            <button class="dropdown-item" type="button" id="exportarDatos"><span class="menu-icon" aria-hidden="true">↓</span> Exportar catálogo</button>
                        <?php else: ?>
                            <a class="dropdown-item" href="index.php?exportar=1"><span class="menu-icon" aria-hidden="true">↓</span> Exportar catálogo</a>
                        <?php endif; ?>
                    </li>
                    <li><a class="dropdown-item" href="reportes.php"><span class="menu-icon" aria-hidden="true">▤</span> Reportes</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><span class="menu-icon" aria-hidden="true">↪</span> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<div class="container app-shell">
<main class="app-main pb-5">
<!-- flash y flash_error contienen mensajes temporales entre redirecciones. -->
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-success" role="status"><?= htmlspecialchars($_SESSION['flash']) ?></div>
<?php unset($_SESSION['flash']); endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
<?php unset($_SESSION['flash_error']); endif; ?>
