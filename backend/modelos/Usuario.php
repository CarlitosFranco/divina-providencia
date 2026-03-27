<?php
namespace Modelos;

use Config\Database;

class Usuario {
    private $db;
    private $table = 'usuarios';

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function obtenerPorEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}