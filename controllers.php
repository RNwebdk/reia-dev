<?php
class IndexController {
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
class RegisterController {
    private $model;
    private $view;

    public function __construct($db, $view) {
        $this->model = new RegisterModel($db);
        $this->view = $view;
    }
    public function indexGet() {
        $flash = get_flash();
        $csrfToken = get_csrf_token();
        destroy_flash();
        $userId = $_SESSION["user-id"] ?? null;

        if ($userId && $_SESSION["is-authenticated"]) {
            set_flash("You're already logged in.", "warning");
            header("Location: /profile");
            exit();
        }
        echo $this->view->render("register.twig", ["title" => "Register", "flash" => $flash, "csrf_token" => $csrfToken]);
    }
    public function indexPost() {
        $csrfToken = $_POST["csrf-token"];
        $username = $_POST["username"];
        $password = $_POST["password"];
        $email = $_POST["email"];
        $error = "";

        if (!hash_equals($csrfToken, get_csrf_token())) {
            $error .= "Possible CSRF attack detected. Please contact the server administrator.<br>";
        }
        if (empty($username)) {
            $error .= "Please enter a username.<br>";
        } elseif (strlen($username) < 2 || strlen($username) > 24) {
            $error .= "Username needs to be between 2 and 24 characters long.<br>";
        }
        if (empty($password)) {
            $error .= "Please enter a password.<br>";
        } elseif (strlen($password) < 8) {
            $error .= "Please enter a password of at least 8 or more characters.<br>";
        }
        if (empty($email)) {
            $error .= "Please enter an e-mail address.<br>";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error .= "Please enter a valid e-mail address.<br>";
        }
        $user = $this->model->selectByUsernameOrEmail($username, $email);

        if ($user) {
            $error .= "A user already exists with the supplied username or e-mail address.<br>";
        }
        if (!empty($error)) {
            set_flash($error, "error");
            set_form_input(["username" => $username, "email" => $email]);
            header("Location: /register");
            exit();
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $this->model->insert($username, $passwordHash, $email, 0);
            set_flash("User registered successfully.", "success");
            header("Location: /login");
            exit();
        }
    }
}
class LoginController {
    private $model;
    private $view;

    public function __construct($db, $view) {
        $this->model = new LoginModel($db);
        $this->view = $view;
    }
    public function indexGet() {
        $flash = get_flash();
        $formInput = get_form_input();
        $csrfToken = get_csrf_token();
        destroy_flash();
        destroy_form_input();
        $userId = $_SESSION["user-id"] ?? null;

        if ($userId && $_SESSION["is-authenticated"]) {
            set_flash("You're already logged in.", "warning");
            header("Location: /profile");
            exit();
        }
        echo $this->view->render("login.twig", ["title" => "Login", "flash" => $flash, "form_input" => $formInput, "csrf_token" => $csrfToken]);
    }
    public function indexPost() {
        $csrfToken = $_POST["csrf-token"];
        $username = $_POST["username"];
        $password = $_POST["password"];
        $error = "";

        if (!hash_equals($csrfToken, get_csrf_token())) {
            $error .= "Possible CSRF attack detected. Please contact the server administrator.<br>";
        }
        if (empty($username)) {
            $error .= "Please enter a username.<br>";
        }
        if (empty($password)) {
            $error .= "Please enter a password.<br>";
        }
        $verify = $this->model->verify($username, $password);

        if (empty($verify)) {
            $error .= "Invalid login credentials, or your account isn't active.<br>";
        }
        if (!empty($error)) {
            set_flash($error, "error");
            set_form_input(["username" => $username]);
            header("Location: /login");
            exit();
        } else {
            $user = $this->model->selectById($verify["id"]);
            $_SESSION["user-id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["is-authenticated"] = true;

            if ($user["role"] === 2) {
                $_SESSION["is-administrator"] = true;
            }
            set_flash("User logged in successfully.", "success");
            header("Location: /profile");
            exit();
        }
    }
}
/**
 * Roles:
 * -1, banned
 * 0, user, unactivated
 * 1, user, activated
 * 2, administrator
 */
class UserController {
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
    public function indexGet($username) {
        $flash = get_flash();
        destroy_flash();
        $userProfile = $this->model->selectByUsername($username);

        if ($userProfile) {
            $title = $userProfile["username"];
        } else {
            $title = "User Not Found";
        }
        echo $this->view->render("user.twig", ["title" => $title, "user_profile" => $userProfile, "user" => $this->user, "flash" => $flash]);
    }
    public function profileGet() {
        $flash = get_flash();
        destroy_flash();
        $userId = $_SESSION["user-id"] ?? null;

        if (!$userId || !$_SESSION["is-authenticated"]) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        echo $this->view->render("profile.twig", ["title" => $user["username"] ?? "Profile", "user" => $this->user, "flash" => $flash]);
    }
    public function adminGet() {
        $flash = get_flash();
        destroy_flash();
        $userId = $_SESSION["user-id"] ?? null;

        if (!$userId || !$_SESSION["is-authenticated"]) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if (!$_SESSION["is-administrator"]) {
            set_flash("You're not authorized to view this page.", "error");
            header("Location: /");
            exit();
        }
        $users = $this->model->selectAll();

        echo $this->view->render("admin.twig", ["title" => "Administrator Panel", "user" => $this->user, "users" => $users, "flash" => $flash]);
    }
    public function activateGet($id) {
        $userId = $_SESSION["user-id"] ?? null;

        if (!$userId || !$_SESSION["is-authenticated"]) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if (!$_SESSION["is-administrator"]) {
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
    public function banGet($id) {
        $userId = $_SESSION["user-id"] ?? null;

        if (!$userId || !$_SESSION["is-authenticated"]) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if (!$_SESSION["is-administrator"]) {
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
    public function promoteGet($id) {
        $userId = $_SESSION["user-id"] ?? null;

        if (!$userId || !$_SESSION["is-authenticated"]) {
            set_flash("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if (!$_SESSION["is-administrator"]) {
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
    public function logoutGet() {
        $flash = get_flash();
        destroy_flash();
        $userId = $_SESSION["user-id"] ?? null;

        if ($userId && $_SESSION["is-authenticated"]) {
            unset($_SESSION["user-id"]);
            unset($_SESSION["username"]);
            unset($_SESSION["is-authenticated"]);
            unset($_SESSION["is-administrator"]);
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
            $this->model->insert($title, $slug, $body, date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), $this->user["username"]);
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
            $this->model->update($title, $body, date("Y-m-d H:i:s"), $this->user["username"], $getSlug);
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
        $targetDir = __DIR__ . "/uploads/";

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777);
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
