<?php
namespace Controladores;

use Modelos\Usuario;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

        // Generar JWT
        $payload = [
            'id' => $usuario['id'],
            'email' => $usuario['email'],
            'nombre' => $usuario['nombre'],
            'rol_id' => $usuario['rol_id'],
            'exp' => time() + (60 * 60 * 24) // 24 horas
        ];
        
        $jwtSecret = getenv('JWT_SECRET');     
        $jwt = JWT::encode($payload, $jwtSecret, 'HS256');

        echo json_encode([
            'token' => $jwt,
            'usuario' => [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'rol_id' => $usuario['rol_id']
            ]
        ]);
    }
}