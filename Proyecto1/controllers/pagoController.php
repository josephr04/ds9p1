<?php
session_start();
require_once "../config/conexion.php";

if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    $_SESSION['checkout_message'] = 'El carrito está vacío.';
    $_SESSION['checkout_message_type'] = 'error';
    header('Location: ../checkout.php');
    exit;
}

$tipo         = trim($_POST['tipo'] ?? '');
$digitos      = trim($_POST['digitos'] ?? '');
$fechaVence   = trim($_POST['fechaVence'] ?? '');
$codSeguridad = trim($_POST['codSeguridad'] ?? '');

// 1. Normalizar Dígitos: Quitar espacios para que quede "4532123456789012"
$digitos = preg_replace('/\D+/', '', $digitos);

// 2. Normalizar Fecha: El input MM/AA se convierte a YYYY-MM-01 para coincidir con la BD
$fechaFormateada = '';
if (preg_match('/^(\d{2})\/(\d{2})$/', $fechaVence, $m)) {
    $mes  = $m[1];
    $anio = '20' . $m[2]; // "28" → "2028"
    $fechaFormateada = "$anio-$mes-01";
}

// Calcular total
$subtotal = 0;
foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$subtotal = round($subtotal, 2);
$total = round($subtotal * 1.07, 2);

// 3. Consulta corregida: Tabla "Tarjetas" (en plural)
$stmt = $conexion->prepare(
    "SELECT idTarjeta, tipo, saldo, saldoMaximo FROM `tarjeta` WHERE tipo = ? AND digitos = ? AND fechaVence = ? AND codSeguridad = ? LIMIT 1"
);
$stmt->bind_param('ssss', $tipo, $digitos, $fechaFormateada, $codSeguridad);
$stmt->execute();
$result = $stmt->get_result();
$card = $result->fetch_assoc();
$stmt->close();

if (!$card) {
    $_SESSION['checkout_message'] = 'Datos de tarjeta incorrectos. Revisa el tipo (Débito/Crédito) y la fecha.';
    $_SESSION['checkout_message_type'] = 'error';
    header('Location: ../checkout.php');
    exit;
}

// 4. Lógica de Saldo según el tipo de tarjeta
$pagoAprobado = false;
$nuevoSaldo = 0;

if ($card['tipo'] === 'Débito') {
    if ((float)$card['saldo'] >= $total) {
        $nuevoSaldo = (float)$card['saldo'] - $total;
        $pagoAprobado = true;
    } else {
        $_SESSION['checkout_message'] = 'Saldo insuficiente.';
    }
} else { // Crédito
    // En crédito, el saldo suele ser lo consumido. No debe pasar el saldoMaximo.
    if (((float)$card['saldo'] + $total) <= (float)$card['saldoMaximo']) {
        $nuevoSaldo = (float)$card['saldo'] + $total;
        $pagoAprobado = true;
    } else {
        $_SESSION['checkout_message'] = 'Límite de crédito excedido.';
    }
}

if ($pagoAprobado) {
    // 1. Actualizar saldo de tarjeta
    $updateStmt = $conexion->prepare("UPDATE `tarjeta` SET saldo = ? WHERE idTarjeta = ?");
    $updateStmt->bind_param('di', $nuevoSaldo, $card['idTarjeta']);
    $updateStmt->execute();
    $updateStmt->close();

    // 2. Calcular impuestos y crear factura
    $impuestos = round($subtotal * 0.07, 2);
    $totalFactura = round($subtotal + $impuestos, 2);

    $facturaStmt = $conexion->prepare(
        "INSERT INTO factura (idTarjeta, subtotal, itbms, total) VALUES (?, ?, ?, ?)"
    );
    $facturaStmt->bind_param('iddd', $card['idTarjeta'], $subtotal, $impuestos, $totalFactura);
    $facturaStmt->execute();
    $idFactura = $facturaStmt->insert_id;
    $facturaStmt->close();

    // 3. Guardar detalles de cada producto
    $detalleStmt = $conexion->prepare(
        "INSERT INTO factura_detalle (idFactura, idProducto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)"
    );

    foreach ($_SESSION['carrito'] as $idProducto => $item) {
        $cantidad = $item['cantidad'];
        $precio = round($item['precio'], 2);
        $detalleStmt->bind_param('isid', $idFactura, $idProducto, $cantidad, $precio);
        $detalleStmt->execute();
    }
    $detalleStmt->close();

    // 4. Guardar ID de factura en sesión
    $_SESSION['idFacturaGenerada'] = $idFactura;
    $_SESSION['carrito'] = [];
    $_SESSION['checkout_message'] = 'Pago aprobado exitosamente.';
    $_SESSION['checkout_message_type'] = 'success';

    header('Location: ../pago_exitoso.php');
    exit;
} else {
    $_SESSION['checkout_message_type'] = 'error';
    header('Location: ../checkout.php');
    exit;
}