<?php
require_once 'check_auth.php';
require_once '../includes/header.php';
require_once '../core/db_connect.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Mi Cuenta</h2>
                </div>
                <div class="card-body">
                    <p><strong>Nombre de Usuario:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Miembro desde:</strong> <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                    <hr>
                    <a href="#" class="btn btn-primary">Cambiar Contraseña</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
