<?php
// actions/reaction_action.php
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso denegado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['id_publicacion'] ?? 0);
    $uid = $_SESSION['user_id'];
    $tipo = $_POST['tipo'] ?? 'UTIL';

    // Regla de Negocio 3: Si ya existe, actualiza; si no, inserta (ON DUPLICATE KEY UPDATE)
    $stmt = $pdo->prepare("INSERT INTO reaccion (id_usuario, id_publicacion, tipo_reaccion) 
                           VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE tipo_reaccion = VALUES(tipo_reaccion)");
    $stmt->execute([$uid, $pid, $tipo]);
}
header("Location: ../index.php");
exit;