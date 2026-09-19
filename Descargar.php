<?php
require "conexion.php";

$token = $_GET["token"] ?? "";

if (empty($token)) {
    die("Token no proporcionado.");
}

$stmt = $conexion->prepare("SELECT * FROM transferencias WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Enlace no válido.");
}

$transferencia = $resultado->fetch_assoc();
$stmt->close();

if (strtotime($transferencia["fecha_expiracion"]) < time()) {
    die("Este enlace ha expirado.");
}

if (!file_exists($transferencia["ruta_archivo"])) {
    die("El archivo ya no existe en el servidor.");
}

// Actualizar contador de descargas usando consulta preparada (seguro contra inyección SQL)
$update = $conexion->prepare("UPDATE transferencias SET descargas = descargas + 1 WHERE token = ?");
$update->bind_param("s", $token);
$update->execute();
$update->close();

// Forzar la descarga del archivo al navegador
$ruta = $transferencia["ruta_archivo"];
header("Content-Description: File Transfer");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . $transferencia["nombre_archivo"] . "\"");
header("Content-Length: " . filesize($ruta));
readfile($ruta);
exit;