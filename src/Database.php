<?php
namespace ReiaDev;

class Database {
    private \PDO $pdo;

    public function __construct(array $config) {
        $dsn = "pgsql:host=" . $config["db_host"] . ";dbname=" . $config["db_name"];

        try {
            $this->pdo = new \PDO($dsn, $config["db_user"], $config["db_pass"], $config["db_options"]);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int) $e->getCode());
        }
    }
    public function getPDO(): \PDO {
        return $this->pdo;
    }
}
