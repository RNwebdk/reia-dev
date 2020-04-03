<?php
namespace ReiaDev\Controller;

use ReiaDev\Model\WikiModel;

class WikiController {
    private $model;
    private $view;
    private $user;

    public function __construct($db, $view, $userModel) {
        $this->model = new WikiModel($db);
        $this->view = $view;

        if (!empty($_SESSION["user-id"]) || !empty($_SESSION["is-authenticated"])) {
            $this->user = $userModel->selectById($_SESSION["user-id"]);
        }
    }
    public function indexGet() {
        $flash = get_flash();
        destroy_flash();
        $articles = $this->model->selectAll();
        echo $this->view->render("wiki.twig", ["title" => "Wiki", "articles" => $articles, "user" => $this->user, "flash" => $flash]);
    }
    public function articleGet($getSlug) {
        $flash = get_flash();
        destroy_flash();
        $article = $this->model->selectBySlug($getSlug);

        if ($article) {
            $title = $article["title"];
            $parser = new \Netcarver\Textile\Parser();
            $body = $parser->setDocumentType("html5")->parse($article["body"]);
        } else {
            $title = $getSlug;
            $body = "";
            header("HTTP/1.1 404 Not Found");
        }
        echo $this->view->render("wiki.article.twig", ["title" => $title, "article" => $article, "body" => $body, "user" => $this->user, "flash" => $flash, "slug" => $getSlug]);
    }
    public function createGet($getSlug = "") {
        $flash = get_flash();
        $formInput = get_form_input();
        $csrfToken = get_csrf_token();
        destroy_flash();
        destroy_form_input();
        $userId = $_SESSION["user-id"] ?? null;

        if (!$userId || !$_SESSION["is-authenticated"]) {
            set_flash("Please login to view this page.", "error");
            header("Location: /wiki");
            exit();
        }
        echo $this->view->render("wiki.create.twig", ["title" => "Create Article", "user" => $this->user, "flash" => $flash, "slug" => $getSlug, "form_input" => $formInput, "csrf_token" => $csrfToken]);
    }
    public function createPost($getSlug = "") {
        $csrfToken = $_POST["csrf-token"];
        $title = $_POST["title"];
        $slug = to_slug($title);
        $body = $_POST["body"];
        $error = "";

        if (!hash_equals($csrfToken, get_csrf_token())) {
            $error .= "Possible CSRF attack detected. Please contact the server administrator.<br>";
        }
        if (empty($title)) {
            $error .= "Please enter a title.<br>";
        } elseif (strlen($title) < 4 || strlen($title) > 64) {
            $error .= "Title needs to be between 4 and 64 characters long.<br>";
        }
        $article = $this->model->selectBySlug($slug);

        if ($article) {
            $error .= "An article by this title already exists.<br>";
        }
        if (!empty($error)) {
            set_flash($error, "error");
            set_form_input(["title" => $title, "body" => $body]);
            header("Location: /wiki/create");
            exit();
        } else {
            $this->model->insert($title, $slug, $body, date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), $this->user["id"]);
            set_flash("Wiki article successfully created!", "success");
            header("Location: /wiki/article/" . $slug);
            exit();
        }
    }
    public function updateGet($getSlug) {
        $flash = get_flash();
        $formInput = get_form_input();
        $csrfToken = get_csrf_token();
        destroy_flash();
        destroy_form_input();
        $userId = $_SESSION["user-id"] ?? null;

        if (!$userId || !$_SESSION["is-authenticated"]) {
            set_flash("Please login to view this page.", "error");
            header("Location: /wiki/article/" . $getSlug);
            exit();
        }
        $article = $this->model->selectBySlug($getSlug);

        if ($article) {
            $title = $article["title"];
        } else {
            $title = $getSlug;
        }
        echo $this->view->render("wiki.update.twig", ["title" => "Update " . $title, "article" => $article, "user" => $this->user, "flash" => $flash, "slug" => $getSlug, "form_input" => $formInput, "csrf_token" => $csrfToken]);
    }
    public function updatePost($getSlug = "") {
        $csrfToken = $_POST["csrf-token"];
        $article = $this->model->selectBySlug($getSlug);
        $title = $_POST["title"];
        $body = $_POST["body"];
        $error = "";

        if (!hash_equals($csrfToken, get_csrf_token())) {
            $error .= "Possible CSRF attack detected. Please contact the server administrator.<br>";
        }
        if (to_slug($title) !== $article["slug"]) {
            $error .= "Title must match slug. Only capitalization and symbols may be changed.";
        }
        if (!empty($error)) {
            set_flash($error, "error");
            set_form_input(["title" => $title, "body" => $body]);
            header("Location: /wiki/update/" . $getSlug);
            exit();
        } else {
            $this->model->update($title, $body, date("Y-m-d H:i:s"), $this->user["id"], $getSlug);
            set_flash("Wiki article successfully updated!", "success");
            header("Location: /wiki/article/" . $getSlug);
            exit();
        }
    }
    public function searchGet($searchTerm = "") {
        $flash = get_flash();
        destroy_flash();

        if (!empty($searchTerm)) {
            $articles = $this->model->search($searchTerm);
        } else {
            $articles = null;
        }
        echo $this->view->render("wiki.search.twig", ["title" => "Search", "search_term" => $searchTerm, "articles" => $articles, "user" => $this->user, "flash" => $flash]);
    }
    public function searchPost() {
        $searchTerm = $_POST["search-term"];
        header("Location: /wiki/search/" . $searchTerm);
        exit();
    }
    public function uploadGet() {
        $flash = get_flash();
        $csrfToken = get_csrf_token();
        destroy_flash();
        $userId = $_SESSION["user-id"] ?? null;

        if (!$userId || !$_SESSION["is-authenticated"]) {
            set_flash("Please login to view this page.", "error");
            header("Location: /wiki");
            exit();
        }
        echo $this->view->render("wiki.upload.twig", ["title" => "Upload", "user" => $this->user, "flash" => $flash, "csrf_token" => $csrfToken]);
    }
    public function uploadPost() {
        $csrfToken = $_POST["csrf-token"];
        $targetDir = $_SERVER["DOCUMENT_ROOT"] . "/uploads/";

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        if ($_FILES["upload"]["error"] === 4) {
            set_flash("No file uploaded.", "error");
            header("Location: /wiki/upload");
            exit();
        }
        $targetFile = $targetDir . basename($_FILES["upload"]["name"]);
        $uploadValid = true;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $error = "";
        $check = getimagesize($_FILES["upload"]["tmp_name"]);

        if (!hash_equals($csrfToken, get_csrf_token())) {
            $error .= "Possible CSRF attack detected. Please contact the server administrator.<br>";
            $uploadValid = false;
        }
        if ($check) {
            $uploadValid = true;
        } else {
            $uploadValid = false;
        }
        if (file_exists($targetFile)) {
            $error .= "Image already exists.<br>";
            $uploadValid = false;
        }
        if ($_FILES["upload"]["size"] > 10240) {
            $error .= "Image too large. Must be 10 KB or less.<br>";
            $uploadValid = false;
        }
        if (!in_array($imageFileType, ["gif", "png", "jpg", "jpeg"])) {
            $error .= "Invalid image type.<br>";
            $uploadValid = false;
        }
        if (!$uploadValid) {
            set_flash($error, "error");
            header("Location: /wiki/upload");
            exit();
        } else {
            if (move_uploaded_file($_FILES["upload"]["tmp_name"], $targetFile)) {
                set_flash("Uploaded image " . basename($_FILES["upload"]["name"]) . " successfully!", "success");
                header("Location: /wiki/upload");
                exit();
            } else {
                set_flash("There was an issue uploading your image.", "error");
                header("Location: /wiki/upload");
                exit();
            }
        }
    }
}
