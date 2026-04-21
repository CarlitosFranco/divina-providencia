<?php
namespace Modelos;

use Config\Database;

class Asistencia {
    private $db;
    private $table = 'asistencia';

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function obtenerPorFechaYPersonal($fecha, $personalId) {
        $query = "SELECT * FROM {$this->table} WHERE fecha = :fecha AND personal_id = :personal_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':personal_id', $personalId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function registrarEntrada($personalId, $fecha, $hora) {
        $query = "INSERT INTO {$this->table} (personal_id, fecha, hora_entrada) 
                  VALUES (:personal_id, :fecha, :hora_entrada)
                  ON DUPLICATE KEY UPDATE hora_entrada = VALUES(hora_entrada)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':personal_id', $personalId);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':hora_entrada', $hora);
        return $stmt->execute();
    }

    public function registrarSalida($personalId, $fecha, $hora) {
        $asistencia = $this->obtenerPorFechaYPersonal($fecha, $personalId);
        if (!$asistencia || !$asistencia['hora_entrada']) {
            return false;
        }
        $entrada = strtotime($asistencia['hora_entrada']);
        $salida = strtotime($hora);
        $diff = ($salida - $entrada) / 3600;
        $horas = round($diff, 2);
        $query = "UPDATE {$this->table} SET hora_salida = :hora_salida, horas_trabajadas = :horas
                  WHERE personal_id = :personal_id AND fecha = :fecha";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':hora_salida', $hora);
        $stmt->bindParam(':horas', $horas);
        $stmt->bindParam(':personal_id', $personalId);
        $stmt->bindParam(':fecha', $fecha);
        return $stmt->execute();
    }
}