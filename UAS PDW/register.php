<?php
require_once 'config.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $message = "Semua field harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid!";
    } elseif (!in_array($role, ['mahasiswa', 'asisten'])) {
        $message = "Peran tidak valid!";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql_insert = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                header("Location: login.php?status=registered");
                exit();
            } else {
                $message = "Terjadi kesalahan. Silakan coba lagi.";
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi</title>
    <style>
        /* ======== Global Styling ======== */
        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* ======== Card ======== */
        .register-card {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border-radius: 12px;
            background-color: #1e1e2f;
            animation: fadeInUp 0.6s ease-out;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.3);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #03e9f4;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            color: #aaa;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            background-color: #1e1e2f;
            color: white;
            border: 1px solid #444;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #03e9f4;
            box-shadow: 0 0 8px #03e9f4;
            outline: none;
        }

        .btn-glow {
            background-color: transparent;
            color: #03e9f4;
            border: 1px solid #03e9f4;
            padding: 10px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.4s ease;
        }

        .btn-glow:hover {
            background-color: #03e9f4;
            color: #fff;
            box-shadow: 0 0 10px #03e9f4, 0 0 40px #03e9f4;
        }

        .message {
            color: #ff6b6b;
            text-align: center;
            margin-bottom: 15px;
        }

        .message.success {
            color: #28d67d;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #03e9f4;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="register-card">
        <h2>Registrasi</h2>
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post" action="register.php">
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" name="nama" id="nama" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="role">Daftar Sebagai</label>
                <select name="role" id="role" required>
                    <option value="mahasiswa">Mahasiswa</option>
                    <option value="asisten">Asisten</option>
                </select>
            </div>
            <button type="submit" class="btn-glow">Daftar</button>
        </form>
        <div class="login-link">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</body>
</html>
