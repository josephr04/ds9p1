<?php
require_once "../config/conexion.php";

$id = $_GET['id'];

$sql = $conexion->query("SELECT * FROM productos WHERE idProducto = '$id'");
$data = $sql->fetch_object();

echo json_encode($data);