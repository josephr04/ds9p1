<?php
$conexion = new mysqli("localhost", "root", "", "ds9p1");
$conexion->set_charset("utf8");
date_default_timezone_set("America/panama");
if($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>