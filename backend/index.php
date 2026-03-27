<?php
// ==================================================
// API REST para Casa de Reposo Divina Providencia
// ==================================================

// --- Configuración de errores (deshabilitar en producción) ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- Cargar autoload y dependencias ---
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// --- Cargar variables de entorno desde .env solo si existe (entorno local) ---
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// --- Exportar variables al entorno (para que getenv() funcione) ---
foreach ($_ENV as $key => $value) {
    putenv("$key=$value");
}

// --- Configuración CORS ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Manejar preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- Función para respuestas de error estandarizadas ---
function errorResponse($code, $message, $details = null) {
    http_response_code($code);
    $response = ['error' => $message];
    if ($details) $response['details'] = $details;
    echo json_encode($response);
    exit;
}

// --- Extraer la ruta limpia (PATH_INFO o REQUEST_URI) ---
$path = '';
if (isset($_SERVER['PATH_INFO'])) {
    $path = trim($_SERVER['PATH_INFO'], '/');
} else {
    $requestUri = $_SERVER['REQUEST_URI'];
    $scriptName = $_SERVER['SCRIPT_NAME'];

    if (strpos($requestUri, $scriptName) === 0) {
        $path = substr($requestUri, strlen($scriptName));
    } else {
        $base = dirname($scriptName);
        $path = str_replace($base, '', $requestUri);
        $path = str_replace('/index.php', '', $path);
    }

    $path = strtok($path, '?');
    $path = trim($path, '/');
}

$segments = explode('/', $path);
$resource = $segments[0] ?? null;
$id = $segments[1] ?? null;

// --- Verificar autenticación (excepto para login) ---
if ($resource !== 'login') {
    require_once __DIR__ . '/middleware/AuthMiddleware.php';
    $usuario = Middleware\AuthMiddleware::verificar();
    $GLOBALS['usuario_actual'] = $usuario;
}

// --- Enrutamiento con manejo de excepciones ---
try {
    switch ($resource) {
        // ========== LOGIN ==========
        case 'login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                errorResponse(405, 'Método no permitido. Use POST.');
            }

            $controllerFile = __DIR__ . '/controladores/AuthControlador.php';
            if (!file_exists($controllerFile)) {
                errorResponse(500, 'Error interno: controlador no encontrado.');
            }

            require_once $controllerFile;
            $controller = new Controladores\AuthControlador();
            $controller->login();
            break;

        // ========== PACIENTES ==========
        case 'pacientes':
            $controllerFile = __DIR__ . '/controladores/PacienteControlador.php';
            if (!file_exists($controllerFile)) {
                errorResponse(500, 'Error interno: controlador no encontrado.');
            }

            require_once $controllerFile;
            $controller = new Controladores\PacienteControlador();
            $method = $_SERVER['REQUEST_METHOD'];

            if ($method === 'GET' && !$id) {
                $controller->listar();
            } elseif ($method === 'GET' && $id) {
                $controller->mostrar($id);
            } elseif ($method === 'POST') {
                $controller->crear();
            } elseif ($method === 'PUT' && $id) {
                $controller->actualizar($id);
            } elseif ($method === 'DELETE' && $id) {
                $controller->eliminar($id);
            } else {
                errorResponse(405, 'Método no permitido para el recurso pacientes.');
            }
            break;

        // ========== PERSONAL ==========
        case 'personal':
            $controllerFile = __DIR__ . '/controladores/PersonalControlador.php';
            if (!file_exists($controllerFile)) {
                errorResponse(500, 'Controlador de personal no encontrado');
            }
            require_once $controllerFile;
            $controller = new Controladores\PersonalControlador();
            $method = $_SERVER['REQUEST_METHOD'];

            if ($method === 'GET' && !$id) {
                $controller->listar();
            } elseif ($method === 'GET' && $id) {
                $controller->mostrar($id);
            } elseif ($method === 'POST') {
                $controller->crear();
            } elseif ($method === 'PUT' && $id) {
                $controller->actualizar($id);
            } elseif ($method === 'DELETE' && $id) {
                $controller->eliminar($id);
            } else {
                errorResponse(405, 'Método no permitido');
            }
            break;

        // ========== TURNOS ==========
        case 'turnos':
            $controllerFile = __DIR__ . '/controladores/TurnoControlador.php';
            if (!file_exists($controllerFile)) {
                errorResponse(500, 'Controlador de turnos no encontrado');
            }
            require_once $controllerFile;
            $controller = new Controladores\TurnoControlador();
            $method = $_SERVER['REQUEST_METHOD'];

            if ($method === 'GET' && !$id) {
                $controller->listar();
            } elseif ($method === 'GET' && $id && isset($segments[2]) && $segments[2] === 'personal') {
                $personalId = $id;
                $controller->listarPorPersonal($personalId);
            } elseif ($method === 'GET' && $id) {
                $controller->mostrar($id);
            } elseif ($method === 'POST') {
                $controller->crear();
            } elseif ($method === 'PUT' && $id) {
                $controller->actualizar($id);
            } elseif ($method === 'DELETE' && $id) {
                $controller->eliminar($id);
            } else {
                errorResponse(405, 'Método no permitido');
            }
            break;

        // ========== ACTIVIDADES ==========
        case 'actividades':
            $controllerFile = __DIR__ . '/controladores/ActividadControlador.php';
            if (!file_exists($controllerFile)) {
                errorResponse(500, 'Controlador de actividades no encontrado');
            }
            require_once $controllerFile;
            $controller = new Controladores\ActividadControlador();
            $method = $_SERVER['REQUEST_METHOD'];

            if ($method === 'GET' && !$id) {
                $controller->listar();
            } elseif ($method === 'GET' && $id) {
                $controller->mostrar($id);
            } elseif ($method === 'POST') {
                $controller->crear();
            } elseif ($method === 'PUT' && $id) {
                $controller->actualizar($id);
            } elseif ($method === 'DELETE' && $id) {
                $controller->eliminar($id);
            } else {
                errorResponse(405, 'Método no permitido');
            }
            break;

        default:
            errorResponse(404, 'Recurso no encontrado', ['path' => $path, 'resource' => $resource]);
            break;
    }
} catch (Exception $e) {
    errorResponse(500, 'Error interno del servidor', $e->getMessage());
}