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
$articulo_id = filter_input(INPUT_POST, 'articulo_id', FILTER_VALIDATE_INT);
$seccion_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // Para ediciones

$tipo_seccion = trim(filter_input(INPUT_POST, 'tipo_seccion', FILTER_SANITIZE_STRING));
$identificador_seccion = trim(filter_input(INPUT_POST, 'identificador_seccion', FILTER_SANITIZE_STRING));
$contenido = trim(filter_input(INPUT_POST, 'contenido', FILTER_SANITIZE_STRING));
$orden = filter_input(INPUT_POST, 'orden', FILTER_VALIDATE_INT) ?? 0;

// Redirección por defecto en caso de error
$redirect_url = 'gestionar_contenido_version.php?version_id=' . $version_id;

if (!$version_id || !$articulo_id || empty($tipo_seccion) || empty($identificador_seccion) || empty($contenido)) {
    $_SESSION['feedback_message'] = 'Faltan datos obligatorios para la sección.';
    $_SESSION['feedback_class'] = 'alert-danger';
    header('Location: ' . $redirect_url);
    exit;
}

try {
    if ($seccion_id) {
        // --- Lógica de ACTUALIZACIÓN ---
        $sql = "UPDATE secciones_articulo SET tipo_seccion = :tipo, identificador_seccion = :identificador, contenido = :contenido, orden = :orden WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'tipo' => $tipo_seccion,
            'identificador' => $identificador_seccion,
            'contenido' => $contenido,
            'orden' => $orden,
            'id' => $seccion_id
        ]);
        $_SESSION['feedback_message'] = 'Sección actualizada correctamente.';
        $_SESSION['feedback_class'] = 'alert-success';

    } else {
        // --- Lógica de CREACIÓN ---
        $sql = "INSERT INTO secciones_articulo (articulo_id, tipo_seccion, identificador_seccion, contenido, orden) VALUES (:articulo_id, :tipo, :identificador, :contenido, :orden)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'articulo_id' => $articulo_id,
            'tipo' => $tipo_seccion,
            'identificador' => $identificador_seccion,
            'contenido' => $contenido,
            'orden' => $orden
        ]);
        $_SESSION['feedback_message'] = 'Sección creada correctamente.';
        $_SESSION['feedback_class'] = 'alert-success';
    }

} catch (PDOException $e) {
    $_SESSION['feedback_message'] = 'Error en la base de datos: ' . $e->getMessage();
    $_SESSION['feedback_class'] = 'alert-danger';
}

header('Location: ' . $redirect_url . '#collapse-' . $articulo_id);
exit;
?>
