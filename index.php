<?php
// Punto de entrada principal de la aplicación
require_once 'includes/header.php';

?>

<?php require_once 'core/db_connect.php'; ?>

<div class="container mt-4">
    <h1>Gestor de Leyes Consolidadas</h1>
    <p>Listado de leyes disponibles en el sistema.</p>

    <?php
    try {
        $stmt = $pdo->query("SELECT id, titulo, numero_ley, organismo, fecha_publicacion_inicial FROM leyes ORDER BY fecha_publicacion_inicial DESC");
        $leyes = $stmt->fetchAll();

        if (count($leyes) > 0) {
            echo '<div class="list-group">';
            foreach ($leyes as $ley) {
                echo '<a href="ver_ley.php?id=' . htmlspecialchars($ley['id']) . '" class="list-group-item list-group-item-action">';
                echo '<div class="d-flex w-100 justify-content-between">';
                echo '<h5 class="mb-1">' . htmlspecialchars($ley['titulo']) . '</h5>';
                echo '<small>Publicada: ' . htmlspecialchars($ley['fecha_publicacion_inicial']) . '</small>';
                echo '</div>';
                echo '<p class="mb-1"><strong>Número:</strong> ' . htmlspecialchars($ley['numero_ley']) . ' | <strong>Organismo:</strong> ' . htmlspecialchars($ley['organismo']) . '</p>';
                echo '</a>';
            }
            echo '</div>';
        } else {
            echo '<div class="alert alert-info" role="alert">No hay leyes registradas en el sistema.</div>';
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger" role="alert">Error al conectar con la base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    ?>
</div>

<?php
require_once 'includes/footer.php';
?>
