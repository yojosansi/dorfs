<?php
// Este script carga la configuración desde la base de datos.
// Se asume que $pdo ya está disponible desde db_connect.php

$CONFIG = [];

// Valores por defecto para evitar errores si la tabla está vacía o una clave falta
$default_config = [
    'app_title' => 'Leyes Consolidadas',
    'navbar_color' => 'bg-dark',
    'navbar_text_color' => 'navbar-dark',
    'custom_css' => ''
];

try {
    $stmt = $pdo->query("SELECT config_key, config_value FROM configuracion");
    $db_config = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Fusionar los valores de la BBDD con los valores por defecto.
    // Los valores de la BBDD sobreescribirán a los por defecto.
    $CONFIG = array_merge($default_config, $db_config);

} catch (PDOException $e) {
    // Si hay un error (ej. la tabla no existe), usamos solo los valores por defecto.
    // Esto hace que la aplicación no se rompa si la migración falla.
    $CONFIG = $default_config;
    // Opcionalmente, se podría registrar este error en algún sitio.
    // error_log("No se pudo cargar la configuración desde la base de datos: " . $e->getMessage());
}
?>
