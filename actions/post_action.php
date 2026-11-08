<?php
// actions/post_action.php
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Acceso denegado");
}

$uid = $_SESSION['user_id'];

// CREAR PUBLICACIÓN usando el Stored Procedure con Transacción (COMMIT / ROLLBACK)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenido = trim($_POST['contenido'] ?? '');
    $id_grupo = !empty($_POST['id_grupo']) ? (int)$_POST['id_grupo'] : null;
    $id_interes = !empty($_POST['id_interes']) ? (int)$_POST['id_interes'] : null;

    if (!empty($contenido)) {
        try {
            $stmt = $pdo->prepare("CALL sp_crear_publicacion_con_comentario(?, ?, ?, ?, NULL, @resultado)");
            $stmt->execute([$uid, $id_grupo, $id_interes, $contenido]);
            header("Location: ../index.php");
            exit;
        } catch (\PDOException $e) {
            die("Error ejecutando SP: " . $e->getMessage());
        }
    }
}

// ELIMINAR PUBLICACIÓN (Disparará el TRIGGER trg_auditoria_publicacion_delete)
if (isset($_GET['delete'])) {
    $pid = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM publicacion WHERE id_publicacion = ? AND (id_usuario = ? OR ? = 'ADMINISTRADOR')");
    $stmt->execute([$pid, $uid, $_SESSION['user_role']]);
    header("Location: ../index.php");
    exit;
}