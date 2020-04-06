<?php
namespace ReiaDev\Controller;

use ReiaDev\Model\RegisterModel;

class RegisterController {
    private $model;
    private $view;

    public function __construct($model, $view) {
        $this->model = $model;
        $this->view = $view;
    }
    public function indexGet() {
        $flash = get_flash();
        $csrfToken = get_csrf_token();
        destroy_flash();
        $userId = $_SESSION["user-id"] ?? null;

        if ($userId && $_SESSION["is-authenticated"]) {
            set_flash("You're already logged in.", "warning");
            header("Location: /profile");
            exit();
        }
        echo $this->view->render("register.twig", ["title" => "Register", "flash" => $flash, "csrf_token" => $csrfToken]);
    }
    public function indexPost() {
        $csrfToken = $_POST["csrf-token"];
        $username = $_POST["username"];
        $password = $_POST["password"];
        $email = $_POST["email"];
        $error = "";

        if (!hash_equals($csrfToken, get_csrf_token())) {
            $error .= "Possible CSRF attack detected. Please contact the server administrator.<br>";
        }
        if (empty($username)) {
            $error .= "Please enter a username.<br>";
        } elseif (strlen($username) < 2 || strlen($username) > 24) {
            $error .= "Username needs to be between 2 and 24 characters long.<br>";
        }
        if (empty($password)) {
            $error .= "Please enter a password.<br>";
        } elseif (strlen($password) < 8) {
            $error .= "Please enter a password of at least 8 or more characters.<br>";
        }
        if (empty($email)) {
            $error .= "Please enter an e-mail address.<br>";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error .= "Please enter a valid e-mail address.<br>";
        }
        $user = $this->model->selectByUsernameOrEmail($username, $email);

        if ($user) {
            $error .= "A user already exists with the supplied username or e-mail address.<br>";
        }
        if (!empty($error)) {
            set_flash($error, "error");
            set_form_input(["username" => $username, "email" => $email]);
            header("Location: /register");
            exit();
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $this->model->insert($username, $passwordHash, $email, 0);
            set_flash("User registered successfully.", "success");
            header("Location: /login");
            exit();
        }
    }
}
