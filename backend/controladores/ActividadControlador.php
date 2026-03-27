<?php
namespace Controladores;

use Modelos\Actividad;
use Modelos\Personal;
use Modelos\Paciente;

class ActividadControlador {
    private $actividadModel;
    private $personalModel;
    private $pacienteModel;

    public function __construct() {
        $this->actividadModel = new Actividad();
        $this->personalModel = new Personal();
        $this->pacienteModel = new Paciente();
    }

    public function listar() {
        $actividades = $this->actividadModel->obtenerTodos();
        echo json_encode($actividades);
    }

    public function mostrar($id) {
        $actividad = $this->actividadModel->obtenerPorId($id);
        if ($actividad) {
            echo json_encode($actividad);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Actividad no encontrada']);
        }
    }

    public function crear() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['personal_id']) || empty($data['paciente_id']) || empty($data['fecha']) || empty($data['descripcion'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Personal, paciente, fecha y descripción son obligatorios']);
            return;
        }
        
        // Verificar que personal y paciente existen
        $personal = $this->personalModel->obtenerPorId($data['personal_id']);
        $paciente = $this->pacienteModel->obtenerPorId($data['paciente_id']);
        
        if (!$personal) {
            http_response_code(404);
            echo json_encode(['error' => 'El personal no existe']);
            return;
        }
        if (!$paciente) {
            http_response_code(404);
            echo json_encode(['error' => 'El paciente no existe']);
            return;
        }
        
        $id = $this->actividadModel->crear($data);
        if ($id) {
            http_response_code(201);
            echo json_encode(['message' => 'Actividad creada', 'id' => $id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear actividad']);
        }
    }

    public function actualizar($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!$this->actividadModel->obtenerPorId($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Actividad no encontrada']);
            return;
        }
        
        if ($this->actividadModel->actualizar($id, $data)) {
            echo json_encode(['message' => 'Actividad actualizada']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar actividad']);
        }
    }

    public function eliminar($id) {
        if (!$this->actividadModel->obtenerPorId($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Actividad no encontrada']);
            return;
        }
        
        if ($this->actividadModel->eliminar($id)) {
            echo json_encode(['message' => 'Actividad eliminada']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar actividad']);
        }
    }
}