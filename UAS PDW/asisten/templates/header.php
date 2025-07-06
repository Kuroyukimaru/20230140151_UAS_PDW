<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?> - MyKlass</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Bintang latar belakang */
        .bg-stars {
            background-image: url('https://www.transparenttextures.com/patterns/stardust.png');
            background-repeat: repeat;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#050510] via-[#0a0f1a] to-[#101d2c] text-white bg-stars">

<div class="flex min-h-screen">
    <!-- Sidebar Luar Angkasa -->
    <aside class="w-64 bg-[#0d111c] text-white flex flex-col shadow-lg border-r border-[#1a1f2c]">
        <div class="p-6 text-center border-b border-[#1a1f2c]">
            <h3 class="text-xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-500">Panel Asisten</h3>
            <p class="text-sm text-gray-400 mt-1"><?php echo htmlspecialchars($_SESSION['nama']); ?></p>
        </div>
        <nav class="flex-grow">
            <ul class="space-y-2 p-4">
                <?php 
                    $activeClass = 'bg-gradient-to-r from-cyan-500 to-purple-600 text-white font-semibold shadow shadow-cyan-500/20';
                    $inactiveClass = 'text-gray-300 hover:bg-[#1a1f2f] hover:text-white transition duration-200';
                ?>
                <li>
                    <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-md">
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="mata_praktikum.php" class="<?php echo ($activePage == 'mata_praktikum') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-md">
                        <span>Mata Praktikum</span>
                    </a>
                </li>
                <li>
                    <a href="modul.php" class="<?php echo ($activePage == 'modul') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-md">
                        <span>Manajemen Modul</span>
                    </a>
                </li>
                <li>
                    <a href="laporan.php" class="<?php echo ($activePage == 'laporan') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-md">
                        <span>Laporan Masuk</span>
                    </a>
                </li>
                <li>
                    <a href="akun.php" class="<?php echo ($activePage == 'akun') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-md">
                        <span>Akun Pengguna</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-6 lg:p-10">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-purple-500">
                <?php echo $pageTitle; ?>
            </h1>
            <a href="../logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow-lg">
                Logout
            </a>
        </header>
