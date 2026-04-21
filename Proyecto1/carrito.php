<?php
session_start();
include "includes/head.php";

$subtotal       = 0;
$envio          = 0.00;
$impuestos_tasa = 0.07;

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}

$impuestos   = $subtotal * $impuestos_tasa;
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

        .cart-title { font-weight: 700; font-size: 1.8rem; }

        .table-header {
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        /* ── Tarjeta de producto ── */
        .cart-item-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
        }

        .product-img {
            width: 72px;
            height: 72px;
            object-fit: contain;
            border-radius: 6px;
            background: #f5f5f5;
            padding: 4px;
            flex-shrink: 0;
        }

        .brand-text {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.04em;
        }

        .product-name {
            font-weight: 600;
            font-size: 0.9rem;
            margin: 2px 0 0;
            color: #111;
        }

        /* ── Control de cantidad ── */
        .qty-wrapper {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            overflow: hidden;
        }

        .qty-btn {
            background: #f5f5f5;
            border: none;
            padding: 4px 12px;
            font-size: 1rem;
            color: #555;
            cursor: pointer;
            transition: background 0.15s;
            line-height: 1.6;
        }

        .qty-btn:hover { background: #ffe5e2; color: var(--primary-red); }

        .qty-input {
            width: 36px;
            text-align: center;
            border: none;
            border-left: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            font-weight: 600;
            font-size: 0.9rem;
            background: transparent;
            padding: 4px 0;
        }

        .price-text   { font-weight: 600; color: var(--primary-red); white-space: nowrap; }
        .subtotal-text { font-weight: 700; white-space: nowrap; }

        .btn-delete {
            color: #bbb;
            background: none;
            border: none;
            font-size: 1rem;
            cursor: pointer;
            transition: color 0.2s;
            padding: 4px 6px;
        }
        .btn-delete:hover { color: var(--primary-red); }

        /* ── Resumen ── */
        .summary-card {
            background: var(--summary-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            position: sticky;
            top: 80px;
        }

        .summary-title { font-weight: 700; font-size: 1.2rem; margin-bottom: 1.5rem; }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .total-row {
            border-top: 2px solid var(--border-color);
            margin-top: 1.25rem;
            padding-top: 1rem;
            font-weight: 700;
            font-size: 1.2rem;
            display: flex;
            justify-content: space-between;
        }

        .btn-proceed {
            background: var(--primary-red);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 13px;
            width: 100%;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 1.25rem;
            transition: opacity 0.2s;
        }
        .btn-proceed:hover { opacity: 0.9; }

        .payment-methods {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 14px;
            flex-wrap: wrap;
        }
        .payment-methods img { height: 22px; opacity: 0.75; }
    </style>
</head>
<body>

<header class="header-cart">
    <div class="container">
        <h1 class="cart-title">
            Carrito
            <small class="text-muted fw-normal fs-6" id="header-qty">
                <?= count($_SESSION['carrito']) ?> artículo(s)
            </small>
        </h1>
    </div>
</header>

<div class="container py-2 mb-5">
    <div class="row g-4">

        <!-- ── Lista de productos ── -->
        <div class="col-lg-8" id="cart-container">

            <!-- Cabecera de columnas (solo desktop) -->
            <div class="row table-header d-none d-lg-flex px-2">
                <div class="col-5">Artículo</div>
                <div class="col-2 text-center">Cantidad</div>
                <div class="col-2 text-center">Precio</div>
                <div class="col-2 text-center">Subtotal</div>
                <div class="col-1"></div>
            </div>

            <?php if (empty($_SESSION['carrito'])): ?>
                <div class="text-center py-5 border rounded">
                    <i class="bi bi-cart-x display-4 text-muted"></i>
                    <p class="mt-3 text-muted">No hay artículos en el carrito.</p>
                    <a href="store.php" class="btn btn-outline-danger mt-2">Volver a la tienda</a>
                </div>
            <?php else: ?>
                <?php foreach ($_SESSION['carrito'] as $id => $item): ?>
                <div class="cart-item-card" id="item-row-<?= $id ?>" data-precio="<?= $item['precio'] ?>">
                    <div class="row align-items-center gy-3">

                        <!-- Imagen + nombre -->
                        <div class="col-lg-5 d-flex align-items-center gap-3">
                            <img src="imagenes/<?= htmlspecialchars($item['imagen']) ?>"
                                 class="product-img" alt="Producto">
                            <div>
                                <div class="brand-text">SKU: <?= $id ?></div>
                                <p class="product-name"><?= htmlspecialchars($item['nombre']) ?></p>
                            </div>
                        </div>

                        <!-- Cantidad -->
                        <div class="col-6 col-lg-2 d-flex justify-content-lg-center">
                            <div class="qty-wrapper">
                                <button class="qty-btn btn-update" data-id="<?= $id ?>" data-action="restar">−</button>
                                <input type="text" class="qty-input" id="qty-<?= $id ?>"
                                       value="<?= $item['cantidad'] ?>" readonly>
                                <button class="qty-btn btn-update" data-id="<?= $id ?>" data-action="sumar">+</button>
                            </div>
                        </div>

                        <!-- Precio unitario -->
                        <div class="col-6 col-lg-2 text-center">
                            <span class="price-text">$<?= number_format($item['precio'], 2) ?></span>
                        </div>

                        <!-- Subtotal fila -->
                        <div class="col-6 col-lg-2 text-center">
                            <span class="subtotal-text" id="subtotal-item-<?= $id ?>">
                                $<?= number_format($item['precio'] * $item['cantidad'], 2) ?>
                            </span>
                        </div>

                        <!-- Eliminar -->
                        <div class="col-6 col-lg-1 d-flex justify-content-end justify-content-lg-center">
                            <button class="btn-delete btn-update" data-id="<?= $id ?>" data-action="eliminar"
                                    title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ── Resumen ── -->
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

                <div class="total-row">
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

            <?php if (!empty($_SESSION['carrito'])): ?>
                <div class="text-center mt-3">
                    <a href="controllers/carritoController.php?accion=vaciar&id=0"
                       class="text-muted text-decoration-none small">
                        <i class="bi bi-x-circle"></i> Vaciar carrito
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
const ITBMS_TASA = 0.07;

/** Formatea número a 2 decimales */
function fmt(val) {
    const n = parseFloat(val);
    if (isNaN(n)) return '0.00';
    return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/**
 * Recalcula subtotal, ITBMS y total leyendo directo del DOM.
 * También sincroniza el badge del navbar y el contador del header.
 */
function recalcularTotales() {
    let subtotal = 0;
    let count    = 0;

    document.querySelectorAll('.cart-item-card').forEach(card => {
        const precio   = parseFloat(card.dataset.precio) || 0;
        const cantidad = parseInt(card.querySelector('.qty-input')?.value) || 0;
        subtotal += precio * cantidad;
        count    += cantidad;
    });

    const impuestos = subtotal * ITBMS_TASA;
    const total     = subtotal + impuestos;

    // Actualizar resumen
    document.getElementById('resumen-subtotal').innerText  = `$${fmt(subtotal)}`;
    document.getElementById('resumen-impuestos').innerText = `$${fmt(impuestos)}`;
    document.getElementById('resumen-total').innerText     = `$${fmt(total)}`;

    // Actualizar contador del título de la página
    document.getElementById('header-qty').innerText = `${count} artículo(s)`;

    const badge = document.querySelector('.badge-carrito');
    if (badge) {
        badge.innerText = count;
        badge.style.display = count > 0 ? 'block' : 'none';
    }
}

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-update');
    if (!btn) return;
    e.preventDefault();

    const id     = btn.dataset.id;
    const action = btn.dataset.action;
    const url    = `controllers/carritoController.php?accion=${action}&id=${id}`;

    fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } })
        .then(res => res.json())
        .then(data => {
            if (data.status !== 'success') return;

            const itemCantidad = parseInt(data.item_cantidad) || 0;

            if (itemCantidad === 0 || action === 'eliminar') {
                const row = document.getElementById(`item-row-${id}`);
                if (row) row.remove();

                if (document.querySelectorAll('.cart-item-card').length === 0) {
                    location.reload();
                    return;
                }
            } else {
                // Actualizar cantidad del input
                const input = document.getElementById(`qty-${id}`);
                if (input) input.value = itemCantidad;

                // Actualizar subtotal de la fila
                const card   = document.getElementById(`item-row-${id}`);
                const precio = parseFloat(card?.dataset.precio) || 0;
                const rowSub = document.getElementById(`subtotal-item-${id}`);
                if (rowSub) rowSub.innerText = `$${fmt(precio * itemCantidad)}`;
            }

            // Siempre recalcular todo desde el DOM (incluye badge del navbar)
            recalcularTotales();
        })
        .catch(err => console.error('Error en carrito:', err));
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>