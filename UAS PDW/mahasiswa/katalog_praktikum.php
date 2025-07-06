<?php
session_start();
$pageTitle = "Katalog Mata Praktikum";
$activePage = "katalog";

// ✅ Cek apakah user sudah login sebagai mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

require_once 'templates/header_mahasiswa.php';

try {
    // 🔌 Koneksi ke database
    $pdo = new PDO("mysql:host=localhost;dbname=myklass", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userId = $_SESSION['user_id']; // 🔁 gunakan 'user_id' sesuai login.php

    // 📨 Proses form pendaftaran praktikum
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['praktikum_id'])) {
        $praktikumId = (int) $_POST['praktikum_id'];

        // Cek apakah sudah terdaftar sebelumnya
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM daftar_praktikum WHERE user_id = ? AND praktikum_id = ?");
        $stmt->execute([$userId, $praktikumId]);

        if ($stmt->fetchColumn() == 0) {
            // Simpan pendaftaran
            $stmt = $pdo->prepare("INSERT INTO daftar_praktikum (user_id, praktikum_id) VALUES (?, ?)");
            $stmt->execute([$userId, $praktikumId]);
            $_SESSION['success'] = "✅ Berhasil mendaftar ke praktikum.";
        } else {
            $_SESSION['info'] = "ℹ️ Kamu sudah terdaftar di praktikum ini.";
        }

        header("Location: katalog_praktikum.php");
        exit;
    }

    // Ambil data praktikum
    $stmt = $pdo->query("SELECT * FROM mata_praktikum ORDER BY nama_praktikum");
    $praktikum = $stmt->fetchAll();

    // Ambil daftar praktikum yang sudah diikuti user
    $stmt = $pdo->prepare("SELECT praktikum_id FROM daftar_praktikum WHERE user_id = ?");
    $stmt->execute([$userId]);
    $terdaftar = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>

<!-- Tampilan Halaman: Katalog Mata Praktikum -->
<div class="p-6 rounded-lg min-h-screen bg-gradient-to-br from-[#0a0f1a] via-[#101d2c] to-[#192b3f] text-white">
    <h2 class="text-2xl font-bold text-cyan-300 mb-6">🪐 Daftar Praktikum</h2>

    <!-- 🔔 Notifikasi -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-800 border border-green-500 text-green-200 px-4 py-3 rounded mb-4 shadow-md">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php elseif (isset($_SESSION['info'])): ?>
        <div class="bg-yellow-800 border border-yellow-500 text-yellow-200 px-4 py-3 rounded mb-4 shadow-md">
            <?= $_SESSION['info']; unset($_SESSION['info']); ?>
        </div>
    <?php endif; ?>

    <!-- 📋 Daftar Praktikum -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($praktikum as $p): ?>
            <div class="bg-[#131825] border border-[#2e3b55] rounded-xl p-5 shadow-lg hover:shadow-blue-600/40 transition">
                <h3 class="text-lg font-bold text-cyan-300"><?= htmlspecialchars($p['nama_praktikum']) ?></h3>
                <p class="text-sm text-gray-400 mt-1">👨‍🏫 Dosen: <?= htmlspecialchars($p['nama_dosen']) ?></p>
                <p class="text-sm text-gray-400">📅 Semester: <?= htmlspecialchars($p['semester']) ?></p>
                <div class="mt-4">
                    <?php if (in_array($p['id'], $terdaftar)): ?>
                        <span class="text-green-400 font-semibold text-sm">✔ Sudah Terdaftar</span>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="praktikum_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition">
                                ✨ Daftar Praktikum
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<?php require_once 'templates/footer_mahasiswa.php'; ?>
