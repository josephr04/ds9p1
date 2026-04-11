<?php
session_start();
header('Content-Type: application/json');
require_once "../config/conexion.php"; 

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$response = ['status' => 'error'];

if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    switch ($accion) {
        case 'agregar':
            if (isset($_SESSION['carrito'][$id])) {
                $_SESSION['carrito'][$id]['cantidad']++;
            } else {
                $res = $conexion->query("SELECT nombre, precioVenta, imagen FROM productos WHERE idProducto = $id");
                if ($reg = $res->fetch_object()) {
                    $_SESSION['carrito'][$id] = [
                        'nombre' => $reg->nombre,
                        'precio' => $reg->precioVenta,
                        'imagen' => $reg->imagen,
                        'cantidad' => 1
                    ];
                }
            }
            break;

        case 'sumar':
            if (isset($_SESSION['carrito'][$id])) $_SESSION['carrito'][$id]['cantidad']++;
            break;

        case 'restar':
            if (isset($_SESSION['carrito'][$id]) && $_SESSION['carrito'][$id]['cantidad'] > 1) {
                $_SESSION['carrito'][$id]['cantidad']--;
            }
            break;

        case 'eliminar':
            unset($_SESSION['carrito'][$id]);
            break;

        case 'vaciar':
            $_SESSION['carrito'] = [];
            break;
    }

    // --- Recalcular Totales ---
    $subtotal = 0;
    $totalPiezas = 0; 
    foreach ($_SESSION['carrito'] as $item) {
        $subtotal += $item['precio'] * $item['cantidad'];
        $totalPiezas += $item['cantidad']; 
    }
    
    $impuestos = $subtotal * 0.07;
    $total = $subtotal + $impuestos;

    $response = [
        'status' => 'success',
        'item_cantidad' => $_SESSION['carrito'][$id]['cantidad'] ?? 0,
        'cart_subtotal' => number_format($subtotal, 2),
        'cart_total' => number_format($total, 2),
        'cart_count' => $totalPiezas 
    ];
}

echo json_encode($response);
exit;