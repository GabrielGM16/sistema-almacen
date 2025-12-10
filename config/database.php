<?php

require_once __DIR__ . "/env.php";

// Cargar .env
loadEnv(__DIR__ . "/../.env");

class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {

            $host = getenv("DB_HOST");
            $user = getenv("DB_USER");
            $pass = getenv("DB_PASS");
            $dbname = getenv("DB_NAME");
            $charset = getenv("DB_CHARSET") ?: "utf8mb4";

            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
