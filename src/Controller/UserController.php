<?php
namespace ReiaDev\Controller;

use ReiaDev\Model\UserModel;
use ReiaDev\User;

class UserController {
    private $model;
    private \Twig\Environment $twig;
    private ?User $user;

    public function __construct($model, $twig) {
        $this->model = $model;
        $this->twig = $twig;

        if (!empty($_SESSION["user-id"])) {
            $u = $this->model->selectById($_SESSION["user-id"]);
            $this->user = new User($u["id"], $u["username"], $u["email"], $u["avatar"], $u["role"]);
        } else {
            $this->user = null;
        }
    }
    protected function render(string $view, array $data): void {
        $data["flash"] = get_flash();
        destroy_flash();
        $data["user"] = $this->user;

        echo $this->twig->render($view, $data);
    }
    public function indexGet($username): void {
        $userProfile = $this->model->selectByUsername($username);
        $this->render("user.twig", ["title" => $userProfile["username"] ?? "User Not Found", "user_profile" => $userProfile]);
    }
    public function profileGet(): void {
        if (!$this->user) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        $this->render("profile.twig", ["title" => $this->user->username ?? "Profile"]);
    }
    public function profilePost(): void {
        $avatarUrl = $_POST["avatar-url"];
        $this->model->updateAvatar($avatarUrl, $this->user->id);
        set_flash("Updated avatar.", "success");
        header("Location: /profile");
        exit();
    }
    public function adminGet(): void {
        if (!$this->user) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if ($this->user->role < 2) {
            set_flash("You're not authorized to view this page.", "error");
            header("Location: /");
            exit();
        }
        $users = $this->model->selectAll();
        $this->render("admin.twig", ["title" => "Administrator Panel", "users" => $users]);
    }
    public function activateGet(int $id): void {
        if (!$this->user) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if ($this->user->role < 2) {
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
    public function banGet(int $id): void {
        if (!$this->user) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if ($this->user->role < 2) {
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
    public function promoteGet(int $id): void {
        if (!$this->user) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if ($this->user->role < 2) {
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
    public function logoutGet(): void {
        if ($this->user) {
            unset($_SESSION["user-id"]);
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
