<?php
namespace Controladores;

use Dompdf\Dompdf;
use Dompdf\Options;
use Modelos\Paciente;
use Modelos\HistorialMedico;
use Modelos\Archivo; // 👈 Importar modelo de archivos

class ExportarControlador {
    private $pacienteModel;
    private $historialModel;
    private $archivoModel;

    public function __construct() {
        $this->pacienteModel = new Paciente();
        $this->historialModel = new HistorialMedico();
        $this->archivoModel = new Archivo(); // 👈 Instanciar modelo Archivo
    }

    public function pacientePDF($id) {
        $id = (int)$id;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de paciente inválido']);
            return;
        }

        $paciente = $this->pacienteModel->obtenerPorId($id);
        if (!$paciente) {
            http_response_code(404);
            echo json_encode(['error' => 'Paciente no encontrado']);
            return;
        }

        $historial = $this->historialModel->obtenerPorPaciente($id);
        $archivos = $this->archivoModel->obtenerPorPaciente($id); // 👈 Obtener archivos

        $html = $this->generarHTML($paciente, $historial, $archivos);

        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("paciente_{$paciente['id']}.pdf", ["Attachment" => true]);
        exit;
    }

    private function generarHTML($paciente, $historial, $archivos) {
        $fecha = date('d/m/Y');
        $nombreCompleto = $paciente['nombres'] . ' ' . $paciente['apellidos'];

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Ficha del Paciente</title>
<style>
body { font-family: Helvetica, Arial, sans-serif; margin: 40px; }
h1 { color: #2c3e50; border-bottom: 2px solid #3498db; }
h2 { color: #34495e; margin-top: 30px; }
.label { font-weight: bold; display: inline-block; width: 180px; }
.card { background: #f8f9fa; border-left: 4px solid #3498db; padding: 12px; margin-bottom: 15px; }
.footer { margin-top: 50px; font-size: 10px; text-align: center; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>
</head>
<body>
<h1>Divina Providencia - Casa de Reposo</h1>
<h2>Ficha del Paciente</h2>
<div>
<p><span class='label'>Nombre completo:</span> $nombreCompleto</p>
<p><span class='label'>Documento:</span> {$paciente['documento_identidad']}</p>
<p><span class='label'>Fecha nacimiento:</span> {$paciente['fecha_nacimiento']}</p>
<p><span class='label'>Género:</span> {$paciente['genero']}</p>
<p><span class='label'>Teléfono:</span> {$paciente['telefono']}</p>
<p><span class='label'>Celular:</span> {$paciente['celular']}</p>
<p><span class='label'>Email:</span> {$paciente['email']}</p>
<p><span class='label'>Dirección:</span> {$paciente['direccion']}</p>
<p><span class='label'>Contacto emergencia:</span> {$paciente['contacto_emergencia_nombre']} ({$paciente['contacto_emergencia_telefono']})</p>
<p><span class='label'>Fecha ingreso:</span> {$paciente['fecha_ingreso']}</p>
<p><span class='label'>Estado:</span> {$paciente['estado']}</p>
<p><span class='label'>Observaciones:</span> {$paciente['observaciones']}</p>
</div>
<h2>Historial Médico</h2>
HTML;
        if (empty($historial)) {
            $html .= "<p>No hay registros de historial médico.</p>";
        } else {
            foreach ($historial as $item) {
                $html .= "<div class='card'>";
                $html .= "<p><strong>Alergias:</strong> {$item['alergias']}</p>";
                $html .= "<p><strong>Enfermedades crónicas:</strong> {$item['enfermedades_cronicas']}</p>";
                $html .= "<p><strong>Antecedentes familiares:</strong> {$item['antecedentes_familiares']}</p>";
                $html .= "<p><strong>Cirugías previas:</strong> {$item['cirugias_previas']}</p>";
                $html .= "<p><strong>Grupo sanguíneo:</strong> {$item['grupo_sanguineo']}</p></div>";
            }
        }

        $html .= "<h2>📎 Documentos Adjuntos</h2>";
        if (empty($archivos)) {
            $html .= "<p>No hay documentos subidos para este paciente.</p>";
        } else {
            $html .= "<table>";
            $html .= "<tr><th>Nombre del archivo</th><th>Fecha de subida</th><th>Tamaño</th></tr>";
            foreach ($archivos as $arch) {
                $fechaSubida = date('d/m/Y', strtotime($arch['created_at']));
                $tamaño = $this->formatearTamaño($arch['tamaño']);
                $html .= "<tr>";
                $html .= "<td>{$arch['nombre_original']}</td>";
                $html .= "<td>{$fechaSubida}</td>";
                $html .= "<td>{$tamaño}</td>";
                $html .= "</tr>";
            }
            $html .= "</table>";
        }

        $html .= "<div class='footer'>Documento generado el $fecha</div></body></html>";
        return $html;
    }

    private function formatearTamaño($bytes) {
        if (!$bytes) return '0 B';
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}