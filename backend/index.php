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

// --- Extraer la ruta limpia de forma robusta ---
$path = '';
if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] !== '') {
    $path = trim($_SERVER['PATH_INFO'], '/');
} elseif (isset($_SERVER['REQUEST_URI'])) {
    $requestUri = $_SERVER['REQUEST_URI'];
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    if ($scriptName && strpos($requestUri, $scriptName) === 0) {
        $path = substr($requestUri, strlen($scriptName));
    } else {
        $base = dirname($scriptName);
        $path = str_replace($base, '', $requestUri);
        $path = str_replace('/index.php', '', $path);
    }
    $path = strtok($path, '?');
    $path = trim($path, '/');
}

if (empty($path) && isset($_SERVER['REQUEST_URI'])) {
    $parsed = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = trim($parsed ?? '', '/');
}

$segments = explode('/', $path);
$resource = $segments[0] ?? null;
$id = $segments[1] ?? null;
$method = $_SERVER['REQUEST_METHOD']; // Definimos $method aquí, antes de usarlo

// --- Enrutamiento con manejo de excepciones ---
try {
    switch ($resource) {
        // ========== LOGIN (NO requiere autenticación) ==========
        case 'login':
            if ($method !== 'POST') {
                errorResponse(405, 'Método no permitido. Use POST.');
            }
            $controllerFile = __DIR__ . '/controladores/AuthControlador.php';
            if (!file_exists($controllerFile)) errorResponse(500, 'Controlador de autenticación no encontrado.');
            require_once $controllerFile;
            $controller = new Controladores\AuthControlador();
            $controller->login();
            break;

        // ========== RECURSOS PROTEGIDOS (requieren autenticación) ==========
        case 'pacientes':
        case 'personal':
        case 'turnos':
        case 'actividades':
        case 'citas':
            // Verificar autenticación
            require_once __DIR__ . '/middleware/AuthMiddleware.php';
            $usuario = Middleware\AuthMiddleware::verificar();
            $GLOBALS['usuario_actual'] = $usuario;

            // Mapeo de recursos a controladores
            $controllerMap = [
                'pacientes'   => 'PacienteControlador',
                'personal'    => 'PersonalControlador',
                'turnos'      => 'TurnoControlador',
                'actividades' => 'ActividadControlador',
                'citas'       => 'CitaControlador'
            ];
            $controllerClass = $controllerMap[$resource];
            $controllerFile = __DIR__ . "/controladores/{$controllerClass}.php";
            if (!file_exists($controllerFile)) errorResponse(500, "Controlador no encontrado: {$controllerClass}");
            require_once $controllerFile;
            $fullClassName = "Controladores\\{$controllerClass}";
            $controller = new $fullClassName();

            // --- Lógica especial para pacientes: ruta /pacientes/{id}/completo
            if ($resource === 'pacientes' && $method === 'GET' && $id && isset($segments[2]) && $segments[2] === 'completo') {
                if (!method_exists($controller, 'obtenerCompleto')) {
                    errorResponse(501, 'Método obtenerCompleto no implementado en el controlador');
                }
                $controller->obtenerCompleto($id);
            }
            // --- Lógica especial para turnos: ruta /turnos/personal/{id}
            elseif ($resource === 'turnos' && $method === 'GET' && isset($segments[2]) && $segments[2] === 'personal') {
                $personalId = $id;
                $controller->listarPorPersonal($personalId);
            }
            // --- CRUD estándar para todos los recursos
            elseif ($method === 'GET' && !$id) {
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
                errorResponse(405, 'Método no permitido para el recurso ' . $resource);
            }
            break;

        default:
            errorResponse(404, 'Recurso no encontrado', ['path' => $path, 'resource' => $resource]);
            break;
    }
} catch (Exception $e) {
    errorResponse(500, 'Error interno del servidor', $e->getMessage());
}