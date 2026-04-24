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

function errorResponse($code, $message, $details = null) {
    http_response_code($code);
    $response = ['error' => $message];
    if ($details) $response['details'] = $details;
    echo json_encode($response);
    exit;
}

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
$method = $_SERVER['REQUEST_METHOD'];

error_log("=== DEBUG === Resource: '$resource', Method: $method, Path: $path, Segments: " . print_r($segments, true));

// ========== MANEJO ESPECIAL PARA EXPORTAR ==========
if ($resource === 'exportar') {
    error_log("=== DEBUG EXPORTAR: Recurso detectado");
    $token = $_GET['token'] ?? null;
    if ($token) {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }
    require_once __DIR__ . '/middleware/AuthMiddleware.php';
    Middleware\AuthMiddleware::verificar();
    if ($method !== 'GET') errorResponse(405, 'Método no permitido');
    if (!isset($segments[1]) || $segments[1] !== 'paciente' || !isset($segments[2])) {
        errorResponse(400, 'Ruta inválida. Use /exportar/paciente/{id}');
    }
    $id = $segments[2];
    require_once __DIR__ . '/controladores/ExportarControlador.php';
    $exportController = new Controladores\ExportarControlador();
    $exportController->pacientePDF($id);
    exit;
}

// ========== MANEJO ESPECIAL PARA ARCHIVOS ==========
if ($resource === 'archivos') {
    error_log("=== DEBUG ARCHIVOS: Recurso detectado");
    $token = $_GET['token'] ?? null;
    if ($token) {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }
    require_once __DIR__ . '/middleware/AuthMiddleware.php';
    Middleware\AuthMiddleware::verificar();
    
    require_once __DIR__ . '/controladores/ArchivoControlador.php';
    $archivoController = new Controladores\ArchivoControlador();
    
    if ($method === 'GET' && isset($segments[1]) && $segments[1] === 'paciente') {
        $archivoController->listarPorPaciente();
    }
    elseif ($method === 'GET' && isset($segments[1]) && $segments[1] === 'descargar' && isset($segments[2])) {
        $archivoController->descargar($segments[2]);
    }
    elseif ($method === 'POST') {
        $archivoController->subir();
    }
    elseif ($method === 'DELETE' && isset($segments[1]) && is_numeric($segments[1])) {
        $archivoController->eliminar($segments[1]);
    }
    else {
        errorResponse(405, 'Método no permitido para archivos');
    }
    exit;
}

// ========== MANEJO ESPECIAL PARA REPORTE PDF ==========
if ($resource === 'reporte') {
    error_log("=== DEBUG REPORTE: Recurso detectado");
    $token = $_GET['token'] ?? null;
    if ($token) {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }
    require_once __DIR__ . '/middleware/AuthMiddleware.php';
    Middleware\AuthMiddleware::verificar();
    if ($method !== 'GET') errorResponse(405, 'Método no permitido');
    require_once __DIR__ . '/controladores/ReporteControlador.php';
    $reporteController = new Controladores\ReporteControlador();
    $reporteController->asistenciasPDF();
    exit;
}

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
    'dietas'      => 'DietaControlador',
    'dashboard'   => 'DashboardControlador'   // 👈 Nuevo recurso para estadísticas
];

try {
    if (!isset($controllerMap[$resource])) {
        errorResponse(404, 'Recurso no encontrado', ['resource' => $resource]);
    }

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

    // ========== RUTAS ESPECIALES ==========
    if ($resource === 'pacientes' && $method === 'GET' && isset($segments[2]) && $segments[2] === 'completo') {
        $id = $segments[1] ?? null;
        $controller->obtenerCompleto($id);
    }
    elseif ($resource === 'turnos' && $method === 'GET' && isset($segments[1]) && $segments[1] === 'personal') {
        $personalId = $segments[2] ?? null;
        if (!$personalId) errorResponse(400, 'ID de personal requerido');
        $controller->listarPorPersonal($personalId);
    }
    // ========== DASHBOARD (estadísticas para gráficos) ==========
    elseif ($resource === 'dashboard') {
        if ($method !== 'GET') errorResponse(405, 'Método no permitido');
        $sub = $segments[1] ?? '';
        if ($sub === 'resumen') {
            $controller->resumen();
        } elseif ($sub === 'pacientes-estado') {
            $controller->pacientesEstado();
        } elseif ($sub === 'asistencias-mes') {
            $controller->asistenciasMes();
        } elseif ($sub === 'turnos-tipo') {
            $controller->turnosPorTipo();
        } else {
            errorResponse(404, 'Ruta de dashboard no encontrada');
        }
    }
    // ========== TURNOS (solo administrador) ==========
    elseif ($resource === 'turnos') {
        if ($usuario['rol_id'] != 1) errorResponse(403, 'No tienes permiso para acceder a turnos');
        ejecutarCRUD($controller, $method, $segments[1] ?? null);
    }
    // ========== USUARIOS (solo administrador) ==========
    elseif ($resource === 'usuarios') {
        if ($usuario['rol_id'] != 1) errorResponse(403, 'No tienes permiso para acceder a usuarios');
        ejecutarCRUD($controller, $method, $segments[1] ?? null);
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
    // ========== ASISTENCIAS (listado y reporte) ==========
    elseif ($resource === 'asistencias') {
        if ($method === 'GET' && isset($segments[1]) && $segments[1] === 'reporte') {
            $controller->reporte();
        } elseif ($method !== 'GET') {
            errorResponse(405, 'Método no permitido');
        } else {
            $controller->listar();
        }
    }
    // ========== CRUD ESTÁNDAR PARA EL RESTO ==========
    else {
        $id = $segments[1] ?? null;
        ejecutarCRUD($controller, $method, $id);
    }
} catch (Throwable $e) {
    errorResponse(500, 'Error interno del servidor', $e->getMessage());
}