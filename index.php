<?php
session_start();
date_default_timezone_set("America/Chicago");

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/bootstrap.php";

use Pimple\Container;
use ReiaDev\Database;
use ReiaDev\Model\UserModel;
use ReiaDev\Model\RegisterModel;
use ReiaDev\Model\LoginModel;
use ReiaDev\Model\WikiModel;
use ReiaDev\Model\ForumModel;
use ReiaDev\Controller\HomeController;
use ReiaDev\Controller\UserController;
use ReiaDev\Controller\RegisterController;
use ReiaDev\Controller\LoginController;
use ReiaDev\Controller\WikiController;
use ReiaDev\Controller\ForumController;

if (!get_csrf_token()) {
    create_csrf_token();
}
$container = new Container();
$container["db"] = function ($c) {
    return (new Database(get_database_config()))->getPDO();
};
$container["twig"] = function ($c) {
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/views");
    return new \Twig\Environment($loader);
};
$container["router"] = function ($c) {
    return new \Bramus\Router\Router();
};
$container["userModel"] = function ($c) {
    return new UserModel($c["db"]);
};
$container["registerModel"] = function ($c) {
    return new RegisterModel($c["db"]);
};
$container["loginModel"] = function ($c) {
    return new LoginModel($c["db"]);
};
$container["wikiModel"] = function ($c) {
    return new WikiModel($c["db"]);
};
$container["forumModel"] = function ($c) {
    return new ForumModel($c["db"]);
};
$container["homeController"] = function ($c) {
    return new HomeController($c["userModel"], $c["twig"]);
};
$container["registerController"] = function ($c) {
    return new RegisterController($c["registerModel"], $c["twig"]);
};
$container["loginController"] = function ($c) {
    return new LoginController($c["loginModel"], $c["twig"]);
};
$container["userController"] = function ($c) {
    return new UserController($c["userModel"], $c["twig"]);
};
$container["wikiController"] = function ($c) {
    return new WikiController($c["wikiModel"], $c["twig"], $c["userModel"]);
};
$container["forumController"] = function ($c) {
    return new ForumController($c["forumModel"], $c["twig"], $c["userModel"]);
};
require_once __DIR__ . "/routes.php";

$container["router"]->run();
