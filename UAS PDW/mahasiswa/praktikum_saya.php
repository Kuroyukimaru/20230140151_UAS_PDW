<?php
session_start();
$pageTitle = 'Praktikum Saya';
$activePage = 'praktikum_saya';

// Cek apakah user sudah login sebagai mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

require_once 'templates/header_mahasiswa.php';

try {
    // Koneksi ke database
    $pdo = new PDO("mysql:host=localhost;dbname=myklass", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userId = $_SESSION['user_id'];

    // Ambil semua praktikum yang sudah diikuti user
    $stmt = $pdo->prepare("
        SELECT mp.*
        FROM daftar_praktikum dp
        JOIN mata_praktikum mp ON dp.praktikum_id = mp.id
        WHERE dp.user_id = ?
        ORDER BY mp.nama_praktikum
    ");
    $stmt->execute([$userId]);
    $praktikumSaya = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>

<!-- Konten -->
<div class="p-6 rounded-lg min-h-screen bg-gradient-to-br from-[#0a0f1a] via-[#101d2c] to-[#192b3f] text-white">
    <h2 class="text-2xl font-bold text-cyan-300 mb-6">🚀 Praktikum yang Kamu Ikuti</h2>

    <?php if (count($praktikumSaya) > 0): ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($praktikumSaya as $p): ?>
                <div class="bg-[#131825] border border-[#2e3b55] rounded-xl p-5 shadow-lg hover:shadow-cyan-500/20 transition">
                    <h3 class="text-lg font-bold text-cyan-400"><?= htmlspecialchars($p['nama_praktikum']) ?></h3>
                    <p class="text-sm text-gray-400 mt-1">👨‍🏫 Dosen: <?= htmlspecialchars($p['nama_dosen']) ?></p>
                    <p class="text-sm text-gray-400">📅 Semester: <?= htmlspecialchars($p['semester']) ?></p>

                    <!-- Tombol ke halaman detail tugas/modul -->
                    <div class="mt-4">
                        <a href="modul.php?praktikum_id=<?= $p['id'] ?>" class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition">
                            🛰️ Lihat Modul
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-300">🚫 Kamu belum mendaftar ke mata praktikum mana pun.</p>
        <a href="katalog_praktikum.php" class="inline-block mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded transition">
            ✨ Daftar Sekarang
        </a>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
