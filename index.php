<?php

require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/src/core/Router.php";

$router = new Router();

$router->get("/", "HomeController@index");
$router->get("/login", "AuthController@login");
$router->post("/login", "AuthController@doLogin");
$router->get("/logout", "AuthController@logout");
$router->get("/dashboard", "DashboardController@index");

$router->run();
