<?php
namespace ReiaDev;

class Database {
    private \PDO $pdo;

    public function __construct(string $dsn, string $user, string $pass, array $options) {
        try {
            $this->pdo = new \PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int) $e->getCode());
        }
    }
    public function getPDO(): \PDO {
        return $this->pdo;
    }
}
