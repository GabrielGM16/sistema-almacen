<?php

require_once __DIR__ . "/../../config/database.php";

class UserModel {
    public function findByUsername($username) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, username, password, role FROM users WHERE username = :u LIMIT 1");
        $stmt->execute([":u" => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

