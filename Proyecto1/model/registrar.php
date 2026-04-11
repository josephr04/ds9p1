<?php
// 1. Errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Conexión (Ruta relativa correcta desde la carpeta model)
require_once "../config/conexion.php"; 

// 3. Verificamos si llegó el código (esto siempre llega si el form se envía)
if (isset($_POST['codigo'])) {
    
    // Captura de datos
    $codigo      = $_POST['codigo'];
    $nombre      = $_POST['nombre'];
    $unidad      = $_POST['unidad'];
    $categoria   = $_POST['categoria'];
    $marca       = $_POST['marca'];
    $precio      = $_POST['precio'];
    $venta       = $_POST['venta'];
    $descripcion = $_POST['descripcion'];

    // Manejo de la imagen
    $nombreImagen  = $_FILES["imagen"]["name"];
    $ruta_temporal = $_FILES["imagen"]["tmp_name"];
    
    // Ruta física para guardar
    $directorio_destino = "../imagenes/";
    
    // Validamos formato de imagen
    $extension = strtolower(pathinfo($nombreImagen, PATHINFO_EXTENSION));
    if ($extension == "jpg" || $extension == "jpeg" || $extension == "png") {

        // Intentar mover el archivo
        if (move_uploaded_file($ruta_temporal, $directorio_destino . $nombreImagen)) {
            
            // 4. Inserción con los nombres de columna de tu DB
            $query = "INSERT INTO productos 
                      (idProducto, nombre, unidad, descripcion, stock, precioCosto, precioVenta, imagen, idCategoria, idMarca) 
                      VALUES 
                      ('$codigo', '$nombre', '$unidad', '$descripcion', 0, '$precio', '$venta', '$nombreImagen', '$categoria', '$marca')";

            if ($conexion->query($query)) {
                // Éxito: volvemos al index con alerta verde
                header("Location: ../index.php?res=success");
                exit(); 
            } else {
                // Error de SQL (ej: código duplicado)
                header("Location: ../index.php?res=error&motivo=duplicado");
                exit();
            }

        } else {
            die("Error: No se pudo mover la imagen. Verifica permisos en la carpeta /imagenes/");
        }
    } else {
        header("Location: ../index.php?res=error&motivo=imagen");
        exit();
    }
} else {
    // Si entras aquí directo sin usar el formulario
    header("Location: ../index.php");
    exit();
}
?>