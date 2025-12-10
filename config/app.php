<?php

session_start();
// Configuración general
$envUrl = getenv('APP_URL');
if ($envUrl) {
    define("BASE_URL", rtrim($envUrl, '/'));
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $base = rtrim(str_replace('/index.php', '', $script), '/');
    define("BASE_URL", $scheme . '://' . $host . $base);
}

// Autocarga de clases
spl_autoload_register(function ($class) {
    $base_dir = __DIR__ . '/../src/';
    $file = $base_dir . str_replace("\\", "/", $class) . ".php";
    if (file_exists($file)) {
        require $file;
    }
});
