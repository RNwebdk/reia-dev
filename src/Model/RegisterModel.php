<?php
namespace ReiaDev\Model;

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
