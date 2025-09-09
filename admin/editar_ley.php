<?php
require_once '../includes/header.php';
require_once '../core/db_connect.php';

$ley_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$ley_id) {
    header('Location: gestionar_leyes.php');
    exit;
}

$feedback_message = '';
$feedback_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim(filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING));
    $numero_ley = trim(filter_input(INPUT_POST, 'numero_ley', FILTER_SANITIZE_STRING));
    $organismo = trim(filter_input(INPUT_POST, 'organismo', FILTER_SANITIZE_STRING));
    $fecha_publicacion = trim(filter_input(INPUT_POST, 'fecha_publicacion_inicial', FILTER_SANITIZE_STRING));

    if (empty($titulo) || empty($fecha_publicacion)) {
        $feedback_message = 'El título y la fecha de publicación son obligatorios.';
        $feedback_class = 'alert-danger';
    } else {
        try {
            $sql = "UPDATE leyes SET titulo = :titulo, numero_ley = :numero_ley, organismo = :organismo, fecha_publicacion_inicial = :fecha_publicacion_inicial WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'titulo' => $titulo,
                'numero_ley' => $numero_ley,
                'organismo' => $organismo,
                'fecha_publicacion_inicial' => $fecha_publicacion,
                'id' => $ley_id
            ]);

            $_SESSION['feedback_message'] = 'Ley "' . htmlspecialchars($titulo) . '" actualizada con éxito.';
            $_SESSION['feedback_class'] = 'alert-success';
            header('Location: gestionar_leyes.php');
            exit;

        } catch (PDOException $e) {
            $feedback_message = 'Error al actualizar la ley: ' . $e->getMessage();
            $feedback_class = 'alert-danger';
        }
    }
}

// Obtener los datos de la ley para mostrarlos en el formulario
try {
    $stmt = $pdo->prepare("SELECT * FROM leyes WHERE id = :id");
    $stmt->execute(['id' => $ley_id]);
    $ley = $stmt->fetch();
    if (!$ley) {
        header('Location: gestionar_leyes.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error al obtener los datos de la ley: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <h1>Editar Ley "<?php echo htmlspecialchars($ley['titulo']); ?>"</h1>

    <?php if ($feedback_message): ?>
        <div class="alert <?php echo htmlspecialchars($feedback_class); ?>" role="alert">
            <?php echo htmlspecialchars($feedback_message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="editar_ley.php?id=<?php echo $ley_id; ?>" method="post">
                <div class="mb-3">
                    <label for="titulo" class="form-label">Título de la Ley <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($ley['titulo']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="numero_ley" class="form-label">Número de Ley</label>
                    <input type="text" class="form-control" id="numero_ley" name="numero_ley" value="<?php echo htmlspecialchars($ley['numero_ley']); ?>">
                </div>
                <div class="mb-3">
                    <label for="organismo" class="form-label">Organismo</label>
                    <input type="text" class="form-control" id="organismo" name="organismo" value="<?php echo htmlspecialchars($ley['organismo']); ?>">
                </div>
                <div class="mb-3">
                    <label for="fecha_publicacion_inicial" class="form-label">Fecha de Publicación Inicial <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_publicacion_inicial" name="fecha_publicacion_inicial" value="<?php echo htmlspecialchars($ley['fecha_publicacion_inicial']); ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="gestionar_leyes.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
