<?php
namespace ReiaDev\Model;

class UserModel extends Model {
    public function selectById($id) {
        $stmt = $this->db->prepare("SELECT id, username, email, avatar, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function selectByUsername($username) {
        $stmt = $this->db->prepare("SELECT id, username, email, avatar, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    public function selectAll() {
        $stmt = $this->db->prepare("SELECT id, username, email, avatar, role FROM users ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function updateRole($role, $id) {
        $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $id]);
    }
    public function updateAvatar($url, $id) {
        $stmt = $this->db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$url, $id]);
    }
}
