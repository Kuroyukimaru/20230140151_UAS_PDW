<?php
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan';
require_once 'templates/header.php';

// Koneksi ke database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=myklass", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Filter
$modulFilter = $_GET['modul'] ?? '';
$mahasiswaFilter = $_GET['mahasiswa'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Ambil data laporan
$query = "SELECT laporan.*, users.nama AS nama_mahasiswa, modul.judul AS nama_modul
          FROM laporan
          JOIN users ON laporan.id_mahasiswa = users.id
          JOIN modul ON laporan.id_modul = modul.id
          WHERE users.role = 'mahasiswa'";
$params = [];

if ($modulFilter !== '') {
    $query .= " AND laporan.id_modul = ?";
    $params[] = $modulFilter;
}
if ($mahasiswaFilter !== '') {
    $query .= " AND laporan.id_mahasiswa = ?";
    $params[] = $mahasiswaFilter;
}
if ($statusFilter !== '') {
    $query .= " AND laporan.status = ?";
    $params[] = $statusFilter;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$laporan = $stmt->fetchAll();

// Dropdown data
$modul = $pdo->query("SELECT id, judul FROM modul")->fetchAll();
$mahasiswa = $pdo->query("SELECT id, nama FROM users WHERE role = 'mahasiswa'")->fetchAll();
?>

<div class="bg-[#121827] text-white p-6 rounded-xl shadow-lg shadow-cyan-500/10">
    <h2 class="text-2xl font-bold mb-6 text-cyan-400">Laporan Masuk</h2>

    <!-- Filter -->
    <form method="GET" class="flex flex-wrap gap-4 mb-6">
        <select name="modul" class="bg-[#1e2636] text-white border border-cyan-500/20 rounded px-3 py-2">
            <option value="">Semua Modul</option>
            <?php foreach ($modul as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $modulFilter == $m['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['judul']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="mahasiswa" class="bg-[#1e2636] text-white border border-cyan-500/20 rounded px-3 py-2">
            <option value="">Semua Mahasiswa</option>
            <?php foreach ($mahasiswa as $mh): ?>
                <option value="<?= $mh['id'] ?>" <?= $mahasiswaFilter == $mh['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($mh['nama']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status" class="bg-[#1e2636] text-white border border-cyan-500/20 rounded px-3 py-2">
            <option value="">Semua Status</option>
            <option value="Belum Dinilai" <?= $statusFilter == 'Belum Dinilai' ? 'selected' : '' ?>>Belum Dinilai</option>
            <option value="Sudah Dinilai" <?= $statusFilter == 'Sudah Dinilai' ? 'selected' : '' ?>>Sudah Dinilai</option>
        </select>

        <button type="submit" class="bg-gradient-to-r from-cyan-500 to-indigo-600 hover:opacity-90 text-white px-4 py-2 rounded shadow">
            Filter
        </button>
    </form>

    <!-- Tabel -->
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border border-cyan-500/20">
            <thead class="bg-[#1e2636] text-cyan-300">
                <tr>
                    <th class="px-4 py-2 border border-cyan-500/10">#</th>
                    <th class="px-4 py-2 border border-cyan-500/10">Mahasiswa</th>
                    <th class="px-4 py-2 border border-cyan-500/10">Modul</th>
                    <th class="px-4 py-2 border border-cyan-500/10">File</th>
                    <th class="px-4 py-2 border border-cyan-500/10">Nilai</th>
                    <th class="px-4 py-2 border border-cyan-500/10">Status</th>
                    <th class="px-4 py-2 border border-cyan-500/10">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($laporan) > 0): ?>
                    <?php foreach ($laporan as $index => $lap): ?>
                        <tr class="hover:bg-[#1a1f2f]">
                            <td class="px-4 py-2 border border-cyan-500/10"><?= $index + 1 ?></td>
                            <td class="px-4 py-2 border border-cyan-500/10"><?= htmlspecialchars($lap['nama_mahasiswa']) ?></td>
                            <td class="px-4 py-2 border border-cyan-500/10"><?= htmlspecialchars($lap['nama_modul']) ?></td>
                            <td class="px-4 py-2 border border-cyan-500/10">
                                <a href="../uploads/laporan/<?= htmlspecialchars($lap['file_laporan']) ?>" target="_blank" class="text-blue-400 hover:underline">Unduh</a>
                            </td>
                            <td class="px-4 py-2 border border-cyan-500/10 text-center">
                                <?= is_null($lap['nilai']) ? '-' : htmlspecialchars($lap['nilai']) ?>
                            </td>
                            <td class="px-4 py-2 border border-cyan-500/10 text-center">
                                <span class="<?= $lap['status'] === 'Sudah Dinilai' ? 'text-green-400' : 'text-yellow-300' ?>">
                                    <?= htmlspecialchars($lap['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-2 border border-cyan-500/10 text-sm">
                                <a href="beri_nilai.php?id=<?= $lap['id'] ?>" class="text-indigo-400 hover:underline">Beri Nilai</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-400">Tidak ada data laporan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
