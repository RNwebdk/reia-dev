<?php
session_start();
date_default_timezone_set("America/Chicago");

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/bootstrap.php";

use Pimple\Container;
use ReiaDev\Version;
use ReiaDev\Database;
use ReiaDev\Flash;
use ReiaDev\CSRFToken;
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

$container = new Container();
$container["version"] = function ($c) {
    return new Version(0, 1, 0);
};
$container["db"] = function ($c) {
    return (new Database(getDatabaseConfig()))->getPDO();
};
$container["twig"] = function ($c) {
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/../views");
    return new \Twig\Environment($loader);
};
$container["flash"] = function ($c) {
    return new Flash();
};
$container["csrfToken"] = function ($c) {
    return new CSRFToken();
};
if (!$container["csrfToken"]->getSession()) {
    $container["csrfToken"]->setSession();
}
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
    return new HomeController($c["userModel"], $c["twig"], $c["flash"], $c["version"]);
};
$container["registerController"] = function ($c) {
    return new RegisterController($c["registerModel"], $c["twig"], $c["flash"], $c["csrfToken"]);
};
$container["loginController"] = function ($c) {
    return new LoginController($c["loginModel"], $c["twig"], $c["flash"], $c["csrfToken"]);
};
$container["userController"] = function ($c) {
    return new UserController($c["userModel"], $c["twig"], $c["flash"], $c["csrfToken"]);
};
$container["wikiController"] = function ($c) {
    return new WikiController($c["wikiModel"], $c["twig"], $c["flash"], $c["csrfToken"], $c["userModel"]);
};
$container["forumController"] = function ($c) {
    return new ForumController($c["forumModel"], $c["twig"], $c["flash"], $c["csrfToken"], $c["userModel"]);
};
require_once __DIR__ . "/routes.php";

$container["router"]->run();
