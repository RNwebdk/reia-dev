<?php
if (!file_exists("articles.json")) {
    $fp = fopen("articles.json", "w");
    fwrite($fp, "[]\n");
    fclose($fp);
}
function getDatabaseConfig(): array {
    if (file_exists(__DIR__ . "/../.env")) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
        $dotenv->load();

        $config["database"] = [
            "db_host" => getenv("DB_HOST"),
            "db_name" => getenv("DB_NAME"),
            "db_user" => getenv("DB_USER"),
            "db_pass" => getenv("DB_PASS")
        ];
    } elseif (getenv("APP_ENV") === "production") {
        $config["database"] = [
            "db_host" => getenv("DB_HOST"),
            "db_name" => getenv("DB_NAME"),
            "db_user" => getenv("DB_USER"),
            "db_pass" => getenv("DB_PASS")
        ];
    }
    $config["database"]["db_options"] = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false
    ];
    return $config["database"];
}
function create_csrf_token(): void {
    $_SESSION["csrf-token"] = bin2hex(random_bytes(32));
}
function get_csrf_token(): ?string {
    return $_SESSION["csrf-token"] ?? null;
}
function destroy_csrf_token(): void {
    unset($_SESSION["csrf-token"]);
}
function setFormInput(array $data): void {
    $_SESSION["form-input"] = $data;
}
function getFormInput(): ?array {
    return $_SESSION["form-input"] ?? null;
}
function destroyFormInput(): void {
    unset($_SESSION["form-input"]);
}