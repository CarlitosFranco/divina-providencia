<?php
namespace Modelos;

use Config\Database;

class Actividad {
    private $db;
    private $table = 'actividades_personal';

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function obtenerTodos() {
        $query = "SELECT a.*, p.nombres as personal_nombres, p.apellidos as personal_apellidos, 
                         pa.nombres as paciente_nombres, pa.apellidos as paciente_apellidos
                  FROM " . $this->table . " a
                  JOIN personal p ON a.personal_id = p.id
                  JOIN pacientes pa ON a.paciente_id = pa.id
                  ORDER BY a.fecha DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function crear($datos) {
        $query = "INSERT INTO " . $this->table . " 
            (personal_id, paciente_id, fecha, descripcion, tipo_actividad)
            VALUES (:personal_id, :paciente_id, :fecha, :descripcion, :tipo_actividad)";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':personal_id', $datos['personal_id']);
        $stmt->bindParam(':paciente_id', $datos['paciente_id']);
        $stmt->bindParam(':fecha', $datos['fecha']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':tipo_actividad', $datos['tipo_actividad']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function actualizar($id, $datos) {
        $query = "UPDATE " . $this->table . " SET 
            personal_id = :personal_id,
            paciente_id = :paciente_id,
            fecha = :fecha,
            descripcion = :descripcion,
            tipo_actividad = :tipo_actividad
            WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':personal_id', $datos['personal_id']);
        $stmt->bindParam(':paciente_id', $datos['paciente_id']);
        $stmt->bindParam(':fecha', $datos['fecha']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':tipo_actividad', $datos['tipo_actividad']);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}