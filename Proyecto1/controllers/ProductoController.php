<?php
require_once "../model/Producto.php";

// 🔎 BUSCAR PRODUCTO (cuando escribes o escaneas)
if (isset($_GET['codigo'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    $codigo = trim($_GET['codigo']);

    $result = Producto::buscar($codigo);

    if ($datos = $result->fetch_object()) {

        echo json_encode([
            "existe" => true,
            "idProducto" => $datos->idProducto,
            "nombre" => $datos->nombre,
            "unidad" => $datos->unidad,
            "stock" => $datos->stock,
            "precio" => $datos->precioCosto,
            "venta" => $datos->precioVenta,
            "descripcion" => $datos->descripcion,
            "imagen" => $datos->imagen ?? null
        ]);

    } else {
        echo json_encode([
            "existe" => false,
            "mensaje" => "Producto no encontrado"
        ]);
    }
    exit();
}


// 💾 GUARDAR PRODUCTO (cuando envías el formulario)
if (isset($_POST['codigo'])) {

    Producto::guardar($_POST);

    header("Location: index.php");
}