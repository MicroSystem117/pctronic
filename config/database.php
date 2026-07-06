<?php

class Database {
    private static $host = "localhost";
    private static $db_name = "Starlink_PCtronic";
    private static $username = "root"; // Ajusta según tu configuración de desarrollo
    private static $password = "";     // Ajusta tu contraseña local aquí
    private static $conn = null;

    public static function connect() {
        if (self::$conn === null) {
            try {
                // Configuramos la cadena DSN con codificación UTF-8 para evitar problemas de acentos
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4";
                
                // Creamos la instancia PDO
                self::$conn = new PDO($dsn, self::$username, self::$password);
                
                // Configuramos PDO para que lance excepciones en caso de errores SQL
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Configuramos para que por defecto devuelva los datos como arreglos asociativos
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
            } catch (PDOException $exception) {
                // Si la conexión falla, detenemos el script y mostramos el error (útil en desarrollo)
                die("Error de conexión a la base de datos: " . $exception->getMessage());
            }
        }
        
        return self::$conn;
    }
}