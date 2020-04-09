<?php
namespace ReiaDev;

class User {
    public int $id;
    public string $username;
    public string $email;
    public ?string $avatar;
    public int $role;

    public function __construct(int $id, string $username, string $email, ?string $avatar, int $role) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->avatar = $avatar;
        $this->role = $role;
    }
}
