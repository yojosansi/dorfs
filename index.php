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
        // 1. Nueva consulta para obtener leyes y sus códigos asociados
        $sql = "SELECT
                    l.id, l.titulo, l.numero_ley, l.organismo, l.fecha_publicacion_inicial,
                    c.titulo as codigo_titulo
                FROM leyes l
                LEFT JOIN ley_codigo lc ON l.id = lc.ley_id
                LEFT JOIN codigos c ON lc.codigo_id = c.id
                ORDER BY c.titulo ASC, l.titulo ASC";

        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();

        // 2. Procesar los resultados en una estructura agrupada
        $leyes_agrupadas = [];
        foreach ($results as $row) {
            $codigo_titulo = $row['codigo_titulo'] ?? 'Leyes sin Código Asignado';
            if (!isset($leyes_agrupadas[$codigo_titulo])) {
                $leyes_agrupadas[$codigo_titulo] = [];
            }
            // Evitar duplicados si una ley está en varios códigos (aunque la lógica actual no lo permite)
            $leyes_agrupadas[$codigo_titulo][$row['id']] = $row;
        }

        // 3. Renderizar la nueva vista agrupada
        if (empty($results)) {
            echo '<div class="alert alert-info" role="alert">No hay leyes registradas en el sistema.</div>';
        } else {
            foreach ($leyes_agrupadas as $codigo_titulo => $leyes) {
                echo '<h3 class="mt-4">' . htmlspecialchars($codigo_titulo) . '</h3>';
                echo '<div class="list-group">';
                foreach ($leyes as $ley) {
                    echo '<a href="ver_ley.php?id=' . htmlspecialchars($ley['id']) . '" class="list-group-item list-group-item-action">';
                    echo '  <div class="d-flex w-100 justify-content-between">';
                    echo '    <h5 class="mb-1">' . htmlspecialchars($ley['titulo']) . '</h5>';
                    echo '    <small>Publicada: ' . htmlspecialchars($ley['fecha_publicacion_inicial']) . '</small>';
                    echo '  </div>';
                    echo '  <p class="mb-1"><strong>Número:</strong> ' . htmlspecialchars($ley['numero_ley']) . ' | <strong>Organismo:</strong> ' . htmlspecialchars($ley['organismo']) . '</p>';
                    echo '</a>';
                }
                echo '</div>';
            }
        }

    } catch (PDOException $e) {
        echo '<div class="alert alert-danger" role="alert">Error al conectar con la base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    ?>
</div>

<?php
require_once 'includes/footer.php';
?>
