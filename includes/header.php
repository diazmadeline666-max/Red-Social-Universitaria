<?php
// includes/header.php
require_once __DIR__ . '/../config/database.php';
$is_logged = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Social ECCI - Comunidad Universitaria</title>
    <!-- Tailwind CSS CDN para interfaz moderna y responsive -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ecciBlue: '#002B49',
                        ecciGold: '#F4B41A',
                        ecciRed: '#E30613'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col font-sans">
    <!-- Navbar Institucional -->
    <nav class="bg-ecciBlue text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <span class="bg-ecciGold text-ecciBlue font-black text-xl px-2 py-1 rounded shadow">ECCI</span>
                    <a href="index.php" class="font-bold text-lg tracking-wide hover:text-ecciGold transition">Red Social ECCI</a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($is_logged): ?>
                        <a href="index.php" class="hover:text-ecciGold flex items-center gap-1.5 text-sm font-medium"><i class="fa-solid fa-house"></i> Inicio</a>
                        <a href="groups.php" class="hover:text-ecciGold flex items-center gap-1.5 text-sm font-medium"><i class="fa-solid fa-users"></i> Grupos</a>
                        <a href="profile.php" class="hover:text-ecciGold flex items-center gap-1.5 text-sm font-medium"><i class="fa-solid fa-id-badge"></i> Mi Perfil</a>
                        <a href="audit.php" class="hover:text-ecciGold flex items-center gap-1.5 text-sm font-medium"><i class="fa-solid fa-shield-halved"></i> Auditoría</a>
                        <span class="text-xs bg-slate-800 px-2.5 py-1 rounded-full border border-slate-600 text-slate-300"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                        <a href="logout.php" class="text-ecciRed hover:text-red-400 text-sm font-medium ml-2"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
                    <?php else: ?>
                        <a href="login.php" class="hover:text-ecciGold text-sm font-medium">Ingresar</a>
                        <a href="register.php" class="bg-ecciGold text-ecciBlue hover:bg-yellow-400 font-semibold px-4 py-2 rounded-lg text-sm transition">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="flex-grow max-w-6xl mx-auto w-full px-4 sm:px-6 py-6">