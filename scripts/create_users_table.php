<?php
require __DIR__ . '/../config/database.php';
$db = Database::getConnection();
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$exists = $db->prepare("SELECT COUNT(*) c FROM users WHERE username = :u");
$exists->execute([':u' => 'admin']);
$count = (int)$exists->fetchColumn();
if ($count === 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $ins = $db->prepare("INSERT INTO users (username, password, role) VALUES (:u, :p, :r)");
    $ins->execute([':u' => 'admin', ':p' => $hash, ':r' => 'admin']);
    echo "Usuario admin creado";
} else {
    echo "Tabla usuarios OK";
}

