<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit;
}

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=myklass", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Statistik jumlah
    $totalPraktikum = $pdo->query("SELECT COUNT(*) FROM mata_praktikum")->fetchColumn();
    $totalModul = $pdo->query("SELECT COUNT(*) FROM modul")->fetchColumn();
    $totalLaporan = $pdo->query("SELECT COUNT(*) FROM laporan")->fetchColumn();
    $totalPengguna = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Aktivitas laporan terbaru
    $stmt = $pdo->query("
        SELECT 
            l.tanggal_upload, 
            u.nama AS nama_mahasiswa, 
            m.judul AS nama_modul
        FROM laporan l
        JOIN users u ON l.id_mahasiswa = u.id
        JOIN modul m ON l.id_modul = m.id
        WHERE l.tanggal_upload IS NOT NULL
        ORDER BY l.tanggal_upload DESC
        LIMIT 5
    ");
    $aktivitasLaporan = $stmt->fetchAll();

    // Ambil nama depan asisten dari session
    $namaLengkap = $_SESSION['nama'];
    $namaDepan = explode(' ', $namaLengkap)[0];

} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>

<div class="p-6 space-y-6 text-white bg-gradient-to-br from-[#050510] via-[#0a0f1a] to-[#101d2c] min-h-screen bg-stars">
    <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-cyan-400 to-purple-500">
        Selamat Datang Kembali, Asisten <?= htmlspecialchars($namaDepan) ?>!
    </h1>

    <!-- Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
        <a href="mata_praktikum.php" class="bg-[#1a1f2f]/80 p-6 rounded-xl hover:bg-cyan-900/40 border border-cyan-400/10 transition space-y-2">
            <p class="text-sm text-gray-400">Kelola</p>
            <h3 class="text-lg font-bold text-cyan-400">Mata Praktikum</h3>
            <p class="text-cyan-300 text-xl font-semibold"><?= $totalPraktikum ?> Praktikum</p>
        </a>
        <a href="modul.php" class="bg-[#1a1f2f]/80 p-6 rounded-xl hover:bg-indigo-900/40 border border-indigo-400/10 transition space-y-2">
            <p class="text-sm text-gray-400">Kelola</p>
            <h3 class="text-lg font-bold text-indigo-400">Modul</h3>
            <p class="text-indigo-300 text-xl font-semibold"><?= $totalModul ?> Modul</p>
        </a>
        <a href="laporan.php" class="bg-[#1a1f2f]/80 p-6 rounded-xl hover:bg-pink-900/40 border border-pink-400/10 transition space-y-2">
            <p class="text-sm text-gray-400">Kelola</p>
            <h3 class="text-lg font-bold text-pink-400">Laporan Masuk</h3>
            <p class="text-pink-300 text-xl font-semibold"><?= $totalLaporan ?> Laporan</p>
        </a>
        <a href="akun.php" class="bg-[#1a1f2f]/80 p-6 rounded-xl hover:bg-slate-800/40 border border-gray-400/10 transition space-y-2">
            <p class="text-sm text-gray-400">Kelola</p>
            <h3 class="text-lg font-bold text-gray-300">Akun Pengguna</h3>
            <p class="text-gray-200 text-xl font-semibold"><?= $totalPengguna ?> Pengguna</p>
        </a>
    </div>

    <!-- Aktivitas Laporan Terbaru -->
    <div class="bg-[#121827]/80 p-6 rounded-xl shadow-md backdrop-blur-md border border-white/5 mt-10">
        <h3 class="text-xl font-bold mb-4 text-white">Aktivitas Laporan Terbaru</h3>
        <div class="space-y-4">
            <?php if (count($aktivitasLaporan) > 0): ?>
                <?php foreach ($aktivitasLaporan as $laporan): ?>
                    <?php
                        $nama = $laporan['nama_mahasiswa'];
                        $modul = $laporan['nama_modul'];
                        $waktu = date('d M Y, H:i', strtotime($laporan['tanggal_upload']));
                        $inisial = strtoupper(substr($nama, 0, 1) . (explode(' ', $nama)[1][0] ?? ''));
                    ?>
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-[#1b2332] flex items-center justify-center mr-4">
                            <span class="font-bold text-cyan-300"><?= $inisial ?></span>
                        </div>
                        <div>
                            <p><strong class="text-white"><?= htmlspecialchars($nama) ?></strong> mengumpulkan laporan untuk <strong class="text-blue-400"><?= htmlspecialchars($modul) ?></strong></p>
                            <p class="text-sm text-gray-500"><?= $waktu ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-400">Belum ada laporan dikumpulkan.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
