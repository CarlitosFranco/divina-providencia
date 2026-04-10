<?php
namespace Controladores;

use Modelos\HistorialMedico;

class HistorialControlador {
    private $historialModel;

    public function __construct() {
        $this->historialModel = new HistorialMedico();
    }

    public function crear() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['paciente_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'paciente_id es requerido']);
            return;
        }
        $result = $this->historialModel->crear($data);
        if ($result) {
            http_response_code(201);
            echo json_encode(['message' => 'Historial creado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear historial']);
        }
    }

    public function actualizar($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $result = $this->historialModel->actualizar($id, $data);
        if ($result) {
            echo json_encode(['message' => 'Historial actualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar historial']);
        }
    }

    public function eliminar($id) {
        $result = $this->historialModel->eliminar($id);
        if ($result) {
            echo json_encode(['message' => 'Historial eliminado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar historial']);
        }
    }
}