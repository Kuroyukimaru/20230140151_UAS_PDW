<?php
$pageTitle = 'Kelola Modul';
$activePage = 'modul';
require_once 'templates/header.php';

$pdo = new PDO("mysql:host=localhost;dbname=myklass", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Tambah / Ubah
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $id_praktikum = $_POST['id_praktikum'];
    $judul = trim($_POST['judul']);
    $fileName = '';

    // Upload file jika ada
    if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['file_materi']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx'];
        if (in_array($ext, $allowed)) {
            $fileName = uniqid() . "." . $ext;
            move_uploaded_file($_FILES['file_materi']['tmp_name'], 'uploads/' . $fileName);
        }
    }

    if ($id == '') {
        $stmt = $pdo->prepare("INSERT INTO modul (id_praktikum, judul, file_materi) VALUES (?, ?, ?)");
        $stmt->execute([$id_praktikum, $judul, $fileName]);
    } else {
        if ($fileName) {
            $stmt = $pdo->prepare("UPDATE modul SET id_praktikum=?, judul=?, file_materi=? WHERE id=?");
            $stmt->execute([$id_praktikum, $judul, $fileName, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE modul SET id_praktikum=?, judul=? WHERE id=?");
            $stmt->execute([$id_praktikum, $judul, $id]);
        }
    }

    header("Location: modul.php");
    exit;
}

// Hapus
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM modul WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        header("Location: modul.php?msg=deleted");
    } catch (PDOException $e) {
        // Gagal karena foreign key constraint (misalnya masih dipakai di `laporan`)
        header("Location: modul.php?error=constraint");
    }
    exit;
}

// Ambil data
$modulList = $pdo->query("SELECT m.*, p.nama_praktikum FROM modul m JOIN mata_praktikum p ON m.id_praktikum = p.id ORDER BY m.id DESC")->fetchAll();
$praktikumList = $pdo->query("SELECT * FROM mata_praktikum")->fetchAll();
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM modul WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch();
}
?>

<!-- FORM -->
<div class="bg-[#121827] text-white p-6 rounded-xl shadow-lg shadow-cyan-500/10 mb-6">
    <h2 class="text-xl font-bold mb-4 text-cyan-400"><?= $editData ? 'Edit' : 'Tambah' ?> Modul</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">

        <div>
            <label class="block text-cyan-300">Mata Praktikum</label>
            <select name="id_praktikum" required class="w-full p-2 bg-[#1e2636] text-white border border-cyan-500/20 rounded">
                <?php foreach ($praktikumList as $praktikum): ?>
                    <option value="<?= $praktikum['id'] ?>" <?= ($editData && $editData['id_praktikum'] == $praktikum['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($praktikum['nama_praktikum']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-cyan-300">Judul Modul</label>
            <input type="text" name="judul" required class="w-full p-2 bg-[#1e2636] text-white border border-cyan-500/20 rounded" value="<?= $editData['judul'] ?? '' ?>">
        </div>

        <div>
            <label class="block text-cyan-300">File Materi (PDF, DOCX)</label>
            <input type="file" name="file_materi" class="w-full p-2 bg-[#1e2636] text-white border border-cyan-500/20 rounded">
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-gradient-to-r from-cyan-500 to-indigo-600 text-white px-4 py-2 rounded shadow hover:opacity-90">
                Simpan
            </button>
            <?php if ($editData): ?>
                <a href="modul.php" class="text-red-400 self-center hover:underline">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- TABEL -->
<div class="bg-[#121827] text-white p-6 rounded-xl shadow-lg shadow-cyan-500/10">
    <h2 class="text-xl font-bold mb-4 text-cyan-400">Daftar Modul</h2>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'constraint'): ?>
        <div class="bg-red-500/10 border border-red-400 text-red-300 p-4 mb-4 rounded">
            Gagal menghapus: Modul ini masih digunakan di laporan mahasiswa.
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="w-full text-sm border border-cyan-500/20">
            <thead class="bg-[#1e2636] text-cyan-300">
                <tr>
                    <th class="px-4 py-2 border border-cyan-500/10">#</th>
                    <th class="px-4 py-2 border border-cyan-500/10">Mata Praktikum</th>
                    <th class="px-4 py-2 border border-cyan-500/10">Judul</th>
                    <th class="px-4 py-2 border border-cyan-500/10">File</th>
                    <th class="px-4 py-2 border border-cyan-500/10">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modulList as $i => $modul): ?>
                    <tr class="hover:bg-[#1a1f2f]">
                        <td class="px-4 py-2 border border-cyan-500/10"><?= $i + 1 ?></td>
                        <td class="px-4 py-2 border border-cyan-500/10"><?= htmlspecialchars($modul['nama_praktikum']) ?></td>
                        <td class="px-4 py-2 border border-cyan-500/10"><?= htmlspecialchars($modul['judul']) ?></td>
                        <td class="px-4 py-2 border border-cyan-500/10">
                            <?php if ($modul['file_materi']): ?>
                                <a href="uploads/<?= $modul['file_materi'] ?>" target="_blank" class="text-blue-400 hover:underline">Lihat</a>
                            <?php else: ?>
                                <span class="text-gray-400 italic">Tidak ada</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2 border border-cyan-500/10 space-x-2 text-sm">
                            <a href="?edit=<?= $modul['id'] ?>" class="text-blue-400 hover:underline">Edit</a>
                            <a href="?delete=<?= $modul['id'] ?>" onclick="return confirm('Hapus modul ini?')" class="text-red-400 hover:underline">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($modulList) === 0): ?>
                    <tr><td colspan="5" class="text-center py-4 text-gray-400">Belum ada modul.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
