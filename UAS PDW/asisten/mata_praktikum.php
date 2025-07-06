<?php
$pageTitle = 'Kelola Mata Praktikum';
$activePage = 'mata_praktikum';
require_once 'templates/header.php';

// Koneksi ke database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=myklass", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Simpan (Create / Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $nama = trim($_POST['nama_praktikum']);
    $dosen = trim($_POST['nama_dosen']);
    $semester = trim($_POST['semester']);

    if ($id === '') {
        $stmt = $pdo->prepare("INSERT INTO mata_praktikum (nama_praktikum, nama_dosen, semester) VALUES (?, ?, ?)");
        $stmt->execute([$nama, $dosen, $semester]);
    } else {
        $stmt = $pdo->prepare("UPDATE mata_praktikum SET nama_praktikum = ?, nama_dosen = ?, semester = ? WHERE id = ?");
        $stmt->execute([$nama, $dosen, $semester, $id]);
    }

    header("Location: mata_praktikum.php");
    exit;
}

// Hapus (Delete)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM mata_praktikum WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: mata_praktikum.php?msg=deleted");
    } catch (PDOException $e) {
        // Gagal hapus karena ada foreign key
        header("Location: mata_praktikum.php?error=constraint");
    }
    exit;
}

// Ambil data
$stmt = $pdo->query("SELECT * FROM mata_praktikum ORDER BY id DESC");
$dataPraktikum = $stmt->fetchAll();

$editData = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM mata_praktikum WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
}
?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Form Tambah/Edit -->
    <div class="bg-[#121827] text-white p-6 rounded-xl shadow-lg shadow-cyan-500/10">
        <h2 class="text-xl font-bold mb-4 text-cyan-400"><?= $editData ? 'Edit' : 'Tambah' ?> Mata Praktikum</h2>
        <form method="post" class="space-y-4">
            <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
            <?php endif; ?>
            <div>
                <label class="block text-cyan-300">Nama Praktikum</label>
                <input type="text" name="nama_praktikum" required
                       class="w-full p-2 bg-[#1e2636] text-white border border-cyan-400/20 rounded"
                       value="<?= $editData['nama_praktikum'] ?? '' ?>">
            </div>
            <div>
                <label class="block text-cyan-300">Nama Dosen</label>
                <input type="text" name="nama_dosen" required
                       class="w-full p-2 bg-[#1e2636] text-white border border-cyan-400/20 rounded"
                       value="<?= $editData['nama_dosen'] ?? '' ?>">
            </div>
            <div>
                <label class="block text-cyan-300">Semester</label>
                <input type="text" name="semester" required
                       class="w-full p-2 bg-[#1e2636] text-white border border-cyan-400/20 rounded"
                       value="<?= $editData['semester'] ?? '' ?>">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="bg-gradient-to-r from-cyan-500 to-indigo-600 hover:opacity-90 text-white px-4 py-2 rounded shadow">
                    <?= $editData ? 'Update' : 'Tambah' ?>
                </button>
                <?php if ($editData): ?>
                    <a href="mata_praktikum.php" class="text-red-400 hover:text-red-300 self-center">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel -->
    <div class="bg-[#121827] text-white p-6 rounded-xl shadow-lg shadow-cyan-500/10 overflow-x-auto">
        <h2 class="text-xl font-bold mb-4 text-cyan-400">Daftar Mata Praktikum</h2>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'constraint'): ?>
            <div class="bg-red-500/10 border border-red-400 text-red-300 p-4 mb-4 rounded">
                Gagal menghapus: Data ini masih digunakan di modul atau data lain.
            </div>
        <?php endif; ?>

        <table class="min-w-full text-sm border border-cyan-400/20">
            <thead class="bg-[#1e2636] text-cyan-300">
                <tr>
                    <th class="border border-cyan-500/10 px-4 py-2">#</th>
                    <th class="border border-cyan-500/10 px-4 py-2">Nama Praktikum</th>
                    <th class="border border-cyan-500/10 px-4 py-2">Dosen</th>
                    <th class="border border-cyan-500/10 px-4 py-2">Semester</th>
                    <th class="border border-cyan-500/10 px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($dataPraktikum) > 0): ?>
                    <?php foreach ($dataPraktikum as $index => $row): ?>
                        <tr class="hover:bg-[#1a1f2f]">
                            <td class="border border-cyan-500/10 px-4 py-2"><?= $index + 1 ?></td>
                            <td class="border border-cyan-500/10 px-4 py-2"><?= htmlspecialchars($row['nama_praktikum']) ?></td>
                            <td class="border border-cyan-500/10 px-4 py-2"><?= htmlspecialchars($row['nama_dosen']) ?></td>
                            <td class="border border-cyan-500/10 px-4 py-2"><?= htmlspecialchars($row['semester']) ?></td>
                            <td class="border border-cyan-500/10 px-4 py-2 space-x-2 text-sm">
                                <a href="?edit=<?= $row['id'] ?>" class="text-blue-400 hover:underline">Edit</a>
                                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus data ini?')" class="text-red-400 hover:underline">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-400">Belum ada data.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
