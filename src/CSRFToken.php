<?php
namespace ReiaDev;

class CSRFToken {
    public function setSession(): void {
        $_SESSION["csrf-token"] = bin2hex(random_bytes(32));
    }
    public function getSession(): ?string {
        return $_SESSION["csrf-token"];
    }
    public function unsetSession(): void {
        unset($_SESSION["csrf-token"]);
    }
}
