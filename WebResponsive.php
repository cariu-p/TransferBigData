<?php
session_start();
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// 1. Conexión obligatoria a la base de datos
require "Conexion.php";

// 2. Ruta blindada y absoluta en el ecosistema Linux
const DIR_ARCHIVOS = '/opt/lampp/almacen/archivos/';

// 3. Consulta estructurada en lugar de escaneo de disco
function obtenerArchivosBD(mysqli $conexion): array
{
  $archivos = [];
  // Leemos de la tabla transferencias generada por entregar.php
  $query = "SELECT nombre_archivo AS nombre, tamano, mime AS tipo, UNIX_TIMESTAMP(fecha_creacion) AS modificado 
              FROM transferencias 
              ORDER BY fecha_creacion DESC 
              LIMIT 100";

  $resultado = $conexion->query($query);

  if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
      $archivos[] = [
        'nombre'     => $fila['nombre'],
        'tamano'     => (int)$fila['tamano'],
        'tipo'       => $fila['tipo'],
        'modificado' => (int)$fila['modificado'],
      ];
    }
  }
  return $archivos;
}

function fmtBytesPHP(int $b): string
{
  $u = ['B', 'KB', 'MB', 'GB', 'TB'];
  $i = 0;
  while ($b >= 1024 && $i < 4) {
    $b /= 1024;
    $i++;
  }
  return round($b, $i ? 1 : 0) . ' ' . $u[$i];
}

