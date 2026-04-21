<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// ========== CLAVE JWT ==========
if (!defined('JWT_SECRET')) {
    define('JWT_SECRET', 'tu_clave_super_secreta_y_larga_de_al_menos_32_caracteres_123456');
}

// ========== CORS ==========
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ========== FUNCIÓN DE ERROR ==========
function errorResponse($code, $message, $details = null) {
    http_response_code($code);
    $response = ['error' => $message];
    if ($details) $response['details'] = $details;
    echo json_encode($response);
    exit;
}

// ========== FUNCIÓN AUXILIAR CRUD (definida antes de usarla) ==========
function ejecutarCRUD($controller, $method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) $controller->mostrar($id);
            else $controller->listar();
            break;
        case 'POST':
            $controller->crear();
            break;
        case 'PUT':
            if ($id) $controller->actualizar($id);
            else errorResponse(400, 'ID requerido para PUT');
            break;
        case 'DELETE':
            if ($id) $controller->eliminar($id);
            else errorResponse(400, 'ID requerido para DELETE');
            break;
        default:
            errorResponse(405, 'Método no permitido');
    }
}

// ========== EXTRACCIÓN DE RUTA ==========
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$path = str_replace($script_name, '', $request_uri);
$path = strtok($path, '?');
$path = trim($path, '/');
$path = preg_replace('#^(divina-providencia/)?backend/#', '', $path);

$segments = explode('/', $path);
$resource = $segments[0] ?? '';
$id = $segments[1] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

error_log("=== DEBUG === Resource: '$resource', Method: $method, Path: $path");

// ========== MAPEO DE CONTROLADORES ==========
$controllerMap = [
    'login'       => 'AuthControlador',
    'pacientes'   => 'PacienteControlador',
    'personal'    => 'PersonalControlador',
    'turnos'      => 'TurnoControlador',
    'actividades' => 'ActividadControlador',
    'citas'       => 'CitaControlador',
    'historial'   => 'HistorialControlador',
    'roles'       => 'RolControlador',
    'usuarios'    => 'UsuarioControlador',
    'asistencia'  => 'AsistenciaControlador',
    'asistencias' => 'AsistenciaControlador',
    'dietas'      => 'DietaControlador'
];

try {
    // Verificar si el recurso existe
    if (!isset($controllerMap[$resource])) {
        errorResponse(404, 'Recurso no encontrado', ['resource' => $resource]);
    }

    // Cargar controlador
    $controllerClass = $controllerMap[$resource];
    $controllerFile = __DIR__ . "/controladores/{$controllerClass}.php";
    if (!file_exists($controllerFile)) {
        errorResponse(500, "Controlador no encontrado: $controllerClass");
    }
    require_once $controllerFile;
    $fullClassName = "Controladores\\$controllerClass";
    $controller = new $fullClassName();

    // ========== LOGIN (público) ==========
    if ($resource === 'login') {
        if ($method !== 'POST') errorResponse(405, 'Método no permitido. Use POST.');
        $controller->login();
        exit;
    }

    // ========== RECURSOS PROTEGIDOS ==========
    require_once __DIR__ . '/middleware/AuthMiddleware.php';
    $usuario = Middleware\AuthMiddleware::verificar();

    // ========== RUTAS ESPECIALES (antes del CRUD) ==========
    if ($resource === 'pacientes' && $method === 'GET' && isset($segments[2]) && $segments[2] === 'completo') {
        $controller->obtenerCompleto($id);
    }
    elseif ($resource === 'turnos' && $method === 'GET' && isset($segments[1]) && $segments[1] === 'personal') {
        $personalId = $segments[2] ?? null;
        if (!$personalId) errorResponse(400, 'ID de personal requerido');
        $controller->listarPorPersonal($personalId);
    }
    // ========== TURNOS (solo administrador) ==========
    elseif ($resource === 'turnos') {
        if ($usuario['rol_id'] != 1) errorResponse(403, 'No tienes permiso para acceder a turnos');
        ejecutarCRUD($controller, $method, $id);
    }
    // ========== USUARIOS (solo administrador) ==========
    elseif ($resource === 'usuarios') {
        if ($usuario['rol_id'] != 1) errorResponse(403, 'No tienes permiso para acceder a usuarios');
        ejecutarCRUD($controller, $method, $id);
    }
    // ========== ASISTENCIA (marcación individual) ==========
    elseif ($resource === 'asistencia') {
        if ($method === 'POST') {
            $sub = $segments[1] ?? '';
            if ($sub === 'entrada') $controller->registrarEntrada();
            elseif ($sub === 'salida') $controller->registrarSalida();
            else errorResponse(404, 'Subrecurso no encontrado');
        } elseif ($method === 'GET') {
            $controller->obtenerPorFecha();
        } else {
            errorResponse(405, 'Método no permitido');
        }
    }
    // ========== LISTADO DE ASISTENCIAS ==========
    elseif ($resource === 'asistencias') {
        if ($method !== 'GET') errorResponse(405, 'Método no permitido');
        $controller->listar();
    }
    // ========== CRUD ESTÁNDAR PARA EL RESTO ==========
    else {
        ejecutarCRUD($controller, $method, $id);
    }
} catch (Throwable $e) {
    errorResponse(500, 'Error interno del servidor', $e->getMessage());
}