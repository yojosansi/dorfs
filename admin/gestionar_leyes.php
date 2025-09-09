<?php
require_once '../includes/header.php';
require_once '../core/db_connect.php';

// Manejo de feedback
$feedback_message = $_SESSION['feedback_message'] ?? null;
$feedback_class = $_SESSION['feedback_class'] ?? '';
unset($_SESSION['feedback_message'], $_SESSION['feedback_class']);

try {
    $stmt = $pdo->query("SELECT id, titulo, numero_ley, organismo, fecha_publicacion_inicial FROM leyes ORDER BY titulo ASC");
    $leyes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener las leyes: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Gestionar Leyes</h1>
        <a href="crear_ley.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i> Crear Nueva Ley
        </a>
    </div>

    <?php if ($feedback_message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($feedback_class); ?>" role="alert">
            <?php echo htmlspecialchars($feedback_message); ?>
        </div>
    <?php endif; ?>

    <p>Aquí puedes ver, editar y eliminar las leyes del sistema.</p>

    <div class="card">
        <div class="card-body">
            <?php if (empty($leyes)): ?>
                <div class="alert alert-info">No hay leyes registradas.</div>
            <?php else: ?>
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Título</th>
                            <th scope="col">Número</th>
                            <th scope="col">Organismo</th>
                            <th scope="col">Publicación</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leyes as $ley): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ley['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($ley['numero_ley']); ?></td>
                                <td><?php echo htmlspecialchars($ley['organismo']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($ley['fecha_publicacion_inicial'])); ?></td>
                                <td>
                                    <a href="gestionar_versiones.php?ley_id=<?php echo $ley['id']; ?>" class="btn btn-info btn-sm" title="Gestionar Versiones">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </a>
                                    <a href="editar_ley.php?id=<?php echo $ley['id']; ?>" class="btn btn-secondary btn-sm" title="Editar Ley">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="eliminar_ley.php?id=<?php echo $ley['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar esta ley y todas sus versiones asociadas?');" title="Eliminar Ley">
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
     <a href="index.php" class="btn btn-secondary mt-3">
        <i class="bi bi-arrow-left"></i> Volver al Panel
    </a>
</div>

<?php
require_once '../includes/footer.php';
?>
