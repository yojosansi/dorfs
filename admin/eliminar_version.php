<?php
session_start();
require_once '../core/db_connect.php';

$version_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$ley_id = filter_input(INPUT_GET, 'ley_id', FILTER_VALIDATE_INT);

if (!$version_id || !$ley_id) {
    // Si falta algún ID, redirigir a la página principal de leyes
    header('Location: gestionar_leyes.php');
    exit;
}

try {
    // La FK en la tabla `articulos` con ON DELETE CASCADE se encargará
    // de borrar en cascada los artículos y sus secciones.
    $stmt = $pdo->prepare("DELETE FROM versiones WHERE id = :id");
    $stmt->execute(['id' => $version_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['feedback_message'] = 'Versión eliminada con éxito.';
        $_SESSION['feedback_class'] = 'alert-success';
    } else {
        $_SESSION['feedback_message'] = 'No se encontró la versión para eliminar.';
        $_SESSION['feedback_class'] = 'alert-warning';
    }

} catch (PDOException $e) {
    $_SESSION['feedback_message'] = 'Error al eliminar la versión: ' . $e->getMessage();
    $_SESSION['feedback_class'] = 'alert-danger';
}

// Redirigir siempre de vuelta a la gestión de versiones de la ley correspondiente
header('Location: gestionar_versiones.php?ley_id=' . $ley_id);
exit;
?>
