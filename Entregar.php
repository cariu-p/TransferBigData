<?php

declare(strict_types=1);
session_start();
require "Conexion.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

const CHUNK_SIZE = 5 * 1024 * 1024;
const MAX_BYTES  = 20 * 1024 * 1024 * 1024;
// RUTAS CORREGIDAS PARA TU ENTORNO LINUX
const DIR_TMP    = '/opt/lampp/almacen/tmp/';
const DIR_FIN    = '/opt/lampp/almacen/archivos/';
const EXT_PROHIBIDAS = ['php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8', 'htaccess', 'cgi', 'pl', 'asp', 'aspx', 'jsp', 'exe', 'bat', 'cmd', 'sh', 'msi', 'scr'];

function responder(array $d, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($d, JSON_UNESCAPED_UNICODE);
    exit;
}
function fatal(string $msg, int $code = 400): void
{
    responder(['status' => 'error', 'mensaje' => $msg], $code);
}

function obtenerSubida(mysqli $c, string $uploadId): array
{
    $stmt = $c->prepare("SELECT * FROM subidas WHERE upload_id = ? AND session_id = ? AND estado = 'en_progreso'");
    $sid = session_id();
    $stmt->bind_param('ss', $uploadId, $sid);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila) fatal('Sesión de subida inválida o expirada', 403);
    return $fila;
}

