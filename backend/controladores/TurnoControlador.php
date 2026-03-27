<?php
namespace Controladores;

use Modelos\Turno;
use Modelos\Personal;

class TurnoControlador {
    private $turnoModel;
    private $personalModel;

    public function __construct() {
        $this->turnoModel = new Turno();
        $this->personalModel = new Personal();
    }

    public function listar() {
        $turnos = $this->turnoModel->obtenerTodos();
        echo json_encode($turnos);
    }

    public function mostrar($id) {
        $turno = $this->turnoModel->obtenerPorId($id);
        if ($turno) {
            echo json_encode($turno);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Turno no encontrado']);
        }
    }

    public function listarPorPersonal($personalId) {
        $turnos = $this->turnoModel->obtenerPorPersonal($personalId);
        echo json_encode($turnos);
    }

    public function crear() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validaciones
        if (empty($data['personal_id']) || empty($data['fecha']) || empty($data['hora_inicio']) || empty($data['hora_fin']) || empty($data['tipo_turno'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Todos los campos son obligatorios']);
            return;
        }
        
        // Verificar que el personal existe
        $personal = $this->personalModel->obtenerPorId($data['personal_id']);
        if (!$personal) {
            http_response_code(404);
            echo json_encode(['error' => 'El personal no existe']);
            return;
        }
        
        $id = $this->turnoModel->crear($data);
        if ($id) {
            http_response_code(201);
            echo json_encode(['message' => 'Turno creado', 'id' => $id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear turno']);
        }
    }

    public function actualizar($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!$this->turnoModel->obtenerPorId($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Turno no encontrado']);
            return;
        }
        
        if ($this->turnoModel->actualizar($id, $data)) {
            echo json_encode(['message' => 'Turno actualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar turno']);
        }
    }

    public function eliminar($id) {
        if (!$this->turnoModel->obtenerPorId($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Turno no encontrado']);
            return;
        }
        
        if ($this->turnoModel->eliminar($id)) {
            echo json_encode(['message' => 'Turno eliminado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar turno']);
        }
    }
}