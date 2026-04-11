<?php
require_once "../config/conexion.php";

class Producto {

    // 🔎 Buscar producto
    public static function buscar($codigo) {
        global $conexion;

        $stmt = $conexion->prepare("SELECT * FROM productos WHERE idProducto = ?");
        $stmt->bind_param("s", $codigo);
        $stmt->execute();

        return $stmt->get_result();
    }

    // 💾 Guardar producto
    public static function guardar($data) {
        global $conexion;

        $stmt = $conexion->prepare("INSERT INTO productos(idProducto, nombre, unidad, descripcion, precioCosto, precioVenta) VALUES(?,?,?,?,?,?)");

        $stmt->bind_param("ssssdd",
            $data['codigo'],
            $data['nombre'],
            $data['unidad'],
            $data['descripcion'],
            $data['precio'],
            $data['venta']
        );

        return $stmt->execute();
    }

}