<?php
/**
 * Script de diagnóstico de conexión a la Base de Datos
 * Puedes ejecutarlo desde la terminal: php config/test_connection.php
 */
require_once __DIR__ . '/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO DE CONEXIÓN A BASE DE DATOS ===\n";

try {
    $db = Database::connect();
    echo "✔ Conexión a la base de datos establecida correctamente.\n\n";

    // Verificar tablas
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas encontradas (" . count($tables) . "):\n";
    foreach ($tables as $table) {
        echo "  - " . $table . "\n";
    }

    if (empty($tables)) {
        echo "\n⚠ La base de datos está conectada pero NO contiene tablas. Asegúrate de haber importado el archivo .sql en phpMyAdmin.\n";
    } else {
        // Comprobar tabla user
        if (in_array('user', $tables, true)) {
            $userCount = $db->query("SELECT COUNT(*) FROM `user`")->fetchColumn();
            echo "\n✔ Tabla 'user' verificada. Total de usuarios registrados: " . $userCount . "\n";
        }
    }
} catch (Throwable $e) {
    echo "✖ Error de conexión: " . $e->getMessage() . "\n";
}
