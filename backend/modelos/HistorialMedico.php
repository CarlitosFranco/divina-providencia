<?php
namespace Modelos;

use Config\Database;

class HistorialMedico {
    private $db;
    private $table = 'historial_medico';

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function obtenerPorPaciente($pacienteId) {
        $query = "SELECT * FROM " . $this->table . " WHERE paciente_id = :paciente_id ORDER BY id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':paciente_id', $pacienteId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function crear($datos) {
        $query = "INSERT INTO " . $this->table . " 
            (paciente_id, alergias, enfermedades_cronicas, antecedentes_familiares, cirugias_previas, grupo_sanguineo)
            VALUES (:paciente_id, :alergias, :enfermedades_cronicas, :antecedentes_familiares, :cirugias_previas, :grupo_sanguineo)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':paciente_id', $datos['paciente_id']);
        $stmt->bindParam(':alergias', $datos['alergias']);
        $stmt->bindParam(':enfermedades_cronicas', $datos['enfermedades_cronicas']);
        $stmt->bindParam(':antecedentes_familiares', $datos['antecedentes_familiares']);
        $stmt->bindParam(':cirugias_previas', $datos['cirugias_previas']);
        $stmt->bindParam(':grupo_sanguineo', $datos['grupo_sanguineo']);
        return $stmt->execute();
    }

    public function actualizar($id, $datos) {
        $query = "UPDATE " . $this->table . " SET
            alergias = :alergias,
            enfermedades_cronicas = :enfermedades_cronicas,
            antecedentes_familiares = :antecedentes_familiares,
            cirugias_previas = :cirugias_previas,
            grupo_sanguineo = :grupo_sanguineo
            WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':alergias', $datos['alergias']);
        $stmt->bindParam(':enfermedades_cronicas', $datos['enfermedades_cronicas']);
        $stmt->bindParam(':antecedentes_familiares', $datos['antecedentes_familiares']);
        $stmt->bindParam(':cirugias_previas', $datos['cirugias_previas']);
        $stmt->bindParam(':grupo_sanguineo', $datos['grupo_sanguineo']);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}