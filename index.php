<?php
session_start();
date_default_timezone_set("America/Chicago");

if (file_exists("config.ini")) {
    $config = parse_ini_file("config.ini", true);
}
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/bootstrap.php";

use ReiaDev\Database;
use ReiaDev\Model\UserModel;
use ReiaDev\Controller\HomeController;
use ReiaDev\Controller\UserController;
use ReiaDev\Controller\RegisterController;
use ReiaDev\Controller\LoginController;
use ReiaDev\Controller\WikiController;
use ReiaDev\Controller\ForumController;

if (!get_csrf_token()) {
    create_csrf_token();
}
$db = (new Database($dsn, $db_user, $db_pass, $db_options))->getPDO();
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/views");
$twig = new \Twig\Environment($loader);
$router = new \Bramus\Router\Router();
$userModel = new UserModel($db);

$router->get("/", function () use ($db, $twig, $userModel) {
    $controller = new HomeController($db, $twig, $userModel);
    $controller->indexGet();
});
$router->get("/about", function () use ($db, $twig, $userModel) {
    $controller = new HomeController($db, $twig, $userModel);
    $controller->aboutGet();
});
$router->get("/register", function () use ($db, $twig) {
    $controller = new RegisterController($db, $twig);
    $controller->indexGet();
});
$router->post("/register", function () use ($db, $twig) {
    $controller = new RegisterController($db, $twig);
    $controller->indexPost();
});
$router->get("/login", function () use ($db, $twig) {
    $controller = new LoginController($db, $twig);
    $controller->indexGet();
});
$router->post("/login", function () use ($db, $twig) {
    $controller = new LoginController($db, $twig);
    $controller->indexPost();
});
$router->get("/logout", function () use ($db, $twig, $userModel) {
    $controller = new UserController($db, $twig, $userModel);
    $controller->logoutGet();
});
$router->get("/profile", function () use ($db, $twig, $userModel) {
    $controller = new UserController($db, $twig, $userModel);
    $controller->profileGet();
});
$router->get("/user/(.*)", function ($username) use ($db, $twig, $userModel) {
    $controller = new UserController($db, $twig, $userModel);
    $controller->indexGet($username);
});
$router->get("/admin", function () use ($db, $twig, $userModel) {
    $controller = new UserController($db, $twig, $userModel);
    $controller->adminGet();
});
$router->get("/admin/activate/(\d+)", function ($id) use ($db, $twig, $userModel) {
    $controller = new UserController($db, $twig, $userModel);
    $controller->activateGet($id);
});
$router->get("/admin/ban/(\d+)", function ($id) use ($db, $twig, $userModel) {
    $controller = new UserController($db, $twig, $userModel);
    $controller->banGet($id);
});
$router->get("/admin/promote/(\d+)", function ($id) use ($db, $twig, $userModel) {
    $controller = new UserController($db, $twig, $userModel);
    $controller->promoteGet($id);
});
$router->get("/wiki", function () use ($db, $twig, $userModel) {
    $controller = new WikiController($db, $twig, $userModel);
    $controller->indexGet();
});
$router->get("/wiki/article/([a-z0-9_-]+)", function ($getSlug) use ($db, $twig, $userModel) {
    $controller = new WikiController($db, $twig, $userModel);
    $controller->articleGet($getSlug);
});
$router->get("/wiki/create", function () use ($db, $twig, $userModel) {
    $controller = new WikiController($db, $twig, $userModel);
    $controller->createGet();
});
$router->get("/wiki/create/([a-z0-9_-]+)", function ($getSlug) use ($db, $twig, $userModel) {
    $controller = new WikiController($db, $twig, $userModel);
    $controller->createGet($getSlug);
});
$router->post("/wiki/create", function () use ($db, $twig, $userModel) {
    $controller = new WikiController($db, $twig, $userModel);
    $controller->createPost();
});
$router->post("/wiki/create/([a-z0-9_-]+)", function ($getSlug) use ($db, $twig, $userModel) {
    $controller = new WikiController($db, $twig, $userModel);
    $controller->createPost($getSlug);
});
$router->get("/wiki/update/([a-z0-9_-]+)", function ($getSlug) use ($db, $twig, $userModel) {
    $controller = new WikiController($db, $twig, $userModel);
    $controller->updateGet($getSlug);
});
$router->post("/wiki/update/([a-z0-9_-]+)", function ($getSlug) use ($db, $twig, $userModel) {
    $controller = new WikiController($db, $twig, $userModel);
    $controller->updatePost($getSlug);
});
$router->get("/wiki/search", function () use ($db, $twig, $userModel) {
    $controller = new WikiController($db, $twig, $userModel);
    $controller->searchGet();
});
$router->get("/wiki/search/(.*)", function ($searchTerm) use ($db, $twig, $userModel) {
    $controller = new WikiController($db, $twig, $userModel);
    $controller->searchGet($searchTerm);
});
$router->post("/wiki/search", function () use ($db, $twig, $userModel) {
    $controller = new WikiController($db, $twig, $userModel);
    $controller->searchPost();
});
$router->get("/wiki/upload", function () use ($db, $twig, $userModel) {
    $controller = new WikiController($db, $twig, $userModel);
    $controller->uploadGet();
});
$router->post("/wiki/upload", function () use ($db, $twig, $userModel) {
    $controller = new WikiController($db, $twig, $userModel);
    $controller->uploadPost();
});
$router->get("/forum", function () use ($db, $twig, $userModel) {
    $controller = new ForumController($db, $twig, $userModel);
    $controller->indexGet();
});
$router->get("/forum/(\d+)", function ($id) use ($db, $twig, $userModel) {
    $controller = new ForumController($db, $twig, $userModel);
    $controller->categoryGet($id);
});
$router->get("/forum/topic/(\d+)", function ($id) use ($db, $twig, $userModel) {
    $controller = new ForumController($db, $twig, $userModel);
    $controller->topicGet($id);
});
$router->post("/forum/topic/(\d+)", function ($id) use ($db, $twig, $userModel) {
    $controller = new ForumController($db, $twig, $userModel);
    $controller->topicPost($id);
});
$router->get("/forum/create/(\d+)", function ($id) use ($db, $twig, $userModel) {
    $controller = new ForumController($db, $twig, $userModel);
    $controller->createGet($id);
});
$router->post("/forum/create/(\d+)", function ($id) use ($db, $twig, $userModel) {
    $controller = new ForumController($db, $twig, $userModel);
    $controller->createPost($id);
});
$router->set404(function () use ($db, $twig) {
    header("HTTP/1.1 404 Not Found");
    echo $twig->render("404.twig", ["title" => "404 Not Found"]);
});
$router->run();