function limpiarTemporales(): void
{
    if (random_int(1, 20) !== 1) return;
    foreach (glob(DIR_TMP . '*.part') ?: [] as $f) {
        if (filemtime($f) < time() - 86400) @unlink($f);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fatal('Método no permitido', 405);
if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) fatal('Token CSRF inválido', 403);

$accion = $_POST['accion'] ?? '';

/* ===================== 1. INICIAR ===================== */
if ($accion === 'iniciar') {
    $nombre = trim((string)($_POST['nombre'] ?? ''));
    $tamano = (int)($_POST['tamano'] ?? 0);
    $chunks = (int)($_POST['chunksTotal'] ?? 0);

    if ($nombre === '' || mb_strlen($nombre) > 255) fatal('Nombre de archivo inválido');
    if ($tamano <= 0 || $tamano > MAX_BYTES)        fatal('Tamaño inválido o mayor al límite de 20 GB');
    if ($chunks !== (int)ceil($tamano / CHUNK_SIZE)) fatal('Número de pedazos incoherente');

    $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
    if (in_array($ext, EXT_PROHIBIDAS, true)) fatal('Tipo de archivo no permitido');

    $uploadId = bin2hex(random_bytes(16));
    $sid      = session_id();
    $nombre   = basename($nombre);

    $stmt = $conexion->prepare("INSERT INTO subidas (upload_id, session_id, nombre_original, tamano, chunks_total, estado, creado) VALUES (?, ?, ?, ?, ?, 'en_progreso', NOW())");
    $stmt->bind_param('sssii', $uploadId, $sid, $nombre, $tamano, $chunks);
    $stmt->execute();
    $stmt->close();

    touch(DIR_TMP . $uploadId . '.part');
    limpiarTemporales();
    responder(['status' => 'iniciado', 'uploadId' => $uploadId]);
}

/* ===================== 2. CHUNK ===================== */
if ($accion === 'chunk') {
    if (!isset($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) fatal('Pedazo no recibido');
    if (!is_uploaded_file($_FILES['chunk']['tmp_name']))                          fatal('Origen inválido');

    $uploadId = preg_replace('/[^a-f0-9]/', '', (string)($_POST['uploadId'] ?? ''));
    $indice   = (int)($_POST['chunkIndex'] ?? -1);
    if (strlen($uploadId) !== 32 || $indice < 0) fatal('Parámetros inválidos');

    $sub  = obtenerSubida($conexion, $uploadId);
    $ruta = DIR_TMP . $uploadId . '.part';

    clearstatcache(true, $ruta);
    $escritos = file_exists($ruta) ? filesize($ruta) : 0;

    if ($escritos !== $indice * CHUNK_SIZE)                       fatal('Pedazo fuera de orden', 409);
    if ($escritos + $_FILES['chunk']['size'] > (int)$sub['tamano']) fatal('Excede el tamaño declarado');

    $dst = fopen($ruta, 'ab');
    if ($dst === false) fatal('No se pudo escribir en disco. Revisa permisos de la carpeta.', 500);
    flock($dst, LOCK_EX);
    $src   = fopen($_FILES['chunk']['tmp_name'], 'rb');
    $bytes = stream_copy_to_stream($src, $dst);
    fclose($src);
    fflush($dst);
    flock($dst, LOCK_UN);
    fclose($dst);

    if ($bytes === false) fatal('Fallo al escribir el pedazo', 500);
    responder(['status' => 'chunk_recibido', 'indice' => $indice]);
}

/* ===================== 3. FINALIZAR ===================== */
if ($accion === 'finalizar') {
    $uploadId = preg_replace('/[^a-f0-9]/', '', (string)($_POST['uploadId'] ?? ''));
    if (strlen($uploadId) !== 32) fatal('Parámetros inválidos');

    $sub  = obtenerSubida($conexion, $uploadId);
    $ruta = DIR_TMP . $uploadId . '.part';

    clearstatcache(true, $ruta);
    if (!file_exists($ruta) || filesize($ruta) !== (int)$sub['tamano']) {
        fatal('El archivo llegó incompleto. Vuelve a intentarlo.', 422);
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($ruta) ?: 'application/octet-stream';
    $ext  = preg_replace('/[^a-z0-9]/', '', strtolower(pathinfo($sub['nombre_original'], PATHINFO_EXTENSION)));
    if (in_array($ext, EXT_PROHIBIDAS, true)) {
        @unlink($ruta);
        fatal('Tipo de archivo no permitido');
    }

    // 1. Recibir y Sanitizar la carpeta (Prevención de Path Traversal)
    $carpetaRaw = $_POST['carpeta'] ?? 'general';
    // Permitir SOLO letras, números, guiones y guiones bajos. Elimina puntos y diagonales.
    $carpetaLimpia = preg_replace('/[^a-zA-Z0-9_-]/', '', $carpetaRaw);
    if (empty($carpetaLimpia)) $carpetaLimpia = 'general';

    // 2. Crear la carpeta físicamente en Linux si no existe
    $rutaDirectorioFinal = DIR_FIN . $carpetaLimpia . '/';
    if (!is_dir($rutaDirectorioFinal)) {
        if (!mkdir($rutaDirectorioFinal, 0755, true)) {
            fatal('Error al crear la carpeta en el servidor', 500);
        }
    }

    // 3. Generar el nombre de disco incluyendo la subcarpeta
    // Ejemplo: "finanzas/a1b2c3d4.pdf"
    $nombreDisco = $carpetaLimpia . '/' . bin2hex(random_bytes(16)) . ($ext !== '' ? '.' . $ext : '');

    if (!rename($ruta, DIR_FIN . $nombreDisco)) fatal('No se pudo mover el archivo a la carpeta', 500);

    $desc = mb_substr(trim((string)($_POST['descripcion'] ?? '')), 0, 200);
    $desc = $desc !== '' ? $desc : null;
    $recep = mb_substr(trim((string)($_POST['receptor'] ?? '')), 0, 120);
    $notif = $recep !== '' ? 1 : 0;
    $recep = $recep !== '' ? $recep : null;

    $dias   = max(0, min(365, (int)($_POST['dias'] ?? 7)));
    $expira = $dias > 0 ? (new DateTimeImmutable("+{$dias} days"))->format('Y-m-d H:i:s') : null;
    $maxDl  = max(1, min(100, (int)($_POST['maxDescargas'] ?? 5)));

    $nivel = min(3, max(1, (int)($_POST['nivelAcceso'] ?? 1)));
    if ($nivel === 2) $maxDl = 1;

    $pass = (string)($_POST['password'] ?? '');
    $hash = $pass !== '' ? password_hash($pass, PASSWORD_DEFAULT) : null;

    $token  = bin2hex(random_bytes(32));
    $tam    = (int)$sub['tamano'];
    $nomOri = $sub['nombre_original'];

    $stmt = $conexion->prepare("INSERT INTO transferencias (token, nombre_archivo, nombre_disco, mime, tamano, descripcion, receptor, nivel_acceso, pass_hash, max_descargas, notificar, fecha_creacion, fecha_expiracion) VALUES (?,?,?,?,?,?,?,?,?,?,?, NOW(), ?)");
    $stmt->bind_param('ssssissisiis', $token, $nomOri, $nombreDisco, $mime, $tam, $desc, $recep, $nivel, $hash, $maxDl, $notif, $expira);
    $stmt->execute();
    $stmt->close();

    $stmt = $conexion->prepare("UPDATE subidas SET estado='completado' WHERE upload_id=?");
    $stmt->bind_param('s', $uploadId);
    $stmt->execute();
    $stmt->close();

    // Aquí integras PHPMailer en el futuro

    responder([
        'status' => 'completado',
        'enlace' => 'http://localhost/TransferBigData/Descargar.php?token=' . $token
    ]);
}

/* ===================== 4. LISTAR CARPETA (EXPLORADOR EN VIVO) ===================== */
if ($accion === 'listar_carpeta') {
    // 1. ESCUDO DE SEGURIDAD (Path Traversal)
    $carpetaRaw = $_POST['carpeta'] ?? 'general';
    // Solo permitimos letras, números y guiones. Destruimos barras y puntos.
    $carpetaLimpia = preg_replace('/[^a-zA-Z0-9_-]/', '', $carpetaRaw);
    if (empty($carpetaLimpia)) {
        $carpetaLimpia = 'general';
    }

    // 2. BÚSQUEDA BLINDADA
    $like = $carpetaLimpia . '/%';

    try {
        $stmt = $conexion->prepare("SELECT nombre_archivo, tamano, fecha_creacion FROM transferencias WHERE nombre_disco LIKE ? ORDER BY fecha_creacion DESC LIMIT 50");
        if (!$stmt) throw new Exception("Fallo en la base de datos. Revisa la conexión.");

        $stmt->bind_param('s', $like);
        $stmt->execute();
        $res = $stmt->get_result();

        $archivos = [];
        while ($fila = $res->fetch_assoc()) {
            $archivos[] = $fila;
        }
        $stmt->close();

        responder(['status' => 'ok', 'archivos' => $archivos]);
    } catch (Exception $e) {
        fatal('Error del servidor: ' . $e->getMessage(), 500);
    }
}
/* ===================== 5. OBTENER DIRECTORIOS ===================== */
if ($accion === 'obtener_carpetas') {
    $carpetas = [];
    $rutas = glob(DIR_FIN . '*', GLOB_ONLYDIR);
    
    if ($rutas) {
        foreach ($rutas as $ruta) {
            $carpetas[] = basename($ruta);
        }
    }
    
    if (empty($carpetas)) {
        $carpetas[] = 'archivos';
    }
    
    responder(['status' => 'ok', 'carpetas' => $carpetas]);
}

fatal('Acción no reconocida');


