<?php
namespace Middleware;

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

        if (strlen($token) < 10) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }

        // Devolvemos datos de ejemplo (deberías obtener el usuario desde la BD según el token)
        // Para que funcione con el token simple, puedes almacenar el token en una tabla de sesiones.
        // Por ahora, devolvemos un administrador.
        return [
            'id' => 1,
            'email' => 'admin@divinaprovidencia.com',
            'rol_id' => 1,
            'personal_id' => 1
        ];
    }
}