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
                <li class="nav-item">
                    <a class="nav-link active fw-semibold" href="index.php">Inicio</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">Productos</a>
                </li>

                <!-- DROPDOWN -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        Categorías
                    </a>
                    <ul class="dropdown-menu shadow border-0">
                        <?php
                        $catNav = $conexion->query("SELECT * FROM categoria");
                        while ($c = $catNav->fetch_object()) {
                            echo "<li><a class='dropdown-item' href='store.php?cat=$c->idCategoria'>$c->nombreCat</a></li>";
                        }
                        ?>
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

                <a href="perfil.php" class="text-dark transition-all hover-opacity">
                    <i class="bi bi-person fs-4"></i>
                </a>

                <?php
                $cartCount = 0;
                if (!empty($_SESSION['carrito'])) {
                    // Sumamos las cantidades de cada producto
                    foreach ($_SESSION['carrito'] as $item) {
                        $cartCount += $item['cantidad'];
                    }
                }
                ?>
                <a href="carrito.php" class="text-dark position-relative">
                    <i class="bi bi-bag fs-4"></i>
                    <?php
                    $cartCount = 0;
                    if (isset($_SESSION['carrito'])) {
                        foreach ($_SESSION['carrito'] as $item) {
                            $cartCount += $item['cantidad'];
                        }
                    }
                    ?>
                    <span class="badge-carrito position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                        style="font-size: 0.65rem; <?= ($cartCount == 0) ? 'display: none;' : '' ?>">
                        <?= $cartCount ?>
                    </span>
                </a>

            </div>

        </div>
    </div>
</nav>