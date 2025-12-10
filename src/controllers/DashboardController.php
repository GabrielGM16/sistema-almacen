<?php

require_once __DIR__ . "/../core/Controller.php";
require_once __DIR__ . "/../core/Session.php";

class DashboardController extends Controller {
    public function index() {
        if (!Session::user()) {
            $this->redirect("/login");
            return;
        }
        $this->view("dashboard", ["title" => "Panel"]);
    }
}

