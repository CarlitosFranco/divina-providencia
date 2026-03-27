<?php
namespace Controladores;

use Modelos\Paciente;

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
        
        // Validaciones básicas
        if (empty($data['nombres']) || empty($data['apellidos']) || empty($data['fecha_nacimiento'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre, apellidos y fecha de nacimiento son obligatorios']);
            return;
        }
        
        // Generar fecha_ingreso si no viene
        if (empty($data['fecha_ingreso'])) {
            $data['fecha_ingreso'] = date('Y-m-d');
        }
        
        // Asignar estado por defecto
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
}