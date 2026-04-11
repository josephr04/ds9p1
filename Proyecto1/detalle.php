<?php
/**
 * PÁGINA DE DETALLE DE PRODUCTO
 * Ubicación: /detalle.php
 */
session_start();
require_once "config/conexion.php";
include "includes/head.php";

// 1. Obtener y validar el ID del producto desde la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: store.php");
    exit;
}

$idProducto = (int)$_GET['id'];

// 2. Consultar la información del producto y su categoría
$query = "SELECT p.*, c.nombreCat 
          FROM productos p 
          LEFT JOIN categoria c ON p.idCategoria = c.idCategoria 
          WHERE p.idProducto = $idProducto 
          LIMIT 1";

$resultado = $conexion->query($query);

// Si el producto no existe, redirigir a la tienda
if ($resultado->num_rows == 0) {
    header("Location: store.php");
    exit;
}

$p = $resultado->fetch_object();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $p->nombre ?> | High Fidelity Detail</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary: #4f46e5;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #ffffff;
            color: var(--text-dark);
            padding-top: 10px;
        }

        .breadcrumb-item a { text-decoration: none; color: var(--text-muted); font-size: 0.9rem; }

        .product-image-container {
            background-color: var(--bg-light);
            border-radius: 24px;
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 500px;
        }

        .product-image-container img {
            max-width: 100%;
            height: auto;
            mix-blend-mode: multiply; 
            transition: transform 0.5s ease;
        }

        .product-image-container:hover img { transform: scale(1.05); }

        .category-badge {
            display: inline-block;
            padding: 6px 16px;
            background-color: #e0e7ff;
            color: var(--primary);
            border-radius: 100px;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
        }

        .product-title { font-size: 2.5rem; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 1rem; }
        .product-price { font-size: 2rem; font-weight: 700; color: var(--primary); margin-bottom: 1.5rem; }
        .product-description { font-size: 1.1rem; line-height: 1.8; color: var(--text-muted); margin-bottom: 2rem; }

        .qty-input-group {
            display: flex;
            align-items: center;
            background: var(--bg-light);
            border-radius: 12px;
            width: fit-content;
            padding: 5px;
            border: 1px solid #e2e8f0;
        }

        .qty-btn {
            border: none;
            background: white;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            font-weight: bold;
            color: var(--text-dark);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            cursor: pointer;
        }

        .qty-display { width: 50px; text-align: center; font-weight: 700; border: none; background: transparent; }

        .btn-add-cart {
            background-color: var(--text-dark);
            color: white;
            padding: 16px 32px;
            border-radius: 14px;
            font-weight: 700;
            border: none;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-add-cart:hover {
            background-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px rgba(79, 70, 229, 0.5);
        }

        .stock-info { font-size: 0.9rem; color: #10b981; font-weight: 600; }

        /* Toast Estilo Tienda */
        #cart-toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            display: none;
            background: #0f172a;
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>

<body>

    <div id="cart-toast">
        <i class="bi bi-check-circle-fill me-2 text-success"></i> Producto añadido al carrito
    </div>

    <div class="container mb-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="store.php">Tienda</a></li>
                <li class="breadcrumb-item"><a href="store.php?cat=<?= $p->idCategoria ?>"><?= $p->nombreCat ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= $p->nombre ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <div class="col-lg-6">
                <div class="product-image-container shadow-sm">
                    <img src="imagenes/<?= $p->imagen ?>" id="main-product-img" alt="<?= $p->nombre ?>">
                </div>
            </div>

            <div class="col-lg-6">
                <div class="ps-lg-4">
                    <span class="category-badge"><?= $p->nombreCat ?></span>
                    <h1 class="product-title"><?= $p->nombre ?></h1>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="text-warning me-2">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
                        </div>
                        <span class="text-muted small">(4.5/5 de 12 opiniones)</span>
                    </div>

                    <div class="product-price">$<?= number_format($p->precioVenta, 2) ?></div>
                    
                    <p class="product-description"><?= $p->descripcion ?></p>

                    <div class="stock-info mb-4">
                        <i class="bi bi-check2-circle me-1"></i> En stock - Disponible para entrega inmediata
                    </div>

                    <hr class="my-4">

                    <div class="row align-items-center">
                        <div class="col-sm-4 mb-3">
                            <label class="form-label small fw-bold text-uppercase">Cantidad</label>
                            <div class="qty-input-group">
                                <button type="button" class="qty-btn" onclick="updateQty(-1)">-</button>
                                <input type="text" id="product-qty" class="qty-display" value="1" readonly>
                                <button type="button" class="qty-btn" onclick="updateQty(1)">+</button>
                            </div>
                        </div>
                        <div class="col-sm-8 mb-3">
                            <label class="form-label d-none d-sm-block">&nbsp;</label>
                            <button type="button" class="btn-add-cart btn-agregar" data-id="<?= $p->idProducto ?>">
                                <i class="bi bi-cart-plus me-2"></i> Añadir al Carrito
                            </button>
                        </div>
                    </div>

                    <div class="row mt-5 g-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-truck fs-4 me-2 text-primary"></i>
                                <span class="small fw-semibold">Envío Gratis</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-shield-check fs-4 me-2 text-primary"></i>
                                <span class="small fw-semibold">Garantía 1 Año</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="bg-light py-5 mt-5">
        <div class="container">
            <h4 class="fw-bold mb-4">Especificaciones Técnicas</h4>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted fw-bold">Categoría:</td>
                            <td><?= $p->nombreCat ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-bold">Modelo:</td>
                            <td>HF-<?= $p->idProducto ?>-2026</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-5 bg-white border-top text-center mt-5">
        <p class="text-muted small mb-0">&copy; 2026 SellFlow High-Fidelity. All Rights Reserved.</p>
    </footer>

    <script>
        // Función para los botones + y -
        function updateQty(val) {
            const qtyInput = document.getElementById('product-qty');
            let current = parseInt(qtyInput.value);
            current += val;
            if (current < 1) current = 1;
            qtyInput.value = current;
        }

        // Lógica AJAX (Igual que en store.php)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-agregar');
            
            if (btn) {
                e.preventDefault();
                const id = btn.getAttribute('data-id');
                const cantidad = document.getElementById('product-qty').value; 
                const toast = document.getElementById('cart-toast');

                // Enviamos ID y CANTIDAD al controlador
                fetch(`controllers/carritoController.php?accion=agregar&id=${id}&cantidad=${cantidad}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // 1. Mostrar notificación
                            if(toast) {
                                toast.style.display = 'block';
                                setTimeout(() => { toast.style.display = 'none'; }, 2000);
                            }

                            // 2. Actualizar el badge en el head.php
                            const cartBadge = document.querySelector('.badge-carrito');
                            if (cartBadge) {
                                cartBadge.innerText = data.cart_count;
                                cartBadge.style.display = 'block';
                                
                                // Animación de feedback
                                cartBadge.style.transition = "transform 0.2s ease";
                                cartBadge.style.transform = "translate(-50%, -50%) scale(1.4)";
                                setTimeout(() => {
                                    cartBadge.style.transform = "translate(-50%, -50%) scale(1)";
                                }, 200);
                            }
                        }
                    })
                    .catch(err => console.error("Error:", err));
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>