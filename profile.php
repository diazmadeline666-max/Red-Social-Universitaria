<?php
// profile.php
require_once 'includes/header.php';

if (!$is_logged) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];
$msg = '';

// Guardar cambios en perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $carrera = trim($_POST['carrera'] ?? '');
    $semestre = (int)($_POST['semestre'] ?? 1);
    $bio = trim($_POST['bio'] ?? '');

    $stmt = $pdo->prepare("UPDATE perfil SET carrera = ?, semestre = ?, bio = ? WHERE id_usuario = ?");
    $stmt->execute([$carrera, $semestre, $bio, $uid]);
    $msg = 'Perfil actualizado con éxito.';
}

// Asociar nuevo interés
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_interest'])) {
    $id_interes = (int)$_POST['id_interes'];
    $dominio = $_POST['nivel_dominio'];

    try {
        $stmt = $pdo->prepare("INSERT INTO usuario_interes (id_usuario, id_interes, nivel_dominio) VALUES (?, ?, ?)");
        $stmt->execute([$uid, $id_interes, $dominio]);
        $msg = 'Interés añadido exitosamente.';
    } catch (\PDOException $e) {
        $msg = 'Ya tienes este interés registrado en tu perfil.';
    }
}

// Obtener datos actuales del usuario
$stmtP = $pdo->prepare("SELECT p.*, u.email_inst, u.codigo_est, u.rol FROM perfil p JOIN usuario u ON p.id_usuario = u.id_usuario WHERE p.id_usuario = ?");
$stmtP->execute([$uid]);
$perfil = $stmtP->fetch();

// Intereses del usuario
$stmtI = $pdo->prepare("SELECT ui.*, i.nombre, i.categoria FROM usuario_interes ui JOIN interes i ON ui.id_interes = i.id_interes WHERE ui.id_usuario = ?");
$stmtI->execute([$uid]);
$mis_intereses = $stmtI->fetchAll();

// Catálogo completo de intereses
$catalogo = $pdo->query("SELECT * FROM interes ORDER BY categoria, nombre")->fetchAll();
?>

<div class="max-w-4xl mx-auto space-y-6">
    <?php if ($msg): ?>
        <div class="bg-blue-50 border border-blue-200 text-blue-700 text-sm p-3 rounded-lg"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Tarjeta de Información Personal -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <h2 class="text-lg font-bold text-ecciBlue mb-4"><i class="fa-solid fa-user-pen text-ecciGold"></i> Mis Datos Académicos</h2>
            <form method="POST" action="profile.php" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Nombre Completo</label>
                    <input type="text" disabled value="<?= htmlspecialchars($perfil['nombre'] . ' ' . $perfil['apellido']) ?>" class="w-full px-3 py-2 bg-slate-100 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Correo Institucional</label>
                    <input type="text" disabled value="<?= htmlspecialchars($perfil['email_inst']) ?>" class="w-full px-3 py-2 bg-slate-100 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Carrera / Programa</label>
                    <input type="text" name="carrera" value="<?= htmlspecialchars($perfil['carrera']) ?>" required class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Semestre</label>
                    <input type="number" name="semestre" min="0" max="12" value="<?= htmlspecialchars($perfil['semestre']) ?>" required class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Biografía</label>
                    <textarea name="bio" rows="3" class="w-full px-3 py-2 border rounded-lg text-sm"><?= htmlspecialchars($perfil['bio'] ?? '') ?></textarea>
                </div>
                <button type="submit" name="update_profile" class="w-full bg-ecciBlue hover:bg-slate-900 text-white font-semibold py-2 rounded-lg text-xs transition">Guardar Cambios</button>
            </form>
        </div>

        <!-- Tarjeta de Intereses (N:M con Nivel de Dominio) -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-ecciBlue"><i class="fa-solid fa-tags text-ecciGold"></i> Mis Intereses y Habilidades</h2>
            
            <form method="POST" action="profile.php" class="p-4 bg-slate-50 rounded-lg space-y-3 border">
                <span class="text-xs font-bold text-slate-700 block">Vincular nuevo interés:</span>
                <select name="id_interes" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                    <?php foreach ($catalogo as $cat): ?>
                        <option value="<?= $cat['id_interes'] ?>"><?= htmlspecialchars($cat['nombre']) ?> (<?= $cat['categoria'] ?>)</option>
                    <?php endforeach; ?>
                </select>
                <select name="nivel_dominio" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                    <option value="BASICO">BÁSICO</option>
                    <option value="INTERMEDIO">INTERMEDIO</option>
                    <option value="AVANZADO">AVANZADO</option>
                </select>
                <button type="submit" name="add_interest" class="w-full bg-slate-800 hover:bg-black text-white text-xs py-2 rounded-lg transition font-medium">Asociar a mi Perfil</button>
            </form>

            <div class="space-y-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Actualmente registrados:</span>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($mis_intereses as $mi): ?>
                        <span class="bg-slate-100 border border-slate-200 px-3 py-1 rounded-full text-xs text-slate-700 flex items-center gap-1.5">
                            <b><?= htmlspecialchars($mi['nombre']) ?></b>
                            <span class="text-[10px] bg-ecciGold text-ecciBlue px-1.5 py-0.5 rounded font-bold"><?= $mi['nivel_dominio'] ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>