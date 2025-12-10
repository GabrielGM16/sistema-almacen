<?php

require_once __DIR__ . "/../core/Controller.php";
require_once __DIR__ . "/../core/Session.php";
require_once __DIR__ . "/../models/UserModel.php";

class AuthController extends Controller {
    public function login() {
        $token = Session::csrf();
        $this->view("login", ["token" => $token, "title" => "Iniciar sesión"]);
    }

    public function doLogin() {
        $token = $_POST["_token"] ?? "";
        if (!Session::validateCsrf($token)) {
            $this->view("login", ["token" => Session::csrf(), "error" => "Token inválido", "title" => "Iniciar sesión"]);
            return;
        }
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        if ($username === "" || $password === "") {
            $this->view("login", ["token" => Session::csrf(), "error" => "Credenciales requeridas", "title" => "Iniciar sesión"]);
            return;
        }
        $model = new UserModel();
        $user = $model->findByUsername($username);
        if (!$user || !password_verify($password, $user["password"])) {
            $this->view("login", ["token" => Session::csrf(), "error" => "Usuario o contraseña incorrectos", "title" => "Iniciar sesión"]);
            return;
        }
        Session::login(["id" => $user["id"], "username" => $user["username"], "role" => $user["role"]]);
        $this->redirect("/dashboard");
    }

    public function logout() {
        Session::logout();
        $this->redirect("/login");
    }
}
