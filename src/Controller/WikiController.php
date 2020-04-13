<?php
namespace ReiaDev\Controller;

use ReiaDev\Model\WikiModel;
use ReiaDev\Flash;
use ReiaDev\CSRFToken;
use ReiaDev\User;

class WikiController {
    private $model;
    private \Twig\Environment $twig;
    private Flash $flash;
    private CSRFToken $csrfToken;
    private ?User $user;

    public function __construct($model, $twig, $flash, $csrfToken, $userModel) {
        $this->model = $model;
        $this->twig = $twig;
        $this->flash = $flash;
        $this->csrfToken = $csrfToken;

        if (!empty($_SESSION["user-id"])) {
            $u = $userModel->selectById($_SESSION["user-id"]);
            $this->user = new User($u["id"], $u["username"], $u["email"], $u["avatar"], $u["role"]);
        } else {
            $this->user = null;
        }
        if (!file_exists("articles.json")) {
            createArticlesJson(array());
        }
    }
    protected function render(string $view, array $data): void {
        $data["flash"] = $this->flash->getSession();
        $data["user"] = $this->user;

        echo $this->twig->render($view, $data);
    }
    public function toSlug(string $str): string {
        $str = strtolower($str);
        $str = trim($str);
        $str = preg_replace("/[^a-z0-9 -]/", "", $str);
        $str = preg_replace("/\s+/", " ", $str);
        $str = str_replace(" ", "-", $str);
    
        return $str;
    }
    public function indexGet(): void {
        $articles = $this->model->selectAll();
        $this->render("wiki.twig", ["title" => "Wiki", "articles" => $articles]);
    }
    public function articleGet(string $getSlug): void {
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
        $this->render("wiki.article.twig", ["title" => $title, "article" => $article, "body" => $body, "slug" => $getSlug]);
    }
    public function createGet(string $getSlug = ""): void {
        $formInput = getFormInput();
        destroyFormInput();

        if (!$this->user) {
            $this->flash->setData("Please login to view this page.", "error");
            header("Location: /wiki");
            exit();
        }
        $this->render("wiki.create.twig", ["title" => "Create Article", "slug" => $getSlug, "form_input" => $formInput, "csrf_token" => $this->csrfToken->getSession()]);
    }
    public function createPost(string $getSlug = ""): void {
        $csrfToken = $_POST["csrf-token"];
        $title = $_POST["title"];
        $slug = $this->toSlug($title);
        $body = $_POST["body"];
        $error = "";

        if (!$this->csrfToken->verify($csrfToken)) {
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
            $this->flash->setData($error, "error");
            setFormInput(["title" => $title, "body" => $body]);
            header("Location: /wiki/create");
        } else {
            $this->model->insert($title, $slug, $body, date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), $this->user->id);
            $slugs = $this->model->selectSlugs();
            createArticlesJson($slugs);
            $this->flash->setData("Wiki article successfully created!", "success");
            header("Location: /wiki/article/" . $slug);
        }
    }
    public function updateGet(string $getSlug): void {
        $formInput = getFormInput();
        destroyFormInput();

        if (!$this->user) {
            $this->flash->setData("Please login to view this page.", "error");
            header("Location: /wiki/article/" . $getSlug);
            exit();
        }
        $article = $this->model->selectBySlug($getSlug);
        $this->render("wiki.update.twig", ["title" => "Update " . ($article["title"] ?? $getSlug), "article" => $article, "slug" => $getSlug, "form_input" => $formInput, "csrf_token" => $this->csrfToken->getSession()]);
    }
    public function updatePost(string $getSlug = ""): void {
        $csrfToken = $_POST["csrf-token"];
        $article = $this->model->selectBySlug($getSlug);
        $title = $_POST["title"];
        $body = $_POST["body"];
        $error = "";

        if (!$this->csrfToken->verify($csrfToken)) {
            $error .= "Possible CSRF attack detected. Please contact the server administrator.<br>";
        }
        if ($this->toSlug($title) !== $article["slug"]) {
            $error .= "Title must match slug. Only capitalization and symbols may be changed.";
        }
        if (!empty($error)) {
            $this->flash->setData($error, "error");
            setFormInput(["title" => $title, "body" => $body]);
            header("Location: /wiki/update/" . $getSlug);
        } else {
            $this->model->update($title, $body, date("Y-m-d H:i:s"), $this->user->id, $getSlug);
            $slugs = $this->model->selectSlugs();
            createArticlesJson($slugs);
            $this->flash->setData("Wiki article successfully updated!", "success");
            header("Location: /wiki/article/" . $getSlug);
        }
    }
    public function searchGet(string $searchTerm = ""): void {
        if (!empty($searchTerm)) {
            $articles = $this->model->search($searchTerm);
        } else {
            $articles = null;
        }
        $this->render("wiki.search.twig", ["title" => "Search", "search_term" => $searchTerm, "articles" => $articles]);
    }
    public function searchPost(): void {
        $searchTerm = $_POST["search-term"];
        header("Location: /wiki/search/" . $searchTerm);
    }
    public function uploadGet(): void {
        if (!$this->user) {
            $this->flash->setData("Please login to view this page.", "error");
            header("Location: /wiki");
            exit();
        }
        $this->render("wiki.upload.twig", ["title" => "Upload", "csrf_token" => $this->csrfToken->getSession()]);
    }
    public function uploadsGet(): void {
        $uploads = $this->model->selectUploadedImages();
        $this->render("wiki.uploads.twig", ["title" => "Uploads", "uploads" => $uploads]);
    }
    public function uploadPost(): void {
        $csrfToken = $_POST["csrf-token"];
        $targetDir = $_SERVER["DOCUMENT_ROOT"] . "/uploads/";

        if (!$this->user) {
            $this->flash->setData("Please login to view this page.", "error");
            header("Location: /wiki");
            exit();
        }
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        if ($_FILES["upload"]["error"] === 4) {
            $this->flash->setData("No file uploaded.", "error");
            header("Location: /wiki/upload");
            exit();
        }
        $targetFile = $targetDir . basename($_FILES["upload"]["name"]);
        $uploadValid = true;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $error = "";
        $check = getimagesize($_FILES["upload"]["tmp_name"]);

        if (!$this->csrfToken->verify($csrfToken)) {
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
            $this->flash->setData($error, "error");
        } elseif (move_uploaded_file($_FILES["upload"]["tmp_name"], $targetFile)) {
            $this->flash->setData("Uploaded image " . basename($_FILES["upload"]["name"]) . " successfully!", "success");
            $this->model->insertUploadedImage($this->user->id, "/uploads/" . basename($_FILES["upload"]["name"]), $check[0], $check[1]);
        } else {
            $this->flash->setData("There was an issue uploading your image.", "error");
        }
        header("Location: /wiki/upload");
    }
    public function downloadGet(string $getSlug): void {
        if (!$this->user) {
            $this->flash->setData("Please login to view this page.", "error");
            header("Location: /wiki");
            exit();
        }

        $article = $this->model->selectBySlug($getSlug);

        if ($article) {
            $content = $article["body"];

            if ($content[-1] !== "\n") {
                $content .= "\n";
            }
            header("Content-Description: File Transfer");
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=" . $article["slug"] . ".textile");
            header("Content-Length: " . strlen($content));
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Expires: 0");
            header("pragma: public");
            echo $content;
        }
    }
}
