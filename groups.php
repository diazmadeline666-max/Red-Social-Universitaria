<?php
// groups.php
require_once 'includes/header.php';

if (!$is_logged) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];
$msg = '';

// Crear grupo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $desc = trim($_POST['descripcion'] ?? '');
    $tipo = $_POST['tipo'] ?? 'PUBLICO';

    if (!empty($nombre)) {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO grupo (id_creador, nombre, descripcion, tipo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$uid, $nombre, $desc, $tipo]);
        $gid = $pdo->lastInsertId();

        // El creador se une automáticamente
        $stmtM = $pdo->prepare("INSERT INTO usuario_grupo (id_usuario, id_grupo) VALUES (?, ?)");
        $stmtM->execute([$uid, $gid]);
        $pdo->commit();
        $msg = 'Grupo creado exitosamente.';
    }
}

// Unirse o salir de grupo
if (isset($_GET['join'])) {
    $gid = (int)$_GET['join'];
    try {
        $stmt = $pdo->prepare("INSERT INTO usuario_grupo (id_usuario, id_grupo) VALUES (?, ?)");
        $stmt->execute([$uid, $gid]);
    } catch (\Exception $e) {}
    header("Location: groups.php");
    exit;
}

if (isset($_GET['leave'])) {
    $gid = (int)$_GET['leave'];
    $stmt = $pdo->prepare("DELETE FROM usuario_grupo WHERE id_usuario = ? AND id_grupo = ?");
    $stmt->execute([$uid, $gid]);
    header("Location: groups.php");
    exit;
}

// Listar grupos y verificar si es miembro
$grupos = $pdo->query("SELECT g.*, perf.nombre AS creador_nombre, 
                       (SELECT COUNT(*) FROM usuario_grupo ug WHERE ug.id_grupo = g.id_grupo) AS total_miembros,
                       (SELECT COUNT(*) FROM usuario_grupo ug WHERE ug.id_grupo = g.id_grupo AND ug.id_usuario = $uid) AS es_miembro
                       FROM grupo g
                       JOIN perfil perf ON g.id_creador = perf.id_usuario
                       ORDER BY g.fecha_creacion DESC")->fetchAll();
?>

<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-ecciBlue">Grupos y Comunidades</h1>
            <p class="text-xs text-slate-500">Espacios de colaboración académica y deportiva</p>
        </div>
    </div>

    <!-- Formulario para crear comunidad -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="font-bold text-sm text-slate-800 mb-3"><i class="fa-solid fa-plus-circle text-ecciGold"></i> Crear un Nuevo Grupo</h3>
        <form method="POST" action="groups.php" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="nombre" placeholder="Nombre del Grupo" required class="px-3 py-2 border rounded-lg text-xs md:col-span-1">
            <input type="text" name="descripcion" placeholder="Descripción breve" required class="px-3 py-2 border rounded-lg text-xs md:col-span-2">
            <div class="flex gap-2 md:col-span-1">
                <select name="tipo" class="px-2 py-2 border rounded-lg text-xs bg-slate-50 flex-grow">
                    <option value="PUBLICO">Público</option>
                    <option value="PRIVADO">Privado</option>
                </select>
                <button type="submit" name="create_group" class="bg-ecciBlue text-white font-medium px-4 py-2 rounded-lg text-xs">Crear</button>
            </div>
        </form>
    </div>

    <!-- Rejilla de Grupos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($grupos as $g): ?>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between space-y-3">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded <?= $g['tipo'] === 'PUBLICO' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>">
                            <?= $g['tipo'] ?>
                        </span>
                        <span class="text-xs text-slate-400"><i class="fa-solid fa-users"></i> <?= $g['total_miembros'] ?> miembros</span>
                    </div>
                    <h3 class="font-bold text-slate-800 text-base"><?= htmlspecialchars($g['nombre']) ?></h3>
                    <p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($g['descripcion']) ?></p>
                </div>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Por: <?= htmlspecialchars($g['creador_nombre']) ?></span>
                    <?php if ($g['es_miembro']): ?>
                        <a href="groups.php?leave=<?= $g['id_grupo'] ?>" class="text-red-500 font-semibold hover:underline">Abandonar</a>
                    <?php else: ?>
                        <a href="groups.php?join=<?= $g['id_grupo'] ?>" class="text-ecciBlue font-semibold hover:underline">Unirme</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>