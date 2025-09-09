<?php
session_start();
require_once '../core/db_connect.php';

// --- Validar que la petición sea POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gestionar_leyes.php');
    exit;
}

// --- Obtener y validar datos del formulario ---
$version_id = filter_input(INPUT_POST, 'version_id', FILTER_VALIDATE_INT);
$articulo_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // Para ediciones
$numero_articulo = trim(filter_input(INPUT_POST, 'numero_articulo', FILTER_SANITIZE_STRING));
$titulo_articulo = trim(filter_input(INPUT_POST, 'titulo_articulo', FILTER_SANITIZE_STRING));
$orden = filter_input(INPUT_POST, 'orden', FILTER_VALIDATE_INT) ?? 0;

// Redirección por defecto en caso de error
$redirect_url = 'gestionar_contenido_version.php?version_id=' . $version_id;

if (!$version_id || empty($numero_articulo)) {
    $_SESSION['feedback_message'] = 'Faltan datos obligatorios (versión o número de artículo).';
    $_SESSION['feedback_class'] = 'alert-danger';
    header('Location: ' . $redirect_url);
    exit;
}

try {
    if ($articulo_id) {
        // --- Lógica de ACTUALIZACIÓN ---
        $sql = "UPDATE articulos SET numero_articulo = :numero, titulo_articulo = :titulo, orden = :orden WHERE id = :id AND version_id = :version_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'numero' => $numero_articulo,
            'titulo' => $titulo_articulo,
            'orden' => $orden,
            'id' => $articulo_id,
            'version_id' => $version_id
        ]);
        $_SESSION['feedback_message'] = 'Artículo actualizado correctamente.';
        $_SESSION['feedback_class'] = 'alert-success';

    } else {
        // --- Lógica de CREACIÓN ---
        $sql = "INSERT INTO articulos (version_id, numero_articulo, titulo_articulo, orden) VALUES (:version_id, :numero, :titulo, :orden)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'version_id' => $version_id,
            'numero' => $numero_articulo,
            'titulo' => $titulo_articulo,
            'orden' => $orden
        ]);
        $_SESSION['feedback_message'] = 'Artículo creado correctamente.';
        $_SESSION['feedback_class'] = 'alert-success';
    }

} catch (PDOException $e) {
    // Manejo de error de duplicado
    if ($e->getCode() == 23000) {
        $_SESSION['feedback_message'] = 'Error: Ya existe un artículo con ese número para esta versión.';
    } else {
        $_SESSION['feedback_message'] = 'Error en la base de datos: ' . $e->getMessage();
    }
    $_SESSION['feedback_class'] = 'alert-danger';
}

header('Location: ' . $redirect_url);
exit;
?>
