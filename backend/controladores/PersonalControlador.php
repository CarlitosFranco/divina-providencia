<?php
namespace Controladores;

use Modelos\Personal;

class PersonalControlador {
    private $personalModel;

    public function __construct() {
        $this->personalModel = new Personal();
    }

    public function listar() {
        $personal = $this->personalModel->obtenerTodos();
        // Agregar el rol de cada empleado consultando la tabla usuarios
        foreach ($personal as &$p) {
            $usuario = $this->personalModel->obtenerUsuarioPorPersonalId($p['id']);
            $p['rol_id'] = $usuario ? $usuario['rol_id'] : null;
            $p['rol_nombre'] = $usuario ? $this->personalModel->obtenerRolNombre($usuario['rol_id']) : 'Sin usuario';
        }
        echo json_encode($personal);
    }

    public function mostrar($id) {
        $item = $this->personalModel->obtenerPorId($id);
        if ($item) {
            echo json_encode($item);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Personal no encontrado']);
        }
    }

    public function crear() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['nombres']) || empty($data['apellidos']) || empty($data['cargo']) || empty($data['fecha_contratacion'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre, apellidos, cargo y fecha de contratación son obligatorios']);
            return;
        }
        
        if (!isset($data['activo'])) {
            $data['activo'] = 1;
        }
        
        $id = $this->personalModel->crear($data);
        if ($id) {
            http_response_code(201);
            echo json_encode(['message' => 'Personal creado', 'id' => $id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear personal']);
        }
    }

    public function actualizar($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!$this->personalModel->obtenerPorId($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Personal no encontrado']);
            return;
        }
        
        if ($this->personalModel->actualizar($id, $data)) {
            echo json_encode(['message' => 'Personal actualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar personal']);
        }
    }

    public function eliminar($id) {
        if (!$this->personalModel->obtenerPorId($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Personal no encontrado']);
            return;
        }
        
        if ($this->personalModel->eliminar($id)) {
            echo json_encode(['message' => 'Personal eliminado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar personal']);
        }
    }
}