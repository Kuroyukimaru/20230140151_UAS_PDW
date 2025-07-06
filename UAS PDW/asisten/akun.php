<?php
$pageTitle = 'Kelola Akun Pengguna';
$activePage = 'akun';
require_once 'templates/header.php';

// Koneksi DB
try {
    $pdo = new PDO("mysql:host=localhost;dbname=myklass", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Simpan (Tambah/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($id === '') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $email, $hashed, $role]);
    } else {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nama=?, email=?, password=?, role=? WHERE id=?");
            $stmt->execute([$nama, $email, $hashed, $role, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nama=?, email=?, role=? WHERE id=?");
            $stmt->execute([$nama, $email, $role, $id]);
        }
    }

    header("Location: akun.php");
    exit;
}

// Hapus akun
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: akun.php");
    exit;
}

// Ambil data akun
$akun = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch();
}
?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Form Tambah/Edit -->
    <div class="bg-[#121827] text-white p-6 rounded-xl shadow-md shadow-cyan-500/10">
        <h2 class="text-2xl font-bold mb-4"><?= $editData ? 'Edit' : 'Tambah' ?> Akun</h2>
        <form method="POST" class="space-y-4">
            <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
            <?php endif; ?>
            <div>
                <label class="block text-cyan-300">Nama</label>
                <input type="text" name="nama" required class="w-full p-2 rounded bg-[#1e2636] text-white border border-cyan-500/20" value="<?= htmlspecialchars($editData['nama'] ?? '') ?>">
            </div>
            <div>
                <label class="block text-cyan-300">Email</label>
                <input type="email" name="email" required class="w-full p-2 rounded bg-[#1e2636] text-white border border-cyan-500/20" value="<?= htmlspecialchars($editData['email'] ?? '') ?>">
            </div>
            <div>
                <label class="block text-cyan-300">Password <?= $editData ? '(kosongkan jika tidak diubah)' : '' ?></label>
                <input type="password" name="password" class="w-full p-2 rounded bg-[#1e2636] text-white border border-cyan-500/20">
            </div>
            <div>
                <label class="block text-cyan-300">Role</label>
                <select name="role" required class="w-full p-2 rounded bg-[#1e2636] text-white border border-cyan-500/20">
                    <option value="">-- Pilih Role --</option>
                    <option value="mahasiswa" <?= ($editData['role'] ?? '') === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                    <option value="asisten" <?= ($editData['role'] ?? '') === 'asisten' ? 'selected' : '' ?>>Asisten</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-gradient-to-r from-cyan-500 to-purple-600 hover:opacity-90 text-white px-4 py-2 rounded shadow">
                    <?= $editData ? 'Update' : 'Tambah' ?>
                </button>
                <?php if ($editData): ?>
                    <a href="akun.php" class="text-red-400 self-center hover:underline">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel Akun -->
    <div class="bg-[#121827] text-white p-6 rounded-xl shadow-md shadow-indigo-500/10 overflow-auto">
        <h2 class="text-2xl font-bold mb-4">Daftar Akun Pengguna</h2>
        <table class="w-full text-sm border border-cyan-500/10">
            <thead class="bg-[#1e2636] text-cyan-300">
                <tr>
                    <th class="px-4 py-2 border border-cyan-500/10">#</th>
                    <th class="px-4 py-2 border border-cyan-500/10">Nama</th>
                    <th class="px-4 py-2 border border-cyan-500/10">Email</th>
                    <th class="px-4 py-2 border border-cyan-500/10">Role</th>
                    <th class="px-4 py-2 border border-cyan-500/10">Dibuat</th>
                    <th class="px-4 py-2 border border-cyan-500/10">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($akun) > 0): ?>
                    <?php foreach ($akun as $index => $row): ?>
                        <tr class="hover:bg-[#1a1f2f]">
                            <td class="px-4 py-2 border border-cyan-500/10"><?= $index + 1 ?></td>
                            <td class="px-4 py-2 border border-cyan-500/10"><?= htmlspecialchars($row['nama']) ?></td>
                            <td class="px-4 py-2 border border-cyan-500/10"><?= htmlspecialchars($row['email']) ?></td>
                            <td class="px-4 py-2 border border-cyan-500/10 capitalize"><?= $row['role'] ?></td>
                            <td class="px-4 py-2 border border-cyan-500/10"><?= $row['created_at'] ?></td>
                            <td class="px-4 py-2 border border-cyan-500/10 space-x-2">
                                <a href="?edit=<?= $row['id'] ?>" class="text-cyan-400 hover:underline text-sm">Edit</a>
                                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus akun ini?')" class="text-pink-400 hover:underline text-sm">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-400">Belum ada akun terdaftar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
