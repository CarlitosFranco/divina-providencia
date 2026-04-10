<?php
namespace Controladores;

use Modelos\Usuario;

class UsuarioControlador {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function listar() {
        $usuarios = $this->usuarioModel->obtenerTodos();
        echo json_encode($usuarios);
    }

    public function mostrar($id) {
        $usuario = $this->usuarioModel->obtenerPorId($id);
        if ($usuario) {
            echo json_encode($usuario);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
        }
    }

    public function crear() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['nombre']) || empty($data['email']) || empty($data['password']) || empty($data['rol_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre, email, contraseña y rol son obligatorios']);
            return;
        }

        // Verificar email único
        if ($this->usuarioModel->obtenerPorEmail($data['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El email ya está registrado']);
            return;
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['activo'] = $data['activo'] ?? 1;

        if ($this->usuarioModel->crear($data)) {
            http_response_code(201);
            echo json_encode(['message' => 'Usuario creado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear usuario']);
        }
    }

    public function actualizar($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $usuarioExistente = $this->usuarioModel->obtenerPorId($id);
        if (!$usuarioExistente) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return;
        }

        // Si se envía password, actualizarla
        if (!empty($data['password'])) {
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            $this->usuarioModel->actualizarPassword($id, $passwordHash);
        }

        $updateData = [
            'nombre' => $data['nombre'] ?? $usuarioExistente['nombre'],
            'email' => $data['email'] ?? $usuarioExistente['email'],
            'rol_id' => $data['rol_id'] ?? $usuarioExistente['rol_id'],
            'activo' => $data['activo'] ?? $usuarioExistente['activo']
        ];

        if ($this->usuarioModel->actualizar($id, $updateData)) {
            echo json_encode(['message' => 'Usuario actualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar usuario']);
        }
    }

    public function eliminar($id) {
        if ($id == 1) {
            http_response_code(400);
            echo json_encode(['error' => 'No se puede eliminar al administrador principal']);
            return;
        }

        if ($this->usuarioModel->eliminar($id)) {
            echo json_encode(['message' => 'Usuario eliminado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar usuario']);
        }
    }
}