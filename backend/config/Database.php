<?php
namespace Config;

use PDO;
use PDOException;

class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $this->host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $this->dbname = $_ENV['DB_NAME'] ?? 'divina_providencia';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}