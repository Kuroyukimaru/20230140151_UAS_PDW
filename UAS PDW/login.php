<?php
session_start();
require_once 'config.php';

// Jika sudah login, arahkan ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'asisten') {
        header("Location: asisten/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'mahasiswa') {
        header("Location: mahasiswa/dashboard.php");
        exit();
    }
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $message = "Email dan password harus diisi!";
    } else {
        $sql = "SELECT id, nama, email, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'asisten') {
                    header("Location: asisten/dashboard.php");
                    exit();
                } elseif ($user['role'] === 'mahasiswa') {
                    header("Location: mahasiswa/dashboard.php");
                    exit();
                } else {
                    $message = "Peran pengguna tidak dikenali.";
                }
            } else {
                $message = "Password salah.";
            }
        } else {
            $message = "Akun dengan email tersebut tidak ditemukan.";
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
    <title>Login</title>
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

        /* ======== Login Card ======== */
        .login-card {
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

        .form-group input {
            width: 100%;
            padding: 10px;
            background-color: #1e1e2f;
            color: white;
            border: 1px solid #444;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .form-group input:focus {
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

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: #03e9f4;
            text-decoration: none;
        }

        .register-link a:hover {
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
    <div class="login-card">
        <h2>Login</h2>
        <?php 
            if (isset($_GET['status']) && $_GET['status'] === 'registered') {
                echo '<p class="message success">Registrasi berhasil! Silakan login.</p>';
            }
            if (!empty($message)) {
                echo '<p class="message">' . htmlspecialchars($message) . '</p>';
            }
        ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" required placeholder="Masukkan email">
            </div>
            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <input type="password" name="password" required placeholder="Masukkan password">
            </div>
            <button type="submit" class="btn-glow">Masuk</button>
        </form>
        <div class="register-link">
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </div>
</body>
</html>