$archivosSubidos = obtenerArchivosBD($conexion);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta name="csrf" content="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#0D1B4B" />
  <title>TBD — Transfer Big Data · Vasanta Comunicaciones</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    /* ═══ DESIGN TOKENS ═══════════════════════════════════════════ */
    :root {
      --navy: #0D1B4B;
      --blue: #1E3A8A;
      --accent: #3B82F6;
      --teal: #0EA5E9;
      --success: #10B981;
      --warning: #F59E0B;
      --danger: #EF4444;
      --surface: #F1F5F9;
      --card: #FFFFFF;
      --border: #E2E8F0;
      --text: #1E293B;
      --muted: #64748B;
      --white: #FFFFFF;
      --sidebar-w: 260px;
      --header-h: 64px;
      --font-display: 'Syne', sans-serif;
      --font-body: 'Plus Jakarta Sans', sans-serif;
      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 18px;
      --shadow-sm: 0 1px 3px rgba(0, 0, 0, .08), 0 1px 2px rgba(0, 0, 0, .05);
      --shadow-md: 0 4px 16px rgba(0, 0, 0, .1);
      --shadow-lg: 0 10px 40px rgba(0, 0, 0, .14);
      --transition: all .25s cubic-bezier(.4, 0, .2, 1);
    }

    /* ═══ BASE ═══════════════════════════════════════════════════ */
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: var(--font-body);
      background: var(--surface);
      color: var(--text);
      overflow-x: hidden;
      font-size: 14px;
      line-height: 1.6;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
      font-family: var(--font-display);
    }

    ::-webkit-scrollbar {
      width: 5px;
      height: 5px;
    }

    ::-webkit-scrollbar-track {
      background: var(--surface);
    }

    ::-webkit-scrollbar-thumb {
      background: var(--accent);
      border-radius: 4px;
    }

    ::selection {
      background: var(--accent);
      color: #fff;
    }

    /* ═══ LAYOUT ══════════════════════════════════════════════════ */
    .app-wrapper {
      display: flex;
      min-height: 100vh;
    }

    /* ═══ SIDEBAR ════════════════════════════════════════════════ */
    .sidebar {
      width: var(--sidebar-w);
      background: var(--navy);
      position: fixed;
      left: 0;
      top: 0;
      bottom: 0;
      z-index: 1050;
      display: flex;
      flex-direction: column;
      transition: transform .3s ease;
      overflow-y: auto;
    }

    .sidebar-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 20px 20px 16px;
      border-bottom: 1px solid rgba(255, 255, 255, .08);
    }

    .brand-icon {
      width: 38px;
      height: 38px;
      background: var(--accent);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      color: #fff;
      flex-shrink: 0;
    }

    .brand-text {
      font-family: var(--font-display);
      font-size: 1rem;
      font-weight: 800;
      color: #fff;
      line-height: 1.1;
    }

    .brand-text small {
      font-size: .65rem;
      color: rgba(255, 255, 255, .45);
      font-family: var(--font-body);
      font-weight: 400;
      display: block;
    }

    .sidebar-close {
      display: none;
      background: none;
      border: none;
      color: rgba(255, 255, 255, .6);
      font-size: 1.2rem;
      margin-left: auto;
      cursor: pointer;
    }

    .nav-section {
      padding: 16px 12px 4px;
      font-size: .68rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      color: rgba(255, 255, 255, .3);
    }

    .sidebar-nav {
      list-style: none;
      padding: 4px 8px;
      flex: 1;
    }

    .sidebar-nav li {
      margin-bottom: 2px;
    }

    .nav-link-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 12px;
      color: rgba(255, 255, 255, .6);
      border-radius: var(--radius-sm);
      cursor: pointer;
      font-size: .875rem;
      font-weight: 500;
      transition: var(--transition);
      text-decoration: none;
      position: relative;
    }

    .nav-link-item:hover {
      background: rgba(255, 255, 255, .08);
      color: #fff;
    }

    .nav-link-item.active {
      background: var(--accent);
      color: #fff;
      font-weight: 600;
    }

    .nav-link-item .nav-icon {
      font-size: 1rem;
      width: 20px;
      text-align: center;
    }

    .nav-badge {
      margin-left: auto;
      background: var(--accent);
      color: #fff;
      font-size: .65rem;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 100px;
    }

    .nav-link-item.active .nav-badge {
      background: rgba(255, 255, 255, .25);
    }

    .sidebar-footer {
      padding: 12px 12px 20px;
      border-top: 1px solid rgba(255, 255, 255, .08);
      margin-top: auto;
    }

    .user-card {
      display: flex;
      align-items: center;
      gap: 10px;
      background: rgba(255, 255, 255, .06);
      border-radius: var(--radius-sm);
      padding: 10px;
      cursor: pointer;
      transition: var(--transition);
    }

    .user-card:hover {
      background: rgba(255, 255, 255, .1);
    }

    .user-avatar {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--accent), var(--teal));
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: .8rem;
      color: #fff;
      flex-shrink: 0;
    }

    .user-info {
      min-width: 0;
    }

    .user-name {
      font-size: .82rem;
      font-weight: 600;
      color: #fff;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .user-role {
      font-size: .7rem;
      color: rgba(255, 255, 255, .4);
    }

    /* ═══ HEADER ═════════════════════════════════════════════════ */
    .header {
      position: fixed;
      top: 0;
      left: var(--sidebar-w);
      right: 0;
      height: var(--header-h);
      background: var(--card);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      padding: 0 24px;
      z-index: 1040;
      gap: 12px;
      box-shadow: var(--shadow-sm);
      transition: left .3s ease;
    }

    .menu-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 1.3rem;
      color: var(--text);
      cursor: pointer;
      padding: 6px;
      border-radius: var(--radius-sm);
    }

    .menu-toggle:hover {
      background: var(--surface);
    }

    .breadcrumb-area {
      flex: 1;
    }

    .page-title {
      font-family: var(--font-display);
      font-size: 1rem;
      font-weight: 700;
      color: var(--navy);
    }

    .page-breadcrumb {
      font-size: .75rem;
      color: var(--muted);
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .hdr-btn {
      width: 36px;
      height: 36px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: .95rem;
      color: var(--muted);
      transition: var(--transition);
      position: relative;
    }

    .hdr-btn:hover {
      border-color: var(--accent);
      color: var(--accent);
    }

    .notif-dot {
      position: absolute;
      top: 6px;
      right: 6px;
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--danger);
      border: 1px solid var(--card);
    }

    .hdr-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--accent), var(--teal));
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: .78rem;
      color: #fff;
      cursor: pointer;
      border: 2px solid var(--border);
    }

    /* ═══ MAIN CONTENT ═══════════════════════════════════════════ */
    .main-content {
      margin-left: var(--sidebar-w);
      margin-top: var(--header-h);
      min-height: calc(100vh - var(--header-h));
      padding: 28px 28px 40px;
      transition: margin-left .3s ease;
    }

    .module {
      display: none;
      animation: fadeSlide .35s ease both;
    }

    .module.active {
      display: block;
    }

    @keyframes fadeSlide {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* ═══ CARDS ══════════════════════════════════════════════════ */
    .card-box {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-sm);
      overflow: hidden;
    }

    .card-header-box {
      background: linear-gradient(135deg, var(--navy), var(--blue));
      padding: 20px 24px;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .card-header-box .ch-icon {
      width: 42px;
      height: 42px;
      background: rgba(255, 255, 255, .12);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      color: #fff;
    }

    .card-header-box h3 {
      font-size: 1rem;
      font-weight: 700;
      color: #fff;
      margin: 0;
    }

    .card-header-box p {
      font-size: .8rem;
      color: rgba(255, 255, 255, .6);
      margin: 0;
    }

    .card-body-box {
      padding: 28px;
    }

    /* ═══ STAT CARDS ═════════════════════════════════════════════ */
    .stat-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 16px;
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 3px;
    }

    .stat-card.c-blue::before {
      background: var(--accent);
    }

    .stat-card.c-teal::before {
      background: var(--teal);
    }

    .stat-card.c-green::before {
      background: var(--success);
    }

    .stat-card.c-orange::before {
      background: var(--warning);
    }

    .stat-card:hover {
      box-shadow: var(--shadow-md);
      transform: translateY(-2px);
    }

    .stat-icon {
      width: 52px;
      height: 52px;
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      flex-shrink: 0;
    }

    .si-blue {
      background: #EFF6FF;
      color: var(--accent);
    }

    .si-teal {
      background: #F0F9FF;
      color: var(--teal);
    }

    .si-green {
      background: #ECFDF5;
      color: var(--success);
    }

    .si-orange {
      background: #FFFBEB;
      color: var(--warning);
    }

    .stat-val {
      font-family: var(--font-display);
      font-size: 1.7rem;
      font-weight: 800;
      color: var(--navy);
      line-height: 1;
    }

    .stat-lbl {
      font-size: .78rem;
      color: var(--muted);
      margin-top: 4px;
    }

    .stat-chg {
      font-size: .72rem;
      font-weight: 600;
    }

    .chg-up {
      color: var(--success);
    }

    .chg-dn {
      color: var(--danger);
    }

    /* ═══ FORM STYLES ════════════════════════════════════════════ */
    .form-section-title {
      font-size: .7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      color: var(--accent);
      margin-bottom: 12px;
      padding-bottom: 8px;
      border-bottom: 2px solid #EFF6FF;
    }

    .form-label {
      font-size: .8rem;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 5px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .req {
      color: var(--danger);
    }

    .form-control,
    .form-select {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      color: var(--text);
      font-family: var(--font-body);
      font-size: .875rem;
      padding: .6rem .9rem;
      transition: var(--transition);
      width: 100%;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--accent);
      background: #fff;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
      outline: none;
    }

    .form-control.is-valid {
      border-color: var(--success);
      background: #f0fdf4;
    }

    .form-control.is-invalid {
      border-color: var(--danger);
      background: #fef2f2;
    }

    .invalid-feedback {
      font-size: .75rem;
      color: var(--danger);
      margin-top: 4px;
    }

    .valid-feedback {
      font-size: .75rem;
      color: var(--success);
      margin-top: 4px;
    }

    .input-group-addon {
      display: flex;
      align-items: center;
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-right: none;
      border-radius: var(--radius-sm) 0 0 var(--radius-sm);
      padding: 0 12px;
      color: var(--muted);
      font-size: .95rem;
    }

    .input-group .form-control {
      border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    }

    .input-group-end {
      display: flex;
      align-items: center;
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-left: none;
      border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
      padding: 0 12px;
      color: var(--muted);
      cursor: pointer;
      transition: var(--transition);
    }

    .input-group-end:hover {
      color: var(--accent);
    }

    .input-grp {
      display: flex;
    }

    .input-grp .form-control {
      border-radius: var(--radius-sm) 0 0 var(--radius-sm);
      border-right: none;
    }

    .form-hint {
      font-size: .75rem;
      color: var(--muted);
      margin-top: 4px;
    }

    .form-check-input {
      width: 16px;
      height: 16px;
      border: 1.5px solid var(--border);
      cursor: pointer;
    }

    .form-check-input:checked {
      background-color: var(--accent);
      border-color: var(--accent);
    }

    .form-check-label {
      font-size: .85rem;
      color: var(--text);
      cursor: pointer;
    }

    .file-drop {
      border: 2px dashed var(--border);
      border-radius: var(--radius-md);
      padding: 2rem;
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
      background: var(--surface);
    }

    .file-drop:hover,
    .file-drop.drag {
      border-color: var(--accent);
      background: #EFF6FF;
    }

    .file-drop i {
      font-size: 2.2rem;
      color: var(--accent);
    }

    .file-drop p {
      font-size: .85rem;
      color: var(--muted);
      margin: 8px 0 0;
    }

    .file-drop span {
      font-size: .8rem;
      color: var(--accent);
      font-weight: 600;
    }

    .progress-bar-custom {
      height: 6px;
      background: var(--border);
      border-radius: 4px;
      overflow: hidden;
      margin-top: 6px;
    }

    .progress-fill {
      height: 100%;
      border-radius: 4px;
      background: linear-gradient(90deg, var(--accent), var(--teal));
      transition: width .4s ease;
    }

    .char-count {
      font-size: .72rem;
      color: var(--muted);
      text-align: right;
      margin-top: 3px;
    }

    .range-value {
      background: var(--accent);
      color: #fff;
      font-size: .72rem;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 100px;
      min-width: 40px;
      text-align: center;
    }

    .form-range {
      accent-color: var(--accent);
    }

    /* Toggle switch */
    .toggle-wrap {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .toggle {
      position: relative;
      width: 42px;
      height: 24px;
    }

    .toggle input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .toggle-slider {
      position: absolute;
      inset: 0;
      background: var(--border);
      border-radius: 24px;
      cursor: pointer;
      transition: var(--transition);
    }

    .toggle-slider::before {
      content: '';
      position: absolute;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      background: #fff;
      left: 3px;
      top: 3px;
      transition: var(--transition);
      box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
    }

    .toggle input:checked+.toggle-slider {
      background: var(--accent);
    }

    .toggle input:checked+.toggle-slider::before {
      transform: translateX(18px);
    }

    .toggle-label {
      font-size: .85rem;
      font-weight: 500;
      color: var(--text);
    }

    /* ═══ BUTTONS ════════════════════════════════════════════════ */
    .btn-primary-v {
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: var(--radius-sm);
      padding: .65rem 1.4rem;
      font-size: .875rem;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      font-family: var(--font-body);
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .btn-primary-v:hover {
      background: #2563EB;
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(59, 130, 246, .35);
    }

    .btn-secondary-v {
      background: var(--surface);
      color: var(--text);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      padding: .65rem 1.4rem;
      font-size: .875rem;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      font-family: var(--font-body);
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .btn-secondary-v:hover {
      border-color: var(--accent);
      color: var(--accent);
    }

    .btn-danger-v {
      background: #FEF2F2;
      color: var(--danger);
      border: 1.5px solid #FECACA;
      border-radius: var(--radius-sm);
      padding: .65rem 1.4rem;
      font-size: .875rem;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      font-family: var(--font-body);
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .btn-danger-v:hover {
      background: var(--danger);
      color: #fff;
    }

    .btn-sm-v {
      padding: .42rem .9rem;
      font-size: .8rem;
    }

    .btn-icon-v {
      width: 34px;
      height: 34px;
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: var(--muted);
      transition: var(--transition);
      font-size: .9rem;
    }

    .btn-icon-v:hover {
      border-color: var(--accent);
      color: var(--accent);
    }

    /* ═══ TABLE ══════════════════════════════════════════════════ */
    .table-wrap {
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead th {
      background: var(--navy);
      color: #fff;
      font-family: var(--font-display);
      font-size: .78rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .5px;
      padding: 12px 16px;
      text-align: left;
      white-space: nowrap;
    }

    thead th:first-child {
      border-radius: var(--radius-sm) 0 0 0;
    }

    thead th:last-child {
      border-radius: 0 var(--radius-sm) 0 0;
    }

    tbody tr {
      border-bottom: 1px solid var(--border);
      transition: var(--transition);
    }

    tbody tr:hover {
      background: #F8FAFF;
    }

    tbody td {
      padding: 12px 16px;
      font-size: .855rem;
      color: var(--text);
    }

    .badge-status {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: .72rem;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 100px;
    }

    .bs-active {
      background: #ECFDF5;
      color: var(--success);
    }

    .bs-expired {
      background: #FEF2F2;
      color: var(--danger);
    }

    .bs-pending {
      background: #FFFBEB;
      color: var(--warning);
    }

    .bs-deleted {
      background: var(--surface);
      color: var(--muted);
    }

    .file-pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: #EFF6FF;
      color: var(--accent);
      font-size: .75rem;
      font-weight: 600;
      padding: 3px 9px;
      border-radius: 100px;
    }

    /* ═══ WIREFRAME / BOSQUEJO ═══════════════════════════════════ */
    .wire-frame {
      border: 2px dashed #CBD5E1;
      border-radius: var(--radius-md);
      background: #F8FAFC;
      padding: 16px;
      position: relative;
      overflow: hidden;
    }

    .wire-label {
      position: absolute;
      top: 8px;
      left: 8px;
      background: var(--navy);
      color: #fff;
      font-size: .65rem;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 4px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .wire-block {
      background: #E2E8F0;
      border-radius: 6px;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #94A3B8;
      font-size: .72rem;
      font-weight: 600;
    }

    .wire-nav {
      height: 36px;
      background: var(--navy);
      border-radius: 6px;
      margin-bottom: 8px;
    }

    .wire-sidebar {
      width: 70px;
      background: #CBD5E1;
      border-radius: 6px;
      flex-shrink: 0;
    }

    .wire-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .wire-row {
      display: flex;
      gap: 6px;
    }

    .wire-card {
      background: #E2E8F0;
      border-radius: 6px;
      flex: 1;
    }

    .wire-form-row {
      display: flex;
      gap: 6px;
      margin-bottom: 6px;
    }

    .wire-input {
      background: #E2E8F0;
      border-radius: 4px;
      height: 22px;
      flex: 1;
    }

    .wire-btn {
      background: var(--accent);
      border-radius: 4px;
      height: 28px;
      width: 70px;
    }

    /* ═══ DB CONNECTION STATUS ═══════════════════════════════════ */
    .db-status-bar {
      background: var(--navy);
      color: #fff;
      border-radius: var(--radius-md);
      padding: 14px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: .875rem;
    }

    .db-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      flex-shrink: 0;
      animation: blink 2s infinite;
    }

    .db-dot.connected {
      background: var(--success);
    }

    .db-dot.disconnected {
      background: var(--danger);
      animation: none;
    }

    @keyframes blink {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: .4;
      }
    }

    .db-info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 10px;
    }

    .db-info-item {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      padding: 12px 14px;
    }

    .db-info-label {
      font-size: .7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--muted);
    }

    .db-info-val {
      font-size: .9rem;
      font-weight: 600;
      color: var(--navy);
      margin-top: 3px;
    }

    .code-block {
      background: #0F172A;
      color: #94A3B8;
      border-radius: var(--radius-md);
      padding: 16px 20px;
      font-family: 'Courier New', monospace;
      font-size: .78rem;
      line-height: 1.7;
      overflow-x: auto;
    }

    .code-block .kw {
      color: #7DD3FC;
    }

    .code-block .str {
      color: #86EFAC;
    }

    .code-block .fn {
      color: #FCA5A5;
    }

    .code-block .cm {
      color: #64748B;
    }

    .code-block .var {
      color: #FDE68A;
    }

    /* ═══ ALERT ══════════════════════════════════════════════════ */
    .alert-v {
      border-radius: var(--radius-sm);
      padding: 12px 16px;
      display: flex;
      gap: 10px;
      align-items: flex-start;
      font-size: .855rem;
    }

    .alert-info {
      background: #EFF6FF;
      border-left: 3px solid var(--accent);
      color: #1E40AF;
    }

    .alert-success {
      background: #ECFDF5;
      border-left: 3px solid var(--success);
      color: #065F46;
    }

    .alert-warning {
      background: #FFFBEB;
      border-left: 3px solid var(--warning);
      color: #92400E;
    }

    .alert-danger {
      background: #FEF2F2;
      border-left: 3px solid var(--danger);
      color: #991B1B;
    }

    /* ═══ TOAST ══════════════════════════════════════════════════ */
    #toast-area {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .toast-v {
      background: var(--navy);
      color: #fff;
      border-radius: var(--radius-md);
      padding: 12px 16px;
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 260px;
      font-size: .855rem;
      box-shadow: var(--shadow-lg);
      animation: toastIn .3s ease;
      border-left: 3px solid var(--accent);
    }

    @keyframes toastIn {
      from {
        opacity: 0;
        transform: translateX(20px);
      }

      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    /* ═══ OVERLAY ════════════════════════════════════════════════ */
    .overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .45);
      z-index: 1049;
    }

    .overlay.show {
      display: block;
    }

    /* ═══ RESPONSIVE ══════════════════════════════════════════════
   BREAKPOINTS:
   • XL ≥1200px: Layout estándar, sidebar fijo
   • LG ≥992px:  Sin cambios
   • MD ≥768px:  Sin cambios
   • SM <768px:  Sidebar colapsable, grids 1-col, Tablas a Tarjetas
   • XS <576px:  Padding reducido, botones full-width
═══════════════════════════════════════════════════════════════ */
    @media (max-width: 991.98px) {
      :root {
        --sidebar-w: 0px;
      }

      .sidebar {
        transform: translateX(-260px);
        width: 260px;
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .sidebar-close {
        display: block;
      }

      .header {
        left: 0;
      }

      .menu-toggle {
        display: flex;
      }

      .main-content {
        margin-left: 0;
      }
    }

    @media (max-width: 767.98px) {
      .main-content {
        padding: 16px 14px 32px;
      }

      .card-body-box {
        padding: 18px;
      }

      .db-info-grid {
        grid-template-columns: 1fr 1fr;
      }

      /* Rescate de botones del header */
      .header-actions .hdr-btn:nth-child(n+2) {
        display: flex;
      }

      .header-actions {
        gap: 6px;
      }

      /* ══ Transformación de Tablas a Tarjetas SIN Tocar HTML ══ */
      .table-wrap {
        overflow: visible;
      }

      table,
      thead,
      tbody,
      th,
      td,
      tr {
        display: block;
        width: 100%;
      }

      /* Ocultar encabezados originales */
      thead tr {
        position: absolute;
        top: -9999px;
        left: -9999px;
      }

      /* Estilo de la tarjeta (Fila) */
      tbody tr {
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        margin-bottom: 16px;
        padding: 12px;
        background: var(--card);
        box-shadow: var(--shadow-sm);
      }

      /* Estilo de cada dato (Celda) */
      tbody td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: none;
        border-bottom: 1px dashed var(--border);
        padding: 10px 0;
        text-align: right;
      }

      tbody td:last-child {
        border-bottom: 0;
        padding-bottom: 0;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 8px;
      }

      /* Formato para los pseudo-títulos inyectados */
      tbody td::before {
        font-weight: 700;
        color: var(--muted);
        font-size: .75rem;
        text-transform: uppercase;
        text-align: left;
        padding-right: 15px;
      }

      /* Inyección de títulos por Módulo usando nth-child */

      /* M1 Dashboard */
      #mod-dashboard tbody td:nth-child(1)::before {
        content: "Archivo";
      }

      #mod-dashboard tbody td:nth-child(2)::before {
        content: "Tipo";
      }

      #mod-dashboard tbody td:nth-child(3)::before {
        content: "Tamaño";
      }

      #mod-dashboard tbody td:nth-child(4)::before {
        content: "Modificado";
      }

      /* M2 Transferencias */
      #mod-transferencias tbody td:nth-child(1)::before {
        content: "ID";
      }

      #mod-transferencias tbody td:nth-child(2)::before {
        content: "Archivo";
      }

      #mod-transferencias tbody td:nth-child(3)::before {
        content: "Receptor";
      }

      #mod-transferencias tbody td:nth-child(4)::before {
        content: "Estado";
      }

      #mod-transferencias tbody td:nth-child(5)::before {
        content: "Expira";
      }

      #mod-transferencias tbody td:nth-child(6)::before {
        content: "Acciones";
      }

      /* M4 Tokens */
      #mod-tokens tbody td:nth-child(1)::before {
        content: "Token";
      }

      #mod-tokens tbody td:nth-child(2)::before {
        content: "Tipo";
      }

      #mod-tokens tbody td:nth-child(3)::before {
        content: "Usos";
      }

      #mod-tokens tbody td:nth-child(4)::before {
        content: "Estado";
      }

      #mod-tokens tbody td:nth-child(5)::before {
        content: "Acciones";
      }

      /* M5 Usuarios */
      #mod-usuarios tbody td:nth-child(1)::before {
        content: "Usuario";
      }

      #mod-usuarios tbody td:nth-child(2)::before {
        content: "Departamento";
      }

      #mod-usuarios tbody td:nth-child(3)::before {
        content: "Rol";
      }

      #mod-usuarios tbody td:nth-child(4)::before {
        content: "Cuota";
      }

      #mod-usuarios tbody td:nth-child(5)::before {
        content: "Estado";
      }

      #mod-usuarios tbody td:nth-child(6)::before {
        content: "Acciones";
      }

      /* M6 Auditoría */
      #mod-auditoria tbody td:nth-child(1)::before {
        content: "Timestamp";
      }

      #mod-auditoria tbody td:nth-child(2)::before {
        content: "Usuario";
      }

      #mod-auditoria tbody td:nth-child(3)::before {
        content: "Acción";
      }

      #mod-auditoria tbody td:nth-child(4)::before {
        content: "Recurso";
      }

      #mod-auditoria tbody td:nth-child(5)::before {
        content: "IP";
      }

      #mod-auditoria tbody td:nth-child(6)::before {
        content: "Resultado";
      }
    }

    @media (max-width: 575.98px) {
      .main-content {
        padding: 12px 10px 28px;
      }

      .card-body-box {
        padding: 14px;
      }

      .header {
        padding: 0 14px;
      }

      .stat-card {
        flex-direction: column;
        text-align: center;
        padding: 16px;
      }

      .db-info-grid {
        grid-template-columns: 1fr;
      }

      .btn-primary-v,
      .btn-secondary-v {
        width: 100%;
        justify-content: center;
      }

      /* Ajuste de wireframes para móvil pequeño */
      .wire-row {
        flex-direction: column;
      }

      .wire-sidebar {
        width: 100%;
        height: 40px !important;
      }
    }

    /* ═══ MISC ═══════════════════════════════════════════════════ */
    .section-divider {
      height: 1px;
      background: var(--border);
      margin: 24px 0;
    }

    .tag {
      display: inline-block;
      background: #EFF6FF;
      color: var(--accent);
      font-size: .72rem;
      font-weight: 700;
      padding: 2px 9px;
      border-radius: 100px;
    }

    .skeleton {
      background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
      background-size: 200% 100%;
      animation: shimmer 1.5s infinite;
      border-radius: 6px;
    }

    @keyframes shimmer {
      0% {
        background-position: 200% 0;
      }

      100% {
        background-position: -200% 0;
      }
    }

    /* ═══ ICONOS LUCIDE (heredan font-size como los antiguos) ═══ */
    [data-lucide],
    svg.lucide {
      width: 1em;
      height: 1em;
      stroke-width: 2;
      vertical-align: -0.125em;
      flex-shrink: 0;
    }
  </style>
