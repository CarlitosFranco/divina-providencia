<?php
namespace Modelos;

use Config\Database;

class Paciente {
    private $db;
    private $table = 'pacientes';

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function obtenerTodos() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Insertar un nuevo paciente
public function crear($datos) {
    $query = "INSERT INTO " . $this->table . " 
        (nombres, apellidos, fecha_nacimiento, genero, documento_identidad, telefono, celular, email, direccion, contacto_emergencia_nombre, contacto_emergencia_telefono, fecha_ingreso, estado, observaciones)
        VALUES (:nombres, :apellidos, :fecha_nacimiento, :genero, :documento_identidad, :telefono, :celular, :email, :direccion, :contacto_emergencia_nombre, :contacto_emergencia_telefono, :fecha_ingreso, :estado, :observaciones)";
    
    $stmt = $this->db->prepare($query);
    
    $stmt->bindParam(':nombres', $datos['nombres']);
    $stmt->bindParam(':apellidos', $datos['apellidos']);
    $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);
    $stmt->bindParam(':genero', $datos['genero']);
    $stmt->bindParam(':documento_identidad', $datos['documento_identidad']);
    $stmt->bindParam(':telefono', $datos['telefono']);
    $stmt->bindParam(':celular', $datos['celular']);
    $stmt->bindParam(':email', $datos['email']);
    $stmt->bindParam(':direccion', $datos['direccion']);
    $stmt->bindParam(':contacto_emergencia_nombre', $datos['contacto_emergencia_nombre']);
    $stmt->bindParam(':contacto_emergencia_telefono', $datos['contacto_emergencia_telefono']);
    $stmt->bindParam(':fecha_ingreso', $datos['fecha_ingreso']);
    $stmt->bindParam(':estado', $datos['estado']);
    $stmt->bindParam(':observaciones', $datos['observaciones']);
    
    if ($stmt->execute()) {
        return $this->db->lastInsertId();
    }
    return false;
}

// Actualizar un paciente existente
public function actualizar($id, $datos) {
    $query = "UPDATE " . $this->table . " SET 
        nombres = :nombres,
        apellidos = :apellidos,
        fecha_nacimiento = :fecha_nacimiento,
        genero = :genero,
        documento_identidad = :documento_identidad,
        telefono = :telefono,
        celular = :celular,
        email = :email,
        direccion = :direccion,
        contacto_emergencia_nombre = :contacto_emergencia_nombre,
        contacto_emergencia_telefono = :contacto_emergencia_telefono,
        fecha_ingreso = :fecha_ingreso,
        estado = :estado,
        observaciones = :observaciones
        WHERE id = :id";
    
    $stmt = $this->db->prepare($query);
    
    $stmt->bindParam(':nombres', $datos['nombres']);
    $stmt->bindParam(':apellidos', $datos['apellidos']);
    $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);
    $stmt->bindParam(':genero', $datos['genero']);
    $stmt->bindParam(':documento_identidad', $datos['documento_identidad']);
    $stmt->bindParam(':telefono', $datos['telefono']);
    $stmt->bindParam(':celular', $datos['celular']);
    $stmt->bindParam(':email', $datos['email']);
    $stmt->bindParam(':direccion', $datos['direccion']);
    $stmt->bindParam(':contacto_emergencia_nombre', $datos['contacto_emergencia_nombre']);
    $stmt->bindParam(':contacto_emergencia_telefono', $datos['contacto_emergencia_telefono']);
    $stmt->bindParam(':fecha_ingreso', $datos['fecha_ingreso']);
    $stmt->bindParam(':estado', $datos['estado']);
    $stmt->bindParam(':observaciones', $datos['observaciones']);
    $stmt->bindParam(':id', $id);
    
    return $stmt->execute();
}

// Eliminar paciente
public function eliminar($id) {
    $query = "DELETE FROM " . $this->table . " WHERE id = :id";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}
public function obtenerHistorialMedico($pacienteId) {
    $query = "SELECT * FROM historial_medico WHERE paciente_id = :paciente_id";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':paciente_id', $pacienteId);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

public function obtenerTratamientos($pacienteId) {
    $query = "SELECT t.*, m.nombre_comercial, m.principio_activo 
              FROM tratamientos t 
              JOIN medicamentos m ON t.medicamento_id = m.id 
              WHERE t.paciente_id = :paciente_id 
              ORDER BY t.fecha_inicio DESC";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':paciente_id', $pacienteId);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

public function obtenerCitas($pacienteId) {
    $query = "SELECT c.*, p.nombres as personal_nombres, p.apellidos as personal_apellidos 
              FROM citas c 
              JOIN personal p ON c.personal_id = p.id 
              WHERE c.paciente_id = :paciente_id 
              ORDER BY c.fecha DESC, c.hora DESC";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':paciente_id', $pacienteId);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

public function obtenerEvoluciones($pacienteId) {
    $query = "SELECT e.*, p.nombres as personal_nombres, p.apellidos as personal_apellidos 
              FROM evoluciones e 
              JOIN personal p ON e.personal_id = p.id 
              WHERE e.paciente_id = :paciente_id 
              ORDER BY e.fecha DESC";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':paciente_id', $pacienteId);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

public function obtenerActividades($pacienteId) {
    $query = "SELECT a.*, p.nombres as personal_nombres, p.apellidos as personal_apellidos 
              FROM actividades_personal a 
              JOIN personal p ON a.personal_id = p.id 
              WHERE a.paciente_id = :paciente_id 
              ORDER BY a.fecha DESC";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':paciente_id', $pacienteId);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
}