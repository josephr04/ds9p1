<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$response = ['status' => 'error'];

if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    $id     = isset($_GET['id'])       ? $_GET['id']       : 0;
    $extra  = isset($_GET['cantidad']) ? (int)$_GET['cantidad'] : 1;

    $apiBase = "http://127.0.0.1:8000/api";

    switch ($accion) {

        case 'agregar':
            if (isset($_SESSION['carrito'][$id])) {
                $_SESSION['carrito'][$id]['cantidad'] += $extra;
            } else {
                $json = @file_get_contents("$apiBase/productos/$id");
                $prod = $json ? json_decode($json, true) : null;

                if ($prod && !isset($prod['error'])) {
                    $_SESSION['carrito'][$id] = [
                        'nombre'   => $prod['nombre'],
                        'precio'   => (float)$prod['precioVenta'],
                        'imagen'   => $prod['imagen'],
                        'cantidad' => $extra
                    ];
                }
            }
            break;

        case 'sumar':
            if (isset($_SESSION['carrito'][$id])) {
                $_SESSION['carrito'][$id]['cantidad']++;
            }
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

    // Recalcular totales
    $subtotal    = 0;
    $totalPiezas = 0;

    foreach ($_SESSION['carrito'] as $item) {
        $subtotal    += $item['precio'] * $item['cantidad'];
        $totalPiezas += $item['cantidad'];
    }

    $impuestos = $subtotal * 0.07;
    $total     = $subtotal + $impuestos;

    // Subtotal e item_subtotal del producto afectado
    $itemCantidad  = $_SESSION['carrito'][$id]['cantidad'] ?? 0;
    $itemPrecio    = $_SESSION['carrito'][$id]['precio']   ?? 0;
    $itemSubtotal  = $itemPrecio * $itemCantidad;

    $response = [
        'status'        => 'success',
        // Datos del item afectado
        'item_cantidad' => $itemCantidad,
        'item_subtotal' => number_format($itemSubtotal, 2, '.', ''),
        // Totales generales del carrito
        'cart_subtotal' => number_format($subtotal, 2, '.', ''),
        'cart_impuestos'=> number_format($impuestos, 2, '.', ''),
        'cart_total'    => number_format($total, 2, '.', ''),
        'cart_count'    => $totalPiezas
    ];
}

echo json_encode($response);
exit;