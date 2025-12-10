<?php
require "config/database.php";

$db = Database::getConnection();

$data = $db->query("SELECT NOW() AS fecha")->fetch();

echo "Conexión OK → " . $data["fecha"];
