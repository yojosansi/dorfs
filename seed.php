<?php
// Script para poblar la base de datos con datos de ejemplo.
// Ejecutar desde la línea de comandos: php seed.php

require 'core/db_connect.php';

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ser ejecutado desde la línea de comandos.');
}

// 1. Limpieza de tablas (fuera de la transacción)
try {
    echo "Limpiando tablas existentes...\n";
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $pdo->exec('TRUNCATE TABLE secciones_articulo');
    $pdo->exec('TRUNCATE TABLE articulos');
    $pdo->exec('TRUNCATE TABLE versiones');
    $pdo->exec('TRUNCATE TABLE leyes');
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    echo "Tablas limpiadas.\n";
} catch (PDOException $e) {
    die("Error al limpiar las tablas: " . $e->getMessage());
}

// 2. Inserción de datos (dentro de una transacción)
try {
    $pdo->beginTransaction();

    // Insertar Ley de ejemplo
    $sql_ley = "INSERT INTO leyes (titulo, numero_ley, fecha_publicacion_inicial, organismo) VALUES (?, ?, ?, ?)";
    $stmt_ley = $pdo->prepare($sql_ley);
    $stmt_ley->execute([
        'Ley General de Telecomunicaciones',
        '9/2014',
        '2014-05-09',
        'Jefatura del Estado'
    ]);
    $ley_id = $pdo->lastInsertId();
    echo "Ley insertada (ID: $ley_id).\n";

    // Insertar Versión inicial
    $sql_version = "INSERT INTO versiones (ley_id, titulo_version, fecha_version, descripcion_cambios) VALUES (?, ?, ?, ?)";
    $stmt_version = $pdo->prepare($sql_version);
    $stmt_version->execute([
        $ley_id,
        'Texto original publicado en BOE',
        '2014-05-10',
        'Publicación inicial de la ley.'
    ]);
    $version_id = $pdo->lastInsertId();
    echo "Versión insertada (ID: $version_id).\n";

    // Insertar Artículos para esa versión
    $sql_articulo = "INSERT INTO articulos (version_id, numero_articulo, titulo_articulo, orden) VALUES (?, ?, ?, ?)";
    $stmt_articulo = $pdo->prepare($sql_articulo);
    $stmt_articulo->execute([$version_id, '1', 'Objeto de la ley', 1]);
    $articulo1_id = $pdo->lastInsertId();
    echo "Artículo 1 insertado (ID: $articulo1_id).\n";

    $stmt_articulo->execute([$version_id, '2', 'Ámbito de aplicación', 2]);
    $articulo2_id = $pdo->lastInsertId();
    echo "Artículo 2 insertado (ID: $articulo2_id).\n";

    // Insertar Secciones para el Artículo 1
    $sql_seccion = "INSERT INTO secciones_articulo (articulo_id, tipo_seccion, identificador_seccion, contenido, orden) VALUES (?, ?, ?, ?, ?)";
    $stmt_seccion = $pdo->prepare($sql_seccion);

    $stmt_seccion->execute([
        $articulo1_id,
        'Apartado',
        '1',
        'La presente Ley tiene por objeto la regulación de las telecomunicaciones, que comprenden la explotación de las redes y la prestación de los servicios de comunicaciones electrónicas y los recursos asociados.',
        1
    ]);
    $stmt_seccion->execute([
        $articulo1_id,
        'Apartado',
        '2',
        'El régimen jurídico de las telecomunicaciones que se establece en esta Ley tiene como fin garantizar el cumplimiento de los objetivos de interés general.',
        2
    ]);
    echo "Secciones para Artículo 1 insertadas.\n";

    // Confirmar transacción
    $pdo->commit();

    echo "\n¡Población de la base de datos completada con éxito!\n";

} catch (PDOException $e) {
    // Revertir transacción en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error al poblar la base de datos: " . $e->getMessage());
}
