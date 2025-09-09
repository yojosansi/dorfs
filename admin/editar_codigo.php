<?php
require_once '../includes/header.php';
require_once '../core/db_connect.php';

$feedback_message = '';
$feedback_class = '';

// Obtener el ID del código de la URL
$codigo_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$codigo_id) {
    // Redirigir o mostrar error si no hay ID
    header('Location: gestionar_codigos.php');
    exit;
}

// --- Lógica para procesar el formulario de asociación ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // El formulario envía un array de `ley_ids`, aunque esté vacío.
    $leyes_asociadas_ids = $_POST['leyes'] ?? [];

    try {
        $pdo->beginTransaction();

        // 1. Borrar todas las asociaciones existentes para este código
        $stmt_delete = $pdo->prepare("DELETE FROM ley_codigo WHERE codigo_id = :codigo_id");
        $stmt_delete->execute(['codigo_id' => $codigo_id]);

        // 2. Insertar las nuevas asociaciones
        if (!empty($leyes_asociadas_ids)) {
            $stmt_insert = $pdo->prepare("INSERT INTO ley_codigo (ley_id, codigo_id) VALUES (:ley_id, :codigo_id)");
            foreach ($leyes_asociadas_ids as $ley_id) {
                $stmt_insert->execute(['ley_id' => $ley_id, 'codigo_id' => $codigo_id]);
            }
        }

        $pdo->commit();
        $feedback_message = 'Asociaciones guardadas con éxito.';
        $feedback_class = 'alert-success';

    } catch (PDOException $e) {
        $pdo->rollBack();
        $feedback_message = 'Error al guardar las asociaciones: ' . $e->getMessage();
        $feedback_class = 'alert-danger';
    }
}


// --- Lógica para obtener los datos necesarios para mostrar el formulario ---
// 1. Obtener detalles del código
$stmt_codigo = $pdo->prepare("SELECT titulo FROM codigos WHERE id = :id");
$stmt_codigo->execute(['id' => $codigo_id]);
$codigo = $stmt_codigo->fetch();

if (!$codigo) {
    // Si el código no existe, redirigir
    header('Location: gestionar_codigos.php');
    exit;
}

// 2. Obtener TODAS las leyes
$stmt_leyes = $pdo->query("SELECT id, titulo, numero_ley FROM leyes ORDER BY titulo ASC");
$todas_las_leyes = $stmt_leyes->fetchAll();

// 3. Obtener los IDs de las leyes YA asociadas a este código
$stmt_asociadas = $pdo->prepare("SELECT ley_id FROM ley_codigo WHERE codigo_id = :codigo_id");
$stmt_asociadas->execute(['codigo_id' => $codigo_id]);
// Usamos fetchAll con PDO::FETCH_COLUMN para obtener un array plano de IDs
$leyes_ya_asociadas_ids = $stmt_asociadas->fetchAll(PDO::FETCH_COLUMN, 0);

?>

<div class="container mt-4">
    <h1>Editar Asociaciones para "<?php echo htmlspecialchars($codigo['titulo']); ?>"</h1>
    <a href="gestionar_codigos.php" class="btn btn-secondary mb-3">Volver a la gestión de códigos</a>

    <?php if ($feedback_message): ?>
        <div class="alert <?php echo $feedback_class; ?>" role="alert">
            <?php echo $feedback_message; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            Seleccione las leyes que pertenecen a este código
        </div>
        <div class="card-body">
            <form action="editar_codigo.php?id=<?php echo htmlspecialchars($codigo_id); ?>" method="post">
                <div class="mb-3" style="max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
                    <?php foreach ($todas_las_leyes as $ley): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="leyes[]" value="<?php echo htmlspecialchars($ley['id']); ?>" id="ley-<?php echo htmlspecialchars($ley['id']); ?>"
                                <?php if (in_array($ley['id'], $leyes_ya_asociadas_ids)) echo 'checked'; ?>
                            >
                            <label class="form-check-label" for="ley-<?php echo htmlspecialchars($ley['id']); ?>">
                                <?php echo htmlspecialchars($ley['titulo']); ?> (<?php echo htmlspecialchars($ley['numero_ley']); ?>)
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Asociaciones</button>
            </form>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
