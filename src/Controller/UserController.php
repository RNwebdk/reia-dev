<?php
namespace ReiaDev\Controller;

use ReiaDev\Model\UserModel;
use ReiaDev\Flash;
use ReiaDev\User;

class UserController {
    private $model;
    private \Twig\Environment $twig;
    private Flash $flash;
    private ?User $user;

    public function __construct($model, $twig, $flash) {
        $this->model = $model;
        $this->twig = $twig;
        $this->flash = $flash;

        if (!empty($_SESSION["user-id"])) {
            $u = $this->model->selectById($_SESSION["user-id"]);
            $this->user = new User($u["id"], $u["username"], $u["email"], $u["avatar"], $u["role"]);
        } else {
            $this->user = null;
        }
    }
    protected function render(string $view, array $data): void {
        $data["flash"] = $this->flash->getSession();
        $data["user"] = $this->user;

        echo $this->twig->render($view, $data);
    }
    public function indexGet($username): void {
        $userProfile = $this->model->selectByUsername($username);
        $this->render("user.twig", ["title" => $userProfile["username"] ?? "User Not Found", "user_profile" => $userProfile]);
    }
    public function profileGet(): void {
        if (!$this->user) {
            $this->flash->setData("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        $this->render("profile.twig", ["title" => $this->user->username ?? "Profile"]);
    }
    public function profilePost(): void {
        $avatarUrl = $_POST["avatar-url"];
        $this->model->updateAvatar($avatarUrl, $this->user->id);
        $this->flash->setData("Updated avatar.", "success");
        header("Location: /profile");
    }
    public function adminGet(): void {
        if (!$this->user) {
            $this->flash->setData("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if ($this->user->role < 2) {
            $this->flash->setData("You're not authorized to view this page.", "error");
            header("Location: /");
            exit();
        }
        $users = $this->model->selectAll();
        $this->render("admin.twig", ["title" => "Administrator Panel", "users" => $users]);
    }
    public function adminAction(string $action, int $id, int $status): void {
        if (!$this->user) {
            $this->flash->setData("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if ($this->user->role < 2) {
            $this->flash->setData("You're not authorized to view this page.", "error");
            header("Location: /");
            exit();
        }
        $currentUser = $this->model->selectById($id);

        if ($currentUser) {
            if ($currentUser["role"] === $status) {
                $this->flash->setData("The user's role is already set to this status.", "warning");
            } else {
                if ($action === "activate") {
                    $flashAction = "Activated";
                } elseif ($action === "ban") {
                    $flashAction = "Banned";
                } elseif ($action === "promote") {
                    $flashAction = "Promoted";
                } else {
                    $flashAction = "Did something to";
                }
                $this->flash->setData($flashAction . " user " . $currentUser["username"] . " successfully!", "success");
                $this->model->updateRole($status, $id);
            }
        } else {
            $this->flash->setData("No user by the ID of " . $id . " exists.", "error");
        }
        header("Location: /admin");
    }
    public function logoutGet(): void {
        if ($this->user) {
            unset($_SESSION["user-id"]);
            $this->flash->setData("User logged out successfully.", "success");
            header("Location: /");
        } else {
            $this->flash->setData("Please login to view this page.", "error");
            header("Location: /login");
        }
    }
}
