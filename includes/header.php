<?php
session_start();

// Incluir la conexión a la BBDD y el cargador de configuración.
// Se usa @ para suprimir warnings en páginas que no lo necesiten (como el instalador si existiera)
// o si el archivo no es encontrado por alguna razón. Una mejor solución sería tener un enrutador central.
@require_once __DIR__ . '/../core/db_connect.php';
@require_once __DIR__ . '/../core/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($CONFIG['app_title'] ?? 'Gestor de Leyes'); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Estilos personalizados -->
    <link href="/css/style.css" rel="stylesheet">
    <!-- CSS Personalizado desde la BBDD -->
    <style>
        <?php echo $CONFIG['custom_css'] ?? ''; ?>
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg <?php echo htmlspecialchars($CONFIG['navbar_text_color'] ?? 'navbar-dark'); ?> <?php echo htmlspecialchars($CONFIG['navbar_color'] ?? 'bg-dark'); ?>">
  <div class="container-fluid">
    <a class="navbar-brand" href="/index.php"><?php echo htmlspecialchars($CONFIG['app_title'] ?? 'Leyes Consolidadas'); ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="/index.php">Inicio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/admin/index.php">Administración</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<main class="container mt-4">
