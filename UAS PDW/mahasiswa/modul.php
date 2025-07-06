<?php
session_start();
$pageTitle = 'Modul';
$activePage = 'modul';

// Cek login & role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

require_once 'templates/header_mahasiswa.php';

// Koneksi database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=myklass", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

$praktikumId = $_GET['praktikum_id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$praktikumId) {
    echo "<p class='text-red-600'>Praktikum tidak ditemukan.</p>";
    require_once 'templates/footer_mahasiswa.php';
    exit;
}

// Proses upload laporan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_modul'])) {
    $id_modul = (int) $_POST['id_modul'];

    if (!isset($_FILES['file_laporan']) || $_FILES['file_laporan']['error'] !== UPLOAD_ERR_OK) {
        echo "<p class='text-red-600'>Upload gagal atau file tidak dipilih.</p>";
    } else {
        $file = $_FILES['file_laporan'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx'];

        if (!in_array($ext, $allowed)) {
            echo "<p class='text-red-600'>Format file tidak valid. Hanya PDF, DOC, atau DOCX.</p>";
        } else {
            $fileName = uniqid("laporan_") . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/laporan/';
            $uploadPath = $uploadDir . $fileName;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $cek = $pdo->prepare("SELECT COUNT(*) FROM laporan WHERE id_mahasiswa = ? AND id_modul = ?");
                $cek->execute([$userId, $id_modul]);

                if ($cek->fetchColumn() == 0) {
                    $stmt = $pdo->prepare("INSERT INTO laporan (id_mahasiswa, id_modul, file_laporan) VALUES (?, ?, ?)");
                    $stmt->execute([$userId, $id_modul, $fileName]);
                    header("Location: modul.php?praktikum_id=$praktikumId");
                    exit;
                } else {
                    echo "<p class='text-yellow-600'>Laporan sudah pernah dikumpulkan sebelumnya.</p>";
                }
            } else {
                echo "<p class='text-red-600'>Gagal menyimpan file ke server.</p>";
            }
        }
    }
}

// Ambil data modul
$stmt = $pdo->prepare("SELECT * FROM modul WHERE id_praktikum = ?");
$stmt->execute([$praktikumId]);
$modulList = $stmt->fetchAll();

// Ambil laporan yang sudah dikumpulkan mahasiswa
$stmt = $pdo->prepare("SELECT * FROM laporan WHERE id_mahasiswa = ?");
$stmt->execute([$userId]);
$laporanSaya = [];
foreach ($stmt as $l) {
    $laporanSaya[$l['id_modul']] = $l;
}
?>

<!-- TAMPILAN -->
<div class="p-6 rounded-lg min-h-screen bg-gradient-to-br from-[#0a0f1a] via-[#101d2c] to-[#192b3f] text-white">
    <h2 class="text-2xl font-bold text-cyan-300 mb-6">📘 Daftar Modul Praktikum</h2>

    <?php foreach ($modulList as $modul): ?>
        <div class="mb-6 bg-[#131825] border border-[#2e3b55] rounded-xl p-5 shadow-lg hover:shadow-cyan-500/20 transition">
            <h3 class="text-xl font-bold text-cyan-400"><?= htmlspecialchars($modul['judul']) ?></h3>

            <?php if ($modul['file_materi']): ?>
                <p class="mt-2 text-sm">
                    📥 <a href="../asisten/uploads/<?= htmlspecialchars($modul['file_materi']) ?>" target="_blank" class="text-blue-400 underline hover:text-cyan-300 transition">
                        Unduh Materi
                    </a>
                </p>
            <?php endif; ?>

            <?php if (isset($laporanSaya[$modul['id']])): 
                $lap = $laporanSaya[$modul['id']];
            ?>
                <div class="mt-4 bg-green-900/30 border border-green-500/50 p-4 rounded-md shadow-md">
                    <p>✅ <span class="text-green-300">Laporan dikumpulkan:</span>
                        <a href="../uploads/laporan/<?= htmlspecialchars($lap['file_laporan']) ?>" target="_blank" class="text-blue-400 underline">
                            <?= htmlspecialchars($lap['file_laporan']) ?>
                        </a>
                    </p>
                    <p>📝 <span class="text-gray-300">Nilai:</span> <?= is_null($lap['nilai']) ? '<span class="italic text-yellow-300">Belum Dinilai</span>' : $lap['nilai'] ?></p>
                    <p>💬 <span class="text-gray-300">Feedback:</span> <?= $lap['feedback'] ?: '<span class="italic text-gray-400">-</span>' ?></p>
                </div>
            <?php else: ?>
                <form method="POST" enctype="multipart/form-data" class="mt-4 space-y-2">
                    <input type="hidden" name="id_modul" value="<?= $modul['id'] ?>">
                    <input type="file" name="file_laporan" required class="bg-[#1e2a3c] border border-gray-600 p-2 w-full rounded text-white file:bg-blue-600 file:text-white file:border-none file:rounded file:px-3 file:py-1 hover:file:bg-blue-700 transition">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded transition">
                        🚀 Kumpulkan Laporan
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>


<?php require_once 'templates/footer_mahasiswa.php'; ?>
