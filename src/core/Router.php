<?php

class Router {

    private $routes = [];

    public function get($route, $action) {
        $this->routes['GET'][$route] = $action;
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_SERVER['REQUEST_URI'];

        $path = parse_url($path, PHP_URL_PATH);
        $base = str_replace("/index.php", "", $_SERVER['SCRIPT_NAME']);
        $path = "/" . trim(str_replace($base, "", $path), "/");

        if ($path == "") $path = "/";

        if (isset($this->routes[$method][$path])) {
            list($controller, $methodName) = explode("@", $this->routes[$method][$path]);

            $controllerFile = __DIR__ . "/../controllers/$controller.php";
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                $obj = new $controller();
                return $obj->$methodName();
            }
        }

        http_response_code(404);
        echo "Página no encontrada";
    }
}
