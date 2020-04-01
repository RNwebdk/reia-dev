<?php
namespace ReiaDev\Model;

class ForumModel extends Model {
    public function selectAll() {
        $stmt = $this->db->prepare("SELECT id, name, description FROM categories");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function selectById($id) {
        $stmt = $this->db->prepare("SELECT id, name, description FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function selectTopics($categoryId) {
        $stmt = $this->db->prepare("SELECT t.id, t.subject, t.reply_count, t.created_at, t.started_by, t.last_reply, t.category_id, u.id AS user_id, u.username FROM topics t INNER JOIN users u ON t.started_by = u.id WHERE category_id = ? ORDER BY t.id ASC");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }
    public function selectTopicById($id) {
        $stmt = $this->db->prepare("SELECT t.id, t.subject, t.reply_count, t.created_at, t.started_by, t.last_reply, t.category_id, c.name AS category_name FROM topics t INNER JOIN categories c ON t.category_id = c.id WHERE t.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function selectPosts($topicId) {
        $stmt = $this->db->prepare("SELECT p.id, p.content, p.created_at, p.started_by, p.topic_id, u.id AS user_id, u.username, u.role AS user_role FROM posts p INNER JOIN users u ON p.started_by = u.id WHERE topic_id = ? ORDER BY p.id ASC");
        $stmt->execute([$topicId]);
        return $stmt->fetchAll();
    }
    public function insertPost($content, $createdAt, $startedBy, $topicId) {
        $stmt = $this->db->prepare("INSERT INTO posts (content, created_at, started_by, topic_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$content, $createdAt, $startedBy, $topicId]);
    }
    public function insertTopic($subject, $createdAt, $startedBy, $categoryId) {
        $stmt = $this->db->prepare("INSERT INTO topics (subject, reply_count, created_at, started_by, category_id) VALUES (?, ?, ?, ?, ?) RETURNING id");
        $stmt->execute([$subject, 0, $createdAt, $startedBy, $categoryId]);
        return $stmt->fetch();
    }
/*
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
*/
}
