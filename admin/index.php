<?php
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <h1>Panel de Administración</h1>
    <p>Bienvenido al panel de administración. Desde aquí puedes gestionar el contenido del sitio.</p>

    <div class="list-group">
        <a href="gestionar_leyes.php" class="list-group-item list-group-item-action">
            <i class="bi bi-gavel me-2"></i> Gestionar Leyes
        </a>
        <a href="gestionar_codigos.php" class="list-group-item list-group-item-action">
            <i class="bi bi-journal-bookmark-fill me-2"></i> Gestionar Códigos
        </a>
        <a href="gestionar_apariencia.php" class="list-group-item list-group-item-action">
            <i class="bi bi-palette-fill me-2"></i> Ajustes de Apariencia
        </a>
        <a href="../index.php" class="list-group-item list-group-item-action mt-3">
            <i class="bi bi-arrow-left-circle me-2"></i> Volver al sitio principal
        </a>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
