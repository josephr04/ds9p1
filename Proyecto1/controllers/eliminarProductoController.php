<?php
require_once "../config/conexion.php";

$response = ['ok' => false, 'mensaje' => ''];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // 1. Opcional: Obtener nombre de imagen para borrar el archivo físico
    $queryImg = $conexion->query("SELECT imagen FROM productos WHERE idProducto = '$id'");
    $prod = $queryImg->fetch_object();

    // 2. Ejecutar eliminación
    $sql = $conexion->query("DELETE FROM productos WHERE idProducto = '$id'");

    if ($sql) {
        // 3. Borrar imagen de la carpeta si existe
        if ($prod && !empty($prod->imagen)) {
            $ruta = "../imagenes/" . $prod->imagen;
            if (file_exists($ruta)) {
                unlink($ruta);
            }
        }
        $response['ok'] = true;
    } else {
        $response['mensaje'] = "Error en la base de datos: " . $conexion->error;
    }
} else {
    $response['mensaje'] = "ID no proporcionado";
}

header('Content-Type: application/json');
echo json_encode($response);