</head>

<body>

  <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon"><i data-lucide="send"></i></div>
      <div class="brand-text">TBD System <small>Vasanta Comunicaciones</small></div>
      <button class="sidebar-close" onclick="closeSidebar()"><i data-lucide="x"></i></button>
    </div>

    <p class="nav-section">Principal</p>
    <ul class="sidebar-nav">
      <li><a class="nav-link-item active" onclick="showModule('dashboard',this)">
          <i data-lucide="layout-dashboard" class="nav-icon"></i> Dashboard
        </a></li>
      <li><a class="nav-link-item" onclick="showModule('transferencias',this)">
          <i data-lucide="arrow-left-right" class="nav-icon"></i> Transferencias
          <span class="nav-badge">12</span>
        </a></li>
      <li><a class="nav-link-item" onclick="showModule('archivos',this)">
          <i data-lucide="folder" class="nav-icon"></i> Archivos
        </a></li>
      <li><a class="nav-link-item" onclick="showModule('tokens',this)">
          <i data-lucide="key" class="nav-icon"></i> Tokens / Permisos
          <span class="nav-badge">3</span>
        </a></li>
    </ul>

    <p class="nav-section">Administración</p>
    <ul class="sidebar-nav">
      <li><a class="nav-link-item" onclick="showModule('usuarios',this)">
          <i data-lucide="users" class="nav-icon"></i> Usuarios
        </a></li>

      <li><a class="nav-link-item" onclick="showModule('bd',this)">
          <i data-lucide="database" class="nav-icon"></i> Base de Datos
        </a></li>

    </ul>

    <p class="nav-section">Sistema</p>
    <ul class="sidebar-nav">
      <li><a class="nav-link-item" onclick="showModule('configuracion',this)">
          <i data-lucide="settings" class="nav-icon"></i> Configuración
        </a></li>
    </ul>

    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-avatar">CL</div>
        <div class="user-info">
          <div class="user-name">Carlos Lorca López</div>
          <div class="user-role">Administrador</div>
        </div>
        <i data-lucide="ellipsis" style="color:rgba(255,255,255,.4);margin-left:auto;"></i>
      </div>
    </div>
  </aside>

  <header class="header" id="header">
    <button class="menu-toggle" onclick="toggleSidebar()"><i data-lucide="menu"></i></button>
    <div class="breadcrumb-area">
      <div class="page-title" id="pageTitle">Dashboard</div>
      <div class="page-breadcrumb">TBD System / <span id="pageBreadcrumb">Inicio</span></div>
    </div>
    <div class="header-actions">
      <div class="hdr-btn" title="Buscar"><i data-lucide="search"></i></div>
      <div class="hdr-btn" title="Notificaciones">
        <i data-lucide="bell"></i>
        <span class="notif-dot"></span>
      </div>
      <div class="hdr-btn" title="Ayuda"><i data-lucide="circle-question-mark"></i></div>
      <div class="hdr-avatar" title="Mi perfil">CL</div>
    </div>
  </header>

  <main class="main-content">

    <section class="module active" id="mod-dashboard">
      <div class="grid grid-cols-12 gap-6 gap-4 mb-6">
        <div class="col-span-6 lg:col-span-3">
          <div class="stat-card c-blue">
            <div class="stat-icon si-blue"><i data-lucide="files"></i></div>
            <div>
              <div class="stat-val"><?= count($archivosSubidos) ?></div>
              <div class="stat-lbl">Archivos almacenados</div>
            </div>
          </div>
        </div>
        <div class="col-span-6 lg:col-span-3">
          <div class="stat-card c-teal">
            <div class="stat-icon si-teal"><i data-lucide="hard-drive"></i></div>
            <div>
              <div class="stat-val"><?= fmtBytesPHP(array_sum(array_column($archivosSubidos, 'tamano'))) ?></div>
              <div class="stat-lbl">Espacio ocupado</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card-box">
        <div class="card-header-box">
          <div class="ch-icon"><i data-lucide="folder-open"></i></div>
          <div>
            <h3>Archivos en el servidor</h3>
            <p><?= htmlspecialchars(DIR_ARCHIVOS) ?></p>
          </div>
        </div>
        <div class="card-body-box" style="padding:0;">
          <div class="table-wrap">
            <?php if (empty($archivosSubidos)): ?>
              <div class="alert-v alert-info" style="margin:20px;">
                <i data-lucide="info"></i>
                <span>Todavía no hay archivos en esta carpeta.</span>
              </div>
            <?php else: ?>
              <table>
                <thead>
                  <tr>
                    <th>Archivo</th>
                    <th>Tipo</th>
                    <th>Tamaño</th>
                    <th>Última modificación</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($archivosSubidos as $a): ?>
                    <tr>
                      <td><span class="file-pill"><i data-lucide="file"></i><?= htmlspecialchars($a['nombre']) ?></span></td>
                      <td style="font-size:.8rem;color:var(--muted);"><?= htmlspecialchars($a['tipo']) ?></td>
                      <td><?= fmtBytesPHP($a['tamano']) ?></td>
                      <td style="font-size:.82rem;"><?= date('d/m/Y H:i', $a['modificado']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>

    <section class="module" id="mod-transferencias">
      <div class="grid grid-cols-12 gap-6">
        <div class="lg:col-span-5">
          <div class="card-box">
            <div class="card-header-box">
              <div class="ch-icon"><i data-lucide="circle-plus"></i></div>
              <div>
                <h3>Nueva Transferencia</h3>
                <p>Enviar archivos internos</p>
              </div>
            </div>


            <div class="card-body-box">
              <form id="frmTransfer" novalidate onsubmit="handleTransferSubmit(event)">
                <div class="form-section-title">Datos del archivo</div>
                <div class="mb-4">
                  <label class="form-label"><i data-lucide="cloud-upload"></i> Archivo <span class="req">*</span></label>
                  <div class="file-drop" id="dropzone" onclick="document.getElementById('fileInput').click()"
                    ondragover="dragOver(event)" ondragleave="dragLeave(event)" ondrop="handleDrop(event)">
                    <i data-lucide="cloud-upload"></i>
                    <p>Arrastra el archivo aquí</p>
                    <span>o haz clic para seleccionar</span>
                    <div style="margin-top:8px;font-size:.72rem;color:var(--muted);">Máx. 20 GB · Todos los formatos</div>
                  </div>
                  <input type="file" id="fileInput" hidden onchange="showFileInfo(this)" />
                  <div id="fileInfo" style="display:none;" class="mt-2">
                    <div class="alert-v alert-success" style="padding:8px 12px;">
                      <i data-lucide="circle-check"></i>
                      <span id="fileName" style="font-size:.82rem;"></span>
                    </div>
                  </div>
                </div>
                <div class="mb-4">
                  <label class="form-label">Descripción</label>
                  <textarea class="form-control" id="tDesc" rows="2" maxlength="200" placeholder="¿Qué contiene este archivo? (opcional)" oninput="countChars(this,'cDesc')"></textarea>
                  <div class="char-count"><span id="cDesc">0</span>/200</div>
                </div>

                <div class="form-section-title">Destinatario (opcional)</div>
                <div class="mb-4">
                  <label class="form-label"><i data-lucide="mail"></i> Notificar a este correo</label>
                  <input type="email" class="form-control" id="tReceptor" placeholder="correo@ejemplo.com" />
                  <div class="form-hint">Déjalo vacío si no quieres avisar a nadie por correo.</div>
                </div>

                <div class="form-section-title">Políticas de caducidad</div>
                <div class="grid grid-cols-12 gap-6 gap-2 mb-4">
                  <div class="col-span-6">
                    <label class="form-label"><i data-lucide="calendar-check"></i> Expira en (días)</label>
                    <input type="number" class="form-control" id="tExpiry" value="7" min="0" max="365" />
                    <div class="form-hint" style="margin-top:2px;">0 = sin expiración</div>
                  </div>
                  <div class="col-span-6">
                    <label class="form-label"><i data-lucide="download"></i> Descargas máx.</label>
                    <input type="number" class="form-control" id="tMaxDl" value="5" min="1" max="100" />
                  </div>
                </div>
                <div class="mb-4">
                  <label class="form-label"><i data-lucide="lock"></i> Contraseña de enlace</label>
                  <div class="input-grp">
                    <input type="password" class="form-control" id="tPass" placeholder="Opcional" autocomplete="new-password" />
                    <div class="input-group-end" onclick="togglePwd('tPass','eyeT')"><i data-lucide="eye" id="eyeT"></i></div>
                  </div>
                </div>
                <div class="mb-6">
                  <label class="form-label"><i data-lucide="shield-check"></i> Nivel de acceso</label>
                  <div class="flex gap-4 flex-wrap">
                    <div class="flex items-center gap-2"><input class="form-check-input" type="radio" name="tAccess" value="1" id="ta1" checked /><label class="form-check-label" for="ta1">Solo lectura</label></div>
                    <div class="flex items-center gap-2"><input class="form-check-input" type="radio" name="tAccess" value="2" id="ta2" /><label class="form-check-label" for="ta2">Descarga única</label></div>
                    <div class="flex items-center gap-2"><input class="form-check-input" type="radio" name="tAccess" value="3" id="ta3" /><label class="form-check-label" for="ta3">Descarga múltiple</label></div>
                  </div>
                </div>

                <div class="flex gap-2 flex-wrap">
                  <button type="submit" class="btn-primary-v" id="btnEnviar"><i data-lucide="send"></i> Crear transferencia</button>
                  <button type="button" class="btn-secondary-v" id="btnCancelar" style="display:none;" onclick="cancelarSubida()"><i data-lucide="circle-stop"></i> Cancelar</button>
                  <button type="reset" class="btn-secondary-v" onclick="limpiarFormulario()"><i data-lucide="circle-x"></i> Limpiar</button>
                </div>

                <div class="mb-4">
                  <label class="form-label"><i data-lucide="folder-tree"></i> Carpeta de destino</label>
                  <div class="flex gap-2">
                    <select class="form-select" id="tCarpeta" onchange="cargarArchivosCarpeta()">
                      <option value="">Cargando carpetas...</option>
                    </select>
                    <button type="button" class="btn-secondary-v" onclick="crearCarpetaUI()" title="Crear nueva carpeta">
                      <i data-lucide="folder-plus"></i>
                    </button>
                  </div>
                </div>

              </form>
            </div>
          </div>
        </div>

        <div class="lg:col-span-7">
          <div class="card-box h-full">
            <div class="card-header-box">
              <div class="ch-icon"><i data-lucide="radio"></i></div>
              <div>
                <h3>Estado de la transferencia</h3>
                <p>Progreso y enlace de descarga</p>
              </div>
            </div>
            <div class="card-body-box" id="panelEstado">
              <div id="estadoVacio" style="text-align:center;padding:40px 20px;color:var(--muted);">
                <i data-lucide="cloud-upload" style="font-size:2.4rem;color:var(--border);"></i>
                <p style="margin-top:10px;font-size:.85rem;">Selecciona un archivo y dale clic en "Crear transferencia" para ver aquí el progreso.</p>
              </div>

              <div id="estadoProgreso" style="display:none;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                  <i data-lucide="refresh-cw" style="font-size:1.3rem;color:var(--accent);"></i>
                  <strong id="nombreSubiendo" style="font-size:.9rem;"></strong>
                </div>
                <div class="progress-bar-custom">
                  <div class="progress-fill" id="uploadProgress" style="width:0%"></div>
                </div>
                <div id="uploadDetalle" style="font-size:.78rem;color:var(--muted);margin-top:6px;"></div>
              </div>

              <div id="resultado"></div>

              <div id="resultado"></div>

              <!-- NUEVO: Contenedor del explorador en vivo -->
              <div class="section-divider mt-6 mb-4"></div>
              <div id="exploradorCarpeta"></div>

            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="module" id="mod-archivos">
      <div class="card-box mb-6">
        <div class="card-header-box">
          <div class="ch-icon"><i data-lucide="folder-plus"></i></div>
          <div>
            <h3>Registrar Archivo</h3>
            <p>Ingresar metadatos al sistema</p>
          </div>
        </div>
        <div class="card-body-box">
          <form id="frmArchivo" novalidate onsubmit="handleSubmit(event,'frmArchivo')">
            <div class="grid grid-cols-12 gap-6 gap-4">
              <div class="md:col-span-6">
                <div class="form-section-title">Metadatos del archivo</div>
                <div class="mb-4">
                  <label class="form-label">Nombre del archivo <span class="req">*</span></label>
                  <input type="text" class="form-control" required placeholder="ej. backup_Q2_2025.zip" id="aNombre" />
                  <div class="invalid-feedback">El nombre es obligatorio.</div>
                </div>
                <div class="mb-4">
                  <label class="form-label">Tipo de archivo <span class="req">*</span></label>
                  <select class="form-select" required id="aTipo">
                    <option value="">— Seleccionar —</option>
                    <option>application/zip</option>
                    <option>application/pdf</option>
                    <option>application/x-tar</option>
                    <option>image/jpeg</option>
                    <option>image/png</option>
                    <option>application/vnd.ms-excel</option>
                    <option>application/octet-stream</option>
                  </select>
                </div>
                <div class="grid grid-cols-12 gap-6 gap-2 mb-4">
                  <div class="col-span-6">
                    <label class="form-label">Tamaño (MB) <span class="req">*</span></label>
                    <input type="number" class="form-control" required placeholder="ej. 2350" min="1" id="aTam" />
                  </div>
                  <div class="col-span-6">
                    <label class="form-label">Hash SHA-256</label>
                    <input type="text" class="form-control" placeholder="Automático" readonly id="aHash" style="background:#f8fafc;" />
                  </div>
                </div>
                <div class="mb-4">
                  <label class="form-label">Ruta en Data Node <span class="req">*</span></label>
                  <div class="input-grp">
                    <div class="input-group-addon"><i data-lucide="server"></i></div>
                    <input type="text" class="form-control" required placeholder="/data/2025/Q2/" id="aRuta" />
                  </div>
                </div>
              </div>
            </div>

            <div class="section-divider"></div>
            <div class="flex gap-2 flex-wrap">
              <button type="submit" class="btn-primary-v"><i data-lucide="save"></i> Registrar archivo</button>
              <button type="button" class="btn-secondary-v" onclick="autoHash()"><i data-lucide="hash"></i> Generar hash</button>
              <button type="reset" class="btn-secondary-v"><i data-lucide="circle-x"></i> Limpiar</button>
            </div>



          </form>
        </div>
      </div>
    </section>

    <section class="module" id="mod-tokens">
      <div class="grid grid-cols-12 gap-6">
        <div class="lg:col-span-6">
          <div class="card-box">
            <div class="card-header-box">
              <div class="ch-icon"><i data-lucide="key"></i></div>
              <div>
                <h3>Generar Token de Acceso</h3>
                <p>Control de permisos y enlace único</p>
              </div>
            </div>
            <div class="card-body-box">
              <form id="frmToken" novalidate onsubmit="handleSubmit(event,'frmToken')">
                <div class="form-section-title">Configuración del token</div>
                <div class="mb-4">
                  <label class="form-label">Transferencia asociada <span class="req">*</span></label>
                  <select class="form-select" required>
                    <option value="">— Seleccionar transferencia —</option>
                    <option>#TBD-001 · backup_Q2.zip</option>
                    <option>#TBD-002 · reporte_v3.pdf</option>
                    <option>#TBD-004 · api_build.tar.gz</option>
                  </select>
                </div>
                <div class="mb-4">
                  <label class="form-label">Tipo de token</label>
                  <div class="flex gap-4 flex-wrap">
                    <div class="flex items-center gap-2"><input class="form-check-input" type="radio" name="tkType" value="download" id="tk1" checked /><label class="form-check-label" for="tk1"><i data-lucide="download"></i> Descarga</label></div>
                    <div class="flex items-center gap-2"><input class="form-check-input" type="radio" name="tkType" value="view" id="tk2" /><label class="form-check-label" for="tk2"><i data-lucide="eye"></i> Solo vista</label></div>
                    <div class="flex items-center gap-2"><input class="form-check-input" type="radio" name="tkType" value="admin" id="tk3" /><label class="form-check-label" for="tk3"><i data-lucide="shield"></i> Admin</label></div>
                  </div>
                </div>
                <div class="grid grid-cols-12 gap-6 gap-2 mb-4">
                  <div class="col-span-6">
                    <label class="form-label">Vigencia <span class="req">*</span></label>
                    <select class="form-select" required>
                      <option>1 hora</option>
                      <option>24 horas</option>
                      <option selected>7 días</option>
                      <option>30 días</option>
                    </select>
                  </div>
                  <div class="col-span-6">
                    <label class="form-label">Usos máximos</label>
                    <input type="number" class="form-control" value="1" min="1" max="999" />
                  </div>
                </div>
                <div class="mb-4">
                  <label class="form-label">Restricción por IP</label>
                  <input type="text" class="form-control" placeholder="ej. 192.168.1.0/24 (opcional)" />
                  <div class="form-hint">CIDR o IP exacta. Vacío = sin restricción.</div>
                </div>
                <div class="mb-4">
                  <label class="form-label">Alcance del permiso</label>
                  <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-2"><input class="form-check-input" type="checkbox" id="pk1" checked /><label class="form-check-label" for="pk1">Descargar archivo</label></div>
                    <div class="flex items-center gap-2"><input class="form-check-input" type="checkbox" id="pk2" /><label class="form-check-label" for="pk2">Ver metadatos</label></div>
                    <div class="flex items-center gap-2"><input class="form-check-input" type="checkbox" id="pk3" /><label class="form-check-label" for="pk3">Re-compartir enlace</label></div>
                    <div class="flex items-center gap-2"><input class="form-check-input" type="checkbox" id="pk4" /><label class="form-check-label" for="pk4">Revocar propio acceso</label></div>
                  </div>
                </div>
                <div class="mb-6">
                  <label class="form-label">Nivel de seguridad</label>
                  <input type="range" class="form-range" id="secLevel" min="1" max="3" value="2" oninput="updateRange(this,'secLevelVal',['Básico','Estándar','Alto'])" />
                  <div class="flex justify-between items-center mt-1">
                    <span style="font-size:.75rem;color:var(--muted);">Básico</span>
                    <span class="range-value" id="secLevelVal">Estándar</span>
                    <span style="font-size:.75rem;color:var(--muted);">Alto</span>
                  </div>
                </div>
                <button type="submit" class="btn-primary-v w-full"><i data-lucide="key"></i> Generar token seguro</button>
              </form>
            </div>
          </div>
        </div>
        <div class="lg:col-span-6">
          <div class="card-box mb-4">
            <div class="card-header-box">
              <div class="ch-icon"><i data-lucide="list-checks"></i></div>
              <div>
                <h3>Tokens activos</h3>
                <p>Gestión y revocación</p>
              </div>
            </div>
            <div class="card-body-box" style="padding:0;">
              <div class="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th>Token</th>
                      <th>Tipo</th>
                      <th>Usos</th>
                      <th>Estado</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td style="font-family:monospace;font-size:.72rem;">eyJhb...Xk9A</td>
                      <td><span class="tag">Descarga</span></td>
                      <td>1/1</td>
                      <td><span class="badge-status bs-active">● Vigente</span></td>
                      <td>
                        <div class="flex gap-1">
                          <div class="btn-icon-v"><i data-lucide="clipboard"></i></div>
                          <div class="btn-icon-v" style="color:var(--danger);"><i data-lucide="circle-slash"></i></div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td style="font-family:monospace;font-size:.72rem;">mZ3Qp...rT2w</td>
                      <td><span class="tag">Vista</span></td>
                      <td>0/5</td>
                      <td><span class="badge-status bs-active">● Vigente</span></td>
                      <td>
                        <div class="flex gap-1">
                          <div class="btn-icon-v"><i data-lucide="clipboard"></i></div>
                          <div class="btn-icon-v" style="color:var(--danger);"><i data-lucide="circle-slash"></i></div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td style="font-family:monospace;font-size:.72rem;">pQ9Kl...nV7s</td>
                      <td><span class="tag">Admin</span></td>
                      <td>3/3</td>
                      <td><span class="badge-status bs-expired">✕ Expirado</span></td>
                      <td>
                        <div class="flex gap-1">
                          <div class="btn-icon-v"><i data-lucide="rotate-cw"></i></div>
                          <div class="btn-icon-v" style="color:var(--danger);"><i data-lucide="trash-2"></i></div>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="card-box">
            <div class="card-body-box">
              <p class="form-section-title">Token generado</p>
              <div class="code-block" id="tokenOutput">
                <span class="cm">// El token aparecerá aquí tras la generación</span>
                <br /><span class="cm">// Ejemplo:</span>
                <br /><span class="var">eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9</span>
                <br /><span class="var">.eyJzdWIiOiJUQkQtMDAxIiwidHlwZSI6ImRvd25sb2FkIiwiZXhwIjoxNzI0MjIyNDAwfQ</span>
                <br /><span class="var">.Xk9A3mQ7pR2wKlnT4sV8bN6cJ5hY1dF0gE9</span>
              </div>
              <div class="mt-4 flex gap-2">
                <button class="btn-secondary-v btn-sm-v" onclick="copyToken()"><i data-lucide="clipboard"></i> Copiar token</button>
                <button class="btn-secondary-v btn-sm-v"><i data-lucide="qr-code"></i> QR del enlace</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="module" id="mod-usuarios">
      <div class="grid grid-cols-12 gap-6">
        <div class="lg:col-span-5">
          <div class="card-box">
            <div class="card-header-box">
              <div class="ch-icon"><i data-lucide="user-plus"></i></div>
              <div>
                <h3>Registrar Usuario</h3>
                <p>Alta en el sistema TBD</p>
              </div>
            </div>
            <div class="card-body-box">
              <form id="frmUsuario" novalidate onsubmit="handleSubmit(event,'frmUsuario')">
                <div class="form-section-title">Datos personales</div>
                <div class="grid grid-cols-12 gap-6 gap-2 mb-4">
                  <div class="col-span-6">
                    <label class="form-label">Nombre <span class="req">*</span></label>
                    <input type="text" class="form-control" required placeholder="Carlos" />
                  </div>
                  <div class="col-span-6">
                    <label class="form-label">Apellido <span class="req">*</span></label>
                    <input type="text" class="form-control" required placeholder="Lorca" />
                  </div>
                </div>
                <div class="mb-4">
                  <label class="form-label"><i data-lucide="mail"></i> Email corporativo <span class="req">*</span></label>
                  <div class="input-grp">
                    <input type="email" class="form-control" required placeholder="usuario" />
                    <div class="input-group-end" style="border:1.5px solid var(--border);border-left:none;background:var(--surface);padding:0 10px;font-size:.82rem;color:var(--muted);">@vasanta.com</div>
                  </div>
                </div>
                <div class="mb-4">
                  <label class="form-label">Departamento <span class="req">*</span></label>
                  <select class="form-select" required>
                    <option value="">— Seleccionar —</option>
                    <option>Sistemas e Informática</option>
                    <option>Almacén y Logística</option>
                    <option>Finanzas</option>
                    <option>Recursos Humanos</option>
                    <option>Dirección General</option>
                  </select>
                </div>
                <div class="form-section-title">Acceso y seguridad</div>
                <div class="mb-4">
                  <label class="form-label"><i data-lucide="contact"></i> Rol <span class="req">*</span></label>
                  <select class="form-select" required>
                    <option value="">— Seleccionar rol —</option>
                    <option>Administrador</option>
                    <option>Operador</option>
                    <option>Visualizador</option>
                    <option>Solo descarga</option>
                  </select>
                </div>
                <div class="mb-4">
                  <label class="form-label"><i data-lucide="lock"></i> Contraseña temporal <span class="req">*</span></label>
                  <div class="input-grp">
                    <input type="password" class="form-control" required placeholder="Mín. 8 caracteres" id="uPass" />
                    <div class="input-group-end" onclick="togglePwd('uPass')"><i data-lucide="eye" id="eyeU"></i></div>
                  </div>
                </div>
                <div class="mb-4">
                  <label class="form-label">Cuota de almacenamiento</label>
                  <input type="range" class="form-range" id="quota" min="1" max="4" value="2" oninput="updateRange(this,'quotaVal',['10 GB','50 GB','100 GB','Sin límite'])" />
                  <div class="flex justify-between items-center mt-1">
                    <span style="font-size:.75rem;color:var(--muted);">10 GB</span>
                    <span class="range-value" id="quotaVal">50 GB</span>
                    <span style="font-size:.75rem;color:var(--muted);">Sin límite</span>
                  </div>
                </div>
                <div class="mb-4">
                  <div class="toggle-wrap">
                    <label class="toggle"><input type="checkbox" checked /><span class="toggle-slider"></span></label>
                    <span class="toggle-label">Cuenta activa al crear</span>
                  </div>
                </div>
                <div class="mb-6">
                  <div class="toggle-wrap">
                    <label class="toggle"><input type="checkbox" /><span class="toggle-slider"></span></label>
                    <span class="toggle-label">Forzar cambio de contraseña</span>
                  </div>
                </div>
                <div class="flex gap-2 flex-wrap">
                  <button type="submit" class="btn-primary-v"><i data-lucide="user-check"></i> Registrar usuario</button>
                  <button type="reset" class="btn-secondary-v"><i data-lucide="circle-x"></i> Cancelar</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="lg:col-span-7">
          <div class="card-box">
            <div class="card-header-box">
              <div class="ch-icon"><i data-lucide="users"></i></div>
              <div>
                <h3>Directorio de usuarios</h3>
                <p>Gestión de accesos al sistema</p>
              </div>
            </div>
            <div class="card-body-box" style="padding:0;">
              <div class="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th>Usuario</th>
                      <th>Departamento</th>
                      <th>Rol</th>
                      <th>Cuota</th>
                      <th>Estado</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                          <div class="user-avatar" style="width:28px;height:28px;font-size:.7rem;">CL</div>
                          <div>
                            <div style="font-size:.82rem;font-weight:600;">Carlos Lorca</div>
                            <div style="font-size:.72rem;color:var(--muted);">c.lorca@vasanta.com</div>
                          </div>
                        </div>
                      </td>
                      <td>Sistemas</td>
                      <td><span class="tag">Admin</span></td>
                      <td>100 GB</td>
                      <td><span class="badge-status bs-active">● Activo</span></td>
                      <td>
                        <div class="flex gap-1">
                          <div class="btn-icon-v"><i data-lucide="pencil"></i></div>
                          <div class="btn-icon-v" style="color:var(--danger);"><i data-lucide="user-x"></i></div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                          <div class="user-avatar" style="width:28px;height:28px;font-size:.7rem;background:linear-gradient(135deg,var(--success),#34D399);">LM</div>
                          <div>
                            <div style="font-size:.82rem;font-weight:600;">Luis Martínez</div>
                            <div style="font-size:.72rem;color:var(--muted);">l.martinez@vasanta.com</div>
                          </div>
                        </div>
                      </td>
                      <td>Logística</td>
                      <td><span class="tag">Operador</span></td>
                      <td>50 GB</td>
                      <td><span class="badge-status bs-active">● Activo</span></td>
                      <td>
                        <div class="flex gap-1">
                          <div class="btn-icon-v"><i data-lucide="pencil"></i></div>
                          <div class="btn-icon-v" style="color:var(--danger);"><i data-lucide="user-x"></i></div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                          <div class="user-avatar" style="width:28px;height:28px;font-size:.7rem;background:linear-gradient(135deg,var(--warning),#FCD34D);color:#92400E;">AG</div>
                          <div>
                            <div style="font-size:.82rem;font-weight:600;">Ana García</div>
                            <div style="font-size:.72rem;color:var(--muted);">a.garcia@vasanta.com</div>
                          </div>
                        </div>
                      </td>
                      <td>Finanzas</td>
                      <td><span class="tag">Visualizador</span></td>
                      <td>10 GB</td>
                      <td><span class="badge-status bs-pending">⏳ Pendiente</span></td>
                      <td>
                        <div class="flex gap-1">
                          <div class="btn-icon-v"><i data-lucide="pencil"></i></div>
                          <div class="btn-icon-v" style="color:var(--danger);"><i data-lucide="user-x"></i></div>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>


    <section class="module" id="mod-bd">
      <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12">
          <div class="card-box">
            <div class="card-header-box">
              <div class="ch-icon"><i data-lucide="database"></i></div>
              <div>
                <h3>Conectividad con la Base de Datos</h3>
                <p>MySQL · vasanta_db · Configuración y estado de la conexión</p>
              </div>
            </div>
            <div class="card-body-box">

              <div class="db-status-bar mb-6">
                <div class="db-dot connected"></div>
                <div style="flex:1;">
                  <div style="font-weight:700;">Conexión establecida correctamente</div>
                  <div style="font-size:.78rem;color:rgba(255,255,255,.5);">PDO MySQL · Latencia 4ms · Pool: 3/10 conexiones activas</div>
                </div>
                <button class="btn-secondary-v btn-sm-v" style="color:var(--accent);border-color:var(--accent);" onclick="testConn()">
                  <i data-lucide="plug"></i> Re-testear
                </button>
              </div>

              <div class="db-info-grid mb-6">
                <div class="db-info-item">
                  <div class="db-info-label">Motor</div>
                  <div class="db-info-val">MySQL 8.0.33</div>
                </div>
                <div class="db-info-item">
                  <div class="db-info-label">Base de datos</div>
                  <div class="db-info-val">vasanta_db</div>
                </div>
                <div class="db-info-item">
                  <div class="db-info-label">Host</div>
                  <div class="db-info-val">192.168.1.100:3306</div>
                </div>
                <div class="db-info-item">
                  <div class="db-info-label">Usuario BD</div>
                  <div class="db-info-val">tbd_user</div>
                </div>
                <div class="db-info-item">
                  <div class="db-info-label">Charset</div>
                  <div class="db-info-val">utf8mb4</div>
                </div>
                <div class="db-info-item">
                  <div class="db-info-label">Collation</div>
                  <div class="db-info-val">utf8mb4_unicode_ci</div>
                </div>
                <div class="db-info-item">
                  <div class="db-info-label">Tablas</div>
                  <div class="db-info-val">6 tablas</div>
                </div>
                <div class="db-info-item">
                  <div class="db-info-label">Servidor web</div>
                  <div class="db-info-val">Apache 2.4 · PHP 8.2</div>
                </div>
              </div>

              <p class="form-section-title">config/database.php — Archivo de configuración</p>
              <div class="code-block mb-6">
                <span class="cm">&lt;?php</span>
                <br /><span class="cm">// ── Configuración de conexión a la BD ─────────────────────────</span>
                <br /><span class="kw">define</span>(<span class="str">'DB_HOST'</span>, <span class="str">'192.168.1.100'</span>);
                <br /><span class="kw">define</span>(<span class="str">'DB_PORT'</span>, <span class="str">'3306'</span>);
                <br /><span class="kw">define</span>(<span class="str">'DB_NAME'</span>, <span class="str">'vasanta_db'</span>);
                <br /><span class="kw">define</span>(<span class="str">'DB_USER'</span>, <span class="str">'tbd_user'</span>);
                <br /><span class="kw">define</span>(<span class="str">'DB_PASS'</span>, <span class="str">'••••••••••••'</span>);
                <br /><span class="kw">define</span>(<span class="str">'DB_CHARSET'</span>, <span class="str">'utf8mb4'</span>);
                <br />
                <br /><span class="cm">// ── Clase de conexión PDO ──────────────────────────────────────</span>
                <br /><span class="kw">class</span> <span class="fn">Database</span> {
                <br /> <span class="kw">private static</span> <span class="var">$instance</span> = <span class="kw">null</span>;
                <br />
                <br /> <span class="kw">public static function</span> <span class="fn">getInstance</span>(): PDO {
                <br /> <span class="kw">if</span> (<span class="var">self</span>::<span class="var">$instance</span> === <span class="kw">null</span>) {
                <br /> <span class="var">$dsn</span> = <span class="str">"mysql:host="</span>.DB_HOST.<span class="str">";port="</span>.DB_PORT.
                <br /> <span class="str">";dbname="</span>.DB_NAME.<span class="str">";charset="</span>.DB_CHARSET;
                <br /> <span class="var">self</span>::<span class="var">$instance</span> = <span class="kw">new</span> <span class="fn">PDO</span>(<span class="var">$dsn</span>, DB_USER, DB_PASS, [
                <br /> PDO::<span class="kw">ATTR_ERRMODE</span> =&gt; PDO::<span class="kw">ERRMODE_EXCEPTION</span>,
                <br /> PDO::<span class="kw">ATTR_DEFAULT_FETCH_MODE</span> =&gt; PDO::<span class="kw">FETCH_ASSOC</span>,
                <br /> PDO::<span class="kw">ATTR_EMULATE_PREPARES</span> =&gt; <span class="kw">false</span>,
                <br /> ]);
                <br /> }
                <br /> <span class="kw">return self</span>::<span class="var">$instance</span>;
                <br /> }
                <br />}
                <br />
                <br /><span class="cm">// ── Uso: $db = Database::getInstance(); ───────────────────────</span>
              </div>

              <p class="form-section-title">Esquema DDL — Tablas principales</p>
              <div class="code-block mb-6">
                <span class="cm">-- ── Tabla de usuarios ──────────────────────────────────────────</span>
                <br /><span class="kw">CREATE TABLE</span> <span class="fn">usuarios</span> (
                <br /> <span class="var">id</span> INT UNSIGNED AUTO_INCREMENT <span class="kw">PRIMARY KEY</span>,
                <br /> <span class="var">nombre</span> VARCHAR(100) NOT NULL,
                <br /> <span class="var">email</span> VARCHAR(150) NOT NULL UNIQUE,
                <br /> <span class="var">password</span> CHAR(60) NOT NULL, <span class="cm">-- bcrypt</span>
                <br /> <span class="var">rol</span> ENUM(<span class="str">'admin','operador','visualizador'</span>) DEFAULT <span class="str">'visualizador'</span>,
                <br /> <span class="var">activo</span> TINYINT(1) DEFAULT 1,
                <br /> <span class="var">creado_en</span> TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                <br />) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                <br />
                <br /><span class="cm">-- ── Tabla de archivos ──────────────────────────────────────────</span>
                <br /><span class="kw">CREATE TABLE</span> <span class="fn">archivos</span> (
                <br /> <span class="var">id</span> INT UNSIGNED AUTO_INCREMENT <span class="kw">PRIMARY KEY</span>,
                <br /> <span class="var">nombre</span> VARCHAR(255) NOT NULL,
                <br /> <span class="var">tipo_mime</span> VARCHAR(100),
                <br /> <span class="var">tamanio_mb</span> DECIMAL(10,2),
                <br /> <span class="var">hash_sha256</span> CHAR(64),
                <br /> <span class="var">ruta_nodo</span> VARCHAR(500),
                <br /> <span class="var">usuario_id</span> INT UNSIGNED, <span class="kw">FOREIGN KEY</span> (<span class="var">usuario_id</span>) <span class="kw">REFERENCES</span> <span class="fn">usuarios</span>(<span class="var">id</span>),
                <br /> <span class="var">estado</span> ENUM(<span class="str">'activo','expirado','eliminado'</span>) DEFAULT <span class="str">'activo'</span>,
                <br /> <span class="var">expira_en</span> DATETIME,
                <br /> <span class="var">creado_en</span> TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                <br />) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                <br />
                <br /><span class="cm">-- ── Tabla de transferencias ─────────────────────────────────────</span>
                <br /><span class="kw">CREATE TABLE</span> <span class="fn">transferencias</span> (
                <br /> <span class="var">id</span> INT UNSIGNED AUTO_INCREMENT <span class="kw">PRIMARY KEY</span>,
                <br /> <span class="var">archivo_id</span> INT UNSIGNED, <span class="kw">FOREIGN KEY</span> (<span class="var">archivo_id</span>) <span class="kw">REFERENCES</span> <span class="fn">archivos</span>(<span class="var">id</span>),
                <br /> <span class="var">emisor_id</span> INT UNSIGNED, <span class="kw">FOREIGN KEY</span> (<span class="var">emisor_id</span>) <span class="kw">REFERENCES</span> <span class="fn">usuarios</span>(<span class="var">id</span>),
                <br /> <span class="var">receptor_id</span> INT UNSIGNED, <span class="kw">FOREIGN KEY</span> (<span class="var">receptor_id</span>) <span class="kw">REFERENCES</span> <span class="fn">usuarios</span>(<span class="var">id</span>),
                <br /> <span class="var">estado</span> ENUM(<span class="str">'pendiente','activo','expirado','eliminado'</span>) DEFAULT <span class="str">'pendiente'</span>,
                <br /> <span class="var">creado_en</span> TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                <br />) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                <br />
                <br /><span class="cm">-- ── Tabla de tokens ─────────────────────────────────────────────</span>
                <br /><span class="kw">CREATE TABLE</span> <span class="fn">tokens</span> (
                <br /> <span class="var">id</span> INT UNSIGNED AUTO_INCREMENT <span class="kw">PRIMARY KEY</span>,
                <br /> <span class="var">transferencia_id</span> INT UNSIGNED, <span class="kw">FOREIGN KEY</span> (<span class="var">transferencia_id</span>) <span class="kw">REFERENCES</span> <span class="fn">transferencias</span>(<span class="var">id</span>),
                <br /> <span class="var">token_hash</span> CHAR(64) NOT NULL UNIQUE,
                <br /> <span class="var">tipo</span> ENUM(<span class="str">'descarga','vista','admin'</span>),
                <br /> <span class="var">usos_max</span> TINYINT UNSIGNED DEFAULT 1,
                <br /> <span class="var">usos_actual</span> TINYINT UNSIGNED DEFAULT 0,
                <br /> <span class="var">expira_en</span> DATETIME NOT NULL,
                <br /> <span class="var">creado_en</span> TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                <br />) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
              </div>

              <p class="form-section-title">Probar conexión</p>
              <form class="grid grid-cols-12 gap-6 gap-2" onsubmit="return false;">
                <div class="col-span-12 sm:col-span-4">
                  <label class="form-label">Host / IP</label>
                  <input type="text" class="form-control" value="192.168.1.100" />
                </div>
                <div class="col-span-6 sm:col-span-2">
                  <label class="form-label">Puerto</label>
                  <input type="number" class="form-control" value="3306" />
                </div>
                <div class="col-span-6 sm:col-span-3">
                  <label class="form-label">Base de datos</label>
                  <input type="text" class="form-control" value="vasanta_db" />
                </div>
                <div class="col-span-12 sm:col-span-3 flex items-end">
                  <button class="btn-primary-v w-full" onclick="testConn()"><i data-lucide="plug"></i> Probar conexión</button>
                </div>
              </form>
              <div id="connResult" class="mt-4" style="display:none;">
                <div class="alert-v alert-success"><i data-lucide="circle-check"></i><span>Conexión exitosa · MySQL 8.0.33 · Latencia 4ms · vasanta_db accesible</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>


    <section class="module" id="mod-configuracion">
      <div class="grid grid-cols-12 gap-6">
        <div class="lg:col-span-6">
          <div class="card-box">
            <div class="card-header-box">
              <div class="ch-icon"><i data-lucide="settings-2"></i></div>
              <div>
                <h3>Configuración del sistema</h3>
                <p>Parámetros globales TBD</p>
              </div>
            </div>
            <div class="card-body-box">
              <form onsubmit="return false;">
                <div class="form-section-title">Límites de transferencia</div>
                <div class="mb-4">
                  <label class="form-label">Tamaño máximo de archivo (GB)</label>
                  <input type="number" class="form-control" value="20" min="1" max="100" />
                </div>
                <div class="mb-4">
                  <label class="form-label">Caducidad por defecto (días)</label>
                  <input type="number" class="form-control" value="7" min="1" max="365" />
                </div>
                <div class="mb-4">
                  <label class="form-label">Descargas máximas por defecto</label>
                  <input type="number" class="form-control" value="5" min="1" />
                </div>
                <div class="form-section-title">Seguridad</div>
                <div class="mb-4">
                  <div class="toggle-wrap"><label class="toggle"><input type="checkbox" checked /><span class="toggle-slider"></span></label><span class="toggle-label">Requerir contraseña en tokens</span></div>
                </div>
                <div class="mb-4">
                  <div class="toggle-wrap"><label class="toggle"><input type="checkbox" checked /><span class="toggle-slider"></span></label><span class="toggle-label">Logging de auditoría completo</span></div>
                </div>
                <div class="mb-4">
                  <div class="toggle-wrap"><label class="toggle"><input type="checkbox" /><span class="toggle-slider"></span></label><span class="toggle-label">Modo mantenimiento</span></div>
                </div>
                <div class="mb-6">
                  <label class="form-label">Tiempo de sesión inactiva (min)</label>
                  <input type="number" class="form-control" value="30" min="5" />
                </div>
                <button class="btn-primary-v"><i data-lucide="save"></i> Guardar configuración</button>
              </form>
            </div>
          </div>
        </div>
        <div class="lg:col-span-6">
          <div class="card-box mb-4">
            <div class="card-header-box">
              <div class="ch-icon"><i data-lucide="bell"></i></div>
              <div>
                <h3>Notificaciones</h3>
                <p>Alertas del sistema</p>
              </div>
            </div>
            <div class="card-body-box">
              <div class="mb-4">
                <div class="toggle-wrap"><label class="toggle"><input type="checkbox" checked /><span class="toggle-slider"></span></label><span class="toggle-label">Email al crear transferencia</span></div>
              </div>
              <div class="mb-4">
                <div class="toggle-wrap"><label class="toggle"><input type="checkbox" checked /><span class="toggle-slider"></span></label><span class="toggle-label">Email al expirar enlace</span></div>
              </div>
              <div class="mb-4">
                <div class="toggle-wrap"><label class="toggle"><input type="checkbox" /><span class="toggle-slider"></span></label><span class="toggle-label">Reporte diario automático</span></div>
              </div>
              <div class="mb-4">
                <div class="toggle-wrap"><label class="toggle"><input type="checkbox" checked /><span class="toggle-slider"></span></label><span class="toggle-label">Alerta de fallo de BD</span></div>
              </div>
              <div class="mb-4">
                <label class="form-label">Email de alertas del sistema</label>
                <input type="email" class="form-control" value="admin@vasanta.com" />
              </div>
              <button class="btn-primary-v btn-sm-v"><i data-lucide="save"></i> Guardar</button>
            </div>
          </div>
          <div class="card-box">
            <div class="card-body-box">
              <p class="form-section-title">Responsive Design — Breakpoints activos</p>
              <div style="display:flex;flex-direction:column;gap:8px;">
                <div class="flex justify-between items-center p-2" style="background:var(--surface);border-radius:var(--radius-sm);"><span style="font-size:.82rem;font-weight:600;">XS — &lt;576px</span><span class="badge-status bs-active">● Móvil pequeño</span></div>
                <div class="flex justify-between items-center p-2" style="background:var(--surface);border-radius:var(--radius-sm);"><span style="font-size:.82rem;font-weight:600;">SM — ≥576px</span><span class="badge-status bs-active">● Smartphone</span></div>
                <div class="flex justify-between items-center p-2" style="background:var(--surface);border-radius:var(--radius-sm);"><span style="font-size:.82rem;font-weight:600;">MD — ≥768px</span><span class="badge-status bs-active">● Tablet</span></div>
                <div class="flex justify-between items-center p-2" style="background:var(--surface);border-radius:var(--radius-sm);"><span style="font-size:.82rem;font-weight:600;">LG — ≥992px</span><span class="badge-status bs-active">● Laptop</span></div>
                <div class="flex justify-between items-center p-2" style="background:var(--surface);border-radius:var(--radius-sm);"><span style="font-size:.82rem;font-weight:600;">XL — ≥1200px</span><span class="badge-status bs-active">● Desktop</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>
  <div id="toast-area"></div>

  <script>
    // ── Iconos Lucide: render inicial y para HTML inyectado dinámicamente ──
    let __iconTimer = null;

    function renderIcons() {
      clearTimeout(__iconTimer);
      __iconTimer = setTimeout(() => window.lucide && window.lucide.createIcons(), 0);
    }
    // Cambia tu evento DOMContentLoaded por este:
    document.addEventListener('DOMContentLoaded', () => {
      renderIcons();
      new MutationObserver(renderIcons).observe(document.body, {
        childList: true,
        subtree: true
      });
      cargarCarpetasUI(); // <--- Ahora iniciamos cargando el menú de carpetas
    });

    // ── Navigation ────────────────────────────────────────────────
    const titles = {
      dashboard: 'Dashboard',
      transferencias: 'Transferencias',
      archivos: 'Archivos',
      tokens: 'Tokens & Permisos',
      usuarios: 'Usuarios',
      auditoria: 'Auditoría',
      bd: 'Base de Datos',
      bosquejo: 'Bosquejo de Módulos',
      configuracion: 'Configuración'
    };
    const crumbs = {
      dashboard: 'Inicio',
      transferencias: 'Módulo de Transferencias',
      archivos: 'Gestión de Archivos',
      tokens: 'Control de Acceso',
      usuarios: 'Administración de Usuarios',
      auditoria: 'Registro de Auditoría',
      bd: 'Conectividad BD',
      bosquejo: 'Wireframes del Sistema',
      configuracion: 'Parámetros del Sistema'
    };

    function showModule(id, el) {
      document.querySelectorAll('.module').forEach(m => m.classList.remove('active'));
      document.querySelectorAll('.nav-link-item').forEach(n => n.classList.remove('active'));
      document.getElementById('mod-' + id).classList.add('active');
      if (el) el.classList.add('active');
      document.getElementById('pageTitle').textContent = titles[id] || id;
      document.getElementById('pageBreadcrumb').textContent = crumbs[id] || id;
      if (window.innerWidth < 992) closeSidebar();
    }

    // ── Sidebar ───────────────────────────────────────────────────
    function toggleSidebar() {
      const s = document.getElementById('sidebar');
      const o = document.getElementById('overlay');
      s.classList.toggle('open');
      o.classList.toggle('show');
    }

    function closeSidebar() {
      document.getElementById('sidebar').classList.remove('open');
      document.getElementById('overlay').classList.remove('show');
    }

    // ── Toast ─────────────────────────────────────────────────────
    function toast(msg, icon = 'circle-check', color = 'var(--success)') {
      const a = document.getElementById('toast-area');
      const t = document.createElement('div');
      t.className = 'toast-v';
      t.style.borderLeftColor = color;
      t.innerHTML = `<i data-lucide="${icon}" style="color:${color};font-size:1.1rem;"></i><span>${msg}</span>`;
      a.appendChild(t);
      setTimeout(() => {
        t.style.opacity = '0';
        t.style.transition = 'opacity .3s';
        setTimeout(() => t.remove(), 300);
      }, 3500);
    }

    // ── Formularios de demo (Archivos, Tokens, Usuarios) ────────────
    // Este handleSubmit genérico YA NO se usa para Transferencias.
    function handleSubmit(e, id) {
      e.preventDefault();
      const form = document.getElementById(id);
      form.classList.add('was-validated');
      const msgs = {
        frmArchivo: 'Archivo registrado en la BD',
        frmToken: 'Token generado correctamente',
        frmUsuario: 'Usuario registrado en el sistema'
      };
      if (form.checkValidity()) {
        toast(msgs[id] || 'Operación exitosa');
        if (id === 'frmToken') {
          document.getElementById('tokenOutput').innerHTML = '<span class="cm">// Token generado:</span><br/><span class="var">eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9</span><br/><span class="var">.eyJzdWIiOiJUQkQtMDAxIiwidHlwZSI6ImRvd25sb2FkIiwiZXhwIjoxNzI0MjIyNDAwfQ</span><br/><span class="var" style="color:var(--success);">.' + Math.random().toString(36).substr(2, 32) + '</span>';
        }
        setTimeout(() => {
          form.reset();
          form.classList.remove('was-validated');
        }, 1200);
      } else {
        toast('Completa los campos requeridos', 'triangle-alert', 'var(--danger)');
      }
    }

    // Reemplaza un icono ya renderizado por otro de Lucide
    function setIcon(el, name) {
      if (!el) return;
      const i = document.createElement('i');
      if (el.id) i.id = el.id;
      i.setAttribute('data-lucide', name);
      const cls = el.getAttribute('class');
      if (cls) i.setAttribute('class', cls.replace(/\blucide\S*/g, '').trim());
      el.replaceWith(i);
      renderIcons();
    }

    // ── Utilidades compartidas ──────────────────────────────────────
    function togglePwd(inputId, eyeId) {
      const i = document.getElementById(inputId);
      const eye = document.getElementById(eyeId);
      if (i.type === 'password') {
        i.type = 'text';
        setIcon(eye, 'eye-off');
      } else {
        i.type = 'password';
        setIcon(eye, 'eye');
      }
    }

    function countChars(el, countId) {
      document.getElementById(countId).textContent = el.value.length;
    }

    function updateRange(el, valId, labels) {
      document.getElementById(valId).textContent = labels[el.value - 1] || el.value;
    }

    function autoHash() {
      const h = document.getElementById('aHash');
      h.value = 'sha256:' + Math.random().toString(16).substr(2, 24) + '...';
      h.style.color = 'var(--success)';
      toast('Hash SHA-256 generado', 'hash', 'var(--accent)');
    }

    function testConn() {
      toast('Probando conexión...', 'plug', 'var(--accent)');
      setTimeout(() => {
        document.getElementById('connResult').style.display = 'block';
        toast('Conexión MySQL exitosa · 4ms', 'circle-check', 'var(--success)');
      }, 1200);
    }

    function copyToken() {
      navigator.clipboard?.writeText('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...').catch(() => {});
      toast('Token copiado al portapapeles', 'clipboard-check', 'var(--accent)');
    }

    // ═══════════════════════════════════════════════════════════════
    // ── Módulo Transferencias: subida por chunks ─────────────────────
    // ═══════════════════════════════════════════════════════════════
    const CHUNK_SIZE = 5 * 1024 * 1024;
    const MAX_BYTES = 20 * 1024 * 1024 * 1024;
    const MAX_REINTENTOS = 4;
    // Antes: const ENDPOINT = 'Subir.php';
    const ENDPOINT = 'Entregar.php';
    const CSRF_META = document.querySelector('meta[name="csrf"]');
    const CSRF = CSRF_META ? CSRF_META.content : '';

    let archivoSel = null,
      ctrlAborto = null,
      enProgreso = false;
    const $t = id => document.getElementById(id);

    function fmtBytes(b) {
      const u = ['B', 'KB', 'MB', 'GB', 'TB'];
      let i = 0;
      while (b >= 1024 && i < u.length - 1) {
        b /= 1024;
        i++;
      }
      return b.toFixed(i ? 1 : 0) + ' ' + u[i];
    }

    function dragOver(e) {
      e.preventDefault();
      $t('dropzone').classList.add('drag');
    }

    function dragLeave(e) {
      e.preventDefault();
      $t('dropzone').classList.remove('drag');
    }

    function handleDrop(e) {
      e.preventDefault();
      dragLeave(e);
      if (e.dataTransfer.files.length) setArchivo(e.dataTransfer.files[0]);
    }

    function showFileInfo(input) {
      if (input.files.length) setArchivo(input.files[0]);
    }

    function resetPanelEstado() {
      $t('estadoVacio').style.display = 'block';
      $t('estadoProgreso').style.display = 'none';
      $t('resultado').innerHTML = '';
      $t('uploadProgress').style.width = '0%';
      $t('uploadDetalle').textContent = '';
    }

    function setArchivo(f) {
      if (f.size === 0) return alert('El archivo está vacío.');
      if (f.size > MAX_BYTES) return alert('El archivo supera el límite de 20 GB.');
      archivoSel = f;
      $t('fileName').textContent = `${f.name} · ${fmtBytes(f.size)}`;
      $t('fileInfo').style.display = 'block';
      resetPanelEstado();
    }

    function limpiarFormulario() {
      if (enProgreso) return;
      archivoSel = null;
      $t('fileInfo').style.display = 'none';
      resetPanelEstado();
    }

    async function enviar(fd) {
      fd.append('csrf', CSRF);
      const res = await fetch(ENDPOINT, {
        method: 'POST',
        body: fd,
        signal: ctrlAborto?.signal
      });
      const txt = await res.text();
      let data;
      try {
        data = JSON.parse(txt);
      } catch {
        const e = new Error('El servidor no devolvió JSON: ' + txt.slice(0, 120));
        e.fatal = true;
        throw e;
      }
      if (!res.ok || data.status === 'error') {
        const e = new Error(data.mensaje || 'Error del servidor');
        e.fatal = res.status >= 400 && res.status < 500 && res.status !== 409;
        throw e;
      }
      return data;
    }

    async function enviarChunk(uploadId, indice, blob) {
      let intento = 0;
      while (true) {
        try {
          const fd = new FormData();
          fd.append('accion', 'chunk');
          fd.append('uploadId', uploadId);
          fd.append('chunkIndex', indice);
          fd.append('chunk', blob, 'parte');
          return await enviar(fd);
        } catch (err) {
          if (err.name === 'AbortError' || err.fatal) throw err;
          if (++intento > MAX_REINTENTOS) throw err;
          $t('uploadDetalle').textContent = `Reintentando pedazo ${indice+1} (${intento}/${MAX_REINTENTOS})…`;
          await new Promise(r => setTimeout(r, 800 * 2 ** (intento - 1)));
        }
      }
    }

    function pintarProgreso(enviados, total, t0) {
      const pct = Math.round(enviados / total * 100);
      $t('uploadProgress').style.width = pct + '%';
      const seg = (performance.now() - t0) / 1000;
      const vel = enviados / seg;
      const rest = vel > 0 ? Math.round((total - enviados) / vel) : 0;
      $t('uploadDetalle').textContent =
        `${pct}% · ${fmtBytes(enviados)} de ${fmtBytes(total)} · ${fmtBytes(vel)}/s · faltan ~${rest}s`;
    }

    function cancelarSubida() {
      ctrlAborto?.abort();
    }

    function mostrarEnlaceGenerado(url) {
      const cont = document.createElement('div');
      cont.className = 'alert-v alert-success';
      cont.style.cssText = 'flex-direction:column;align-items:stretch;gap:8px;padding:14px 16px;margin-top:16px;';

      const titulo = document.createElement('div');
      titulo.innerHTML = '<i data-lucide="circle-check"></i> <strong>Archivo subido correctamente</strong>';
      cont.appendChild(titulo);

      const fila = document.createElement('div');
      fila.style.cssText = 'display:flex;gap:8px;';

      const input = document.createElement('input');
      input.type = 'text';
      input.readOnly = true;
      input.value = url;
      input.className = 'form-control';
      input.style.cssText = 'font-size:.8rem;background:#fff;';
      input.onclick = () => input.select();

      const btnCopiar = document.createElement('button');
      btnCopiar.type = 'button';
      btnCopiar.className = 'btn-secondary-v btn-sm-v';
      btnCopiar.innerHTML = '<i data-lucide="clipboard"></i> Copiar';
      btnCopiar.onclick = async () => {
        try {
          await navigator.clipboard.writeText(url);
        } catch {
          input.select();
          document.execCommand('copy');
        }
        btnCopiar.innerHTML = '<i data-lucide="clipboard-check"></i> Copiado';
        setTimeout(() => btnCopiar.innerHTML = '<i data-lucide="clipboard"></i> Copiar', 1800);
      };

      fila.append(input, btnCopiar);
      cont.appendChild(fila);
      $t('resultado').innerHTML = '';
      $t('resultado').appendChild(cont);
    }

    async function handleTransferSubmit(e) {
      e.preventDefault();
      if (enProgreso) return;
      if (!archivoSel) {
        alert('Selecciona un archivo.');
        return;
      }

      enProgreso = true;
      ctrlAborto = new AbortController();
      $t('btnEnviar').disabled = true;
      $t('btnCancelar').style.display = 'inline-flex';

      $t('estadoVacio').style.display = 'none';
      $t('estadoProgreso').style.display = 'block';
      $t('nombreSubiendo').textContent = archivoSel.name;
      $t('resultado').innerHTML = '';

      const total = Math.ceil(archivoSel.size / CHUNK_SIZE);

      try {
        const fdIni = new FormData();
        fdIni.append('accion', 'iniciar');
        fdIni.append('nombre', archivoSel.name);
        fdIni.append('tamano', archivoSel.size);
        fdIni.append('chunksTotal', total);
        const {
          uploadId
        } = await enviar(fdIni);

        const t0 = performance.now();
        for (let i = 0; i < total; i++) {
          const blob = archivoSel.slice(i * CHUNK_SIZE, Math.min((i + 1) * CHUNK_SIZE, archivoSel.size));
          await enviarChunk(uploadId, i, blob);
          pintarProgreso(Math.min((i + 1) * CHUNK_SIZE, archivoSel.size), archivoSel.size, t0);
        }

        const fdFin = new FormData();
        fdFin.append('accion', 'finalizar');
        fdFin.append('uploadId', uploadId);
        fdFin.append('descripcion', $t('tDesc').value.trim());
        fdFin.append('receptor', $t('tReceptor').value.trim());
        fdFin.append('dias', $t('tExpiry').value);
        fdFin.append('maxDescargas', $t('tMaxDl').value);
        fdFin.append('password', $t('tPass').value);
        fdFin.append('nivelAcceso', document.querySelector('input[name="tAccess"]:checked').value);
        fdFin.append('carpeta', document.getElementById('tCarpeta').value);
        const fin = await enviar(fdFin);

        mostrarEnlaceGenerado(fin.enlace);
        toast('Transferencia creada exitosamente');

      } catch (err) {
        $t('estadoProgreso').style.display = 'none';
        const abortado = err.name === 'AbortError';
        $t('resultado').innerHTML =
          `<div class="alert-v alert-danger" style="padding:10px 12px;font-size:.82rem;">
         ${abortado ? 'Subida cancelada por el usuario.' : 'Error: ' + err.message}
       </div>`;
      } finally {
        enProgreso = false;
        ctrlAborto = null;
        $t('btnEnviar').disabled = false;
        $t('btnCancelar').style.display = 'none';
      }
    }

    // Añade esta función en cualquier parte de tu script
    function crearCarpetaUI() {
      const nombre = prompt("Nombre de la nueva carpeta (solo letras, números y guiones):");
      if (nombre) {
        // Limpiamos la entrada en el frontend por UX (el backend lo volverá a hacer por seguridad)
        const limpio = nombre.replace(/[^a-zA-Z0-9_-]/g, '');
        if (limpio) {
          const select = document.getElementById('tCarpeta');
          const opcion = new Option('/' + limpio, limpio, true, true);
          select.add(opcion);
          toast('Carpeta "' + limpio + '" seleccionada', 'folder-plus', 'var(--accent)');
        } else {
          alert('Nombre de carpeta inválido.');
        }
      }
    }

    function crearCarpetaUI() {
      const nombre = prompt("Nombre de la nueva carpeta (sin espacios):");
      if (nombre) {
        const limpio = nombre.replace(/[^a-zA-Z0-9_-]/g, '');
        if (limpio) {
          const select = document.getElementById('tCarpeta');
          const opcion = new Option('/' + limpio, limpio, true, true);
          select.add(opcion);
          toast('Carpeta /' + limpio + ' lista', 'folder-plus', 'var(--accent)');
          cargarArchivosCarpeta();
        } else {
          alert('Nombre inválido.');
        }
      }
    }

    async function cargarCarpetasUI(carpetaSeleccionada = null) {
      const select = document.getElementById('tCarpeta');
      if (!select) return;

      const fd = new FormData();
      fd.append('accion', 'obtener_carpetas');

      try {
        // 1. Usamos tu función enviar() que ya maneja errores, abortos y el token CSRF
        const data = await enviar(fd);

        if (data.status === 'ok') {
          select.innerHTML = ''; // Limpiamos el "Cargando..."

          // 2. Llenamos el menú con las carpetas reales de Linux
          data.carpetas.forEach(c => {
            select.add(new Option('/' + c, c));
          });

          // 3. Forzamos la selección si el administrador acaba de crear una
          if (carpetaSeleccionada) {
            select.value = carpetaSeleccionada;
          }

          // 4. Inmediatamente cargamos la tabla de archivos para la carpeta que quedó activa
          cargarArchivosCarpeta();
        }
      } catch (err) {
        // SI FALLA, TE LO GRITA EN LA INTERFAZ, NADA DE SILENCIOS
        select.innerHTML = `<option value="">Fallo crítico: ${err.message}</option>`;
        select.style.borderColor = 'var(--danger)';
        select.style.color = 'var(--danger)';
      }
    }

    async function cargarArchivosCarpeta() {
      const select = document.getElementById('tCarpeta');
      // Extraemos el valor del menú, o usamos 'archivos' por defecto si algo falla
      const carpeta = select ? select.value : 'archivos';
      const cont = document.getElementById('exploradorCarpeta');

      if (!cont) return;

      // Pintamos el spinner de carga
      cont.innerHTML = '<div style="text-align:center; padding:20px; color:var(--muted);"><i data-lucide="loader" class="animate-spin" style="display:inline-block;"></i> Consultando base de datos...</div>';
      renderIcons();

      const fd = new FormData();
      fd.append('accion', 'listar_carpeta');
      fd.append('carpeta', carpeta);

      try {
        // Usamos tu función enviar() nativa que ya maneja errores y CSRF
        const data = await enviar(fd);

        if (data.archivos.length === 0) {
          cont.innerHTML = `<div class="alert-v alert-info"><i data-lucide="info"></i><span>La carpeta /${carpeta} está vacía. Los archivos que subas aparecerán aquí.</span></div>`;
        } else {
          let html = `<div class="form-section-title">Contenido de la carpeta /${carpeta}</div>
                  <div class="table-wrap" style="max-height: 280px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--radius-sm);">
                    <table>
                      <thead style="position: sticky; top: 0; background: var(--navy); z-index: 10;">
                        <tr>
                          <th>Archivo</th>
                          <th>Tamaño</th>
                          <th>Fecha</th>
                        </tr>
                      </thead>
                      <tbody>`;
          data.archivos.forEach(a => {
            html += `<tr>
                   <td><span class="file-pill"><i data-lucide="file"></i> ${a.nombre_archivo}</span></td>
                   <td style="font-weight:600; font-size:.8rem;">${fmtBytes(parseInt(a.tamano))}</td>
                   <td style="font-size:.75rem; color:var(--muted);">${a.fecha_creacion}</td>
                 </tr>`;
          });
          html += `</tbody></table></div>`;
          cont.innerHTML = html;
        }
      } catch (err) {
        cont.innerHTML = `<div class="alert-v alert-danger"><i data-lucide="triangle-alert"></i><span>Fallo al cargar archivos: ${err.message}</span></div>`;
      }
      renderIcons();
    }

    function crearCarpetaUI() {
      const nombre = prompt("Nombre de la nueva carpeta (sin espacios):");
      if (nombre) {
        const limpio = nombre.replace(/[^a-zA-Z0-9_-]/g, '');
        if (limpio) {
          const select = document.getElementById('tCarpeta');
          // Verificamos si la carpeta no existe ya en el menú
          const existe = Array.from(select.options).some(opt => opt.value === limpio);
          if (!existe) {
            select.add(new Option('/' + limpio, limpio, true, true));
          } else {
            select.value = limpio;
          }
          toast('Carpeta /' + limpio + ' activa', 'folder-plus', 'var(--accent)');
          cargarArchivosCarpeta();
        } else {
          alert('Nombre inválido.');
        }
      }
    }
  </script>
</body>

</html>