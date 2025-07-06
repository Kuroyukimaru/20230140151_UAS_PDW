<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$pageTitle = $pageTitle ?? 'Dashboard';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Mahasiswa - <?= htmlspecialchars($pageTitle); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-[#0a0f1a] via-[#101d2c] to-[#192b3f] text-white font-sans min-h-screen">

<!-- Navbar luar angkasa -->
<nav class="bg-[#0f172a] shadow-md border-b border-blue-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo dan menu -->
            <div class="flex items-center space-x-6">
    <span class="text-cyan-400 text-2xl font-bold tracking-wide">MyKlass</span>
    <?php 
        $activeClass = 'text-cyan-300 border-b-2 border-cyan-400';
        $inactiveClass = 'text-gray-300 hover:text-cyan-300 hover:border-cyan-400';
    ?>
    <a href="dashboard.php" class="<?= ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?> px-3 py-2 text-sm font-medium">Dashboard</a>
    <a href="praktikum_saya.php" class="<?= ($activePage == 'praktikum_saya') ? $activeClass : $inactiveClass; ?> px-3 py-2 text-sm font-medium">Praktikum Saya</a>
    <a href="katalog_praktikum.php" class="<?= ($activePage == 'katalog_praktikum') ? $activeClass : $inactiveClass; ?> px-3 py-2 text-sm font-medium">Cari Praktikum</a>
</div>

            

            <!-- Logout -->
            <div>
                <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded-md transition">
                    Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Konten utama -->
<div class="max-w-7xl mx-auto px-4 py-8">
