<?php
namespace ReiaDev;

class CSRFToken {
    public function setSession(): void {
        $_SESSION["csrf-token"] = bin2hex(random_bytes(32));
    }
    public function getSession(): ?string {
        $csrfToken = $_SESSION["csrf-token"] ?? null;
        return $csrfToken;
    }
    public function unsetSession(): void {
        unset($_SESSION["csrf-token"]);
    }
    public function verify($formToken): bool {
        return hash_equals($formToken, $this->getSession());
    }
}
