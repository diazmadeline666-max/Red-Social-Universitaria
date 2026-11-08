<?php
// audit.php
require_once 'includes/header.php';

if (!$is_logged) {
    header("Location: login.php");
    exit;
}

// Consultar la tabla de auditoría alimentada por el Trigger
$logs = $pdo->query("SELECT * FROM auditoria_seguridad ORDER BY fecha DESC LIMIT 50")->fetchAll();
?>

<div class="max-w-4xl mx-auto space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-ecciBlue"><i class="fa-solid fa-shield-halved text-ecciGold"></i> Registro de Auditoría (Triggers)</h1>
            <p class="text-xs text-slate-500">Muestra los registros generados de forma automática por el trigger <code class="font-mono bg-slate-200 px-1 rounded">trg_auditoria_publicacion_delete</code></p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-xs">
            <thead class="bg-slate-50 font-semibold text-slate-600">
                <tr>
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">Tabla</th>
                    <th class="px-4 py-3 text-left">Operación</th>
                    <th class="px-4 py-3 text-left">Registro Afectado</th>
                    <th class="px-4 py-3 text-left">Detalles</th>
                    <th class="px-4 py-3 text-left">Fecha / Hora</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-400">No hay registros de auditoría aún. Elimina una publicación en el Feed para probar el trigger.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono"><?= $l['id_auditoria'] ?></td>
                            <td class="px-4 py-3 font-semibold text-ecciBlue"><?= htmlspecialchars($l['tabla_afectada']) ?></td>
                            <td class="px-4 py-3"><span class="bg-red-100 text-red-700 px-2 py-0.5 rounded font-bold"><?= $l['operacion'] ?></span></td>
                            <td class="px-4 py-3 font-mono"><?= $l['id_registro_afectado'] ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($l['detalles']) ?></td>
                            <td class="px-4 py-3 text-slate-400"><?= $l['fecha'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>