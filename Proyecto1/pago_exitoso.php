<?php
session_start();
require_once "config/conexion.php";

// Solo accesible si hubo un pago aprobado
if (($_SESSION['checkout_message_type'] ?? '') !== 'success') {
    header('Location: store.php');
    exit;
}

$message = $_SESSION['checkout_message'] ?? 'Pago realizado.';
$idFactura = $_SESSION['idFacturaGenerada'] ?? null;

$factura = null;
$detalles = [];

if ($idFactura) {
    $facturaStmt = $conexion->prepare(
        "SELECT idFactura, subtotal, itbms, total FROM factura WHERE idFactura = ?"
    );
    $facturaStmt->bind_param('i', $idFactura);
    $facturaStmt->execute();
    $factura = $facturaStmt->get_result()->fetch_assoc();
    $facturaStmt->close();

    if ($factura) {
        $detalleStmt = $conexion->prepare(
            "SELECT fd.cantidad, fd.precio_unitario, p.descripcion 
             FROM factura_detalle fd 
             LEFT JOIN productos p ON fd.idProducto = p.idProducto 
             WHERE fd.idFactura = ?"
        );
        $detalleStmt->bind_param('i', $idFactura);
        $detalleStmt->execute();
        $detalles = $detalleStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $detalleStmt->close();
    }
}

unset($_SESSION['checkout_message'], $_SESSION['checkout_message_type'], $_SESSION['idFacturaGenerada']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Exitoso</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(43, 57, 74, .10);
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }

        .checkmark-circle {
            width: 90px;
            height: 90px;
            background: #d1fae5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .checkmark-circle i {
            font-size: 2.8rem;
            color: #059669;
        }

        .success-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #111;
            margin-bottom: .5rem;
        }

        .success-subtitle {
            color: #6b7280;
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        .btn-store {
            padding: 12px 32px;
            font-weight: 600;
            border-radius: 10px;
        }

        .invoice-section {
            text-align: left;
            background: #f9fafb;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .invoice-header {
            font-weight: 600;
            color: #111;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .invoice-item {
            display: flex;
            justify-content: space-between;
            padding: .7rem 0;
            font-size: .95rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .invoice-item:last-of-type {
            border-bottom: none;
        }

        .invoice-totals {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #059669;
        }

        .invoice-total {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            color: #059669;
            font-size: 1.2rem;
        }

        .invoice-row {
            display: flex;
            justify-content: space-between;
            padding: .45rem 0;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="checkmark-circle">
            <i class="bi bi-check-lg"></i>
        </div>
        <h1 class="success-title">¡Pago aprobado!</h1>
        <p class="success-subtitle">
            Tu pedido ha sido procesado correctamente.<br>
            Gracias por tu compra.
        </p>

        <div class="alert alert-success d-flex align-items-center gap-2 text-start mb-4" role="alert">
            <i class="bi bi-shield-check fs-5"></i>
            <span><?= htmlspecialchars($message) ?></span>
        </div>

        <?php if ($factura): ?>
            <div class="invoice-section">
                <div class="invoice-header">
                    <i class="bi bi-receipt"></i> Factura #<?= $factura['idFactura'] ?>
                </div>

                <?php if (!empty($detalles)): ?>
                    <?php foreach ($detalles as $item): ?>
                        <div class="invoice-item">
                            <div>
                                <strong><?= htmlspecialchars($item['descripcion'] ?: 'Producto') ?></strong><br>
                                <small style="color: #6b7280;">
                                    <?= $item['cantidad'] ?> × $<?= number_format($item['precio_unitario'], 2) ?>
                                </small>
                            </div>
                            <div style="font-weight: 600;">
                                $<?= number_format($item['cantidad'] * $item['precio_unitario'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No se encontraron productos para esta factura.</p>
                <?php endif; ?>

                <div class="invoice-totals">
                    <div class="invoice-row">
                        <span>Subtotal:</span>
                        <span>$<?= number_format($factura['subtotal'], 2) ?></span>
                    </div>
                    <div class="invoice-row">
                        <span>ITBMS (7%):</span>
                        <span>$<?= number_format($factura['itbms'], 2) ?></span>
                    </div>
                    <div class="invoice-total">
                        <span>Total:</span>
                        <span>$<?= number_format($factura['total'], 2) ?></span>
                    </div>
                </div>

                <div style="font-size: .85rem; color: #6b7280; margin-top: 1rem;">
                    📅 Factura generada correctamente.
                </div>
            </div>
        <?php endif; ?>

        <a href="store.php" class="btn btn-danger btn-store w-100">
            <i class="bi bi-shop me-2"></i>Volver a la tienda
        </a>
    </div>
</body>
</html>