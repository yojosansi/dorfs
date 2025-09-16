<?php
require_once 'core/db_connect.php';

try {
    $sql = file_get_contents('migration_add_users.sql');
    $pdo->exec($sql);
    echo "Migration applied successfully.\n";
} catch (PDOException $e) {
    die("Error applying migration: " . $e->getMessage() . "\n");
}
?>
