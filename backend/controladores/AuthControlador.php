<?php
namespace Controladores;

use Modelos\Usuario;

class AuthControlador {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email y contraseña son requeridos']);
            return;
        }

        $usuario = $this->usuarioModel->obtenerPorEmail($email);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciales inválidas']);
            return;
        }

        // Token simple (sin JWT)
        $token = bin2hex(random_bytes(32));

        echo json_encode([
            'token' => $token,
            'usuario' => [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'rol_id' => $usuario['rol_id'],
                'personal_id' => $usuario['personal_id'] ?? null
            ]
        ]);
    }
}