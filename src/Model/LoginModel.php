<?php
namespace ReiaDev\Model;

class LoginModel extends Model {
    public function verify($username, $password) {
        $stmt = $this->db->prepare("SELECT id, username, password, role FROM users WHERE username ILIKE ?");
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
