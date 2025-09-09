<?php
session_start();
require_once '../core/db_connect.php';

$ley_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$ley_id) {
    header('Location: gestionar_leyes.php');
    exit;
}

try {
    // La FK en la tabla `versiones` con ON DELETE CASCADE se encargará
    // de borrar en cascada las versiones, artículos y secciones.
    $stmt = $pdo->prepare("DELETE FROM leyes WHERE id = :id");
    $stmt->execute(['id' => $ley_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['feedback_message'] = 'Ley eliminada con éxito.';
        $_SESSION['feedback_class'] = 'alert-success';
    } else {
        $_SESSION['feedback_message'] = 'No se encontró la ley para eliminar.';
        $_SESSION['feedback_class'] = 'alert-warning';
    }

} catch (PDOException $e) {
    $_SESSION['feedback_message'] = 'Error al eliminar la ley: ' . $e->getMessage();
    $_SESSION['feedback_class'] = 'alert-danger';
}

header('Location: gestionar_leyes.php');
exit;
?>
