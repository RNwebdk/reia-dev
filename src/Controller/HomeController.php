<?php
namespace ReiaDev\Controller;

use ReiaDev\Model\UserModel;

class HomeController {
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
    public function indexGet() {
        $flash = get_flash();
        destroy_flash();
        echo $this->view->render("index.twig", ["title" => "Home", "user" => $this->user, "flash" => $flash]);
    }
    public function aboutGet() {
        $flash = get_flash();
        destroy_flash();
        echo $this->view->render("about.twig", ["title" => "About", "user" => $this->user, "flash" => $flash]);
    }
}
