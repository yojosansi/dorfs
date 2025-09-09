<?php
require_once '../includes/header.php';
// db_connect y config ya están cargados por el header

$feedback_message = '';
$feedback_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y preparar los datos del POST
    $settings_to_save = [
        'app_title' => filter_input(INPUT_POST, 'app_title', FILTER_SANITIZE_STRING),
        'navbar_color' => filter_input(INPUT_POST, 'navbar_color', FILTER_SANITIZE_STRING),
        'navbar_text_color' => filter_input(INPUT_POST, 'navbar_text_color', FILTER_SANITIZE_STRING),
        // No sanitizamos el CSS personalizado para permitir cualquier regla, pero se debe escapar en la salida.
        'custom_css' => $_POST['custom_css'] ?? ''
    ];

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO configuracion (config_key, config_value) VALUES (:key, :value)
                ON DUPLICATE KEY UPDATE config_value = :value";
        $stmt = $pdo->prepare($sql);

        foreach ($settings_to_save as $key => $value) {
            $stmt->execute(['key' => $key, 'value' => $value]);
        }

        $pdo->commit();
        $feedback_message = 'La configuración de la apariencia ha sido guardada con éxito.';
        $feedback_class = 'alert-success';

        // Recargar la configuración para que se muestre la nueva en el formulario
        $stmt = $pdo->query("SELECT config_key, config_value FROM configuracion");
        $db_config = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $CONFIG = array_merge($CONFIG, $db_config);


    } catch (PDOException $e) {
        $pdo->rollBack();
        $feedback_message = 'Error al guardar la configuración: ' . $e->getMessage();
        $feedback_class = 'alert-danger';
    }
}
?>

<div class="container mt-4">
    <h1>Ajustes de Apariencia</h1>
    <p>Modifica la apariencia general del sitio. Los cambios se aplicarán inmediatamente.</p>

    <?php if ($feedback_message): ?>
        <div class="alert <?php echo htmlspecialchars($feedback_class); ?>" role="alert">
            <?php echo htmlspecialchars($feedback_message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="gestionar_apariencia.php" method="post">
                <div class="mb-3">
                    <label for="app_title" class="form-label">Título de la Aplicación</label>
                    <input type="text" class="form-control" id="app_title" name="app_title" value="<?php echo htmlspecialchars($CONFIG['app_title']); ?>">
                </div>

                <div class="row">
                    <div class="col-md-6">
                         <div class="mb-3">
                            <label for="navbar_color" class="form-label">Color de Fondo de la Barra de Navegación</label>
                            <select class="form-select" id="navbar_color" name="navbar_color">
                                <?php
                                $colores = ['bg-dark' => 'Oscuro', 'bg-primary' => 'Azul Primario', 'bg-secondary' => 'Gris Secundario', 'bg-light' => 'Claro'];
                                foreach ($colores as $clase => $nombre) {
                                    $selected = ($CONFIG['navbar_color'] === $clase) ? 'selected' : '';
                                    echo "<option value=\"$clase\" $selected>$nombre</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                     <div class="col-md-6">
                         <div class="mb-3">
                            <label for="navbar_text_color" class="form-label">Color del Texto de la Barra de Navegación</label>
                            <select class="form-select" id="navbar_text_color" name="navbar_text_color">
                                <?php
                                $colores_texto = ['navbar-dark' => 'Claro (para fondos oscuros)', 'navbar-light' => 'Oscuro (para fondos claros)'];
                                foreach ($colores_texto as $clase => $nombre) {
                                    $selected = ($CONFIG['navbar_text_color'] === $clase) ? 'selected' : '';
                                    echo "<option value=\"$clase\" $selected>$nombre</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="custom_css" class="form-label">CSS Personalizado</label>
                    <textarea class="form-control" id="custom_css" name="custom_css" rows="8" placeholder="Ej: body { font-family: 'Georgia', serif; }"><?php echo htmlspecialchars($CONFIG['custom_css']); ?></textarea>
                    <div class="form-text">El CSS que añadas aquí se inyectará en la cabecera de todas las páginas.</div>
                </div>

                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="index.php" class="btn btn-secondary">Volver al Panel</a>
            </form>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
