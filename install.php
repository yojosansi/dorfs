<?php
// --- CONFIGURACIÓN Y SEGURIDAD ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config_file = 'core/db_connect.php';
$lock_file = 'install.lock';
$errors = [];
$success_message = '';

// Si el archivo de configuración ya existe, no permitir reinstalar.
if (file_exists($config_file)) {
    die('<strong>Error:</strong> La aplicación ya parece estar instalada. Si deseas reinstalarla, por favor, elimina el archivo <code>' . $config_file . '</code> y vuelve a cargar esta página.');
}

// --- LÓGICA DE INSTALACIÓN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recoger y validar datos del formulario
    $dbhost = trim($_POST['dbhost']);
    $dbname = trim($_POST['dbname']);
    $dbuser = trim($_POST['dbuser']);
    $dbpass = trim($_POST['dbpass']);

    if (empty($dbhost) || empty($dbname) || empty($dbuser)) {
        $errors[] = "Por favor, completa todos los campos obligatorios.";
    }

    if (empty($errors)) {
        try {
            // 2. Conectar al servidor MySQL (sin seleccionar la BBDD aún)
            $pdo_server = new PDO("mysql:host=$dbhost", $dbuser, $dbpass);
            $pdo_server->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 3. Crear la base de datos si no existe
            $pdo_server->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // 4. Conectar a la nueva base de datos
            $pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 5. Leer y ejecutar schema.sql
            $schema_sql = file_get_contents('schema.sql');
            if ($schema_sql === false) throw new Exception("No se pudo leer el archivo schema.sql");
            $pdo->exec($schema_sql);

            // 6. Leer y ejecutar migration_add_codes.sql
            $migration_sql = file_get_contents('migration_add_codes.sql');
            if ($migration_sql === false) throw new Exception("No se pudo leer el archivo migration_add_codes.sql");
            $pdo->exec($migration_sql);

            // 7. Leer y ejecutar migration_add_config_table.sql
            $config_migration_sql = file_get_contents('migration_add_config_table.sql');
            if ($config_migration_sql === false) throw new Exception("No se pudo leer el archivo migration_add_config_table.sql");
            $pdo->exec($config_migration_sql);

            // 8. Crear el archivo de configuración core/db_connect.php
            $config_content = "<?php
// Detalles de la conexión a la base de datos
\$host = '$dbhost';
\$dbname = '$dbname';
\$user = '$dbuser';
\$pass = '$dbpass';
\$charset = 'utf8mb4';

// Opciones de PDO
\$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// DSN (Data Source Name)
\$dsn = \"mysql:host=\$host;dbname=\$dbname;charset=\$charset\";

try {
    \$pdo = new PDO(\$dsn, \$user, \$pass, \$options);
} catch (PDOException \$e) {
    // En un entorno de producción, no mostrarías el error detallado
    throw new PDOException(\$e->getMessage(), (int)\$e->getCode());
}
?>";
            if (file_put_contents($config_file, $config_content) === false) {
                throw new Exception("No se pudo escribir el archivo de configuración en <code>$config_file</code>. Verifica los permisos de escritura.");
            }

            // Si todo fue bien, mostramos el mensaje final
            $success_message = "¡Instalación completada con éxito!";

        } catch (PDOException $e) {
            $errors[] = "Error de base de datos: " . $e->getMessage();
        } catch (Exception $e) {
            $errors[] = "Error general: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Gestor de Leyes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title text-center">Instalación del Gestor de Leyes</h1>
            </div>
            <div class="card-body">
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <strong><?php echo $success_message; ?></strong>
                        <p>La aplicación ha sido configurada correctamente. Ahora puedes acceder a la página principal.</p>
                        <p class="fw-bold">Por seguridad, el archivo de instalación (<code>install.php</code>) ha sido eliminado.</p>
                        <a href="index.php" class="btn btn-primary mt-3">Ir a la página principal</a>
                    </div>
                    <?php
                        // Eliminar el propio archivo de instalación
                        @unlink(__FILE__);
                    ?>
                <?php else: ?>
                    <p>Bienvenido al asistente de instalación. Por favor, proporciona los detalles de tu base de datos MySQL.</p>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>¡Error!</strong>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="install.php" method="post">
                    <div class="mb-3">
                        <label for="dbhost" class="form-label">Servidor de Base de Datos</label>
                        <input type="text" class="form-control" id="dbhost" name="dbhost" value="127.0.0.1" required>
                        <div class="form-text">Normalmente es 'localhost' o '127.0.0.1'.</div>
                    </div>
                    <div class="mb-3">
                        <label for="dbname" class="form-label">Nombre de la Base de Datos</label>
                        <input type="text" class="form-control" id="dbname" name="dbname" required>
                        <div class="form-text">El script intentará crearla si no existe.</div>
                    </div>
                    <div class="mb-3">
                        <label for="dbuser" class="form-label">Usuario de la Base de Datos</label>
                        <input type="text" class="form-control" id="dbuser" name="dbuser" required>
                    </div>
                    <div class="mb-3">
                        <label for="dbpass" class="form-label">Contraseña del Usuario</label>
                        <input type="password" class="form-control" id="dbpass" name="dbpass">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Instalar Ahora</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
            <div class="card-footer text-muted text-center">
                Gestor de Leyes Consolidadas
            </div>
        </div>
    </div>
</body>
</html>
