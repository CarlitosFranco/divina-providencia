<?php
namespace Controladores;

use Modelos\Cita;
use Modelos\Paciente;
use Modelos\Personal;

class CitaControlador {
    private $citaModel;
    private $pacienteModel;
    private $personalModel;

    public function __construct() {
        $this->citaModel = new Cita();
        $this->pacienteModel = new Paciente();
        $this->personalModel = new Personal();
    }

    public function listar() {
        $citas = $this->citaModel->obtenerTodos();
        echo json_encode($citas);
    }

    public function mostrar($id) {
        $cita = $this->citaModel->obtenerPorId($id);
        if ($cita) {
            echo json_encode($cita);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Cita no encontrada']);
        }
    }

    public function crear() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validaciones básicas
        if (empty($data['paciente_id']) || empty($data['personal_id']) || empty($data['fecha']) || empty($data['hora'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Paciente, personal, fecha y hora son obligatorios']);
            return;
        }
        
        // Verificar existencia de paciente y personal
        $paciente = $this->pacienteModel->obtenerPorId($data['paciente_id']);
        $personal = $this->personalModel->obtenerPorId($data['personal_id']);
        
        if (!$paciente) {
            http_response_code(404);
            echo json_encode(['error' => 'El paciente no existe']);
            return;
        }
        if (!$personal) {
            http_response_code(404);
            echo json_encode(['error' => 'El personal no existe']);
            return;
        }
        
        // Asignar estado por defecto
        if (empty($data['estado'])) {
            $data['estado'] = 'Programada';
        }
        
        $id = $this->citaModel->crear($data);
        if ($id) {
            http_response_code(201);
            echo json_encode(['message' => 'Cita creada', 'id' => $id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear cita']);
        }
    }

    public function actualizar($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!$this->citaModel->obtenerPorId($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Cita no encontrada']);
            return;
        }
        
        if ($this->citaModel->actualizar($id, $data)) {
            echo json_encode(['message' => 'Cita actualizada']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar cita']);
        }
    }

    public function eliminar($id) {
        if (!$this->citaModel->obtenerPorId($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Cita no encontrada']);
            return;
        }
        
        if ($this->citaModel->eliminar($id)) {
            echo json_encode(['message' => 'Cita eliminada']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar cita']);
        }
    }
}