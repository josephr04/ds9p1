<?php
session_start();
include "includes/head.php";

if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header('Location: store.php');
    exit;
}

$apiBase = "http://127.0.0.1:8000/api";
require_once "config/conexion.php";

foreach ($_SESSION['carrito'] as $id => &$item) {
    if (empty($item['descripcion'])) {
        $json = @file_get_contents("$apiBase/productos/$id");
        $prod = $json ? json_decode($json, true) : null;

        if ($prod && isset($prod['descripcion'])) {
            $item['descripcion'] = $prod['descripcion'];
        } else {
            $stmt = $conexion->prepare("SELECT descripcion FROM productos WHERE idProducto = ?");
            if ($stmt) {
                $stmt->bind_param('s', $id);
                $stmt->execute();
                $stmt->bind_result($descripcion);
                if ($stmt->fetch()) {
                    $item['descripcion'] = $descripcion;
                }
                $stmt->close();
            }
        }

        $_SESSION['carrito'][$id] = $item;
    }
}
unset($item);

$subtotal       = 0;
$impuestos_tasa = 0.07;

foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}

$impuestos   = $subtotal * $impuestos_tasa;
$total_final = $subtotal + $impuestos;

$message = $_SESSION['checkout_message'] ?? null;
$message_type = $_SESSION['checkout_message_type'] ?? 'success';
unset($_SESSION['checkout_message'], $_SESSION['checkout_message_type']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar Pedido</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Inter', sans-serif;
            color: #333;
        }

        .page-header {
            padding: 28px 0 18px;
        }

        .card-payment {
            border: none;
            border-radius: 16px;
            box-shadow: 0 18px 45px rgba(43, 57, 74, .08);
        }

        .card-payment .card-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
        }

        .btn-pay {
            width: 100%;
            padding: 14px;
            font-weight: 700;
        }

        .summary-box {
            border: 1px solid #dee2e6;
            border-radius: 14px;
            background: #fff;
            padding: 1.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .summary-total {
            font-size: 1.25rem;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="page-header text-center mb-4">
            <h1 class="h2">Pasarela de Pago</h1>
            <p class="text-muted mb-0">Ingresa los datos de tu tarjeta para completar el pedido.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type === 'error' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <div class="row gy-4">
            <!-- Pasarela de pago - Izquierda -->
            <div class="col-lg-5">
                <div class="card card-payment shadow-sm">
                    <div class="card-body">
                        <h2 class="h5 mb-4">Datos de la tarjeta</h2>
                        <form action="controllers/pagoController.php" method="post" autocomplete="off">
                            <div class="row g-3">
                                <!-- Sección del Select en checkout.php -->
                                <div class="col-md-12">
                                    <label for="tipo" class="form-label">Tipo de tarjeta</label>
                                    <select id="tipo" name="tipo" class="form-select" required>
                                        <option value="" disabled selected>Selecciona</option>
                                        <!-- Los valores deben ser idénticos a los de la imagen image_efaf5e.png -->
                                        <option value="Crédito">Tarjeta de crédito</option>
                                        <option value="Débito">Tarjeta de débito</option>
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label for="digitos" class="form-label">Número de tarjeta</label>
                                    <!-- El patrón acepta números y espacios, pero PHP los limpiará -->
                                    <input type="text" id="digitos" name="digitos" class="form-control" maxlength="19" placeholder="4532 1234 5678 9012" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="fechaVence" class="form-label">Fecha de vencimiento</label>
                                    <input type="text" id="fechaVence" name="fechaVence" class="form-control"
                                        maxlength="5" placeholder="MM/AA" pattern="\d{2}/\d{2}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="codSeguridad" class="form-label">Código CVV</label>
                                    <input type="text" id="codSeguridad" name="codSeguridad" class="form-control" maxlength="4" placeholder="123" pattern="[0-9]{3,4}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Monto a pagar</label>
                                    <input type="text" class="form-control" value="$<?= number_format($total_final, 2) ?>" readonly>
                                </div>
                            </div>

                            <div class="mt-4 d-grid">
                                <button type="submit" class="btn btn-danger btn-pay">Pagar ahora</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Productos seleccionados - Derecha -->
            <div class="col-lg-7">
                <div class="card card-payment shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="h5 mb-4">Productos en tu pedido</h2>
                        <?php foreach ($_SESSION['carrito'] as $id => $item): ?>
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                <img src="imagenes/<?= htmlspecialchars($item['imagen']) ?>"
                                    class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;" alt="Producto">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($item['nombre']) ?></h6>
                                    <p class="text-muted small mb-1"><?= htmlspecialchars($item['descripcion'] ?? '') ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Cantidad: <?= $item['cantidad'] ?> x $<?= number_format($item['precio'], 2) ?></span>
                                        <span class="fw-bold">$<?= number_format($item['precio'] * $item['cantidad'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Resumen de la orden -->
                <div class="summary-box">
                    <h3 class="h5 mb-3">Resumen de tu orden</h3>
                    <div class="summary-row"><span>Subtotal</span><strong>$<?= number_format($subtotal, 2) ?></strong></div>
                    <div class="summary-row"><span>ITBMS (7%)</span><strong>$<?= number_format($impuestos, 2) ?></strong></div>
                    <div class="summary-row summary-total"><span>Total a pagar</span><strong>$<?= number_format($total_final, 2) ?></strong></div>
                    <hr>
                    <p class="small text-muted">Se verificará la tarjeta contra la base de datos y se descontará el saldo si es válida. Asegúrate de que la tarjeta tenga suficiente saldo.</p>
                    <a href="carrito.php" class="btn btn-outline-secondary w-100">Volver al carrito</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Formatear número de tarjeta automáticamente
        document.getElementById('digitos').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = value.trim();
        });

        // Formatear fecha MM/AA automáticamente
        document.getElementById('fechaVence').addEventListener('input', function(e) {
            const input = e.target;
            let digits = input.value.replace(/\D/g, '');

            if (digits.length > 2) {
                input.value = digits.substring(0, 2) + '/' + digits.substring(2, 4);
            } else {
                input.value = digits;
            }
        });

        // Validación básica antes de enviar
        document.querySelector('form').addEventListener('submit', function(e) {
            const tipo = document.getElementById('tipo').value;
            const digitos = document.getElementById('digitos').value.replace(/\s/g, '');
            const fechaVence = document.getElementById('fechaVence').value;
            const codSeguridad = document.getElementById('codSeguridad').value;

            const partes = fechaVence.split('/');
            const fechaValida = partes.length === 2 && partes[0].length === 2 && partes[1].length === 2 
                                && parseInt(partes[0]) >= 1 && parseInt(partes[0]) <= 12;

            if (!tipo || digitos.length < 12 || !fechaValida || codSeguridad.length < 3) {
                e.preventDefault();
                if (!fechaValida) {
                    document.getElementById('fechaVence').classList.add('is-invalid');
                }
                alert('Por favor, completa todos los campos correctamente.');
            }
        });
    </script>
</body>

</html>