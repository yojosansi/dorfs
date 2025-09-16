<?php
require_once '../includes/header.php';
require_once '../core/db_connect.php';

$feedback_message = '';
$feedback_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (empty($username) || empty($email) || empty($password)) {
        $feedback_message = 'Por favor, rellene todos los campos.';
        $feedback_class = 'alert-danger';
    } elseif ($password !== $password_confirm) {
        $feedback_message = 'Las contraseñas no coinciden.';
        $feedback_class = 'alert-danger';
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashed_password]);
            $feedback_message = 'Usuario registrado con éxito. Ahora puede iniciar sesión.';
            $feedback_class = 'alert-success';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $feedback_message = 'El nombre de usuario o el correo electrónico ya existen.';
            } else {
                $feedback_message = 'Error al registrar el usuario: ' . $e->getMessage();
            }
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
                    <h2>Registro de Usuario</h2>
                </div>
                <div class="card-body">
                    <?php if ($feedback_message): ?>
                        <div class="alert <?php echo $feedback_class; ?>" role="alert">
                            <?php echo $feedback_message; ?>
                        </div>
                    <?php endif; ?>
                    <form action="register.php" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Registrarse</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
