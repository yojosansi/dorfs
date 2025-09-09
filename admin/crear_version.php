<?php
require_once '../includes/header.php';
require_once '../core/db_connect.php';

$ley_id = filter_input(INPUT_GET, 'ley_id', FILTER_VALIDATE_INT);
if (!$ley_id) {
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

    if (empty($titulo_version) || empty($fecha_version)) {
        $feedback_message = 'El título y la fecha de la versión son obligatorios.';
        $feedback_class = 'alert-danger';
    } else {
        try {
            $sql = "INSERT INTO versiones (ley_id, titulo_version, fecha_version, descripcion_cambios, enlace_publicacion) VALUES (:ley_id, :titulo_version, :fecha_version, :descripcion_cambios, :enlace_publicacion)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'ley_id' => $ley_id,
                'titulo_version' => $titulo_version,
                'fecha_version' => $fecha_version,
                'descripcion_cambios' => $descripcion_cambios,
                'enlace_publicacion' => $enlace_publicacion
            ]);

            $_SESSION['feedback_message'] = 'Versión "' . htmlspecialchars($titulo_version) . '" creada con éxito.';
            $_SESSION['feedback_class'] = 'alert-success';
            header('Location: gestionar_versiones.php?ley_id=' . $ley_id);
            exit;

        } catch (PDOException $e) {
            $feedback_message = 'Error al crear la versión: ' . $e->getMessage();
            $feedback_class = 'alert-danger';
        }
    }
}

// Obtener el título de la ley para mostrarlo
try {
    $stmt_ley = $pdo->prepare("SELECT titulo FROM leyes WHERE id = :id");
    $stmt_ley->execute(['id' => $ley_id]);
    $ley = $stmt_ley->fetch();
} catch (PDOException $e) {
    die("Error al obtener la ley: " . $e->getMessage());
}

?>

<div class="container mt-4">
    <h1>Crear Nueva Versión</h1>
    <h5 class="text-muted">Para la ley: <?php echo htmlspecialchars($ley['titulo']); ?></h5>

    <?php if ($feedback_message): ?>
        <div class="alert <?php echo htmlspecialchars($feedback_class); ?>" role="alert">
            <?php echo htmlspecialchars($feedback_message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="crear_version.php?ley_id=<?php echo $ley_id; ?>" method="post">
                <div class="mb-3">
                    <label for="titulo_version" class="form-label">Título de la Versión <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="titulo_version" name="titulo_version" required>
                </div>
                <div class="mb-3">
                    <label for="fecha_version" class="form-label">Fecha de la Versión <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_version" name="fecha_version" required>
                </div>
                <div class="mb-3">
                    <label for="descripcion_cambios" class="form-label">Descripción de Cambios</label>
                    <textarea class="form-control" id="descripcion_cambios" name="descripcion_cambios" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="enlace_publicacion" class="form-label">Enlace a Publicación Oficial</label>
                    <input type="url" class="form-control" id="enlace_publicacion" name="enlace_publicacion" placeholder="https://example.com">
                </div>

                <button type="submit" class="btn btn-primary">Crear Versión</button>
                <a href="gestionar_versiones.php?ley_id=<?php echo $ley_id; ?>" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
