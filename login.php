<?php
// login.php
require_once 'includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT u.id_usuario, u.password_hash, u.rol, p.nombre, p.apellido 
                                FROM usuario u 
                                LEFT JOIN perfil p ON u.id_usuario = p.id_usuario 
                                WHERE u.email_inst = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Para contraseñas de prueba iniciales ($2y$10$tZ2EknI4l8gVmsmPqQ77rOJ04mS35v3ZqWfhz7K2Ceqe7s/0gI2Xm es "123456")
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_name'] = $user['nombre'] ?? 'Estudiante';
            $_SESSION['user_role'] = $user['rol'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Credenciales inválidas o correo no registrado.';
        }
    } else {
        $error = 'Por favor complete todos los campos.';
    }
}
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-md border border-slate-200 mt-10">
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-ecciBlue">Ingreso al Campus</h2>
        <p class="text-sm text-slate-500">Conéctate con tu comunidad académica</p>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-50 text-red-700 text-sm p-3 rounded-lg border border-red-200 mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="space-y-4">
        <div>
            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Correo Institucional</label>
            <input type="email" name="email" required placeholder="ejemplo@ecci.edu.co" 
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ecciBlue text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Contraseña</label>
            <input type="password" name="password" required placeholder="******" 
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ecciBlue text-sm">
        </div>
        <button type="submit" class="w-full bg-ecciBlue hover:bg-slate-900 text-white font-semibold py-2.5 rounded-lg text-sm transition">Iniciar Sesión</button>
    </form>
    
    <div class="mt-4 text-center text-xs text-slate-500">
        ¿No tienes cuenta? <a href="register.php" class="text-ecciGold font-semibold hover:underline">Regístrate aquí</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>