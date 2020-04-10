<?php
namespace ReiaDev\Controller;

use ReiaDev\Model\LoginModel;
use ReiaDev\Flash;

class LoginController {
    private $model;
    private $view;
    private Flash $flash;

    public function __construct($model, $view, $flash) {
        $this->model = $model;
        $this->view = $view;
        $this->flash = $flash;
    }
    public function indexGet() {
        $flash = $this->flash->getSession();
        $formInput = getFormInput();
        $csrfToken = get_csrf_token();
        destroyFormInput();
        $userId = $_SESSION["user-id"] ?? null;

        if ($userId) {
            $this->flash->setData("You're already logged in.", "warning");
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
            $this->flash->setData($error, "error");
            setFormInput(["username" => $username]);
            header("Location: /login");
        } else {
            $user = $this->model->selectById($verify["id"]);
            $_SESSION["user-id"] = $user["id"];
            $this->flash->setData("User logged in successfully.", "success");
            header("Location: /profile");
        }
    }
}
