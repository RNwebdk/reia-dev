<?php
namespace ReiaDev\Controller;

use ReiaDev\Model\ForumModel;
use ReiaDev\Flash;
use ReiaDev\User;

class ForumController {
    private $model;
    private \Twig\Environment $twig;
    private Flash $flash;
    private ?User $user;

    public function __construct($model, $twig, $flash, $userModel) {
        $this->model = $model;
        $this->twig = $twig;
        $this->flash = $flash;

        if (!empty($_SESSION["user-id"])) {
            $u = $userModel->selectById($_SESSION["user-id"]);
            $this->user = new User($u["id"], $u["username"], $u["email"], $u["avatar"], $u["role"]);
        } else {
            $this->user = null;
        }
    }
    protected function render(string $view, array $data): void {
        $data["flash"] = $this->flash->getSession();
        $data["user"] = $this->user;

        echo $this->twig->render($view, $data);
    }
    public function indexGet(): void {
        $categories = $this->model->selectCategories();
        $this->render("forum.twig", ["title" => "Forums", "categories" => $categories]);
    }
    public function categoryGet(int $categoryId): void {
        $category = $this->model->selectCategoryById($categoryId);

        if ($category) {
            $title = "Forums - " . $category["name"];
        } else {
            $this->flash->setData("No category found.", "warning");
            header("Location: /forum");
            exit();
        }
        $topics = $this->model->selectTopics($categoryId);
        $this->render("forum.category.twig", ["title" => $title, "category" => $category, "topics" => $topics]);
    }
    public function topicGet(int $topicId): void {
        $formInput = get_form_input();
        $csrfToken = get_csrf_token();
        destroy_form_input();
        $topic = $this->model->selectTopicById($topicId);

        if ($topic) {
            $title = "Forums - " . $topic["subject"];
        } else {
            $this->flash->setData("No topic found.", "warning");
            header("Location: /forum");
            exit();
        }
        $posts = $this->model->selectPosts($topicId);
        $parser = new \Netcarver\Textile\Parser();

        foreach ($posts as &$post) {
            $post["content"] = $parser->setDocumentType("html5")->parse($post["content"]);
        }
        $this->render("forum.topic.twig", ["title" => $title, "topic" => $topic, "posts" => $posts, "form_input" => $formInput, "csrf_token" => $csrfToken]);
    }
    public function topicPost(int $topicId): void {
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
            $this->flash->setData($error, "error");
            set_form_input(["content" => $content]);
            header("Location: /forum/topic/" . $topicId);
        } else {
            $topic = $this->model->selectTopicById($topicId);

            if ($topic["is_locked"]) {
                $this->flash->setData("This topic is locked. No replies can be made.", "error");
                header("Location: /forum/topic" . $topicId);
                exit();
            }
            $post = $this->model->insertPost($content, date("Y-m-d H:i:s"), $this->user->id, $topicId);
            $this->model->updateTopic($this->user->id, date("Y-m-d H:i:s"), $topicId);
            $this->model->updateLatestTopic($topic["category_id"], $topicId);
            $this->flash->setData("Post created successfully!", "success");
            header("Location: /forum/topic/" . $topicId . "#post" . $post["id"]);
        }
    }
    public function createGet(int $categoryId): void {
        $formInput = get_form_input();
        $csrfToken = get_csrf_token();
        destroy_form_input();

        if (!$this->user) {
            $this->flash->setData("Please login to view this page.", "error");
            header("Location: /forum/" . $categoryId);
            exit();
        }
        $this->render("forum.create.twig", ["title" => "Create Topic", "category_id" => $categoryId, "form_input" => $formInput, "csrf_token" => $csrfToken]);
    }
    public function createPost(int $categoryId): void {
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
        } elseif (strlen($content) > 4096) {
            $error .= "Content cannot be longer than 4096 characters.";
        }
        if (!empty($error)) {
            $this->flash->setData($error, "error");
            set_form_input(["subject" => $subject, "content" => $content]);
            header("Location: /forum/create/" . $categoryId);
        } else {
            $topic = $this->model->insertTopic($subject, date("Y-m-d H:i:s"), $this->user->id, $categoryId);

            if (!empty($topic)) {
                $this->model->insertPost($content, date("Y-m-d H:i:s"), $this->user->id, $topic["id"]);
                $this->model->updateLatestTopic($topic["category_id"], $topic["id"]);
                $this->flash->setData("Topic created successfully!", "success");
                header("Location: /forum/topic/" . $topic["id"]);
            } else {
                $this->flash->setData("Something went wrong creating your topic.", "error");
                set_form_input(["subject" => $subject, "content" => $content]);
                header("Location: /forum/create/" . $categoryId);
            }
        }
    }
    public function updateGet(int $postId): void {
        $formInput = get_form_input();
        $csrfToken = get_csrf_token();
        destroy_form_input();

        if (!$this->user) {
            $this->flash->setData("Please login to view this page.", "error");
            header("Location: /forum");
            exit();
        }
        $post = $this->model->selectPostById($postId);

        if ($post) {
            if ($post["started_by"] !== $this->user->id) {
                $this->flash->setData("You don't have permission to edit this post.", "error");
                header("Location: /forum/topic/" . $post["topic_id"]);
                exit();
            }
        } else {
            $this->flash->setData("Cannot locate post.", "error");
            header("Location: /forum");
            exit();
        }
        $this->render("forum.update.twig", ["title" => "Update Post", "post" => $post, "form_input" => $formInput, "csrf_token" => $csrfToken]);
    }
    public function updatePost(int $postId): void {
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
            $this->flash->setData($error, "error");
            set_form_input(["content" => $content]);
            header("Location: /forum/update/" . $postId);
        } else {
            $post = $this->model->updatePost($content, $postId, );
            $this->flash->setData("Post updated successfully!", "success");
            header("Location: /forum/topic/" . $post["topic_id"]);
        }
    }
    public function adminAction(string $action, int $id, int $status): void {
        if (!$this->user) {
            $this->flash->setData("Please login to view this page.", "error");
            header("Location: /login");
            exit();
        }
        if ($this->user->role < 2) {
            $this->flash->setData("You're not authorized to do that.", "error");
            header("Location: /forum/topic/" . $id);
            exit();
        }
        if ($action === "lock") {
            $this->model->updateTopicLocked($status, $id);
            $this->flash->setData(($status ? "Locked" : "Unlocked") . " topic.", "success");
        } elseif ($action === "sticky") {
            $this->model->updateTopicStickied($status, $id);
            $this->flash->setData(($status ? "Stuck" : "Unstuck") . " topic.", "success");
        } else {
            $this->flash->setData("Unknown administrative action.", "error");
        }
        header("Location: /forum/topic/" . $id);
    }
}
