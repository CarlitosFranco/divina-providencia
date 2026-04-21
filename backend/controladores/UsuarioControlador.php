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

        // Crear registro en personal
        $cargo = $this->obtenerCargoPorRol($data['rol_id']);
        $personalData = [
            'nombres' => $data['nombre'],
            'apellidos' => $data['apellidos'] ?? null,
            'documento_identidad' => $data['documento_identidad'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'],
            'cargo' => $cargo,
            'especialidad' => $data['especialidad'] ?? null,
            'fecha_contratacion' => date('Y-m-d'),
            'activo' => 1
        ];
        $personalId = $this->personalModel->crear($personalData);
        if (!$personalId) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear el registro de personal. Verifique que el email no exista ya.']);
            return;
        }

        // Crear usuario
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
            $this->personalModel->eliminar($personalId);
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear usuario']);
        }
    }

    // ... los métodos actualizar y eliminar se mantienen igual (no es necesario cambiarlos)
}