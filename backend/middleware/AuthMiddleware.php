<?php
namespace Middleware;

class AuthMiddleware {
    public static function verificar() {
        // Intentar obtener el token de diferentes formas (compatible con proxy)
        $headers = getallheaders();
        $token = null;

        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
        } elseif (isset($headers['authorization'])) {
            $token = str_replace('Bearer ', '', $headers['authorization']);
        }

        // Si no viene en headers, intentar desde $_SERVER (para algunos servidores)
        if (!$token && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
        }

        // Si aún no hay token, devolver usuario administrador por defecto (solo para pruebas)
        if (!$token) {
            // En desarrollo, permitir acceso sin token (comenta en producción)
            return [
                'id' => 1,
                'email' => 'admin@divinaprovidencia.com',
                'rol_id' => 1,
                'personal_id' => 1
            ];
        }

        // Aquí deberías validar el token (JWT, etc.). Por ahora, aceptamos cualquier token.
        return [
            'id' => 1,
            'email' => 'admin@divinaprovidencia.com',
            'rol_id' => 1,
            'personal_id' => 1
        ];
    }
}