<?php
// actions/comment_action.php
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso no autorizado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = (int)($_POST['id_publicacion'] ?? 0);
    $uid = $_SESSION['user_id'];
    $contenido = trim($_POST['contenido'] ?? '');

    if ($pid > 0 && !empty($contenido)) {
        $stmt = $pdo->prepare("INSERT INTO comentario (id_publicacion, id_usuario, contenido) VALUES (?, ?, ?)");
        $stmt->execute([$pid, $uid, $contenido]);
    }
}
header("Location: ../index.php");
exit;