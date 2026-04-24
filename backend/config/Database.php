<?php
namespace Config;

class Database {
    private $host = 'sql304.infinityfree.com';
    private $dbname = 'if0_41747233_divina_providencia';
    private $user = 'if0_41747233';
    private $pass = 'DsFUKA57yxfw3P';   // Cámbiala después por seguridad
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