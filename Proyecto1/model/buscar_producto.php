<?php
require_once "conexion.php";

$codigo = $_GET['codigo'];

$stmt = $conexion->prepare("SELECT * FROM productos WHERE codigo = ?");
$stmt->bind_param("s", $codigo);
$stmt->execute();
$result = $stmt->get_result();

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
    echo json_encode(["existe" => false]);
}