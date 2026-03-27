<?php
try {
    $pdo = new PDO('mysql:host=gondola.proxy.rlwy.net;port=57921;dbname=railway', 'root', 'CKqCxsgSedvhPNmdpeCIpnjUXPuhgtig');
    echo "Conexión exitosa";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}