<?php
session_start();
include "includes/head.php";

// Lógica de Categoría
$idCatSeleccionada = isset($_GET['cat']) ? (int)$_GET['cat'] : (isset($_SESSION['categoria_actual']) ? $_SESSION['categoria_actual'] : null);

if (isset($_GET['cat'])) {
    $_SESSION['categoria_actual'] = (int)$_GET['cat'];
}

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// ─── Obtener productos desde la API ───────────────────────────────────────────
$apiBase = "http://127.0.0.1:8000/api";

// Construir URL según si hay categoría seleccionada
$urlProductos = $idCatSeleccionada
    ? "$apiBase/productos?categoria_id=$idCatSeleccionada"
    : "$apiBase/productos";

$responseProductos = @file_get_contents($urlProductos);
$productos = $responseProductos ? json_decode($responseProductos, true) : [];

// ─── Obtener categorías desde la API ─────────────────────────────────────────
$urlCategorias = "$apiBase/categorias";
$responseCategorias = @file_get_contents($urlCategorias);
$categorias = $responseCategorias ? json_decode($responseCategorias, true) : [];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High Fidelity | Premium Electronics Store</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --bg-body: #fdfdfd;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --primary: #4f46e5;
            --accent: #f59e0b;
            --white: #ffffff;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-dark);
        }

        .store-header {
            padding: 5rem 0 3rem;
        }

        .sidebar-title {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 1.5rem;
        }

        .filter-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--text-muted);
            text-decoration: none;
            padding: 10px 14px;
            margin-bottom: 4px;
            font-size: 0.9rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .filter-link:hover,
        .filter-link.active {
            background: #f1f5f9;
            color: var(--primary);
            font-weight: 600;
        }

        .product-card {
            background: var(--white);
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 12px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .img-container {
            background: #f8fafc;
            border-radius: 12px;
            aspect-ratio: 1 / 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .img-container img {
            width: 80%;
            height: auto;
            transition: transform 0.5s ease;
        }

        .product-card:hover img {
            transform: scale(1.1);
        }

        .quick-view-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--white);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }

        .product-card:hover .quick-view-btn {
            opacity: 1;
            transform: translateY(0);
        }

        .quick-view-btn:hover {
            background: var(--primary);
            color: white;
        }

        .add-to-cart-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: var(--text-dark);
            color: white;
            padding: 14px;
            text-align: center;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.05em;
            opacity: 0;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .product-card:hover .add-to-cart-overlay {
            opacity: 1;
        }

        .product-info {
            padding: 1.25rem 0.5rem 0.5rem;
        }

        .product-name {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .product-description-short {
            font-size: 0.85rem;
            color: var(--text-muted);
            height: 2.4rem;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .price-tag {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--primary);
        }

        #cart-toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            display: none;
            background: var(--text-dark);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>

    <div id="cart-toast"><i class="bi bi-check-circle-fill me-2 text-success"></i> Producto añadido al carrito</div>

    <div class="container">
        <header class="store-header text-center">
            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-3 fw-bold">CATÁLOGO 2026</span>
            <h1 class="display-4 fw-extrabold mb-3">High Fidelity Electronics</h1>
            <p class="text-muted mx-auto" style="max-width: 600px;">Equipamiento de vanguardia diseñado para los entusiastas de la tecnología.</p>
        </header>

        <div class="row mt-5">
            <aside class="col-lg-3 pe-lg-5 mb-5">
                <h6 class="sidebar-title">Colecciones</h6>
                <nav>
                    <a href="store.php?cat=0" class="filter-link <?= !$idCatSeleccionada ? 'active' : '' ?>">
                        Todos los productos <i class="bi bi-chevron-right small"></i>
                    </a>
                    <?php foreach ($categorias as $c): ?>
                        <?php $active = ($idCatSeleccionada == $c['idCategoria']) ? 'active' : ''; ?>
                        <a href="store.php?cat=<?= $c['idCategoria'] ?>" class="filter-link <?= $active ?>">
                            <?= htmlspecialchars($c['nombreCat']) ?> <i class="bi bi-chevron-right small"></i>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </aside>

            <main class="col-lg-9">
                <div class="row g-4" id="contenedor-productos">
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $p): ?>

                            <div class="col-md-6 col-xl-4">
                                <article class="product-card">
                                    <button class="quick-view-btn" data-bs-toggle="modal" data-bs-target="#modal<?= $p['idProducto'] ?>">
                                        <i class="bi bi-arrows-fullscreen"></i>
                                    </button>

                                    <div class="img-container">
                                        <a href="detalle.php?id=<?= $p['idProducto'] ?>">
                                            <img src="imagenes/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                                        </a>
                                        <button class="add-to-cart-overlay btn-agregar" data-id="<?= $p['idProducto'] ?>">
                                            AÑADIR AL CARRITO
                                        </button>
                                    </div>

                                    <div class="product-info">
                                        <p class="text-uppercase fw-bold text-primary mb-1" style="font-size: 0.65rem;">
                                            <?= htmlspecialchars($p['categoria']['nombreCat']) ?>
                                        </p>
                                        <h3 class="product-name"><?= htmlspecialchars($p['nombre']) ?></h3>
                                        <p class="product-description-short"><?= htmlspecialchars($p['descripcion']) ?></p>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="price-tag">$<?= number_format((float)$p['precioVenta'], 2) ?></span>
                                            <span class="text-success small fw-bold">
                                                <i class="bi bi-check-circle-fill me-1"></i>
                                                <?= $p['stock'] > 0 ? 'Stock' : 'Sin stock' ?>
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            </div>

                            <!-- Modal Quick View -->
                            <div class="modal fade" id="modal<?= $p['idProducto'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content border-0">
                                        <div class="modal-body p-0">
                                            <div class="row g-0">
                                                <div class="col-md-6 bg-light p-5 d-flex align-items-center">
                                                    <img src="imagenes/<?= htmlspecialchars($p['imagen']) ?>" class="img-fluid rounded shadow-sm" alt="<?= htmlspecialchars($p['nombre']) ?>">
                                                </div>
                                                <div class="col-md-6 p-4 p-lg-5">
                                                    <button type="button" class="btn-close float-end" data-bs-dismiss="modal"></button>
                                                    <span class="text-primary fw-bold small text-uppercase">
                                                        <?= htmlspecialchars($p['categoria']['nombreCat']) ?>
                                                    </span>
                                                    <h2 class="fw-bold mt-2 mb-3"><?= htmlspecialchars($p['nombre']) ?></h2>
                                                    <p class="text-muted mb-4"><?= htmlspecialchars($p['descripcion']) ?></p>
                                                    <div class="bg-light p-3 rounded-3 mb-4">
                                                        <span class="h3 fw-extrabold text-dark">$<?= number_format((float)$p['precioVenta'], 2) ?></span>
                                                    </div>
                                                    <div class="d-grid">
                                                        <button class="btn btn-dark btn-lg py-3 fw-bold rounded-3 btn-agregar" data-id="<?= $p['idProducto'] ?>">
                                                            <i class="bi bi-cart3 me-2"></i>Añadir al Carrito
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">No se encontraron productos.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-agregar') || e.target.closest('.btn-agregar')) {
                e.preventDefault();

                const btn = e.target.classList.contains('btn-agregar') ? e.target : e.target.closest('.btn-agregar');
                const id = btn.getAttribute('data-id');
                const toast = document.getElementById('cart-toast');

                fetch(`controllers/carritoController.php?accion=agregar&id=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            toast.style.display = 'block';
                            setTimeout(() => {
                                toast.style.display = 'none';
                            }, 2500);

                            const cartBadge = document.querySelector('.badge-carrito');
                            if (cartBadge) {
                                cartBadge.innerText = data.cart_count;
                                cartBadge.style.display = data.cart_count > 0 ? 'inline-block' : 'none';
                            }
                        }
                    })
                    .catch(err => console.error("Error:", err));
            }
        });
    </script>
</body>

</html>