<?php
// Datos por defecto de XAMPP: usuario "root" y sin contraseña
$host = "localhost";
$usuario = "root";
$password = "";
$bd = "wetransfer_demo";

$conexion = new mysqli($host, $usuario, $password, $bd);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}