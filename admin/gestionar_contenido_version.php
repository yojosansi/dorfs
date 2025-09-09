<?php
require_once '../includes/header.php';
require_once '../core/db_connect.php';

$version_id = filter_input(INPUT_GET, 'version_id', FILTER_VALIDATE_INT);
if (!$version_id) {
    header('Location: gestionar_leyes.php');
    exit;
}

// --- Lógica para obtener datos de la versión y la ley ---
try {
    $stmt_version = $pdo->prepare(
        "SELECT v.titulo_version, v.ley_id, l.titulo as ley_titulo
         FROM versiones v
         JOIN leyes l ON v.ley_id = l.id
         WHERE v.id = :id"
    );
    $stmt_version->execute(['id' => $version_id]);
    $version_info = $stmt_version->fetch();
    if (!$version_info) {
        header('Location: gestionar_leyes.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error al obtener datos de la versión: " . $e->getMessage());
}

// --- Lógica para obtener el contenido (artículos y secciones) ---
try {
    $sql_contenido = "SELECT
                        a.id as articulo_id, a.numero_articulo, a.titulo_articulo, a.orden as articulo_orden,
                        s.id as seccion_id, s.tipo_seccion, s.identificador_seccion, s.contenido, s.orden as seccion_orden
                      FROM articulos a
                      LEFT JOIN secciones_articulo s ON a.id = s.articulo_id
                      WHERE a.version_id = :version_id
                      ORDER BY a.orden, a.id, s.orden, s.id";
    $stmt_contenido = $pdo->prepare($sql_contenido);
    $stmt_contenido->execute(['version_id' => $version_id]);
    $rows = $stmt_contenido->fetchAll();

    // Agrupar secciones por artículo
    $articulos = [];
    foreach ($rows as $row) {
        $articulo_id = $row['articulo_id'];
        if (!isset($articulos[$articulo_id])) {
            $articulos[$articulo_id] = [
                'id' => $articulo_id,
                'numero' => $row['numero_articulo'],
                'titulo' => $row['titulo_articulo'],
                'orden' => $row['articulo_orden'],
                'secciones' => []
            ];
        }
        if ($row['seccion_id']) {
            $articulos[$articulo_id]['secciones'][] = [
                'id' => $row['seccion_id'],
                'tipo' => $row['tipo_seccion'],
                'identificador' => $row['identificador_seccion'],
                'contenido' => $row['contenido'],
                'orden' => $row['seccion_orden']
            ];
        }
    }

} catch (PDOException $e) {
    die("Error al obtener el contenido: " . $e->getMessage());
}
?>

<div class="container mt-5">
    <!-- Encabezado y Navegación -->
    <h1>Gestionar Contenido</h1>
    <p class="lead">
        Ley: <strong><?php echo htmlspecialchars($version_info['ley_titulo']); ?></strong><br>
        Versión: <strong><?php echo htmlspecialchars($version_info['titulo_version']); ?></strong>
    </p>
    <a href="gestionar_versiones.php?ley_id=<?php echo $version_info['ley_id']; ?>" class="btn btn-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Volver a Versiones
    </a>

    <!-- Manejo de Feedback -->
    <?php if (isset($_SESSION['feedback_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['feedback_class']; ?>" role="alert">
            <?php echo $_SESSION['feedback_message']; ?>
        </div>
        <?php unset($_SESSION['feedback_message'], $_SESSION['feedback_class']); ?>
    <?php endif; ?>

    <!-- Sección para Añadir un Nuevo Artículo -->
    <div class="card mb-4">
        <div class="card-header">
            Añadir Nuevo Artículo
        </div>
        <div class="card-body">
            <form action="guardar_articulo.php" method="post">
                <input type="hidden" name="version_id" value="<?php echo $version_id; ?>">
                <div class="row">
                    <div class="col-md-3">
                        <label for="numero_articulo" class="form-label">Número</label>
                        <input type="text" class="form-control" name="numero_articulo" required>
                    </div>
                    <div class="col-md-7">
                        <label for="titulo_articulo" class="form-label">Título</label>
                        <input type="text" class="form-control" name="titulo_articulo">
                    </div>
                    <div class="col-md-2">
                        <label for="orden_articulo" class="form-label">Orden</label>
                        <input type="number" class="form-control" name="orden" value="0">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Guardar Artículo</button>
            </form>
        </div>
    </div>

    <!-- Listado de Artículos Existentes -->
    <h2>Artículos de esta Versión</h2>
    <hr>
    <?php if (empty($articulos)): ?>
        <div class="alert alert-info">Aún no hay artículos para esta versión.</div>
    <?php else: ?>
        <div class="accordion" id="accordionArticulos">
            <?php foreach ($articulos as $articulo): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $articulo['id']; ?>">
                            Artículo <?php echo htmlspecialchars($articulo['numero']); ?>: <?php echo htmlspecialchars($articulo['titulo']); ?>
                        </button>
                    </h2>
                    <div id="collapse-<?php echo $articulo['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionArticulos">
                        <div class="accordion-body">
                            <!-- Editar Artículo -->
                            <form action="guardar_articulo.php?id=<?php echo $articulo['id']; ?>" method="post" class="mb-4 p-3 border rounded">
                               <h5>Editar Artículo</h5>
                                <input type="hidden" name="version_id" value="<?php echo $version_id; ?>">
                                <div class="row">
                                    <div class="col-md-3"><input type="text" name="numero_articulo" value="<?php echo htmlspecialchars($articulo['numero']); ?>" class="form-control"></div>
                                    <div class="col-md-6"><input type="text" name="titulo_articulo" value="<?php echo htmlspecialchars($articulo['titulo']); ?>" class="form-control"></div>
                                    <div class="col-md-1"><input type="number" name="orden" value="<?php echo htmlspecialchars($articulo['orden']); ?>" class="form-control"></div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-secondary btn-sm">Guardar</button>
                                        <a href="eliminar_contenido.php?tipo=articulo&id=<?php echo $articulo['id']; ?>&version_id=<?php echo $version_id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro? Se eliminará el artículo y todas sus secciones.');">Eliminar</a>
                                    </div>
                                </div>
                            </form>

                            <!-- Secciones del Artículo -->
                            <h6 class="mt-4">Secciones</h6>
                            <?php if (!empty($articulo['secciones'])): ?>
                                <ul class="list-group mb-3">
                                <?php foreach ($articulo['secciones'] as $seccion): ?>
                                    <li class="list-group-item">
                                        <form action="guardar_seccion.php?id=<?php echo $seccion['id']; ?>" method="post">
                                            <input type="hidden" name="version_id" value="<?php echo $version_id; ?>">
                                            <input type="hidden" name="articulo_id" value="<?php echo $articulo['id']; ?>">
                                            <div class="input-group">
                                                <input type="text" name="tipo_seccion" value="<?php echo htmlspecialchars($seccion['tipo']); ?>" class="form-control form-control-sm">
                                                <input type="text" name="identificador_seccion" value="<?php echo htmlspecialchars($seccion['identificador']); ?>" class="form-control form-control-sm">
                                                <input type="number" name="orden" value="<?php echo htmlspecialchars($seccion['orden']); ?>" class="form-control form-control-sm">
                                            </div>
                                            <textarea name="contenido" class="form-control form-control-sm mt-2" rows="2"><?php echo htmlspecialchars($seccion['contenido']); ?></textarea>
                                            <div class="mt-2">
                                                <button type="submit" class="btn btn-outline-secondary btn-sm">Guardar</button>
                                                <a href="eliminar_contenido.php?tipo=seccion&id=<?php echo $seccion['id']; ?>&version_id=<?php echo $version_id; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Seguro de eliminar esta sección?');">Eliminar</a>
                                            </div>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <!-- Añadir Nueva Sección -->
                            <form action="guardar_seccion.php" method="post" class="p-3 border rounded bg-light">
                                <h6>Añadir Nueva Sección</h6>
                                <input type="hidden" name="version_id" value="<?php echo $version_id; ?>">
                                <input type="hidden" name="articulo_id" value="<?php echo $articulo['id']; ?>">
                                 <div class="input-group">
                                    <input type="text" name="tipo_seccion" placeholder="Tipo (Ej: Apartado)" class="form-control">
                                    <input type="text" name="identificador_seccion" placeholder="ID (Ej: 1, a)" class="form-control">
                                    <input type="number" name="orden" value="0" class="form-control">
                                </div>
                                <textarea name="contenido" class="form-control mt-2" rows="2" placeholder="Contenido de la sección..."></textarea>
                                <button type="submit" class="btn btn-primary btn-sm mt-2">Añadir Sección</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
