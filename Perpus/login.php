<?php
session_start();
require 'koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // SELECT berdasarkan username saja dulu
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['password'] === $password) {
        // Simpan data user ke session
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['id'] = $user['id']; // supaya bisa digunakan di halaman lain

        header('Location: ' . ($user['role'] === 'admin' ? 'admin_dashboard.php' : 'index.php'));
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0c3fc, #8ec5fc);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .login-card {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.85);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            color: #333;
        }

        .login-card .form-control {
            background-color: #f0f0f0;
            border: none;
            color: #333;
        }

        .login-card .form-control:focus {
            background-color: #e8e8e8;
            box-shadow: none;
        }

        .login-icon {
            font-size: 3rem;
            text-align: center;
            margin-bottom: 1rem;
            color: #6c63ff;
        }

        .btn-primary {
            background-color: #6c63ff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #5a52d4;
        }

        .alert {
            background-color: #ffccd5;
            border: none;
            color: #721c24;
        }

        a {
            color: #6c63ff;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-icon text-center">
            <i class="bi bi-person-circle"></i>
        </div>
        <h3 class="text-center mb-4">Login Perpustakaan</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus placeholder="Masukkan username">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required placeholder="Masukkan password">
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <div class="text-center mt-3">
            <small>Belum punya akun? <a href="register.php">Daftar</a></small>
        </div>
    </div>
</body>
</html>
