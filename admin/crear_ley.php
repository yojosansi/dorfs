<?php
require_once '../includes/header.php';
require_once '../core/db_connect.php';

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
            $sql = "INSERT INTO leyes (titulo, numero_ley, organismo, fecha_publicacion_inicial) VALUES (:titulo, :numero_ley, :organismo, :fecha_publicacion_inicial)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'titulo' => $titulo,
                'numero_ley' => $numero_ley,
                'organismo' => $organismo,
                'fecha_publicacion_inicial' => $fecha_publicacion
            ]);

            $_SESSION['feedback_message'] = 'Ley "' . htmlspecialchars($titulo) . '" creada con éxito.';
            $_SESSION['feedback_class'] = 'alert-success';
            header('Location: gestionar_leyes.php');
            exit;

        } catch (PDOException $e) {
            $feedback_message = 'Error al crear la ley: ' . $e->getMessage();
            $feedback_class = 'alert-danger';
        }
    }
}
?>

<div class="container mt-4">
    <h1>Crear Nueva Ley</h1>
    <p>Rellena el formulario para añadir una nueva ley al sistema.</p>

    <?php if ($feedback_message): ?>
        <div class="alert <?php echo htmlspecialchars($feedback_class); ?>" role="alert">
            <?php echo htmlspecialchars($feedback_message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="crear_ley.php" method="post">
                <div class="mb-3">
                    <label for="titulo" class="form-label">Título de la Ley <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="titulo" name="titulo" required>
                </div>
                <div class="mb-3">
                    <label for="numero_ley" class="form-label">Número de Ley</label>
                    <input type="text" class="form-control" id="numero_ley" name="numero_ley">
                </div>
                <div class="mb-3">
                    <label for="organismo" class="form-label">Organismo</label>
                    <input type="text" class="form-control" id="organismo" name="organismo">
                </div>
                <div class="mb-3">
                    <label for="fecha_publicacion_inicial" class="form-label">Fecha de Publicación Inicial <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_publicacion_inicial" name="fecha_publicacion_inicial" required>
                </div>

                <button type="submit" class="btn btn-primary">Crear Ley</button>
                <a href="gestionar_leyes.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
