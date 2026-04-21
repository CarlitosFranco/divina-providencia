<?php
namespace Controladores;

use Modelos\Dieta;

class DietaControlador {
    private $dietaModel;

    public function __construct() {
        $this->dietaModel = new Dieta();
    }

    public function listar() {
        $dietas = $this->dietaModel->obtenerTodos();
        echo json_encode($dietas);
    }

    public function mostrar($id) {
        $dieta = $this->dietaModel->obtenerPorId($id);
        echo json_encode($dieta);
    }

    public function crear() {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->dietaModel->crear($data)) {
            http_response_code(201);
            echo json_encode(['message' => 'Dieta creada']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear dieta']);
        }
    }

    public function actualizar($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->dietaModel->actualizar($id, $data)) {
            echo json_encode(['message' => 'Dieta actualizada']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar dieta']);
        }
    }

    public function eliminar($id) {
        if ($this->dietaModel->eliminar($id)) {
            echo json_encode(['message' => 'Dieta eliminada']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar dieta']);
        }
    }
}