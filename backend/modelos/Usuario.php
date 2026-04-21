<?php
namespace Modelos;

use Config\Database;

class Usuario {
    private $db;
    private $table = 'usuarios';

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function obtenerTodos() {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                  FROM {$this->table} u
                  JOIN roles r ON u.rol_id = r.id
                  ORDER BY u.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                  FROM {$this->table} u
                  JOIN roles r ON u.rol_id = r.id
                  WHERE u.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function obtenerPorEmail($email) {
        // Incluimos personal_id en la selección
        $query = "SELECT id, nombre, email, password, rol_id, personal_id, activo 
                  FROM {$this->table} 
                  WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function crear($datos) {
        // Agregamos personal_id a la inserción
        $query = "INSERT INTO {$this->table} 
                  (nombre, email, password, rol_id, personal_id, activo)
                  VALUES (:nombre, :email, :password, :rol_id, :personal_id, :activo)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':password', $datos['password']);
        $stmt->bindParam(':rol_id', $datos['rol_id']);
        $stmt->bindParam(':personal_id', $datos['personal_id']);
        $stmt->bindParam(':activo', $datos['activo']);
        return $stmt->execute();
    }

    public function actualizar($id, $datos) {
        // Puedes incluir personal_id si quieres actualizarlo (opcional)
        $query = "UPDATE {$this->table} SET
                  nombre = :nombre,
                  email = :email,
                  rol_id = :rol_id,
                  personal_id = :personal_id,
                  activo = :activo
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':rol_id', $datos['rol_id']);
        $stmt->bindParam(':personal_id', $datos['personal_id']);
        $stmt->bindParam(':activo', $datos['activo']);
        return $stmt->execute();
    }

    public function actualizarPassword($id, $password) {
        $query = "UPDATE {$this->table} SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password', $password);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}