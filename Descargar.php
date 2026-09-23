<?php

declare(strict_types=1);
require "Conexion.php";

// RUTA LINUX CORRECTA
const DIR_FIN = '/opt/lampp/almacen/archivos/';

// 1. Recibir el token de la URL (Ej. ?token=abcd...)
$token = preg_replace('/[^a-f0-9]/', '', (string)($_GET['token'] ?? ''));

if (empty($token)) {
    http_response_code(400);
    exit('Token no proporcionado.');
}

// 2. Buscar en la base de datos real (conectando lo que subir.php guardó)
$stmt = $conexion->prepare("SELECT nombre_archivo, nombre_disco, nivel_acceso, fecha_expiracion FROM transferencias WHERE token = ?");
$stmt->bind_param('s', $token);
$stmt->execute();
$fila = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$fila) {
    http_response_code(404);
    exit('Ticket inválido o enlace inexistente.');
}

if (!empty($fila['fecha_expiracion']) && strtotime($fila['fecha_expiracion']) < time()) {
    http_response_code(403);
    exit('El enlace ha expirado.');
}

$base = realpath(DIR_FIN);
$ruta = realpath(DIR_FIN . $fila['nombre_disco']);

if ($ruta === false || !str_starts_with($ruta, $base) || !is_file($ruta)) {
    http_response_code(404);
    exit('Archivo físico no encontrado en el almacenamiento ciego.');
}

// 3. Tu lógica original de Streaming (Intacta)
$tam     = filesize($ruta);
$inicio  = 0;
$fin     = $tam - 1;
$parcial = false;

if (!empty($_SERVER['HTTP_RANGE']) && preg_match('/^bytes=(\d*)-(\d*)$/', trim($_SERVER['HTTP_RANGE']), $m)) {
    if ($m[1] === '' && $m[2] === '') {
        header("Content-Range: bytes */$tam");
        http_response_code(416);
        exit;
    }
    if ($m[1] === '') {
        $inicio = max(0, $tam - (int)$m[2]);
    } else {
        $inicio = (int)$m[1];
        if ($m[2] !== '') $fin = min((int)$m[2], $tam - 1);
    }
    if ($inicio > $fin || $inicio >= $tam) {
        header("Content-Range: bytes */$tam");
        http_response_code(416);
        exit;
    }
    $parcial = true;
}

$largo  = $fin - $inicio + 1;
$nombre = $fila['nombre_archivo'];
$ascii  = preg_replace('/[^\x20-\x7E]/', '_', str_replace('"', '', $nombre));

while (ob_get_level()) ob_end_clean();
ini_set('zlib.output_compression', '0');
set_time_limit(0);
ignore_user_abort(true);

$disposicion = ((int)$fila['nivel_acceso'] === 1) ? 'inline' : 'attachment';

http_response_code($parcial ? 206 : 200);
header('Content-Type: application/octet-stream');
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: ' . $disposicion . '; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode($nombre));
header('Content-Length: ' . $largo);
header('Accept-Ranges: bytes');
header('Cache-Control: private, no-store');
header('Referrer-Policy: no-referrer');
if ($parcial) header("Content-Range: bytes $inicio-$fin/$tam");

$fp = fopen($ruta, 'rb');
fseek($fp, $inicio);
$restante = $largo;
while ($restante > 0 && !feof($fp) && !connection_aborted()) {
    $bloque = fread($fp, (int)min(262144, $restante));
    if ($bloque === false) break;
    echo $bloque;
    $restante -= strlen($bloque);
    flush();
}
fclose($fp);
exit;
