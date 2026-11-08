<?php
// register.php
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo_est'] ?? '');
    $email = trim($_POST['email_inst'] ?? '');
    $pass = $_POST['password'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $carrera = trim($_POST['carrera'] ?? '');
    $semestre = (int)($_POST['semestre'] ?? 1);

    // Validación institucional estricta (Regla de negocio 1)
    if (!str_ends_with($email, '@ecci.edu.co')) {
        $error = 'El correo debe ser institucional (@ecci.edu.co)';
    } elseif (empty($codigo) || empty($pass) || empty($nombre)) {
        $error = 'Todos los campos marcados son obligatorios.';
    } else {
        try {
            // Transacción para registrar cuenta + perfil
            $pdo->beginTransaction();

            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO usuario (codigo_est, email_inst, password_hash, rol) VALUES (?, ?, ?, 'ESTUDIANTE')");
            $stmt->execute([$codigo, $email, $hash]);
            $id_usuario = $pdo->lastInsertId();

            $stmtPerfil = $pdo->prepare("INSERT INTO perfil (id_usuario, nombre, apellido, carrera, semestre, bio) VALUES (?, ?, ?, ?, ?, '¡Hola! Soy nuevo en la red social.')");
            $stmtPerfil->execute([$id_usuario, $nombre, $apellido, $carrera, $semestre]);

            $pdo->commit();
            $success = 'Cuenta creada satisfactoriamente. Ya puedes iniciar sesión.';
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $error = 'Error en el registro: Correo o código ya existente.';
        }
    }
}
?>

<div class="max-w-lg mx-auto bg-white p-8 rounded-xl shadow-md border border-slate-200 mt-6">
    <h2 class="text-2xl font-bold text-ecciBlue text-center mb-2">Registro Institucional</h2>
    <p class="text-sm text-slate-500 text-center mb-6">Plataforma Oficial de Comunidad ECCI</p>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-700 text-sm p-3 rounded-lg border border-red-200 mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="bg-green-50 text-green-700 text-sm p-3 rounded-lg border border-green-200 mb-4">
            <?= htmlspecialchars($success) ?> <a href="login.php" class="font-bold underline ml-1">Ingresar</a>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nombre</label>
                <input type="text" name="nombre" required class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Apellido</label>
                <input type="text" name="apellido" required class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Código Estudiantil</label>
                <input type="text" name="codigo_est" required placeholder="Ej. 138000" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Semestre (0 a 12)</label>
                <input type="number" min="0" max="12" name="semestre" value="1" required class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Carrera / Programa</label>
            <input type="text" name="carrera" placeholder="Ej. Ingeniería de Sistemas" required class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Correo Institucional</label>
            <input type="email" name="email_inst" placeholder="tu.nombre@ecci.edu.co" required class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Contraseña</label>
            <input type="password" name="password" required class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>

        <button type="submit" class="w-full bg-ecciBlue hover:bg-slate-900 text-white font-semibold py-2.5 rounded-lg text-sm transition">Crear Cuenta</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>    