<?php
require_once '../includes/header.php';
require_once '../core/db_connect.php';

$version_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$version_id) {
    header('Location: gestionar_leyes.php');
    exit;
}

$feedback_message = '';
$feedback_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo_version = trim(filter_input(INPUT_POST, 'titulo_version', FILTER_SANITIZE_STRING));
    $fecha_version = trim(filter_input(INPUT_POST, 'fecha_version', FILTER_SANITIZE_STRING));
    $descripcion_cambios = trim(filter_input(INPUT_POST, 'descripcion_cambios', FILTER_SANITIZE_STRING));
    $enlace_publicacion = trim(filter_input(INPUT_POST, 'enlace_publicacion', FILTER_SANITIZE_URL));
    $ley_id = filter_input(INPUT_POST, 'ley_id', FILTER_VALIDATE_INT); // Hidden field

    if (empty($titulo_version) || empty($fecha_version)) {
        $feedback_message = 'El título y la fecha de la versión son obligatorios.';
        $feedback_class = 'alert-danger';
    } else {
        try {
            $sql = "UPDATE versiones SET titulo_version = :titulo_version, fecha_version = :fecha_version, descripcion_cambios = :descripcion_cambios, enlace_publicacion = :enlace_publicacion WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'titulo_version' => $titulo_version,
                'fecha_version' => $fecha_version,
                'descripcion_cambios' => $descripcion_cambios,
                'enlace_publicacion' => $enlace_publicacion,
                'id' => $version_id
            ]);

            $_SESSION['feedback_message'] = 'Versión "' . htmlspecialchars($titulo_version) . '" actualizada con éxito.';
            $_SESSION['feedback_class'] = 'alert-success';
            header('Location: gestionar_versiones.php?ley_id=' . $ley_id);
            exit;

        } catch (PDOException $e) {
            $feedback_message = 'Error al actualizar la versión: ' . $e->getMessage();
            $feedback_class = 'alert-danger';
        }
    }
}

// Obtener los datos de la versión para mostrarlos en el formulario
try {
    $stmt = $pdo->prepare("SELECT v.*, l.titulo as ley_titulo FROM versiones v JOIN leyes l ON v.ley_id = l.id WHERE v.id = :id");
    $stmt->execute(['id' => $version_id]);
    $version = $stmt->fetch();
    if (!$version) {
        header('Location: gestionar_leyes.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error al obtener los datos de la versión: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <h1>Editar Versión "<?php echo htmlspecialchars($version['titulo_version']); ?>"</h1>
    <h5 class="text-muted">De la ley: <?php echo htmlspecialchars($version['ley_titulo']); ?></h5>

    <?php if ($feedback_message): ?>
        <div class="alert <?php echo htmlspecialchars($feedback_class); ?>" role="alert">
            <?php echo htmlspecialchars($feedback_message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="editar_version.php?id=<?php echo $version_id; ?>" method="post">
                <input type="hidden" name="ley_id" value="<?php echo htmlspecialchars($version['ley_id']); ?>">

                <div class="mb-3">
                    <label for="titulo_version" class="form-label">Título de la Versión <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="titulo_version" name="titulo_version" value="<?php echo htmlspecialchars($version['titulo_version']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="fecha_version" class="form-label">Fecha de la Versión <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_version" name="fecha_version" value="<?php echo htmlspecialchars($version['fecha_version']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="descripcion_cambios" class="form-label">Descripción de Cambios</label>
                    <textarea class="form-control" id="descripcion_cambios" name="descripcion_cambios" rows="3"><?php echo htmlspecialchars($version['descripcion_cambios']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="enlace_publicacion" class="form-label">Enlace a Publicación Oficial</label>
                    <input type="url" class="form-control" id="enlace_publicacion" name="enlace_publicacion" placeholder="https://example.com" value="<?php echo htmlspecialchars($version['enlace_publicacion']); ?>">
                </div>

                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="gestionar_versiones.php?ley_id=<?php echo htmlspecialchars($version['ley_id']); ?>" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
