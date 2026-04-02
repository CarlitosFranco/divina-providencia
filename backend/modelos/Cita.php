<?php
namespace Modelos;

use Config\Database;

class Cita {
    private $db;
    private $table = 'citas';

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function obtenerTodos() {
        $query = "SELECT c.*, 
                         p.nombres as paciente_nombres, p.apellidos as paciente_apellidos,
                         pe.nombres as personal_nombres, pe.apellidos as personal_apellidos
                  FROM " . $this->table . " c
                  JOIN pacientes p ON c.paciente_id = p.id
                  JOIN personal pe ON c.personal_id = pe.id
                  ORDER BY c.fecha DESC, c.hora ASC";
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
            (paciente_id, personal_id, fecha, hora, motivo, estado, observaciones)
            VALUES (:paciente_id, :personal_id, :fecha, :hora, :motivo, :estado, :observaciones)";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':paciente_id', $datos['paciente_id']);
        $stmt->bindParam(':personal_id', $datos['personal_id']);
        $stmt->bindParam(':fecha', $datos['fecha']);
        $stmt->bindParam(':hora', $datos['hora']);
        $stmt->bindParam(':motivo', $datos['motivo']);
        $stmt->bindParam(':estado', $datos['estado']);
        $stmt->bindParam(':observaciones', $datos['observaciones']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function actualizar($id, $datos) {
        $query = "UPDATE " . $this->table . " SET 
            paciente_id = :paciente_id,
            personal_id = :personal_id,
            fecha = :fecha,
            hora = :hora,
            motivo = :motivo,
            estado = :estado,
            observaciones = :observaciones
            WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':paciente_id', $datos['paciente_id']);
        $stmt->bindParam(':personal_id', $datos['personal_id']);
        $stmt->bindParam(':fecha', $datos['fecha']);
        $stmt->bindParam(':hora', $datos['hora']);
        $stmt->bindParam(':motivo', $datos['motivo']);
        $stmt->bindParam(':estado', $datos['estado']);
        $stmt->bindParam(':observaciones', $datos['observaciones']);
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