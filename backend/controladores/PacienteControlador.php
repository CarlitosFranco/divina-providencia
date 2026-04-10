<?php
namespace Controladores;

use Modelos\Paciente;
use Modelos\HistorialMedico;

class PacienteControlador {
    private $pacienteModel;

    public function __construct() {
        $this->pacienteModel = new Paciente();
    }

    public function listar() {
        $pacientes = $this->pacienteModel->obtenerTodos();
        echo json_encode($pacientes);
    }

    public function mostrar($id) {
        $paciente = $this->pacienteModel->obtenerPorId($id);
        if ($paciente) {
            echo json_encode($paciente);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Paciente no encontrado']);
        }
    }

    public function crear() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['nombres']) || empty($data['apellidos']) || empty($data['fecha_nacimiento'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre, apellidos y fecha de nacimiento son obligatorios']);
            return;
        }
        
        if (empty($data['fecha_ingreso'])) {
            $data['fecha_ingreso'] = date('Y-m-d');
        }
        if (empty($data['estado'])) {
            $data['estado'] = 'Activo';
        }
        
        $id = $this->pacienteModel->crear($data);
        if ($id) {
            http_response_code(201);
            echo json_encode(['message' => 'Paciente creado', 'id' => $id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear paciente']);
        }
    }

    public function actualizar($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!$this->pacienteModel->obtenerPorId($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Paciente no encontrado']);
            return;
        }
        
        if ($this->pacienteModel->actualizar($id, $data)) {
            echo json_encode(['message' => 'Paciente actualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar paciente']);
        }
    }

    public function eliminar($id) {
        if (!$this->pacienteModel->obtenerPorId($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Paciente no encontrado']);
            return;
        }
        
        if ($this->pacienteModel->eliminar($id)) {
            echo json_encode(['message' => 'Paciente eliminado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar paciente']);
        }
    }

    // Método para obtener todos los datos completos del paciente (incluyendo historial médico)
    public function obtenerCompleto($id) {
        $paciente = $this->pacienteModel->obtenerPorId($id);
        if (!$paciente) {
            http_response_code(404);
            echo json_encode(['error' => 'Paciente no encontrado']);
            return;
        }

        // Obtener historial médico
        $historialModel = new HistorialMedico();
        $historial = $historialModel->obtenerPorPaciente($id);

        // Devolver solo paciente e historial
        echo json_encode([
            'paciente' => $paciente,
            'historial' => $historial
        ]);
    }
}