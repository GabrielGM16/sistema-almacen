<?php

require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/src/core/Router.php";

$router = new Router();

// Ruta base
$router->get("/", "HomeController@index");

// Ejecutar router
$router->run();
