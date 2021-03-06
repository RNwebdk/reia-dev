<?php
namespace ReiaDev\Model;

class WikiModel extends Model {
    public function selectAll() {
        $stmt = $this->db->prepare("SELECT id, title, slug, body, created_at, last_modified, modified_by FROM articles ORDER BY title ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function selectBySlug($slug) {
        $stmt = $this->db->prepare("SELECT a.id, a.title, a.slug, a.body, a.created_at, a.last_modified, a.modified_by, u.id as user_id, u.username FROM articles a INNER JOIN users u ON a.modified_by = u.id WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    public function insert($title, $slug, $body, $createdAt, $lastModified, $modifiedBy) {
        $stmt = $this->db->prepare("INSERT INTO articles (title, slug, body, created_at, last_modified, modified_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $body, $createdAt, $lastModified, $modifiedBy]);
    }
    public function update($title, $body, $lastModified, $modifiedBy, $slug) {
        $stmt = $this->db->prepare("UPDATE articles SET title = ?, body = ?, last_modified = ?, modified_by = ? WHERE slug = ?");
        $stmt->execute([$title, $body, $lastModified, $modifiedBy, $slug]);
    }
    public function search($term) {
        $stmt = $this->db->prepare("SELECT a.title, a.slug, a.last_modified, a.modified_by, u.id as user_id, u.username FROM articles a INNER JOIN users u ON a.modified_by = u.id WHERE title ILIKE ? OR body ILIKE ?");
        $stmt->bindValue(1, "%" . $term . "%", \PDO::PARAM_STR);
        $stmt->bindValue(2, "%" . $term . "%", \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function selectSlugs(): array {
        $stmt = $this->db->prepare("SELECT slug FROM articles");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function selectUploadedImages(): array {
        $stmt = $this->db->prepare("SELECT ui.id, ui.created_by, ui.url, ui.width, ui.height, u.id as user_id, u.username FROM uploaded_images ui INNER JOIN users u ON ui.created_by = u.id");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function insertUploadedImage($createdBy, $url, $width, $height): void {
        $stmt = $this->db->prepare("INSERT INTO uploaded_images (created_by, url, width, height) VALUES (?, ?, ?, ?)");
        $stmt->execute([$createdBy, $url, $width, $height]);
    }
}
