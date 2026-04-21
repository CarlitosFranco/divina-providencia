<?php
namespace Controladores;

use Dompdf\Dompdf;
use Dompdf\Options;

class ReporteControlador {
    public function asistenciasPDF() {
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        $personalId = $_GET['personal_id'] ?? null;

        $db = (new \Config\Database())->getConnection();
        $query = "SELECT a.*, p.nombres, p.apellidos 
                  FROM asistencia a
                  JOIN personal p ON a.personal_id = p.id
                  WHERE a.fecha BETWEEN :fecha_inicio AND :fecha_fin";
        if ($personalId) {
            $query .= " AND a.personal_id = :personal_id";
        }
        $query .= " ORDER BY a.fecha DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':fecha_inicio', $fechaInicio);
        $stmt->bindParam(':fecha_fin', $fechaFin);
        if ($personalId) {
            $stmt->bindParam(':personal_id', $personalId);
        }
        $stmt->execute();
        $asistencias = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Calcular resumen
        $resumen = [];
        foreach ($asistencias as $a) {
            $key = $a['personal_id'];
            if (!isset($resumen[$key])) {
                $resumen[$key] = [
                    'personal_id' => $a['personal_id'],
                    'nombres' => $a['nombres'],
                    'apellidos' => $a['apellidos'],
                    'total_horas' => 0,
                    'dias_trabajados' => 0
                ];
            }
            if ($a['horas_trabajadas']) {
                $resumen[$key]['total_horas'] += (float)$a['horas_trabajadas'];
                $resumen[$key]['dias_trabajados']++;
            }
        }
        $resumen = array_values($resumen);

        $html = $this->generarHTML($asistencias, $resumen, $fechaInicio, $fechaFin);
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("reporte_asistencias.pdf", ["Attachment" => true]);
        exit;
    }

    private function generarHTML($asistencias, $resumen, $fechaInicio, $fechaFin) {
        $fecha = date('d/m/Y');
        $html = "<html><head><meta charset='UTF-8'><title>Reporte de Asistencias</title><style>
            body { font-family: Helvetica, Arial, sans-serif; margin: 30px; }
            h1 { color: #2c3e50; }
            .info { margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .resumen-table { margin-top: 30px; }
            .footer { margin-top: 40px; font-size: 10px; text-align: center; }
        </style></head><body>
        <h1>Divina Providencia - Reporte de Asistencias</h1>
        <div class='info'>
            <p><strong>Período:</strong> " . date('d/m/Y', strtotime($fechaInicio)) . " al " . date('d/m/Y', strtotime($fechaFin)) . "</p>
        </div>
        <h2>Detalle de asistencias</h2>
        <table>
            <thead><tr><th>Empleado</th><th>Fecha</th><th>Entrada</th><th>Salida</th><th>Horas</th></tr></thead>
            <tbody>";
        foreach ($asistencias as $a) {
            $html .= "<tr>
                        <td>{$a['nombres']} {$a['apellidos']}</td>
                        <td>" . date('d/m/Y', strtotime($a['fecha'])) . "</td>
                        <td>{$a['hora_entrada']}</td>
                        <td>{$a['hora_salida']}</td>
                        <td>{$a['horas_trabajadas']}</td>
                    </tr>";
        }
        if (empty($asistencias)) {
            $html .= "<tr><td colspan='5'>No hay registros en el período seleccionado.</td></tr>";
        }
        $html .= "</tbody></table>
        <h2>Resumen por empleado</h2>
        <table class='resumen-table'>
            <thead><tr><th>Empleado</th><th>Días trabajados</th><th>Total horas</th></tr></thead>
            <tbody>";
        foreach ($resumen as $r) {
            $html .= "<tr>
                        <td>{$r['nombres']} {$r['apellidos']}</td>
                        <td>{$r['dias_trabajados']}</td>
                        <td>{$r['total_horas']} h</td>
                    </tr>";
        }
        if (empty($resumen)) {
            $html .= "<tr><td colspan='3'>Sin datos</td></tr>";
        }
        $html .= "</tbody></table>
        <div class='footer'>Documento generado el $fecha</div>
        </body></html>";
        return $html;
    }
}