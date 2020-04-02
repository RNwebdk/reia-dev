<?php
namespace ReiaDev\Controller;

use ReiaDev\Model\UserModel;

class UserController {
    private $model;
    private $view;
    private $user;

    public function __construct($db, $view, $userModel) {
        $this->model = new UserModel($db);
        $this->view = $view;

        if (!empty($_SESSION["user-id"]) || !empty($_SESSION["is-authenticated"])) {
            $this->user = $userModel->selectById($_SESSION["user-id"]);
        }
    }
    public function indexGet($username) {
        $flash = get_flash();
        destroy_flash();
        $userProfile = $this->model->selectByUsername($username);

        if ($userProfile) {
            $title = $userProfile["username"];
        } else {
            $title = "User Not Found";
        }
        echo $this->view->render("user.twig", ["title" => $title, "user_profile" => $userProfile, "user" => $this->user, "flash" => $flash]);
    }
    public function profileGet() {
        $flash = get_flash();
        destroy_flash();
        $userId = $_SESSION["user-id"] ?? null;

        if (!$userId || !$_SESSION["is-authenticated"]) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        echo $this->view->render("profile.twig", ["title" => $user["username"] ?? "Profile", "user" => $this->user, "flash" => $flash]);
    }
    public function profilePost() {
        $avatarUrl = $_POST["avatar-url"];
        $this->model->updateAvatar($avatarUrl, $this->user["id"]);
        set_flash("Updated avatar.", "success");
        header("Location: /profile");
        exit();
    }
    public function adminGet() {
        $flash = get_flash();
        destroy_flash();
        $userId = $_SESSION["user-id"] ?? null;

        if (!$userId || !$_SESSION["is-authenticated"]) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if (!$_SESSION["is-administrator"]) {
            set_flash("You're not authorized to view this page.", "error");
            header("Location: /");
            exit();
        }
        $users = $this->model->selectAll();

        echo $this->view->render("admin.twig", ["title" => "Administrator Panel", "user" => $this->user, "users" => $users, "flash" => $flash]);
    }
    public function activateGet($id) {
        $userId = $_SESSION["user-id"] ?? null;

        if (!$userId || !$_SESSION["is-authenticated"]) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if (!$_SESSION["is-administrator"]) {
            set_flash("You're not authorized to view this page.", "error");
            header("Location: /");
            exit();
        }
        $currentUser = $this->model->selectById($id);

        if ($currentUser) {
            if ($currentUser["role"] > 0) {
                set_flash("This user is already activated.", "error");
            } else {
                $this->model->updateRole(1, $id);
                set_flash("Activated user " . $currentUser["username"] . " successfully!", "success");
            }
        } else {
            set_flash("No user by the ID of " . $id . " exists.", "error");
        }
        header("Location: /admin");
        exit();
    }
    public function banGet($id) {
        $userId = $_SESSION["user-id"] ?? null;

        if (!$userId || !$_SESSION["is-authenticated"]) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if (!$_SESSION["is-administrator"]) {
            set_flash("You're not authorized to view this page.", "error");
            header("Location: /");
            exit();
        }
        $currentUser = $this->model->selectById($id);

        if ($currentUser) {
            if ($currentUser["role"] === -1) {
                set_flash("This user is already banned.", "error");
            } else {
                $this->model->updateRole(-1, $id);
                set_flash("Banned user " . $currentUser["username"] . " successfully!", "success");
            }
        } else {
            set_flash("No user by the ID of " . $id . " exists.", "error");
        }
        header("Location: /admin");
        exit();
    }
    public function promoteGet($id) {
        $userId = $_SESSION["user-id"] ?? null;

        if (!$userId || !$_SESSION["is-authenticated"]) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if (!$_SESSION["is-administrator"]) {
            set_flash("You're not authorized to view this page.", "error");
            header("Location: /");
            exit();
        }
        $currentUser = $this->model->selectById($id);

        if ($currentUser) {
            if ($currentUser["role"] === 2) {
                set_flash("This user is already an administrator.", "error");
            } else {
                $this->model->updateRole(2, $id);
                set_flash("Promoted user " . $currentUser["username"] . " to administrator successfully!", "success");
            }
        } else {
            set_flash("No user by the ID of " . $id . " exists.", "error");
        }
        header("Location: /admin");
        exit();
    }
    public function logoutGet() {
        $flash = get_flash();
        destroy_flash();
        $userId = $_SESSION["user-id"] ?? null;

        if ($userId && $_SESSION["is-authenticated"]) {
            unset($_SESSION["user-id"]);
            unset($_SESSION["username"]);
            unset($_SESSION["is-authenticated"]);
            unset($_SESSION["is-administrator"]);
            set_flash("User logged out successfully.", "success");
            header("Location: /");
            exit();
        } else {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
    }
}
