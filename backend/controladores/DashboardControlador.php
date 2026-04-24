<?php
namespace Controladores;

use Config\Database;

class DashboardControlador {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    // Resumen general (tarjetas)
    public function resumen() {
        try {
            // Total pacientes activos
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM pacientes WHERE estado = 'Activo'");
            $pacientesActivos = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Total personal activo
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM personal WHERE activo = 1");
            $personalActivo = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Turnos de hoy
            $hoy = date('Y-m-d');
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM turnos_personal WHERE fecha = :fecha");
            $stmt->bindParam(':fecha', $hoy);
            $stmt->execute();
            $turnosHoy = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Citas de hoy
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM citas WHERE fecha = :fecha");
            $stmt->bindParam(':fecha', $hoy);
            $stmt->execute();
            $citasHoy = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Asistencias de hoy (entrada o salida registrada)
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM asistencia WHERE fecha = :fecha AND (hora_entrada IS NOT NULL OR hora_salida IS NOT NULL)");
            $stmt->bindParam(':fecha', $hoy);
            $stmt->execute();
            $asistenciasHoy = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            echo json_encode([
                'pacientes_activos' => (int)$pacientesActivos,
                'personal_activo' => (int)$personalActivo,
                'turnos_hoy' => (int)$turnosHoy,
                'citas_hoy' => (int)$citasHoy,
                'asistencias_hoy' => (int)$asistenciasHoy
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Pacientes por estado (para gráfico de pastel)
    public function pacientesEstado() {
        try {
            $stmt = $this->db->query("SELECT estado, COUNT(*) as cantidad FROM pacientes GROUP BY estado");
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo json_encode($data);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Asistencias por mes (últimos 12 meses)
    public function asistenciasMes() {
        try {
            $meses = [];
            $data = [];
            for ($i = 11; $i >= 0; $i--) {
                $mes = date('Y-m', strtotime("-$i months"));
                $meses[] = $mes;
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM asistencia WHERE fecha LIKE :mes");
                $param = $mes . '%';
                $stmt->bindParam(':mes', $param);
                $stmt->execute();
                $total = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
                $data[] = (int)$total;
            }
            echo json_encode([
                'labels' => $meses,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Turnos por tipo (Mañana, Tarde, Noche) - opcional para otro gráfico
    public function turnosPorTipo() {
        try {
            $stmt = $this->db->query("SELECT tipo_turno, COUNT(*) as cantidad FROM turnos_personal GROUP BY tipo_turno");
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo json_encode($data);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}