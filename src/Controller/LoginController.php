<?php
namespace ReiaDev\Controller;

use ReiaDev\Model\LoginModel;

class LoginController {
    private $model;
    private $view;

    public function __construct($model, $view) {
        $this->model = $model;
        $this->view = $view;
    }
    public function indexGet() {
        $flash = get_flash();
        $formInput = get_form_input();
        $csrfToken = get_csrf_token();
        destroy_flash();
        destroy_form_input();
        $userId = $_SESSION["user-id"] ?? null;

        if ($userId) {
            set_flash("You're already logged in.", "warning");
            header("Location: /profile");
            exit();
        }
        echo $this->view->render("login.twig", ["title" => "Login", "flash" => $flash, "form_input" => $formInput, "csrf_token" => $csrfToken]);
    }
    public function indexPost() {
        $csrfToken = $_POST["csrf-token"];
        $username = $_POST["username"];
        $password = $_POST["password"];
        $error = "";

        if (!hash_equals($csrfToken, get_csrf_token())) {
            $error .= "Possible CSRF attack detected. Please contact the server administrator.<br>";
        }
        if (empty($username)) {
            $error .= "Please enter a username.<br>";
        }
        if (empty($password)) {
            $error .= "Please enter a password.<br>";
        }
        $verify = $this->model->verify($username, $password);

        if (empty($verify)) {
            $error .= "Invalid login credentials, or your account isn't active.<br>";
        }
        if (!empty($error)) {
            set_flash($error, "error");
            set_form_input(["username" => $username]);
            header("Location: /login");
            exit();
        } else {
            $user = $this->model->selectById($verify["id"]);
            $_SESSION["user-id"] = $user["id"];
            set_flash("User logged in successfully.", "success");
            header("Location: /profile");
            exit();
        }
    }
}
