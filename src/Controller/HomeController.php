<?php
namespace ReiaDev\Controller;

use ReiaDev\Model\UserModel;
use ReiaDev\Version;
use ReiaDev\Flash;
use ReiaDev\User;

class HomeController {
    private $model;
    private \Twig\Environment $twig;
    private Flash $flash;
    private Version $version;
    private ?User $user;

    public function __construct($model, $twig, $flash, $version) {
        $this->model = $model;
        $this->twig = $twig;
        $this->flash = $flash;
        $this->version = $version;

        if (!empty($_SESSION["user-id"])) {
            $u = $this->model->selectById($_SESSION["user-id"]);
            $this->user = new User($u["id"], $u["username"], $u["email"], $u["avatar"], $u["role"]);
        } else {
            $this->user = null;
        }
    }
    protected function render(string $view, array $data): void {
        $data["version"] = $this->version->getVersion();
        $data["flash"] = $this->flash->getSession();
        $data["user"] = $this->user;

        echo $this->twig->render($view, $data);
    }
    public function indexGet(): void {
        $this->render("index.twig", ["title" => "Home"]);
    }
    public function aboutGet(): void {
        $this->render("about.twig", ["title" => "About"]);
    }
}
