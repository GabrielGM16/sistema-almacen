<?php

require_once __DIR__ . "/../../config/database.php";

class HomeModel {

    public function ejemplo() {
        $db = Database::getConnection();

        $query = $db->query("SELECT NOW() AS fecha");
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}
