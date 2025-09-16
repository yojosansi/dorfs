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
?>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h2><?php echo htmlspecialchars($ley_info['titulo']); ?></h2></div>
                    <div class="card-body">
                        <h5 class="card-title">Versión: <?php echo htmlspecialchars($ley_info['titulo_version']); ?></h5>
                        <p class="card-text"><strong>Número de Ley:</strong> <?php echo htmlspecialchars($ley_info['numero_ley']); ?><br>
                        <strong>Fecha de la versión:</strong> <?php echo htmlspecialchars($ley_info['fecha_version']); ?></p>
                    </div>
                </div>

                <h3 class="mt-4">Contenido de la Ley</h3>
                <?php if (empty($articulos)): ?>
                    <div class="alert alert-info">Esta versión de la ley no tiene artículos registrados.</div>
                <?php else: ?>
                    <div class="accordion" id="accordionLey">
                        <?php foreach ($articulos as $articulo_id => $articulo):
                            $id_html = "articulo-" . $articulo_id;
                        ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-<?php echo $id_html; ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $id_html; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $id_html; ?>">
                                        <strong>Artículo <?php echo htmlspecialchars($articulo['numero_articulo']); ?>.</strong> <?php echo htmlspecialchars($articulo['titulo_articulo']); ?>
                                    </button>
                                </h2>
                                <div id="collapse-<?php echo $id_html; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $id_html; ?>" data-bs-parent="#accordionLey">
                                    <div class="accordion-body">
                                        <?php if (empty($articulo['secciones'])): ?>
                                            Este artículo no tiene contenido detallado.
                                        <?php else: ?>
                                            <?php foreach ($articulo['secciones'] as $seccion): ?>
                                                <p><strong><?php echo htmlspecialchars($seccion['tipo']); ?> <?php echo htmlspecialchars($seccion['identificador']); ?>.</strong> <?php echo nl2br(htmlspecialchars($seccion['contenido'])); ?></p>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <?php
                $stmt_versiones = $pdo->prepare("SELECT id, titulo_version, fecha_version FROM versiones WHERE ley_id = :ley_id ORDER BY fecha_version DESC");
                $stmt_versiones->execute(['ley_id' => $ley_id]);
                $todas_las_versiones = $stmt_versiones->fetchAll();

                if (count($todas_las_versiones) > 1):
                ?>
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Comparar Versiones</h5>
                            <form action="comparar_versiones.php" method="get">
                                <input type="hidden" name="ley_id" value="<?php echo htmlspecialchars($ley_id); ?>">
                                <div class="mb-3">
                                    <label for="version_a" class="form-label">Comparar versión:</label>
                                    <select name="version_a" id="version_a" class="form-select">
                                        <?php foreach ($todas_las_versiones as $v): ?>
                                            <option value="<?php echo htmlspecialchars($v['id']); ?>"><?php echo htmlspecialchars($v['titulo_version']); ?> (<?php echo htmlspecialchars($v['fecha_version']); ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="version_b" class="form-label">Con versión:</label>
                                    <select name="version_b" id="version_b" class="form-select">
                                        <?php
                                        $segunda_opcion_seleccionada = false;
                                        foreach ($todas_las_versiones as $v): ?>
                                            <option value="<?php echo htmlspecialchars($v['id']); ?>" <?php if (!$segunda_opcion_seleccionada) { echo 'selected'; $segunda_opcion_seleccionada = true; } ?>><?php echo htmlspecialchars($v['titulo_version']); ?> (<?php echo htmlspecialchars($v['fecha_version']); ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Comparar</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
<?php
    }
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error al consultar la base de datos: ' . $e->getMessage() . '</div>';
}


require_once 'includes/footer.php';
?>
