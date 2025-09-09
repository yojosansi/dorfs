<?php
require_once 'includes/header.php';
require_once 'core/db_connect.php';

// 2. Recoger de forma segura los IDs de versión y ley.
$ley_id = filter_input(INPUT_GET, 'ley_id', FILTER_VALIDATE_INT);
$version_a_id = filter_input(INPUT_GET, 'version_a', FILTER_VALIDATE_INT);
$version_b_id = filter_input(INPUT_GET, 'version_b', FILTER_VALIDATE_INT);

echo '<div class="container mt-4">';

// Manejo de errores básico
if (!$ley_id || !$version_a_id || !$version_b_id) {
    echo '<div class="alert alert-danger">Error: Faltan parámetros o son inválidos para la comparación.</div>';
} elseif ($version_a_id == $version_b_id) {
    echo '<div class="alert alert-warning">Por favor, seleccione dos versiones diferentes para comparar.</div>';
    echo '<a href="ver_ley.php?id=' . htmlspecialchars($ley_id) . '" class="btn btn-primary">Volver a la ley</a>';
} else {
    // --- PASO 3: OBTENER DATOS DE AMBAS VERSIONES ---

    /**
     * Obtiene el contenido estructurado de una versión de la ley.
     * @param int $version_id El ID de la versión a obtener.
     * @param PDO $pdo El objeto de conexión a la base de datos.
     * @return array El contenido de la versión, con artículos y secciones.
     */
    function getContenidoVersion(int $version_id, PDO $pdo): array {
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
            // Usamos el número de artículo como clave para facilitar la comparación.
            $numero_articulo = $row['numero_articulo'];
            if (!isset($articulos[$numero_articulo])) {
                $articulos[$numero_articulo] = [
                    'titulo_articulo' => $row['titulo_articulo'],
                    'secciones' => []
                ];
            }

            if ($row['seccion_id']) {
                $articulos[$numero_articulo]['secciones'][] = [
                    'tipo' => $row['tipo_seccion'],
                    'identificador' => $row['identificador_seccion'],
                    'contenido' => $row['contenido']
                ];
            }
        }
        return $articulos;
    }

    // Obtenemos el contenido de ambas versiones
    $contenido_A = getContenidoVersion($version_a_id, $pdo);
    $contenido_B = getContenidoVersion($version_b_id, $pdo);

    // --- PASO 4: IMPLEMENTAR EL ALGORITMO DE COMPARACIÓN ---
    $diff = [];
    $todos_los_numeros = array_unique(array_merge(array_keys($contenido_A), array_keys($contenido_B)));
    // ksort($todos_los_numeros, SORT_NATURAL); // Opcional: ordenar los artículos naturalmente

    foreach ($todos_los_numeros as $numero) {
        $en_A = isset($contenido_A[$numero]);
        $en_B = isset($contenido_B[$numero]);

        if ($en_A && !$en_B) {
            // Eliminado
            $diff[$numero] = [
                'status' => 'eliminado',
                'data_A' => $contenido_A[$numero]
            ];
        } elseif (!$en_A && $en_B) {
            // Añadido
            $diff[$numero] = [
                'status' => 'añadido',
                'data_B' => $contenido_B[$numero]
            ];
        } else {
            // Existe en ambos, comprobar si hay modificaciones
            // Comparamos una representación JSON de las secciones para ver si hay cambios.
            if (json_encode($contenido_A[$numero]['secciones']) != json_encode($contenido_B[$numero]['secciones'])) {
                // Modificado
                $diff[$numero] = [
                    'status' => 'modificado',
                    'data_A' => $contenido_A[$numero],
                    'data_B' => $contenido_B[$numero]
                ];
            } else {
                // Sin cambios (opcional, se puede añadir si se quiere mostrar todo)
                // $diff[$numero] = [
                //     'status' => 'sin_cambios',
                //     'data_A' => $contenido_A[$numero]
                // ];
            }
        }
    }

    // --- PASO 5: MOSTRAR LOS RESULTADOS DE LA COMPARACIÓN ---
    echo "<h1>Resultado de la Comparación</h1>";
    echo '<a href="ver_ley.php?id=' . htmlspecialchars($ley_id) . '" class="btn btn-secondary mb-3">Volver a la vista de la ley</a>';

    if (empty($diff)) {
        echo '<div class="alert alert-success" role="alert">No se encontraron diferencias entre las versiones seleccionadas.</div>';
    } else {
        // Opcional: Mostrar un resumen de los cambios
        $summary = array_count_values(array_column($diff, 'status'));
        echo '<p>';
        if (isset($summary['añadido'])) echo '<span class="badge bg-success me-1">' . $summary['añadido'] . ' añadido(s)</span>';
        if (isset($summary['eliminado'])) echo '<span class="badge bg-danger me-1">' . $summary['eliminado'] . ' eliminado(s)</span>';
        if (isset($summary['modificado'])) echo '<span class="badge bg-warning text-dark me-1">' . $summary['modificado'] . ' modificado(s)</span>';
        echo '</p><hr>';

        foreach ($diff as $numero => $change) {
            $status = $change['status'];
            $alert_class = '';
            switch ($status) {
                case 'añadido': $alert_class = 'success'; break;
                case 'eliminado': $alert_class = 'danger'; break;
                case 'modificado': $alert_class = 'warning'; break;
            }

            echo '<div class="alert alert-' . $alert_class . '">';
            echo '  <h5 class="alert-heading">Artículo ' . htmlspecialchars($numero) . ' - ' . strtoupper($status) . '</h5>';

            if ($status === 'añadido') {
                echo '  <p><strong>' . htmlspecialchars($change['data_B']['titulo_articulo']) . '</strong></p>';
                foreach ($change['data_B']['secciones'] as $seccion) {
                    echo '<p>' . nl2br(htmlspecialchars($seccion['contenido'])) . '</p>';
                }
            } elseif ($status === 'eliminado') {
                echo '  <p><strong>' . htmlspecialchars($change['data_A']['titulo_articulo']) . '</strong></p>';
                foreach ($change['data_A']['secciones'] as $seccion) {
                    echo '<p>' . nl2br(htmlspecialchars($seccion['contenido'])) . '</p>';
                }
            } elseif ($status === 'modificado') {
                echo '<div class="row">';
                echo '  <div class="col-md-6">';
                echo '    <h6>Antes:</h6>';
                echo '    <div class="p-2 bg-light border rounded">';
                echo '      <p><strong>' . htmlspecialchars($change['data_A']['titulo_articulo']) . '</strong></p>';
                foreach ($change['data_A']['secciones'] as $seccion) {
                    echo '<p>' . nl2br(htmlspecialchars($seccion['contenido'])) . '</p>';
                }
                echo '    </div>';
                echo '  </div>';
                echo '  <div class="col-md-6">';
                echo '    <h6>Después:</h6>';
                echo '    <div class="p-2 bg-light border rounded">';
                echo '      <p><strong>' . htmlspecialchars($change['data_B']['titulo_articulo']) . '</strong></p>';
                foreach ($change['data_B']['secciones'] as $seccion) {
                    echo '<p>' . nl2br(htmlspecialchars($seccion['contenido'])) . '</p>';
                }
                echo '    </div>';
                echo '  </div>';
                echo '</div>';
            }
            echo '</div>';
        }
    }
}

echo '</div>';

require_once 'includes/footer.php';
?>
