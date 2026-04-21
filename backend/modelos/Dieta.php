<?php
namespace Modelos;

use Config\Database;

class Dieta {
    private $db;
    private $table = 'dietas';

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function obtenerTodos() {
        $query = "SELECT d.*, p.nombres, p.apellidos 
                  FROM {$this->table} d
                  JOIN pacientes p ON d.paciente_id = p.id
                  ORDER BY d.fecha DESC";
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
        $query = "INSERT INTO {$this->table} (paciente_id, descripcion, tipo_comida, fecha)
                  VALUES (:paciente_id, :descripcion, :tipo_comida, :fecha)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':paciente_id', $datos['paciente_id']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':tipo_comida', $datos['tipo_comida']);
        $stmt->bindParam(':fecha', $datos['fecha']);
        return $stmt->execute();
    }

    public function actualizar($id, $datos) {
        $query = "UPDATE {$this->table} SET 
                  paciente_id = :paciente_id,
                  descripcion = :descripcion,
                  tipo_comida = :tipo_comida,
                  fecha = :fecha
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':paciente_id', $datos['paciente_id']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':tipo_comida', $datos['tipo_comida']);
        $stmt->bindParam(':fecha', $datos['fecha']);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}