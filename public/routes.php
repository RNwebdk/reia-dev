<?php
$router = $container["router"];

$router->get("/", function () use ($container) {
    return $container["homeController"]->indexGet();
});
$router->get("/about", function () use ($container) {
    return $container["homeController"]->aboutGet();
});
$router->get("/register", function () use ($container) {
    return $container["registerController"]->indexGet();
});
$router->post("/register", function () use ($container) {
    return $container["registerController"]->indexPost();
});
$router->get("/login", function () use ($container) {
    return $container["loginController"]->indexGet();
});
$router->post("/login", function () use ($container) {
    return $container["loginController"]->indexPost();
});
$router->get("/logout", function () use ($container) {
    return $container["userController"]->logoutGet();
});
$router->get("/profile", function () use ($container) {
    return $container["userController"]->profileGet();
});
$router->post("/profile", function () use ($container) {
    return $container["userController"]->profilePost();
});
$router->get("/user/(.*)", function ($username) use ($container) {
    return $container["userController"]->indexGet($username);
});
$router->mount("/admin", function () use ($router, $container) {
    $router->get("/", function () use ($container) {
        return $container["userController"]->adminGet();
    });
    $router->get("/(\w+)/(\d+)/(\d+|-\d+)", function ($action, $id, $status) use ($container) {
        return $container["userController"]->adminAction($action, $id, $status);
    });
});
$router->mount("/wiki", function () use ($router, $container) {
    $router->get("/", function () use ($container) {
        return $container["wikiController"]->indexGet();
    });
    $router->get("/article/([a-z0-9_-]+)", function ($getSlug) use ($container) {
        return $container["wikiController"]->articleGet($getSlug);
    });
    $router->get("/create", function () use ($container) {
        return $container["wikiController"]->createGet();
    });
    $router->get("/create/([a-z0-9_-]+)", function ($getSlug) use ($container) {
        return $container["wikiController"]->createGet($getSlug);
    });
    $router->post("/create", function () use ($container) {
        return $container["wikiController"]->createPost();
    });
    $router->post("/create/([a-z0-9_-]+)", function ($getSlug) use ($container) {
        return $container["wikiController"]->createPost($getSlug);
    });
    $router->get("/update/([a-z0-9_-]+)", function ($getSlug) use ($container) {
        return $container["wikiController"]->updateGet($getSlug);
    });
    $router->post("/update/([a-z0-9_-]+)", function ($getSlug) use ($container) {
        return $container["wikiController"]->updatePost($getSlug);
    });
    $router->get("/search", function () use ($container) {
        return $container["wikiController"]->searchGet();
    });
    $router->get("/search/(.*)", function ($searchTerm) use ($container) {
        return $container["wikiController"]->searchGet($searchTerm);
    });
    $router->post("/search", function () use ($container) {
        return $container["wikiController"]->searchPost();
    });
    $router->get("/upload", function () use ($container) {
        return $container["wikiController"]->uploadGet();
    });
    $router->post("/upload", function () use ($container) {
        return $container["wikiController"]->uploadPost();
    });
    $router->get("/uploads", function () use ($container) {
        return $container["wikiController"]->uploadsGet();
    });
    $router->get("/download/([a-z0-9_-]+)", function ($getSlug) use ($container) {
        return $container["wikiController"]->downloadGet($getSlug);
    });
});
$router->mount("/forum", function () use ($router, $container) {
    $router->get("/", function () use ($container) {
        return $container["forumController"]->indexGet();
    });
    $router->get("/(\d+)", function ($id) use ($container) {
        return $container["forumController"]->categoryGet($id);
    });
    $router->get("/topic/(\d+)", function ($id) use ($container) {
        return $container["forumController"]->topicGet($id);
    });
    $router->post("/topic/(\d+)", function ($id) use ($container) {
        return $container["forumController"]->topicPost($id);
    });
    $router->get("/create/(\d+)", function ($id) use ($container) {
        return $container["forumController"]->createGet($id);
    });
    $router->post("/create/(\d+)", function ($id) use ($container) {
        return $container["forumController"]->createPost($id);
    });
    $router->get("/update/(\d+)", function ($id) use ($container) {
        return $container["forumController"]->updateGet($id);
    });
    $router->post("/update/(\d+)", function ($id) use ($container) {
        return $container["forumController"]->updatePost($id);
    });
    $router->get("/admin/(\w+)/(\d+)/(\d+)", function ($action, $id, $status) use ($container) {
        return $container["forumController"]->adminAction($action, $id, $status);
    });
});
$router->set404(function () use ($container) {
    header("HTTP/1.1 404 Not Found");
    echo $container["twig"]->render("404.twig", ["title" => "404 Not Found"]);
});
