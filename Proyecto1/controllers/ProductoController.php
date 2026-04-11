<?php
require_once "../model/Producto.php";

// 🔎 BUSCAR PRODUCTO (cuando escribes o escaneas)
if (isset($_GET['codigo'])) {

    $codigo = trim($_GET['codigo']);

    $result = Producto::buscar($codigo);

    if ($datos = $result->fetch_object()) {

        echo json_encode([
            "existe" => true,
            "nombre" => $datos->nombre,
            "unidad" => $datos->unidad,
            "precio" => $datos->precioCosto,
            "venta" => $datos->precioVenta,
            "descripcion" => $datos->descripcion
        ]);

    } else {
        echo json_encode([
            "existe" => false
        ]);
    }
}


// 💾 GUARDAR PRODUCTO (cuando envías el formulario)
if (isset($_POST['codigo'])) {

    Producto::guardar($_POST);

    header("Location: index.php");
}