<?php
session_start();
$pageTitle = "Beri Nilai";

// Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit;
}

// Validasi ID laporan
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID laporan tidak valid.");
}

$idLaporan = (int) $_GET['id'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=myklass", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ambil data laporan
    $stmt = $pdo->prepare("SELECT laporan.*, users.nama AS nama_mahasiswa, modul.judul AS judul_modul 
                           FROM laporan
                           JOIN users ON laporan.id_mahasiswa = users.id
                           JOIN modul ON laporan.id_modul = modul.id
                           WHERE laporan.id = ?");
    $stmt->execute([$idLaporan]);
    $laporan = $stmt->fetch();

    if (!$laporan) {
        die("Laporan tidak ditemukan.");
    }

    // Simpan nilai
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nilai = $_POST['nilai'];
        $feedback = trim($_POST['feedback']);

        if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
            $error = "Nilai harus berupa angka antara 0-100.";
        } else {
            $stmt = $pdo->prepare("UPDATE laporan SET nilai = ?, feedback = ?, status = 'Sudah Dinilai' WHERE id = ?");
            $stmt->execute([$nilai, $feedback, $idLaporan]);
            $success = "✅ Nilai berhasil disimpan.";

            // Refresh data
            $stmt = $pdo->prepare("SELECT laporan.*, users.nama AS nama_mahasiswa, modul.judul AS judul_modul 
                                   FROM laporan
                                   JOIN users ON laporan.id_mahasiswa = users.id
                                   JOIN modul ON laporan.id_modul = modul.id
                                   WHERE laporan.id = ?");
            $stmt->execute([$idLaporan]);
            $laporan = $stmt->fetch();
        }
    }

} catch (PDOException $e) {
    die("Koneksi atau query gagal: " . $e->getMessage());
}

require_once 'templates/header.php';
?>

<div class="bg-[#121827] text-white p-6 rounded-xl shadow-md shadow-indigo-500/10 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold mb-4 text-cyan-400">Beri Nilai Laporan</h2>

    <?php if (isset($success)): ?>
        <div class="bg-green-900 text-green-300 border border-green-600 p-3 rounded mb-4">
            <?= $success ?>
        </div>
    <?php elseif (isset($error)): ?>
        <div class="bg-red-900 text-red-300 border border-red-600 p-3 rounded mb-4">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <p class="mb-2"><strong>Nama Mahasiswa:</strong> <?= htmlspecialchars($laporan['nama_mahasiswa']) ?></p>
    <p class="mb-2"><strong>Modul:</strong> <?= htmlspecialchars($laporan['judul_modul']) ?></p>
    <p class="mb-4"><strong>File Laporan:</strong>
        <a href="../uploads/laporan/<?= htmlspecialchars($laporan['file_laporan']) ?>" class="text-blue-400 underline hover:text-blue-300" target="_blank">
            Unduh Laporan
        </a>
    </p>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-sm text-cyan-300">Nilai (0-100):</label>
            <input type="number" name="nilai" min="0" max="100" required
                   value="<?= htmlspecialchars($laporan['nilai'] ?? '') ?>"
                   class="w-full p-2 bg-[#1e2636] border border-cyan-400/20 rounded text-white focus:outline-none focus:ring focus:ring-cyan-600">
        </div>
        <div>
            <label class="block text-sm text-cyan-300">Feedback:</label>
            <textarea name="feedback" rows="4" class="w-full p-2 bg-[#1e2636] border border-cyan-400/20 rounded text-white focus:outline-none focus:ring focus:ring-indigo-500"><?= htmlspecialchars($laporan['feedback'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="bg-gradient-to-r from-cyan-500 to-indigo-600 hover:opacity-90 text-white px-4 py-2 rounded shadow">
            Simpan Nilai
        </button>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>
