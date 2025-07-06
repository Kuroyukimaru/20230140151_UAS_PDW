<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Dashboard';
$activePage = 'dashboard';

require_once 'templates/header_mahasiswa.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=myklass", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userId = $_SESSION['user_id'];

    // Jumlah praktikum diikuti
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM daftar_praktikum WHERE user_id = ?");
    $stmt->execute([$userId]);
    $jumlahPraktikum = $stmt->fetchColumn();

    // Jumlah tugas selesai
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM laporan WHERE id_mahasiswa = ? AND status = 'Sudah Dinilai'");
    $stmt->execute([$userId]);
    $jumlahSelesai = $stmt->fetchColumn();

    // Jumlah tugas menunggu
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM laporan WHERE id_mahasiswa = ? AND status = 'Belum Dinilai'");
    $stmt->execute([$userId]);
    $jumlahMenunggu = $stmt->fetchColumn();

    // Notifikasi laporan terbaru
    $stmt = $pdo->prepare("
        SELECT l.*, m.judul AS nama_modul
        FROM laporan l
        JOIN modul m ON l.id_modul = m.id
        WHERE l.id_mahasiswa = ?
        ORDER BY l.tanggal_upload DESC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $notifikasi = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>

<!-- Tema Luar Angkasa -->
<div class="p-6 space-y-6 text-white bg-gradient-to-br from-[#0a0f1a] via-[#101d2c] to-[#192b3f] min-h-screen rounded-b-2xl">

    <!-- Header Selamat Datang -->
    <div class="bg-[#131825] p-6 rounded-xl shadow-lg">
        <h1 class="text-3xl font-bold text-cyan-400">Selamat Datang Kembali, <?= htmlspecialchars($_SESSION['nama']); ?>!</h1>
        <p class="mt-2 text-gray-400">Tetap semangat menyelesaikan modul praktikummu 🚀</p>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-[#131825] p-6 rounded-xl shadow-lg text-center">
            <div class="text-5xl font-extrabold text-blue-400"><?= $jumlahPraktikum ?></div>
            <div class="mt-2 text-gray-300">Praktikum Diikuti</div>
        </div>
        <div class="bg-[#131825] p-6 rounded-xl shadow-lg text-center">
            <div class="text-5xl font-extrabold text-green-400"><?= $jumlahSelesai ?></div>
            <div class="mt-2 text-gray-300">Tugas Selesai</div>
        </div>
        <div class="bg-[#131825] p-6 rounded-xl shadow-lg text-center">
            <div class="text-5xl font-extrabold text-yellow-300"><?= $jumlahMenunggu ?></div>
            <div class="mt-2 text-gray-300">Tugas Menunggu</div>
        </div>
    </div>

    <!-- Notifikasi -->
    <div class="bg-[#131825] p-6 rounded-xl shadow-lg">
        <h3 class="text-2xl font-bold text-cyan-300 mb-4">Notifikasi Terbaru</h3>
        <ul class="space-y-4">
            <?php if (count($notifikasi) > 0): ?>
                <?php foreach ($notifikasi as $n): ?>
                    <li class="flex items-start gap-4 p-3 border-b border-gray-700 last:border-none">
                        <span class="text-xl">
                            <?php if ($n['status'] === 'Sudah Dinilai'): ?>
                                🔔
                            <?php elseif ($n['status'] === 'Belum Dinilai'): ?>
                                ⏳
                            <?php else: ?>
                                📥
                            <?php endif; ?>
                        </span>
                        <div>
                            <?php if ($n['status'] === 'Sudah Dinilai'): ?>
                                Nilai untuk <span class="text-blue-400 font-semibold"><?= htmlspecialchars($n['nama_modul']) ?></span> telah diberikan:
                                <span class="text-green-400 font-semibold"><?= $n['nilai'] ?> 💯</span>
                                <?php if (!empty($n['feedback'])): ?>
                                    <br><span class="text-gray-400 italic">Feedback: <?= htmlspecialchars($n['feedback']) ?></span>
                                <?php endif; ?>
                            <?php elseif ($n['status'] === 'Belum Dinilai'): ?>
                                Laporan untuk <span class="text-yellow-400 font-semibold"><?= htmlspecialchars($n['nama_modul']) ?></span> telah dikumpulkan dan sedang menunggu penilaian.
                            <?php else: ?>
                                Laporan dikumpulkan untuk <span class="text-blue-400 font-semibold"><?= htmlspecialchars($n['nama_modul']) ?></span>.
                            <?php endif; ?>
                            <div class="text-sm text-gray-500">
                                <?= date('d M Y, H:i', strtotime($n['tanggal_upload'])) ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="text-gray-400">Belum ada aktivitas laporan.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
