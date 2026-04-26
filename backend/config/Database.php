<?php
namespace Config;

class Database {
    private $host = 'localhost';
    private $dbname = 'divina_providencia';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';

    public function getConnection() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $pdo = new \PDO($dsn, $this->user, $this->pass);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (\PDOException $e) {
            error_log("Error de conexión a BD: " . $e->getMessage());
            return null;
        }
    }
}