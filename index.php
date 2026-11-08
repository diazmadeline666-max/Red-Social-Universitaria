<?php
// index.php
require_once 'includes/header.php';

if (!$is_logged) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// Obtener intereses y grupos para los selectores
$intereses = $pdo->query("SELECT * FROM interes ORDER BY nombre ASC")->fetchAll();
$grupos = $pdo->query("SELECT * FROM grupo ORDER BY nombre ASC")->fetchAll();

// Consulta principal con JOINS: publicaciones, autor, grupo, total de reacciones (usando la función SQL)
$query = "SELECT p.*, perf.nombre, perf.apellido, perf.carrera, 
                 g.nombre AS grupo_nombre, i.nombre AS interes_nombre,
                 fn_total_reacciones(p.id_publicacion) AS total_reacciones
          FROM publicacion p
          JOIN perfil perf ON p.id_usuario = perf.id_usuario
          LEFT JOIN grupo g ON p.id_grupo = g.id_grupo
          LEFT JOIN interes i ON p.id_interes = i.id_interes
          ORDER BY p.fecha_creacion DESC";
$publicaciones = $pdo->query($query)->fetchAll();
?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <!-- Barra Lateral Izquierda: Perfil Resumido -->
    <div class="md:col-span-1">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm sticky top-20">
            <div class="w-16 h-16 bg-ecciBlue text-white rounded-full flex items-center justify-center font-bold text-2xl mx-auto mb-3 shadow">
                <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
            </div>
            <h3 class="text-center font-bold text-slate-800"><?= htmlspecialchars($_SESSION['user_name']) ?></h3>
            <p class="text-center text-xs text-slate-500 mb-3"><?= htmlspecialchars($_SESSION['user_role']) ?></p>
            <div class="border-t border-slate-100 pt-3 text-xs space-y-2 text-slate-600">
                <p><i class="fa-solid fa-graduation-cap text-ecciGold mr-1"></i> Comunidad ECCI</p>
                <p><i class="fa-solid fa-lock text-green-600 mr-1"></i> Sesión Segura (PDO)</p>
            </div>
        </div>
    </div>

    <!-- Feed Central -->
    <div class="md:col-span-3 space-y-6">
        <!-- Tarjeta de Creación de Publicación -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <h2 class="text-base font-bold text-ecciBlue mb-3 flex items-center gap-2">
                <i class="fa-solid fa-pen text-ecciGold"></i> ¿Qué quieres compartir hoy?
            </h2>
            <form action="actions/post_action.php" method="POST" class="space-y-3">
                <textarea name="contenido" rows="3" required placeholder="Escribe tu consulta académica, proyecto o debate..." 
                          class="w-full p-3 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-ecciBlue outline-none"></textarea>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <select name="id_grupo" class="px-3 py-2 border border-slate-200 rounded-lg text-xs bg-slate-50">
                        <option value="">-- Sin Grupo (Muro General) --</option>
                        <?php foreach ($grupos as $g): ?>
                            <option value="<?= $g['id_grupo'] ?>"><?= htmlspecialchars($g['nombre']) ?> (<?= $g['tipo'] ?>)</option>
                        <?php endforeach; ?>
                    </select>

                    <select name="id_interes" class="px-3 py-2 border border-slate-200 rounded-lg text-xs bg-slate-50">
                        <option value="">-- Asociar Interés Temático --</option>
                        <?php foreach ($intereses as $it): ?>
                            <option value="<?= $it['id_interes'] ?>"><?= htmlspecialchars($it['nombre']) ?> [<?= $it['categoria'] ?>]</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-xs text-slate-400">Máx. 1000 caracteres</span>
                    <button type="submit" name="action" value="create_sp" 
                            class="bg-ecciBlue hover:bg-slate-900 text-white font-medium px-5 py-2 rounded-lg text-xs transition flex items-center gap-1.5 shadow">
                        <i class="fa-solid fa-paper-plane"></i> Publicar (vía Procedimiento)
                    </button>
                </div>
            </form>
        </div>

        <!-- Listado de Publicaciones -->
        <?php foreach ($publicaciones as $pub): ?>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($pub['nombre'] . ' ' . $pub['apellido']) ?></span>
                        <span class="text-xs text-slate-400 ml-2"><?= htmlspecialchars($pub['carrera']) ?> &bull; <?= $pub['fecha_creacion'] ?></span>
                    </div>
                    <?php if ($pub['id_usuario'] == $uid || $_SESSION['user_role'] === 'ADMINISTRADOR'): ?>
                        <a href="actions/post_action.php?delete=<?= $pub['id_publicacion'] ?>" 
                           onclick="return confirm('¿Seguro que deseas eliminar esta publicación? Activará el trigger de auditoría.')"
                           class="text-xs text-red-500 hover:underline"><i class="fa-solid fa-trash"></i></a>
                    <?php endif; ?>
                </div>

                <p class="text-slate-700 text-sm whitespace-pre-line"><?= htmlspecialchars($pub['contenido']) ?></p>

                <div class="flex items-center gap-2 pt-1">
                    <?php if ($pub['grupo_nombre']): ?>
                        <span class="bg-blue-50 text-blue-700 text-xs px-2.5 py-0.5 rounded-full font-medium"><i class="fa-solid fa-users text-xs mr-1"></i><?= htmlspecialchars($pub['grupo_nombre']) ?></span>
                    <?php endif; ?>
                    <?php if ($pub['interes_nombre']): ?>
                        <span class="bg-amber-50 text-amber-700 text-xs px-2.5 py-0.5 rounded-full font-medium"><i class="fa-solid fa-tag text-xs mr-1"></i><?= htmlspecialchars($pub['interes_nombre']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Barra de Reacciones -->
                <div class="border-y border-slate-100 py-2 flex items-center justify-between text-xs text-slate-600">
                    <span><i class="fa-solid fa-thumbs-up text-ecciGold"></i> <?= $pub['total_reacciones'] ?> Reacciones</span>
                    <div class="flex gap-2">
                        <form action="actions/reaction_action.php" method="POST" class="inline">
                            <input type="hidden" name="id_publicacion" value="<?= $pub['id_publicacion'] ?>">
                            <button type="submit" name="tipo" value="ME_INTERESA" class="hover:text-ecciBlue font-semibold px-2 py-1 rounded bg-slate-50 border">Me Interesa</button>
                            <button type="submit" name="tipo" value="UTIL" class="hover:text-ecciBlue font-semibold px-2 py-1 rounded bg-slate-50 border">Útil</button>
                            <button type="submit" name="tipo" value="ME_SUMO" class="hover:text-ecciBlue font-semibold px-2 py-1 rounded bg-slate-50 border">Me Sumo</button>
                        </form>
                    </div>
                </div>

                <!-- Comentarios -->
                <div class="space-y-2 pt-2">
                    <?php
                    $stmtC = $pdo->prepare("SELECT c.*, p.nombre, p.apellido FROM comentario c JOIN perfil p ON c.id_usuario = p.id_usuario WHERE c.id_publicacion = ? ORDER BY c.fecha_comentario ASC");
                    $stmtC->execute([$pub['id_publicacion']]);
                    $comentarios = $stmtC->fetchAll();
                    ?>
                    <?php foreach ($comentarios as $c): ?>
                        <div class="bg-slate-50 p-2.5 rounded-lg text-xs">
                            <span class="font-bold text-slate-800"><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellido']) ?>:</span>
                            <span class="text-slate-600 ml-1"><?= htmlspecialchars($c['contenido']) ?></span>
                        </div>
                    <?php endforeach; ?>

                    <form action="actions/comment_action.php" method="POST" class="flex gap-2 pt-1">
                        <input type="hidden" name="id_publicacion" value="<?= $pub['id_publicacion'] ?>">
                        <input type="text" name="contenido" required placeholder="Escribe una respuesta..." class="flex-grow px-3 py-1.5 border rounded-lg text-xs outline-none focus:border-ecciBlue">
                        <button type="submit" class="bg-slate-800 hover:bg-black text-white text-xs px-3 py-1.5 rounded-lg transition font-medium">Comentar</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>