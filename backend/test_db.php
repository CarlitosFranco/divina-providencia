<?php
require_once 'config/Database.php';

$db = new Config\Database();
$conn = $db->getConnection();

if ($conn) {
    echo "✅ Conexión exitosa a la base de datos.";
    $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    $row = $stmt->fetch();
    echo " Usuarios encontrados: " . $row['total'];
} else {
    echo "❌ Error de conexión. Revisa las credenciales en config/Database.php";
}