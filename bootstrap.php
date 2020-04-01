<?php
if (!file_exists("articles.json")) {
    $fp = fopen("articles.json", "w");
    fwrite($fp, "[]\n");
    fclose($fp);
}
function set_flash(string $message, string $type): void {
    $_SESSION["flash"] = ["message" => $message, "type" => $type];
}
function get_flash(): ?array {
    return $_SESSION["flash"] ?? null;
}
function destroy_flash(): void {
    unset($_SESSION["flash"]);
}
function set_form_input(array $data): void {
    $_SESSION["form-input"] = $data;
}
function get_form_input(): ?array {
    return $_SESSION["form-input"] ?? null;
}
function destroy_form_input(): void {
    unset($_SESSION["form-input"]);
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
function to_slug(string $str): string {
    $str = strtolower($str);
    $str = trim($str);
    $str = preg_replace("/[^a-z0-9 -]/", "", $str);
    $str = preg_replace("/\s+/", " ", $str);
    $str = str_replace(" ", "-", $str);

    return $str;
}
$db_host = $config["database"]["db_host"];
$db_name = $config["database"]["db_name"];
$db_user = $config["database"]["db_user"];
$db_pass = $config["database"]["db_pass"];
$dsn = "pgsql:host=" . $db_host . ";dbname=" . $db_name;

$db_options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];
