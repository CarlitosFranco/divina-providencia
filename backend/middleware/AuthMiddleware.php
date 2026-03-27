<?php
namespace Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    public static function verificar() {
        $headers = getallheaders();
        $token = null;

        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
        }

        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token no proporcionado']);
            exit;
        }

        $secret = getenv('JWT_SECRET') ?: $_ENV['JWT_SECRET'];

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido', 'detalle' => $e->getMessage()]);
            exit;
        }
    }
}