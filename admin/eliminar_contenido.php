<?php
session_start();
require_once '../core/db_connect.php';

// --- Obtener y validar datos de la URL ---
$tipo = filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_STRING); // 'articulo' o 'seccion'
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$version_id = filter_input(INPUT_GET, 'version_id', FILTER_VALIDATE_INT);

// Redirección por defecto
$redirect_url = $version_id ? 'gestionar_contenido_version.php?version_id=' . $version_id : 'gestionar_leyes.php';

if (!$id || !$tipo || !$version_id) {
    $_SESSION['feedback_message'] = 'Parámetros inválidos para la eliminación.';
    $_SESSION['feedback_class'] = 'alert-danger';
    header('Location: ' . $redirect_url);
    exit;
}

try {
    $pdo->beginTransaction();

    if ($tipo === 'articulo') {
        // La FK en `secciones_articulo` con ON DELETE CASCADE se encarga de las secciones
        $stmt = $pdo->prepare("DELETE FROM articulos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $message = 'Artículo eliminado correctamente.';

    } elseif ($tipo === 'seccion') {
        // Si es una sección, obtenemos el ID del artículo para el ancla en la URL
        $stmt_get_articulo = $pdo->prepare("SELECT articulo_id FROM secciones_articulo WHERE id = :id");
        $stmt_get_articulo->execute(['id' => $id]);
        $articulo_id = $stmt_get_articulo->fetchColumn();
        if ($articulo_id) {
            $redirect_url .= '#collapse-' . $articulo_id;
        }

        $stmt = $pdo->prepare("DELETE FROM secciones_articulo WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $message = 'Sección eliminada correctamente.';

    } else {
        throw new Exception("Tipo de contenido no válido.");
    }

    if ($stmt->rowCount() > 0) {
        $_SESSION['feedback_message'] = $message;
        $_SESSION['feedback_class'] = 'alert-success';
    } else {
        $_SESSION['feedback_message'] = 'No se encontró el contenido para eliminar.';
        $_SESSION['feedback_class'] = 'alert-warning';
    }

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['feedback_message'] = 'Error al eliminar: ' . $e->getMessage();
    $_SESSION['feedback_class'] = 'alert-danger';
}

header('Location: ' . $redirect_url);
exit;
?>
