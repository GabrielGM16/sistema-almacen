<?php

class Controller {
    protected function view($view, $data = []) {
        extract($data);
        require __DIR__ . "/../views/$view.php";
    }

    protected function redirect($path) {
        header("Location: " . BASE_URL . $path);
        exit;
    }
}
