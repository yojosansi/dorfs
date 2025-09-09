<?php
// Configuración y conexión de la base de datos

// --- Credenciales de la Base de Datos ---
// ATENCIÓN: En un entorno de producción, estas credenciales deben ser
// gestionadas de forma segura, por ejemplo, a través de variables de entorno.
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'password'); // Cambiar por la contraseña de tu BD
define('DB_NAME', 'leyes_consolidadas');

// --- Crear la conexión PDO ---
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // En un entorno de desarrollo, podemos mostrar el error.
    // En producción, se debería registrar el error y mostrar un mensaje genérico.
    error_log("Error de conexión a la BD: " . $e->getMessage());
    die('Error de conexión con la base de datos. Por favor, intente más tarde.');
}

// La variable $pdo estará disponible para cualquier script que incluya este archivo.
?>
