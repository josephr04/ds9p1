<?php
session_start();
require_once "config/conexion.php";
include "includes/head.php";

// Inicializar variables de cálculo
$subtotal = 0;
$envio = 0.00; 
$impuestos_tasa = 0.07; 

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}

$impuestos = $subtotal * $impuestos_tasa;
$total_final = $subtotal + $envio + $impuestos;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --bg-body: #ffffff;
            --text-dark: #333333;
            --text-muted: #777777;
            --primary-red: #db3c2b;
            --border-color: #e5e5e5;
            --summary-bg: #f9f9f9;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
        }

        .header-cart {
            border-bottom: 4px solid var(--primary-red);
            padding: 20px 0;
            margin-bottom: 30px;
        }

        .cart-title {
            font-weight: 700;
            font-size: 1.8rem;
        }

        .table-header {
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .cart-item-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .product-info-grid {
            display: grid;
            grid-template-columns: 100px 1fr 150px 100px 100px 40px;
            align-items: center;
            gap: 15px;
        }

        .product-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .brand-text {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }

        .product-title {
            font-weight: 600;
            font-size: 0.95rem;
            margin: 0;
            color: #000;
        }

        .qty-wrapper {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            width: fit-content;
        }
        .qty-btn {
            background: none;
            border: none;
            padding: 5px 12px;
            font-size: 1.2rem;
            color: #999;
            transition: 0.2s;
            cursor: pointer;
        }
        .qty-btn:hover { color: var(--primary-red); }
        .qty-input {
            width: 40px;
            text-align: center;
            border: none;
            font-weight: 600;
            background: transparent;
        }

        .price-text { font-weight: 600; color: var(--primary-red); }
        .subtotal-text { font-weight: 600; }

        .btn-delete {
            color: #999;
            background: none;
            border: none;
            font-size: 1.1rem;
            cursor: pointer;
        }
        .btn-delete:hover { color: var(--primary-red); }

        .summary-card {
            background: var(--summary-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
        }

        .summary-title { font-weight: 700; font-size: 1.2rem; margin-bottom: 1.5rem; }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            font-size: 0.95rem;
        }

        .total-row {
            border-top: 2px solid var(--border-color);
            margin-top: 1.5rem;
            padding-top: 1rem;
            font-weight: 700;
            font-size: 1.3rem;
        }

        .btn-proceed {
            background: var(--primary-red);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px;
            width: 100%;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 1.5rem;
        }

        .payment-methods {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .payment-methods img { height: 25px; opacity: 0.8; }
    </style>
</head>
<body>

<header class="header-cart">
    <div class="container">
        <h1 class="cart-title">Carrito <small class="text-muted fw-normal fs-6" id="header-qty"><?= count($_SESSION['carrito']) ?> artículo(s)</small></h1>
    </div>
</header>

<div class="container py-2">
    <div class="row g-4">
        <div class="col-lg-8" id="cart-container">
            <div class="row table-header d-none d-lg-flex">
                <div class="col-6">Artículos</div>
                <div class="col-2 text-center">Cantidad</div>
                <div class="col-2 text-center">Precio</div>
                <div class="col-2 text-center">Subtotal</div>
            </div>

            <?php if (empty($_SESSION['carrito'])): ?>
                <div class="text-center py-5 border rounded">
                    <i class="bi bi-cart-x display-4 text-muted"></i>
                    <p class="mt-3">No hay artículos en el carrito.</p>
                    <a href="store.php" class="btn btn-outline-danger">Volver a la tienda</a>
                </div>
            <?php else: ?>
                <?php foreach($_SESSION['carrito'] as $id => $item): ?>
                <div class="cart-item-card" id="item-row-<?= $id ?>">
                    <div class="product-info-grid">
                        <img src="imagenes/<?= $item['imagen'] ?>" class="product-img" alt="Producto">
                        
                        <div>
                            <span class="brand-text">MARCA | SKU: <?= $id ?></span>
                            <h3 class="product-title"><?= $item['nombre'] ?></h3>
                        </div>

                        <div class="qty-wrapper">
                            <button class="qty-btn btn-update" data-id="<?= $id ?>" data-action="restar">-</button>
                            <input type="text" class="qty-input" id="qty-<?= $id ?>" value="<?= $item['cantidad'] ?>" readonly>
                            <button class="qty-btn btn-update" data-id="<?= $id ?>" data-action="sumar">+</button>
                        </div>

                        <div class="text-center">
                            <span class="price-text">$<?= number_format($item['precio'], 2) ?></span>
                        </div>

                        <div class="text-center">
                            <span class="subtotal-text" id="subtotal-item-<?= $id ?>">$<?= number_format($item['precio'] * $item['cantidad'], 2) ?></span>
                        </div>

                        <div class="text-end">
                            <button class="btn-delete btn-update" data-id="<?= $id ?>" data-action="eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="summary-card">
                <h2 class="summary-title">Resumen</h2>
                
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="resumen-subtotal">$<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Envío</span>
                    <span class="text-muted small">Calculado en el pago</span>
                </div>
                <div class="summary-row">
                    <span>ITBMS (7%)</span>
                    <span id="resumen-impuestos">$<?= number_format($impuestos, 2) ?></span>
                </div>

                <div class="total-row d-flex justify-content-between">
                    <span>Total</span>
                    <span id="resumen-total">$<?= number_format($total_final, 2) ?></span>
                </div>

                <button class="btn-proceed" onclick="location.href='checkout.php'">
                    Proceder al pago
                </button>

                <div class="payment-methods">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal">
                    <span class="badge bg-light text-dark border">Visa</span>
                    <span class="badge bg-light text-dark border">ACH</span>
                </div>
            </div>
            
            <?php if(!empty($_SESSION['carrito'])): ?>
                <div class="text-center mt-3">
                    <a href="controllers/carritoController.php?accion=vaciar&id=0" class="text-muted text-decoration-none small"><i class="bi bi-x-circle"></i> Vaciar carrito</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<script>
// Usamos delegación de eventos para mayor eficiencia
document.addEventListener('click', function (e) {
    if (e.target.closest('.btn-update')) {
        e.preventDefault();
        
        const btn = e.target.closest('.btn-update');
        const id = btn.dataset.id;
        const action = btn.dataset.action;
        const url = `controllers/carritoController.php?accion=${action}&id=${id}`;

        fetch(url, {
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // 1. Si el producto se eliminó o llegó a 0
                if (data.item_cantidad === 0 || action === 'eliminar') {
                    const row = document.getElementById(`item-row-${id}`);
                    if(row) row.remove();
                    if (data.cart_count === 0) location.reload(); // Recarga si el carrito queda vacío
                } else {
                    // 2. Actualizar cantidad e input
                    const input = document.getElementById(`qty-${id}`);
                    if(input) input.value = data.item_cantidad;
                    
                    // 3. Actualizar subtotal de la fila
                    const rowSub = document.getElementById(`subtotal-item-${id}`);
                    if(rowSub) rowSub.innerText = `$${data.item_subtotal}`;
                }

                // 4. Actualizar Totales del Resumen y Header
                document.getElementById('resumen-subtotal').innerText = `$${data.cart_subtotal}`;
                document.getElementById('resumen-impuestos').innerText = `$${data.cart_impuestos}`;
                document.getElementById('resumen-total').innerText = `$${data.cart_total}`;
                document.getElementById('header-qty').innerText = `${data.cart_count} artículo(s)`;
            }
        })
        .catch(error => console.error('Error:', error));
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>