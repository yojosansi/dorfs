<?php
require_once '../includes/header.php';
require_once '../core/db_connect.php';

$feedback_message = '';
$feedback_class = '';

// --- Lógica para Crear un Nuevo Código ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_codigo'])) {
    $titulo_codigo = trim(filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING));

    if (empty($titulo_codigo)) {
        $feedback_message = 'El título del código no puede estar vacío.';
        $feedback_class = 'alert-danger';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO codigos (titulo) VALUES (:titulo)");
            $stmt->execute(['titulo' => $titulo_codigo]);
            $feedback_message = 'Código "' . htmlspecialchars($titulo_codigo) . '" creado con éxito.';
            $feedback_class = 'alert-success';
        } catch (PDOException $e) {
            // El código 23000 es para violación de integridad (ej. UNIQUE)
            if ($e->getCode() == 23000) {
                $feedback_message = 'Error: Ya existe un código con ese título.';
            } else {
                $feedback_message = 'Error al crear el código: ' . $e->getMessage();
            }
            $feedback_class = 'alert-danger';
        }
    }
}

// --- Lógica para Listar Códigos Existentes ---
$stmt_codigos = $pdo->query("SELECT id, titulo, created_at FROM codigos ORDER BY titulo ASC");
$codigos = $stmt_codigos->fetchAll();

?>

<div class="container mt-4">
    <h1>Gestionar Códigos</h1>
    <p>Aquí puedes crear nuevos códigos para agrupar leyes y ver los existentes.</p>

    <?php if ($feedback_message): ?>
        <div class="alert <?php echo $feedback_class; ?>" role="alert">
            <?php echo $feedback_message; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Columna para crear códigos -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    Crear Nuevo Código
                </div>
                <div class="card-body">
                    <form action="gestionar_codigos.php" method="post">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título del Código</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        <button type="submit" name="crear_codigo" class="btn btn-primary">Crear</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Columna para listar códigos -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Códigos Existentes
                </div>
                <div class="card-body">
                    <?php if (empty($codigos)): ?>
                        <p>No hay códigos creados todavía.</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($codigos as $codigo): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php echo htmlspecialchars($codigo['titulo']); ?>
                                        <br>
                                        <small class="text-muted">Creado: <?php echo date('d/m/Y', strtotime($codigo['created_at'])); ?></small>
                                    </div>
                                    <a href="editar_codigo.php?id=<?php echo htmlspecialchars($codigo['id']); ?>" class="btn btn-secondary btn-sm">Asociar Leyes</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
