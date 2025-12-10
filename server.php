<?php
// Router para servidor embebido: sirve archivos estáticos y enruta el resto a index.php
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $path;
    if (is_file($file)) {
        return false;
    }
}
require __DIR__ . '/index.php';
