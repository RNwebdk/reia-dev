<?php
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
try {
    $db = new PDO($dsn, $db_user, $db_pass, $db_options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int) $e->getCode());
}
