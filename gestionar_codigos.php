<?php
require_once 'auth/check_auth.php';
require_once 'includes/header.php';
require_once 'core/db_connect.php';

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
    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h4 class="my-0 fw-normal">Crear Nuevo Código</h4>
                </div>
                <div class="card-body">
                    <?php if ($feedback_message): ?>
                        <div class="alert <?php echo $feedback_class; ?>" role="alert">
                            <?php echo $feedback_message; ?>
                        </div>
                    <?php endif; ?>
                    <form action="gestionar_codigos.php" method="post">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título del Código</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        <button type="submit" name="crear_codigo" class="btn btn-primary w-100">Crear Código</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="my-0 fw-normal">Códigos Existentes</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($codigos)): ?>
                        <div class="alert alert-info">No hay códigos creados todavía.</div>
                    <?php else: ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Título</th>
                                    <th scope="col">Fecha de Creación</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($codigos as $codigo): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($codigo['titulo']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($codigo['created_at'])); ?></td>
                                        <td>
                                            <a href="editar_codigo.php?id=<?php echo htmlspecialchars($codigo['id']); ?>" class="btn btn-secondary btn-sm">Asociar Leyes</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
