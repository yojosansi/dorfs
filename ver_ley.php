<?php
require_once 'includes/header.php';
require_once 'core/db_connect.php';

// 1. Obtener y validar el ID de la ley desde la URL.
// Usamos filter_input para más seguridad y convertimos a entero.
$ley_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$ley_id) {
    // Si no hay ID o no es un entero válido, mostramos un error y salimos.
    echo '<div class="alert alert-danger">Error: No se ha especificado una ley válida.</div>';
    require_once 'includes/footer.php';
    exit;
}

try {
    // 2. Implementar la consulta a la base de datos.
    // Primero, obtenemos los detalles de la ley y su última versión.
    $sql_ley_version = "SELECT l.titulo, l.numero_ley, v.id as version_id, v.titulo_version, v.fecha_version
                        FROM leyes l
                        JOIN versiones v ON l.id = v.ley_id
                        WHERE l.id = :ley_id
                        ORDER BY v.fecha_version DESC
                        LIMIT 1";

    $stmt_ley_version = $pdo->prepare($sql_ley_version);
    $stmt_ley_version->execute(['ley_id' => $ley_id]);
    $ley_info = $stmt_ley_version->fetch();

    if (!$ley_info) {
        echo '<div class="alert alert-warning">No se encontró la ley o no tiene versiones.</div>';
    } else {
        $version_id = $ley_info['version_id'];

        // Ahora, obtenemos todos los artículos y sus secciones para esa versión.
        $sql_contenido = "SELECT
                            a.id as articulo_id,
                            a.numero_articulo,
                            a.titulo_articulo,
                            s.id as seccion_id,
                            s.tipo_seccion,
                            s.identificador_seccion,
                            s.contenido
                          FROM articulos a
                          LEFT JOIN secciones_articulo s ON a.id = s.articulo_id
                          WHERE a.version_id = :version_id
                          ORDER BY a.orden, s.orden";

        $stmt_contenido = $pdo->prepare($sql_contenido);
        $stmt_contenido->execute(['version_id' => $version_id]);
        $rows = $stmt_contenido->fetchAll();

        // Procesamos los resultados para agrupar las secciones por artículo.
        $articulos = [];
        foreach ($rows as $row) {
            $articulo_id = $row['articulo_id'];
            if (!isset($articulos[$articulo_id])) {
                $articulos[$articulo_id] = [
                    'numero_articulo' => $row['numero_articulo'],
                    'titulo_articulo' => $row['titulo_articulo'],
                    'secciones' => []
                ];
            }

            if ($row['seccion_id']) {
                $articulos[$articulo_id]['secciones'][] = [
                    'tipo' => $row['tipo_seccion'],
                    'identificador' => $row['identificador_seccion'],
                    'contenido' => $row['contenido']
                ];
            }
        }

        // 3. Mostrar el contenido de la ley.
        echo '<div class="card">';
        echo '  <div class="card-header"><h2>' . htmlspecialchars($ley_info['titulo']) . '</h2></div>';
        echo '  <div class="card-body">';
        echo '    <h5 class="card-title">Versión: ' . htmlspecialchars($ley_info['titulo_version']) . '</h5>';
        echo '    <p class="card-text"><strong>Número de Ley:</strong> ' . htmlspecialchars($ley_info['numero_ley']) . '<br>';
        echo '    <strong>Fecha de la versión:</strong> ' . htmlspecialchars($ley_info['fecha_version']) . '</p>';
        echo '  </div>';
        echo '</div>';
        echo '<hr>';

        // --- INICIO: Formulario de comparación de versiones ---
        $stmt_versiones = $pdo->prepare("SELECT id, titulo_version, fecha_version FROM versiones WHERE ley_id = :ley_id ORDER BY fecha_version DESC");
        $stmt_versiones->execute(['ley_id' => $ley_id]);
        $todas_las_versiones = $stmt_versiones->fetchAll();

        if (count($todas_las_versiones) > 1) {
            echo '<div class="card bg-light mb-4">';
            echo '  <div class="card-body">';
            echo '    <h5 class="card-title">Comparar Versiones</h5>';
            echo '    <form action="comparar_versiones.php" method="get" class="row g-3 align-items-end">';
            echo '      <input type="hidden" name="ley_id" value="' . htmlspecialchars($ley_id) . '">';

            // Dropdown para Versión A
            echo '      <div class="col-md-5">';
            echo '        <label for="version_a" class="form-label">Comparar versión:</label>';
            echo '        <select name="version_a" id="version_a" class="form-select">';
            foreach ($todas_las_versiones as $v) {
                echo '          <option value="' . htmlspecialchars($v['id']) . '">' . htmlspecialchars($v['titulo_version']) . ' (' . htmlspecialchars($v['fecha_version']) . ')</option>';
            }
            echo '        </select>';
            echo '      </div>';

            // Dropdown para Versión B
            echo '      <div class="col-md-5">';
            echo '        <label for="version_b" class="form-label">Con versión:</label>';
            echo '        <select name="version_b" id="version_b" class="form-select">';
            // Seleccionar la segunda más reciente por defecto, si existe
            $segunda_opcion_seleccionada = false;
            foreach ($todas_las_versiones as $v) {
                echo '          <option value="' . htmlspecialchars($v['id']) . '"' . (!$segunda_opcion_seleccionada ? ' selected' : '') . '>' . htmlspecialchars($v['titulo_version']) . ' (' . htmlspecialchars($v['fecha_version']) . ')</option>';
                $segunda_opcion_seleccionada = true; // Solo la primera vez
            }
            echo '        </select>';
            echo '      </div>';

            // Botón de envío
            echo '      <div class="col-md-2">';
            echo '        <button type="submit" class="btn btn-primary w-100">Comparar</button>';
            echo '      </div>';
            echo '    </form>';
            echo '  </div>';
            echo '</div>';
        }
        // --- FIN: Formulario de comparación de versiones ---

        if (empty($articulos)) {
            echo '<div class="alert alert-info">Esta versión de la ley no tiene artículos registrados.</div>';
        } else {
            echo '<h3>Contenido de la Ley</h3>';
            echo '<div class="accordion" id="accordionLey">';

            foreach ($articulos as $articulo_id => $articulo) {
                $id_html = "articulo-" . $articulo_id;
                echo '<div class="accordion-item">';
                echo '  <h2 class="accordion-header" id="heading-' . $id_html . '">';
                echo '    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-' . $id_html . '" aria-expanded="false" aria-controls="collapse-' . $id_html . '">';
                echo '      <strong>Artículo ' . htmlspecialchars($articulo['numero_articulo']) . '.</strong> ' . htmlspecialchars($articulo['titulo_articulo']);
                echo '    </button>';
                echo '  </h2>';
                echo '  <div id="collapse-' . $id_html . '" class="accordion-collapse collapse" aria-labelledby="heading-' . $id_html . '" data-bs-parent="#accordionLey">';
                echo '    <div class="accordion-body">';

                if (empty($articulo['secciones'])) {
                    echo 'Este artículo no tiene contenido detallado.';
                } else {
                    foreach ($articulo['secciones'] as $seccion) {
                        echo '<p><strong>' . htmlspecialchars($seccion['tipo']) . ' ' . htmlspecialchars($seccion['identificador']) . '.</strong> ' . nl2br(htmlspecialchars($seccion['contenido'])) . '</p>';
                    }
                }

                echo '    </div>';
                echo '  </div>';
                echo '</div>';
            }

            echo '</div>'; // Cierre de accordion
        }

    }

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error al consultar la base de datos: ' . $e->getMessage() . '</div>';
}


require_once 'includes/footer.php';
?>
