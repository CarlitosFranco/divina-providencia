<?php
namespace Modelos;

use Config\Database;

class Archivo {
    private $db;
    private $table = 'paciente_archivos';

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function obtenerPorPaciente($pacienteId) {
        $query = "SELECT * FROM {$this->table} WHERE paciente_id = :paciente_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':paciente_id', $pacienteId);
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
                  (paciente_id, nombre_original, nombre_servidor, ruta, tipo, tamaño)
                  VALUES (:paciente_id, :nombre_original, :nombre_servidor, :ruta, :tipo, :tamaño)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':paciente_id', $datos['paciente_id']);
        $stmt->bindParam(':nombre_original', $datos['nombre_original']);
        $stmt->bindParam(':nombre_servidor', $datos['nombre_servidor']);
        $stmt->bindParam(':ruta', $datos['ruta']);
        $stmt->bindParam(':tipo', $datos['tipo']);
        $stmt->bindParam(':tamaño', $datos['tamaño']);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}