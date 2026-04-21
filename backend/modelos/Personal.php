<?php
namespace Modelos;

use Config\Database;

class Personal {
    private $db;
    private $table = 'personal';

    public function __construct() {
        $this->db = (new Database())->getConnection();
        if (!$this->db) throw new \Exception("Error de conexión a BD");
    }

    public function existeEmail($email) {
        $query = "SELECT id FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    public function obtenerTodos() {
        $query = "SELECT * FROM {$this->table}";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function crear($datos) {
        $query = "INSERT INTO {$this->table} 
            (nombres, apellidos, documento_identidad, telefono, email, cargo, especialidad, fecha_contratacion, activo)
            VALUES (:nombres, :apellidos, :documento_identidad, :telefono, :email, :cargo, :especialidad, :fecha_contratacion, :activo)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nombres', $datos['nombres']);
        $stmt->bindParam(':apellidos', $datos['apellidos']);
        $stmt->bindParam(':documento_identidad', $datos['documento_identidad']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':cargo', $datos['cargo']);
        $stmt->bindParam(':especialidad', $datos['especialidad']);
        $stmt->bindParam(':fecha_contratacion', $datos['fecha_contratacion']);
        $stmt->bindParam(':activo', $datos['activo']);
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function actualizar($id, $datos) {
        $query = "UPDATE {$this->table} SET 
            nombres = :nombres,
            apellidos = :apellidos,
            documento_identidad = :documento_identidad,
            telefono = :telefono,
            email = :email,
            cargo = :cargo,
            especialidad = :especialidad,
            fecha_contratacion = :fecha_contratacion,
            activo = :activo
            WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nombres', $datos['nombres']);
        $stmt->bindParam(':apellidos', $datos['apellidos']);
        $stmt->bindParam(':documento_identidad', $datos['documento_identidad']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':cargo', $datos['cargo']);
        $stmt->bindParam(':especialidad', $datos['especialidad']);
        $stmt->bindParam(':fecha_contratacion', $datos['fecha_contratacion']);
        $stmt->bindParam(':activo', $datos['activo']);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function obtenerUsuarioPorPersonalId($personalId) {
        $query = "SELECT * FROM usuarios WHERE personal_id = :personal_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':personal_id', $personalId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function obtenerRolNombre($rolId) {
        $query = "SELECT nombre FROM roles WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $rolId);
        $stmt->execute();
        $rol = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $rol ? $rol['nombre'] : null;
    }
}