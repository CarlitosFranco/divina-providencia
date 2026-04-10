<?php
namespace Modelos;

use Config\Database;

class Rol {
    private $db;
    private $table = 'roles';

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function obtenerTodos() {
        $query = "SELECT * FROM {$this->table} ORDER BY id";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}