<?php
class Model {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }
}
class WikiModel extends Model {
    public function selectAll() {
        $stmt = $this->db->prepare("SELECT id, title, slug, body, created_at, last_modified, modified_by FROM articles ORDER BY title ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function selectBySlug($slug) {
        $stmt = $this->db->prepare("SELECT id, title, slug, body, created_at, last_modified, modified_by FROM articles WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    public function insert($title, $slug, $body, $createdAt, $lastModified, $modifiedBy) {
        $stmt = $this->db->prepare("INSERT INTO articles (title, slug, body, created_at, last_modified, modified_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $body, $createdAt, $lastModified, $modifiedBy]);
        $this->generateJSON();
    }
    public function update($title, $body, $lastModified, $modifiedBy, $slug) {
        $stmt = $this->db->prepare("UPDATE articles SET title = ?, body = ?, last_modified = ?, modified_by = ? WHERE slug = ?");
        $stmt->execute([$title, $body, $lastModified, $modifiedBy, $slug]);
        $this->generateJSON();
    }
    public function search($term) {
        $stmt = $this->db->prepare("SELECT title, slug, last_modified, modified_by FROM articles WHERE title ILIKE ? OR body ILIKE ?");
        $stmt->bindValue(1, "%" . $term . "%", PDO::PARAM_STR);
        $stmt->bindValue(2, "%" . $term . "%", PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function generateJSON() {
        $stmt = $this->db->prepare("SELECT slug FROM articles");
        $stmt->execute();
        $articles = $stmt->fetchAll();

        if (!empty($articles)) {
            $json = [];

            foreach ($articles as $article) {
                $json[] = $article["slug"];
            }
            $fp = fopen("articles.json", "w");
            fwrite($fp, json_encode($json, JSON_PRETTY_PRINT) . "\n");
            fclose($fp);
        }
    }
}
class RegisterModel extends Model {
    public function selectByUsernameOrEmail($username, $email) {
        $stmt = $this->db->prepare("SELECT id, username, password, email FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        return $stmt->fetch();
    }
    public function insert($username, $password, $email, $role) {
        $stmt = $this->db->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $email, $role]);
    }
}
class LoginModel extends Model {
    public function verify($username, $password) {
        $stmt = $this->db->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user["password"]) && $user["role"] > 0) {
                return $user;
            }
            return false;
        }
        return false;
    }
    public function selectById($id) {
        $stmt = $this->db->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
class UserModel extends Model {
    public function selectById($id) {
        $stmt = $this->db->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function selectByUsername($username) {
        $stmt = $this->db->prepare("SELECT id, username, email, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    public function selectAll() {
        $stmt = $this->db->prepare("SELECT id, username, email, role FROM users ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function updateRole($role, $id) {
        $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $id]);
    }
}
