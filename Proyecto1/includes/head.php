<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de la API y obtención de categorías
$apiBase    = "http://127.0.0.1:8000/api";
$catNav     = json_decode(@file_get_contents("$apiBase/categorias"), true) ?? [];

// Lógica del contador del carrito
$cartCount = 0;
if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $cartCount += $item['cantidad'];
    }
}
?>

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
    <div class="container">

        <!-- LOGO -->
        <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2" href="store.php">
             SellFlow
        </a>

        <!-- BOTÓN MÓVIL (Hamburguesa) -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContenido">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- CONTENIDO DEL NAVBAR -->
        <div class="collapse navbar-collapse mt-3 mt-lg-0" id="navbarContenido">

            <!-- SECCIÓN IZQUIERDA: LINKS DE NAVEGACIÓN -->
            <ul class="navbar-nav me-auto mb-3 mb-lg-0 gap-lg-2 text-center text-lg-start">
                
                <?php if (isset($_SESSION['empleado'])): ?>
                    <?php if ($_SESSION['empleado']['rol'] == 1): ?>
                        <!-- VISTA PARA ADMINISTRADOR -->
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="store.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-primary fw-bold" href="index.php">
                                <i class="bi bi-box-seam-fill me-1"></i>Panel Inventario
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- VISTA PARA EMPLEADO ESTÁNDAR -->
                        <li class="nav-item">
                            <a class="nav-link text-primary fw-bold" href="empleado.php">
                                <i class="bi bi-box-seam-fill me-1"></i>Panel Empleado
                            </a>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- VISTA PARA CLIENTE/INVITADO -->
                    <li class="nav-item">
                        <a class="nav-link active fw-semibold" href="store.php">Inicio</a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link" href="store.php?cat=0">Productos</a>
                </li>

                <!-- DROPDOWN DE CATEGORÍAS -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="dropCat" role="button" data-bs-toggle="dropdown">
                        Categorías
                    </a>
                    <ul class="dropdown-menu shadow border-0 animate slideIn">
                        <?php foreach ($catNav as $c): ?>
                            <li>
                                <a class="dropdown-item" href="store.php?cat=<?= $c['idCategoria'] ?>">
                                    <?= htmlspecialchars($c['nombreCat']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        
                        <?php if (empty($catNav)): ?>
                            <li><span class="dropdown-item text-muted">Sin categorías</span></li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>

            <!-- SECCIÓN CENTRAL: BUSCADOR -->
            <form class="d-flex w-100 w-lg-auto mb-3 mb-lg-0 position-relative me-lg-4" action="store.php" method="GET">
                <input class="form-control rounded-pill ps-5" type="search" name="q" placeholder="Buscar productos...">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            </form>

            <!-- SECCIÓN DERECHA: ICONOS DE USUARIO Y CARRITO -->
            <div class="d-flex justify-content-center justify-content-lg-end align-items-center gap-4">

                <?php if (isset($_SESSION['empleado'])): ?>
                    <!-- MENÚ DE PERFIL SI HAY SESIÓN -->
                    <div class="dropdown">
                        <a href="#" class="text-dark dropdown-toggle d-flex align-items-center gap-1" data-bs-toggle="dropdown" style="text-decoration:none;">
                            <i class="bi bi-person-circle fs-4 text-primary"></i>
                            <span class="d-none d-lg-inline small fw-medium"><?= explode(' ', $_SESSION['empleado']['nombre'])[0] ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li class="px-3 py-2">
                                <p class="mb-0 small text-muted">Conectado como:</p>
                                <p class="mb-0 fw-bold small"><?= htmlspecialchars($_SESSION['empleado']['nombre']) ?></p>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            
                            <!-- Acceso rápido al panel desde el perfil para el Admin -->
                            <?php if ($_SESSION['empleado']['rol'] == 1): ?>
                                <li>
                                    <a class="dropdown-item" href="index.php">
                                        <i class="bi bi-speedometer2 me-2"></i>Ir al Dashboard
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li>
                                <a class="dropdown-item" href="perfil.php">
                                    <i class="bi bi-person me-2"></i>Mi perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- BOTÓN LOGIN SI NO HAY SESIÓN -->
                    <a href="login.php" class="text-dark hover-primary" title="Iniciar Sesión">
                        <i class="bi bi-person fs-4"></i>
                    </a>
                <?php endif; ?>

                <!-- BOTÓN CARRITO -->
                <a href="carrito.php" class="text-dark position-relative" title="Ver Carrito">
                    <i class="bi bi-bag fs-4"></i>
                    <span class="badge-carrito position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                        style="font-size: 0.65rem; <?= ($cartCount == 0) ? 'display: none;' : '' ?>">
                        <?= $cartCount ?>
                    </span>
                </a>

            </div>

        </div>
    </div>
</nav>

<style>
    /* Pequeño ajuste visual para mejorar la experiencia */
    .hover-primary:hover { color: #0d6efd !important; transition: 0.3s; }
    .nav-link:hover { color: #0d6efd !important; }
    .dropdown-item:active { background-color: #0d6efd; }
    
    @media (max-width: 991px) {
        .navbar-nav { padding-top: 1rem; }
    }
</style>