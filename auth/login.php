<?php
session_start();
require_once '../includes/header.php';
require_once '../core/db_connect.php';

$feedback_message = '';
$feedback_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $feedback_message = 'Por favor, rellene todos los campos.';
        $feedback_class = 'alert-danger';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: ../index.php');
                exit;
            } else {
                $feedback_message = 'Nombre de usuario o contraseña incorrectos.';
                $feedback_class = 'alert-danger';
            }
        } catch (PDOException $e) {
            $feedback_message = 'Error al iniciar sesión: ' . $e->getMessage();
            $feedback_class = 'alert-danger';
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2>Iniciar Sesión</h2>
                </div>
                <div class="card-body">
                    <?php if ($feedback_message): ?>
                        <div class="alert <?php echo $feedback_class; ?>" role="alert">
                            <?php echo $feedback_message; ?>
                        </div>
                    <?php endif; ?>
                    <form action="login.php" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
