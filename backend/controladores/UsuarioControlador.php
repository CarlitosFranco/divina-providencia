<?php
namespace Controladores;

use Modelos\Usuario;
use Modelos\Personal;

class UsuarioControlador {
    private $usuarioModel;
    private $personalModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
        $this->personalModel = new Personal();
    }

    private function obtenerCargoPorRol($rolId) {
        $cargos = [
            1 => 'Administrador',
            3 => 'Enfermera',
            5 => 'Cocina'
        ];
        return $cargos[$rolId] ?? 'Personal';
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

        // Verificar email único en usuarios
        if ($this->usuarioModel->obtenerPorEmail($data['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El email ya está registrado como usuario']);
            return;
        }

        // Verificar email único en personal
        if ($this->personalModel->existeEmail($data['email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El email ya está registrado en la tabla de personal']);
            return;
        }

        // Preparar datos para personal
        $cargo = $this->obtenerCargoPorRol($data['rol_id']);
        $personalData = [
            'nombres' => $data['nombre'],
            'apellidos' => $data['apellidos'] ?? '',
            'documento_identidad' => $data['documento_identidad'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'],
            'cargo' => $cargo,
            'especialidad' => $data['especialidad'] ?? null,
            'fecha_contratacion' => date('Y-m-d'),
            'activo' => 1
        ];

        // Intentar crear personal
        $personalId = $this->personalModel->crear($personalData);
        if (!$personalId) {
            // Registrar el error en el log de Apache
            error_log("Error al crear personal. Datos: " . json_encode($personalData));
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear el registro de personal. Verifique que la tabla personal acepte valores nulos.']);
            return;
        }

        // Crear usuario vinculado
        $usuarioData = [
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'rol_id' => $data['rol_id'],
            'personal_id' => $personalId,
            'activo' => $data['activo'] ?? 1
        ];

        if ($this->usuarioModel->crear($usuarioData)) {
            http_response_code(201);
            echo json_encode(['message' => 'Usuario y personal creados correctamente']);
        } else {
            // Si falla la creación del usuario, eliminar el personal recién creado
            $this->personalModel->eliminar($personalId);
            error_log("Error al crear usuario después de crear personal. ID personal: $personalId");
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

        // Actualizar personal asociado si existe
        if (!empty($usuarioExistente['personal_id'])) {
            $personalData = [
                'nombres' => $data['nombre'] ?? $usuarioExistente['nombre'],
                'apellidos' => $data['apellidos'] ?? '',
                'documento_identidad' => $data['documento_identidad'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'email' => $data['email'] ?? $usuarioExistente['email'],
                'cargo' => $this->obtenerCargoPorRol($data['rol_id'] ?? $usuarioExistente['rol_id']),
                'especialidad' => $data['especialidad'] ?? null,
                'activo' => $data['activo'] ?? 1
            ];
            $this->personalModel->actualizar($usuarioExistente['personal_id'], $personalData);
        }

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
        $usuario = $this->usuarioModel->obtenerPorId($id);
        if ($usuario && $usuario['personal_id']) {
            $this->personalModel->eliminar($usuario['personal_id']);
        }
        if ($this->usuarioModel->eliminar($id)) {
            echo json_encode(['message' => 'Usuario eliminado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar usuario']);
        }
    }
}