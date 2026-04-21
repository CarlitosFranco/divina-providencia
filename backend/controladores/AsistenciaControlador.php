<?php
namespace Controladores;

use Modelos\Asistencia;

class AsistenciaControlador {
    private $asistenciaModel;

    public function __construct() {
        $this->asistenciaModel = new Asistencia();
    }

    private function obtenerPersonalId() {
        global $usuario; // viene del middleware
        return $usuario['personal_id'] ?? null;
    }

    public function registrarEntrada() {
        $data = json_decode(file_get_contents("php://input"), true);
        $personalId = $data['personal_id'] ?? $this->obtenerPersonalId();
        $fecha = $data['fecha'] ?? date('Y-m-d');
        if (!$personalId) {
            http_response_code(400);
            echo json_encode(['error' => 'personal_id requerido']);
            return;
        }
        $hora = date('H:i:s');
        $result = $this->asistenciaModel->registrarEntrada($personalId, $fecha, $hora);
        if ($result) {
            echo json_encode(['message' => 'Entrada registrada', 'hora' => $hora]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al registrar entrada']);
        }
    }

    public function registrarSalida() {
        $data = json_decode(file_get_contents("php://input"), true);
        $personalId = $data['personal_id'] ?? $this->obtenerPersonalId();
        $fecha = $data['fecha'] ?? date('Y-m-d');
        if (!$personalId) {
            http_response_code(400);
            echo json_encode(['error' => 'personal_id requerido']);
            return;
        }
        $hora = date('H:i:s');
        $result = $this->asistenciaModel->registrarSalida($personalId, $fecha, $hora);
        if ($result) {
            echo json_encode(['message' => 'Salida registrada', 'hora' => $hora]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al registrar salida (primero registre entrada)']);
        }
    }

    public function obtenerPorFecha() {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $personalId = $_GET['personal_id'] ?? $this->obtenerPersonalId();
        if (!$personalId) {
            http_response_code(400);
            echo json_encode(['error' => 'personal_id requerido']);
            return;
        }
        $asistencia = $this->asistenciaModel->obtenerPorFechaYPersonal($fecha, $personalId);
        echo json_encode($asistencia ?: null);
    }

    public function listar() {
    // Solo administradores pueden ver todas las asistencias (opcional)
        global $usuario;
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        $personalId = $_GET['personal_id'] ?? null;

        $query = "SELECT a.*, p.nombres, p.apellidos 
                  FROM asistencia a
                  JOIN personal p ON a.personal_id = p.id
                  WHERE a.fecha BETWEEN :fecha_inicio AND :fecha_fin";
        if ($personalId) {
            $query .= " AND a.personal_id = :personal_id";
        }
        $query .= " ORDER BY a.fecha DESC, a.id DESC";

        $db = (new \Config\Database())->getConnection();
        $stmt = $db->prepare($query);
        $stmt->bindParam(':fecha_inicio', $fechaInicio);
        $stmt->bindParam(':fecha_fin', $fechaFin);
        if ($personalId) {
            $stmt->bindParam(':personal_id', $personalId);
        }
        $stmt->execute();
        $asistencias = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode($asistencias);
    }
}