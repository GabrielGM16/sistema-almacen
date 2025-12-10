<?php

require_once __DIR__ . "/../core/Controller.php";
require_once __DIR__ . "/../models/HomeModel.php";

class HomeController extends Controller {

    public function index() {
        $model = new HomeModel();
        $data = $model->ejemplo();

        $this->view("home", ["fecha" => $data["fecha"]]);
    }
}
