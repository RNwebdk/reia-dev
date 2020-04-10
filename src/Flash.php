<?php
namespace ReiaDev;

class Flash {
    public string $message;
    public string $type;

    public function setData(string $message, string $type): void {
        $this->message = $message;
        $this->type = $type;
        $this->setSession();
    }
    public function setSession(): void {
        $_SESSION["flash"] = ["message" => $this->message, "type" => $this->type];
    }
    public function getSession(): ?array {
        $flash = $_SESSION["flash"] ?? null;
        $this->unsetSession();
        return $flash;
    }
    public function unsetSession(): void {
        unset($_SESSION["flash"]);
    }
}
