<?php
namespace Controladores;

use Dompdf\Dompdf;
use Dompdf\Options;
use Modelos\Paciente;
use Modelos\HistorialMedico;

class ExportarControlador {
    private $pacienteModel;
    private $historialModel;

    public function __construct() {
        $this->pacienteModel = new Paciente();
        $this->historialModel = new HistorialMedico();
    }

    public function pacientePDF($id) {
        // Depuración: log del ID recibido
        error_log("=== ExportarControlador: ID recibido = " . $id);

        // Validar ID
        if (!is_numeric($id) || $id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de paciente inválido']);
            return;
        }

        // Obtener datos del paciente
        $paciente = $this->pacienteModel->obtenerPorId($id);
        if (!$paciente) {
            http_response_code(404);
            echo json_encode(['error' => 'Paciente no encontrado con ID: ' . $id]);
            return;
        }

        // Obtener historial médico
        $historial = $this->historialModel->obtenerPorPaciente($id);

        // Generar HTML para el PDF
        $html = $this->generarHTML($paciente, $historial);

        // Configurar Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Courier'); // Fuente más compatible
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Enviar el PDF al navegador (descarga)
        $dompdf->stream("paciente_{$paciente['id']}.pdf", ["Attachment" => true]);
        exit;
    }

    private function generarHTML($paciente, $historial) {
        $fecha = date('d/m/Y');
        $nombreCompleto = $paciente['nombres'] . ' ' . $paciente['apellidos'];

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ficha del Paciente</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; margin: 40px; }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; }
        .info p { margin: 5px 0; }
        .label { font-weight: bold; display: inline-block; width: 180px; }
        .card { background: #f8f9fa; border-left: 4px solid #3498db; padding: 12px; margin-bottom: 15px; }
        .footer { margin-top: 50px; font-size: 10px; text-align: center; color: #7f8c8d; }
    </style>
</head>
<body>
    <h1>Divina Providencia - Casa de Reposo</h1>
    <h2>Ficha del Paciente</h2>
    <div class="info">
        <p><span class="label">Nombre completo:</span> $nombreCompleto</p>
        <p><span class="label">Documento:</span> {$paciente['documento_identidad']}</p>
        <p><span class="label">Fecha nacimiento:</span> {$paciente['fecha_nacimiento']}</p>
        <p><span class="label">Género:</span> {$paciente['genero']}</p>
        <p><span class="label">Teléfono:</span> {$paciente['telefono']}</p>
        <p><span class="label">Celular:</span> {$paciente['celular']}</p>
        <p><span class="label">Email:</span> {$paciente['email']}</p>
        <p><span class="label">Dirección:</span> {$paciente['direccion']}</p>
        <p><span class="label">Contacto emergencia:</span> {$paciente['contacto_emergencia_nombre']} ({$paciente['contacto_emergencia_telefono']})</p>
        <p><span class="label">Fecha ingreso:</span> {$paciente['fecha_ingreso']}</p>
        <p><span class="label">Estado:</span> {$paciente['estado']}</p>
        <p><span class="label">Observaciones:</span> {$paciente['observaciones']}</p>
    </div>
    <h2>Historial Médico</h2>
HTML;
        if (empty($historial)) {
            $html .= "<p>No hay registros de historial médico.</p>";
        } else {
            foreach ($historial as $item) {
                $html .= <<<HTML
    <div class="card">
        <p><strong>Alergias:</strong> {$item['alergias']}</p>
        <p><strong>Enfermedades crónicas:</strong> {$item['enfermedades_cronicas']}</p>
        <p><strong>Antecedentes familiares:</strong> {$item['antecedentes_familiares']}</p>
        <p><strong>Cirugías previas:</strong> {$item['cirugias_previas']}</p>
        <p><strong>Grupo sanguíneo:</strong> {$item['grupo_sanguineo']}</p>
    </div>
HTML;
            }
        }
        $html .= "<div class='footer'>Documento generado el $fecha</div></body></html>";
        return $html;
    }
}