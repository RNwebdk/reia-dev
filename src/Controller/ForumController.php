<?php
namespace ReiaDev\Controller;

use ReiaDev\Model\ForumModel;

class ForumController {
    private $userModel;
    private $model;
    private $view;
    private $user;

    public function __construct($db, $view, $userModel) {
        $this->userModel = $userModel;
        $this->model = new ForumModel($db);
        $this->view = $view;

        if (!empty($_SESSION["user-id"]) || !empty($_SESSION["is-authenticated"])) {
            $this->user = $userModel->selectById($_SESSION["user-id"]);
        }
    }
    public function indexGet() {
        $flash = get_flash();
        destroy_flash();
        $categories = $this->model->selectCategories();

        echo $this->view->render("forum.twig", ["title" => "Forums", "categories" => $categories, "user" => $this->user, "flash" => $flash]);
    }
    public function categoryGet($categoryId) {
        $flash = get_flash();
        destroy_flash();
        $category = $this->model->selectCategoryById($categoryId);

        if ($category) {
            $title = "Forums - " . $category["name"];
        } else {
            set_flash("No category found.", "warning");
            header("Location: /forum");
            exit();
        }
        $topics = $this->model->selectTopics($categoryId);
        echo $this->view->render("forum.category.twig", ["title" => $title, "category" => $category, "topics" => $topics, "user" => $this->user, "flash" => $flash]);
    }
    public function topicGet($topicId) {
        $flash = get_flash();
        $formInput = get_form_input();
        $csrfToken = get_csrf_token();
        destroy_flash();
        destroy_form_input();
        $topic = $this->model->selectTopicById($topicId);

        if ($topic) {
            $title = "Forums - " . $topic["subject"];
        } else {
            set_flash("No topic found.", "warning");
            header("Location: /forum");
            exit();
        }
        $posts = $this->model->selectPosts($topicId);

        echo $this->view->render("forum.topic.twig", ["title" => $title, "topic" => $topic, "posts" => $posts, "user" => $this->user, "flash" => $flash, "form_input" => $formInput, "csrf_token" => $csrfToken]);
    }
    public function topicPost($topicId) {
        $csrfToken = $_POST["csrf-token"];
        $content = $_POST["content"];
        $error = "";

        if (!hash_equals($csrfToken, get_csrf_token())) {
            $error .= "Possible CSRF attack detected. Please contact the server administrator.<br>";
        }
        if (empty($content)) {
            $error .= "Please enter content to post.<br>";
        } elseif (strlen($content) > 4096) {
            $error .= "Content cannot be longer than 4096 characters.";
        }
        if (!empty($error)) {
            set_flash($error, "error");
            set_form_input(["content" => $content]);
            header("Location: /forum/topic/" . $topicId);
            exit();
        } else {
            $topic = $this->model->selectTopicById($topicId);
            $this->model->insertPost($content, date("Y-m-d H:i:s"), $this->user["id"], $topicId);
            $this->model->updateTopic($this->user["id"], date("Y-m-d H:i:s"), $topicId);
            $this->model->updateLatestTopic($topic["category_id"], $topicId);
            set_flash("Post created successfully!", "success");
            header("Location: /forum/topic/" . $topicId);
            exit();
        }
    }
    public function createGet($categoryId) {
        $flash = get_flash();
        $formInput = get_form_input();
        $csrfToken = get_csrf_token();
        destroy_flash();
        destroy_form_input();
        $userId = $_SESSION["user-id"] ?? null;

        if (!$userId || !$_SESSION["is-authenticated"]) {
            set_flash("Please login to view this page.", "error");
            header("Location: /forum/" . $categoryId);
            exit();
        }
        echo $this->view->render("forum.create.twig", ["title" => "Create Topic", "category_id" => $categoryId, "user" => $this->user, "flash" => $flash, "form_input" => $formInput, "csrf_token" => $csrfToken]);
    }
    public function createPost($categoryId) {
        $csrfToken = $_POST["csrf-token"];
        $subject = $_POST["subject"];
        $content = $_POST["content"];
        $error = "";

        if (!hash_equals($csrfToken, get_csrf_token())) {
            $error .= "Possible CSRF attack detected. Please contact the server administrator.<br>";
        }
        if (empty($subject)) {
            $error .= "Please enter a subject.<br>";
        } elseif (strlen($subject) < 8 || strlen($subject) > 64) {
            $error .= "Subject needs to be between 8 and 64 characters long.<br>";
        }
        if (empty($content)) {
            $error .= "Please enter content to post.<br>";
        }
        if (!empty($error)) {
            set_flash($error, "error");
            set_form_input(["subject" => $subject, "content" => $content]);
            header("Location: /forum/create/" . $categoryId);
            exit();
        } else {
            $topic = $this->model->insertTopic($subject, date("Y-m-d H:i:s"), $this->user["id"], $categoryId);

            if (!empty($topic)) {
                $this->model->insertPost($content, date("Y-m-d H:i:s"), $this->user["id"], $topic["id"]);
                $this->model->updateLatestTopic($topic["category_id"], $topic["id"]);
                set_flash("Topic created successfully!", "success");
                header("Location: /forum/topic/" . $topic["id"]);
                exit();
            } else {
                set_flash("Something went wrong creating your topic.", "error");
                set_form_input(["subject" => $subject, "content" => $content]);
                header("Location: /forum/create/" . $categoryId);
                exit();
            }
        }
    }
}
