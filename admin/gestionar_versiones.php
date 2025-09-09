<?php
require_once '../includes/header.php';
require_once '../core/db_connect.php';

$ley_id = filter_input(INPUT_GET, 'ley_id', FILTER_VALIDATE_INT);
if (!$ley_id) {
    header('Location: gestionar_leyes.php');
    exit;
}

// Obtener el título de la ley para mostrarlo
try {
    $stmt_ley = $pdo->prepare("SELECT titulo FROM leyes WHERE id = :id");
    $stmt_ley->execute(['id' => $ley_id]);
    $ley = $stmt_ley->fetch();
    if (!$ley) {
        header('Location: gestionar_leyes.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error al obtener la ley: " . $e->getMessage());
}

// Manejo de feedback
$feedback_message = $_SESSION['feedback_message'] ?? null;
$feedback_class = $_SESSION['feedback_class'] ?? '';
unset($_SESSION['feedback_message'], $_SESSION['feedback_class']);

try {
    $stmt = $pdo->prepare("SELECT id, titulo_version, fecha_version, descripcion_cambios FROM versiones WHERE ley_id = :ley_id ORDER BY fecha_version DESC");
    $stmt->execute(['ley_id' => $ley_id]);
    $versiones = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener las versiones: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1>Gestionar Versiones</h1>
            <h5 class="text-muted">Ley: <?php echo htmlspecialchars($ley['titulo']); ?></h5>
        </div>
        <a href="crear_version.php?ley_id=<?php echo $ley_id; ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i> Crear Nueva Versión
        </a>
    </div>

    <?php if ($feedback_message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($feedback_class); ?>" role="alert">
            <?php echo htmlspecialchars($feedback_message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (empty($versiones)): ?>
                <div class="alert alert-info">No hay versiones registradas para esta ley.</div>
            <?php else: ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Título de la Versión</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($versiones as $version): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($version['titulo_version']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($version['fecha_version'])); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($version['descripcion_cambios'])); ?></td>
                                <td>
                                    <a href="gestionar_contenido_version.php?version_id=<?php echo $version['id']; ?>" class="btn btn-success btn-sm" title="Gestionar Contenido (Artículos/Secciones)">
                                        <i class="bi bi-body-text"></i>
                                    </a>
                                    <a href="editar_version.php?id=<?php echo $version['id']; ?>" class="btn btn-secondary btn-sm" title="Editar Versión">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="eliminar_version.php?id=<?php echo $version['id']; ?>&ley_id=<?php echo $ley_id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro? Se eliminará esta versión y todo su contenido.');" title="Eliminar Versión">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <a href="gestionar_leyes.php" class="btn btn-secondary mt-3">
        <i class="bi bi-arrow-left"></i> Volver a Leyes
    </a>
</div>

<?php
require_once '../includes/footer.php';
?>
