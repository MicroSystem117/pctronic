<?php

class Database {
    private static $host = "localhost";
    private static $db_name = "Starlink_PCtronic";
    private static $username = "microsystem";
    private static $password = "Mjolnir.1911";
    private static $port = "3306";
    private static $conn = null;

    private static function initConfig() {
        // 1. Cargar archivo local de configuración si existe (ideal para VPS / producción)
        $localConfig = __DIR__ . '/database.local.php';
        if (file_exists($localConfig)) {
            $config = require $localConfig;
            if (is_array($config)) {
                if (!empty($config['host'])) self::$host = $config['host'];
                if (!empty($config['db_name'])) self::$db_name = $config['db_name'];
                if (!empty($config['username'])) self::$username = $config['username'];
                if (isset($config['password'])) self::$password = $config['password'];
                if (!empty($config['port'])) self::$port = $config['port'];
            }
        }

        // 2. Permitir variables de entorno
        if (getenv('DB_HOST')) self::$host = getenv('DB_HOST');
        if (getenv('DB_NAME')) self::$db_name = getenv('DB_NAME');
        if (getenv('DB_USER')) self::$username = getenv('DB_USER');
        if (getenv('DB_PASS') !== false) self::$password = getenv('DB_PASS');
        if (getenv('DB_PORT')) self::$port = getenv('DB_PORT');
    }

    public static function connect() {
        if (self::$conn === null) {
            self::initConfig();

            try {
                // Configuramos la cadena DSN con codificación UTF-8 para evitar problemas de acentos
                $dsn = "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$db_name . ";charset=utf8mb4";
                
                // Creamos la instancia PDO
                self::$conn = new PDO($dsn, self::$username, self::$password);
                
                // Configuramos PDO para que lance excepciones en caso de errores SQL
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Configuramos para que por defecto devuelva los datos como arreglos asociativos
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
            } catch (PDOException $exception) {
                // Si la conexión falla, mostramos el detalle para facilitar el diagnóstico
                die("Error de conexión a la base de datos: " . $exception->getMessage() . 
                    " [Host: " . self::$host . ", Base de Datos: " . self::$db_name . ", Usuario: " . self::$username . "]");
            }
        }
        
        return self::$conn;
    }
}