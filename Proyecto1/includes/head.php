<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Categorías desde la API
$apiBase    = "http://127.0.0.1:8000/api";
$catNav     = json_decode(@file_get_contents("$apiBase/categorias"), true) ?? [];

// Contador del carrito
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

        <!-- BOTÓN MÓVIL -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContenido">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- CONTENIDO -->
        <div class="collapse navbar-collapse mt-3 mt-lg-0" id="navbarContenido">

        <!-- LINKS -->
        <ul class="navbar-nav me-auto mb-3 mb-lg-0 gap-lg-2 text-center text-lg-start">

            <?php if (isset($_SESSION['empleado']) && $_SESSION['empleado']['rol'] == 1): ?>
                <li class="nav-item">
                    <a class="nav-link active fw-semibold" href="index.php">Inicio</a>
                </li>
            <?php elseif (isset($_SESSION['empleado']) && $_SESSION['empleado']['rol'] != 1): ?>
                <li class="nav-item">
                    <a class="nav-link active fw-semibold" href="empleado.php">Panel empleado</a>
                </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link" href="store.php?cat=0">Productos</a>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    Categorías
                </a>
                <ul class="dropdown-menu shadow border-0">
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

            <!-- BUSCADOR -->
            <form class="d-flex w-100 w-lg-auto mb-3 mb-lg-0 position-relative">
                <input class="form-control rounded-pill ps-5" type="search" placeholder="Buscar productos...">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            </form>

            <!-- ICONOS -->
            <div class="d-flex justify-content-center justify-content-lg-end align-items-center gap-4">

                <?php if (isset($_SESSION['empleado'])): ?>
                    <div class="dropdown">
                        <a href="#" class="text-dark dropdown-toggle" data-bs-toggle="dropdown" style="text-decoration:none;">
                            <i class="bi bi-person-fill fs-4 text-primary"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li>
                                <span class="dropdown-item-text small text-muted">
                                    <?= htmlspecialchars($_SESSION['empleado']['nombre']) ?>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
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
                    <a href="login.php" class="text-dark">
                        <i class="bi bi-person fs-4"></i>
                    </a>
                <?php endif; ?>

                <a href="carrito.php" class="text-dark position-relative">
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