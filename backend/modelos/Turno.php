<?php
namespace Modelos;

use Config\Database;

class Turno {
    private $db;
    private $table = 'turnos_personal';

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function obtenerTodos() {
        // Traer turnos con datos del personal (join)
        $query = "SELECT t.*, p.nombres, p.apellidos 
                  FROM " . $this->table . " t
                  JOIN personal p ON t.personal_id = p.id
                  ORDER BY t.fecha DESC, t.hora_inicio ASC";
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

    public function obtenerPorPersonal($personalId) {
        $query = "SELECT * FROM " . $this->table . " WHERE personal_id = :personal_id ORDER BY fecha DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':personal_id', $personalId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function crear($datos) {
        $query = "INSERT INTO " . $this->table . " 
            (personal_id, fecha, hora_inicio, hora_fin, tipo_turno)
            VALUES (:personal_id, :fecha, :hora_inicio, :hora_fin, :tipo_turno)";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':personal_id', $datos['personal_id']);
        $stmt->bindParam(':fecha', $datos['fecha']);
        $stmt->bindParam(':hora_inicio', $datos['hora_inicio']);
        $stmt->bindParam(':hora_fin', $datos['hora_fin']);
        $stmt->bindParam(':tipo_turno', $datos['tipo_turno']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function actualizar($id, $datos) {
        $query = "UPDATE " . $this->table . " SET 
            personal_id = :personal_id,
            fecha = :fecha,
            hora_inicio = :hora_inicio,
            hora_fin = :hora_fin,
            tipo_turno = :tipo_turno
            WHERE id = :id";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':personal_id', $datos['personal_id']);
        $stmt->bindParam(':fecha', $datos['fecha']);
        $stmt->bindParam(':hora_inicio', $datos['hora_inicio']);
        $stmt->bindParam(':hora_fin', $datos['hora_fin']);
        $stmt->bindParam(':tipo_turno', $datos['tipo_turno']);
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