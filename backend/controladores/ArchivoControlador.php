<?php
namespace Controladores;

use Modelos\Archivo;

class ArchivoControlador {
    private $archivoModel;
    private $uploadDir;

    public function __construct() {
        $this->archivoModel = new Archivo();
        $this->uploadDir = __DIR__ . '/../uploads/pacientes/';
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function listarPorPaciente() {
        $pacienteId = $_GET['paciente_id'] ?? null;
        if (!$pacienteId) {
            http_response_code(400);
            echo json_encode(['error' => 'paciente_id requerido']);
            return;
        }
        $archivos = $this->archivoModel->obtenerPorPaciente($pacienteId);
        echo json_encode($archivos);
    }

    public function subir() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $pacienteId = $_POST['paciente_id'] ?? null;
        if (!$pacienteId) {
            http_response_code(400);
            echo json_encode(['error' => 'paciente_id requerido']);
            return;
        }

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'Error al subir el archivo']);
            return;
        }

        $file = $_FILES['archivo'];
        $nombreOriginal = $file['name'];
        $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
        $nombreServidor = uniqid() . '.' . $extension;
        $rutaDestino = $this->uploadDir . $nombreServidor;

        if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo guardar el archivo']);
            return;
        }

        $datos = [
            'paciente_id' => $pacienteId,
            'nombre_original' => $nombreOriginal,
            'nombre_servidor' => $nombreServidor,
            'ruta' => '/backend/uploads/pacientes/' . $nombreServidor,
            'tipo' => $file['type'],
            'tamaño' => $file['size']
        ];

        if ($this->archivoModel->crear($datos)) {
            http_response_code(201);
            echo json_encode(['message' => 'Archivo subido correctamente']);
        } else {
            unlink($rutaDestino);
            http_response_code(500);
            echo json_encode(['error' => 'Error al registrar el archivo']);
        }
    }

    public function eliminar($id) {
        $archivo = $this->archivoModel->obtenerPorId($id);
        if (!$archivo) {
            http_response_code(404);
            echo json_encode(['error' => 'Archivo no encontrado']);
            return;
        }

        $rutaCompleta = __DIR__ . '/..' . $archivo['ruta'];
        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }

        if ($this->archivoModel->eliminar($id)) {
            echo json_encode(['message' => 'Archivo eliminado']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar archivo']);
        }
    }

    public function descargar($id) {
        $archivo = $this->archivoModel->obtenerPorId($id);
        if (!$archivo) {
            http_response_code(404);
            echo json_encode(['error' => 'Archivo no encontrado']);
            return;
        }

        $rutaCompleta = __DIR__ . '/..' . $archivo['ruta'];
        if (!file_exists($rutaCompleta)) {
            http_response_code(404);
            echo json_encode(['error' => 'Archivo no existe en el servidor']);
            return;
        }

        header('Content-Type: ' . $archivo['tipo']);
        header('Content-Disposition: attachment; filename="' . $archivo['nombre_original'] . '"');
        header('Content-Length: ' . filesize($rutaCompleta));
        readfile($rutaCompleta);
        exit;
    }
}