<?php
require "conexion.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["archivo"])) {

    $archivo = $_FILES["archivo"];

    // Validación básica de errores de subida
    if ($archivo["error"] !== UPLOAD_ERR_OK) {
        die("Error al subir el archivo. Código: " . $archivo["error"]);
    }

    $nombreOriginal = basename($archivo["name"]);
    $nombreGuardado = uniqid() . "_" . $nombreOriginal; // evita que se sobrescriban archivos con el mismo nombre
    $rutaDestino = "uploads/" . $nombreGuardado;

    // Token seguro de 32 caracteres hexadecimales
    $token = bin2hex(random_bytes(16));

    // Leer cantidad y unidad elegidas en el formulario (con valores por defecto por seguridad)
    $cantidad = isset($_POST["cantidad"]) ? (int) $_POST["cantidad"] : 7;
    $unidad = $_POST["unidad"] ?? "dias";

    if ($cantidad < 1) {
        $cantidad = 1;
    }

    // Traducir la unidad elegida al formato que entiende strtotime()
    switch ($unidad) {
        case "segundos":
            $intervalo = "+{$cantidad} seconds";
            $textoUnidad = "segundo(s)";
            break;
        case "minutos":
            $intervalo = "+{$cantidad} minutes";
            $textoUnidad = "minuto(s)";
            break;
        case "dias":
        default:
            $intervalo = "+{$cantidad} days";
            $textoUnidad = "día(s)";
            break;
    }

    if (move_uploaded_file($archivo["tmp_name"], $rutaDestino)) {

        $fechaCreacion = date("Y-m-d H:i:s");
        $fechaExpiracion = date("Y-m-d H:i:s", strtotime($intervalo));

        $stmt = $conexion->prepare(
            "INSERT INTO transferencias (token, nombre_archivo, ruta_archivo, fecha_creacion, fecha_expiracion)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $token, $nombreOriginal, $rutaDestino, $fechaCreacion, $fechaExpiracion);
        $stmt->execute();
        $stmt->close();

        $enlace = "http://localhost/wetransfer_demo/descargar.php?token=" . $token;
        echo "<h2>¡Archivo subido con éxito!</h2>";
        echo "<p>Tu enlace de descarga (válido por {$cantidad} {$textoUnidad}):</p>";
        echo "<a href='$enlace'>$enlace</a>";
        echo "<p><small>Expira exactamente el: {$fechaExpiracion}</small></p>";
    } else {
        echo "Error al mover el archivo al servidor.";
    }
} else {
    echo "No se recibió ningún archivo.";
}