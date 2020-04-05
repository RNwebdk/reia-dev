<?php
session_start();
date_default_timezone_set("America/Chicago");

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
$db = (new Database(get_database_config()))->getPDO();
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
$router->post("/profile", function () use ($db, $twig, $userModel) {
    $controller = new UserController($db, $twig, $userModel);
    $controller->profilePost();
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
$router->mount("/wiki", function () use ($router, $db, $twig, $userModel) {
    $router->get("/", function () use ($db, $twig, $userModel) {
        $controller = new WikiController($db, $twig, $userModel);
        $controller->indexGet();
    });
    $router->get("/article/([a-z0-9_-]+)", function ($getSlug) use ($db, $twig, $userModel) {
        $controller = new WikiController($db, $twig, $userModel);
        $controller->articleGet($getSlug);
    });
    $router->get("/create", function () use ($db, $twig, $userModel) {
        $controller = new WikiController($db, $twig, $userModel);
        $controller->createGet();
    });
    $router->get("/create/([a-z0-9_-]+)", function ($getSlug) use ($db, $twig, $userModel) {
        $controller = new WikiController($db, $twig, $userModel);
        $controller->createGet($getSlug);
    });
    $router->post("/create", function () use ($db, $twig, $userModel) {
        $controller = new WikiController($db, $twig, $userModel);
        $controller->createPost();
    });
    $router->post("/create/([a-z0-9_-]+)", function ($getSlug) use ($db, $twig, $userModel) {
        $controller = new WikiController($db, $twig, $userModel);
        $controller->createPost($getSlug);
    });
    $router->get("/update/([a-z0-9_-]+)", function ($getSlug) use ($db, $twig, $userModel) {
        $controller = new WikiController($db, $twig, $userModel);
        $controller->updateGet($getSlug);
    });
    $router->post("/update/([a-z0-9_-]+)", function ($getSlug) use ($db, $twig, $userModel) {
        $controller = new WikiController($db, $twig, $userModel);
        $controller->updatePost($getSlug);
    });
    $router->get("/search", function () use ($db, $twig, $userModel) {
        $controller = new WikiController($db, $twig, $userModel);
        $controller->searchGet();
    });
    $router->get("/search/(.*)", function ($searchTerm) use ($db, $twig, $userModel) {
        $controller = new WikiController($db, $twig, $userModel);
        $controller->searchGet($searchTerm);
    });
    $router->post("/search", function () use ($db, $twig, $userModel) {
        $controller = new WikiController($db, $twig, $userModel);
        $controller->searchPost();
    });
    $router->get("/upload", function () use ($db, $twig, $userModel) {
        $controller = new WikiController($db, $twig, $userModel);
        $controller->uploadGet();
    });
    $router->post("/upload", function () use ($db, $twig, $userModel) {
        $controller = new WikiController($db, $twig, $userModel);
        $controller->uploadPost();
    });
});
$router->mount("/forum", function () use ($router, $db, $twig, $userModel) {
    $router->get("/", function () use ($db, $twig, $userModel) {
        $controller = new ForumController($db, $twig, $userModel);
        $controller->indexGet();
    });
    $router->get("/(\d+)", function ($id) use ($db, $twig, $userModel) {
        $controller = new ForumController($db, $twig, $userModel);
        $controller->categoryGet($id);
    });
    $router->get("/topic/(\d+)", function ($id) use ($db, $twig, $userModel) {
        $controller = new ForumController($db, $twig, $userModel);
        $controller->topicGet($id);
    });
    $router->post("/topic/(\d+)", function ($id) use ($db, $twig, $userModel) {
        $controller = new ForumController($db, $twig, $userModel);
        $controller->topicPost($id);
    });
    $router->get("/create/(\d+)", function ($id) use ($db, $twig, $userModel) {
        $controller = new ForumController($db, $twig, $userModel);
        $controller->createGet($id);
    });
    $router->post("/create/(\d+)", function ($id) use ($db, $twig, $userModel) {
        $controller = new ForumController($db, $twig, $userModel);
        $controller->createPost($id);
    });
    $router->get("/update/(\d+)", function ($id) use ($db, $twig, $userModel) {
        $controller = new ForumController($db, $twig, $userModel);
        $controller->updateGet($id);
    });
    $router->post("/update/(\d+)", function ($id) use ($db, $twig, $userModel) {
        $controller = new ForumController($db, $twig, $userModel);
        $controller->updatePost($id);
    });
    $router->get("/topic/lock/(\d+)", function ($id) use ($db, $twig, $userModel) {
        $controller = new ForumController($db, $twig, $userModel);
        $controller->lockTopic($id);
    });
    $router->get("/topic/unlock/(\d+)", function ($id) use ($db, $twig, $userModel) {
        $controller = new ForumController($db, $twig, $userModel);
        $controller->unlockTopic($id);
    });
});
$router->set404(function () use ($db, $twig) {
    header("HTTP/1.1 404 Not Found");
    echo $twig->render("404.twig", ["title" => "404 Not Found"]);
});
$router->run();
