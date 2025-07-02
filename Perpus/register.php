<?php
include 'koneksi.php'; // Pastikan file config.php berisi koneksi database

// Proses registrasi
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (empty($username) || empty($password) || empty($confirm)) {
        $error = "Semua field harus diisi!";
    } elseif ($password !== $confirm) {
        $error = "Password dan konfirmasi tidak cocok!";
    } else {
        $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $role = "user";
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $password, $role);
            if ($stmt->execute()) {
                header("Location: login.php"); // 🔁 Redirect ke halaman login
                exit;
            } else {
                $error = "Gagal mendaftar. Silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register Perpustakaan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      height: 100vh;
      background: linear-gradient(to bottom right, #d8b4fe, #a5b4fc);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card {
      border: none;
      border-radius: 20px;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
    }
    .icon-circle {
      background-color: #8b5cf6;
      width: 60px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      margin: 0 auto 1rem;
    }
    .icon-circle svg {
      color: white;
    }
    .btn-purple {
      background-color: #7c3aed;
      color: white;
    }
    .btn-purple:hover {
      background-color: #6d28d9;
      color: white;
    }
    .text-purple {
      color: #7c3aed;
    }
  </style>
</head>
<body>

  <div class="card p-4" style="width: 100%; max-width: 400px;">
    <div class="icon-circle">
      <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
        <path d="M11 10c2.21 0 4 1.79 4 4v.5a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5V14c0-2.21 1.79-4 4-4h6zm-5.216-1A3 3 0 1 1 11 6a3 3 0 0 1-5.216 3z"/>
        <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14z"/>
      </svg>
    </div>
    <h4 class="text-center fw-semibold mb-3">Register Perpustakaan</h4>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
      </div>
      <div class="mb-4">
        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
      </div>
      <button type="submit" class="btn btn-purple w-100">Daftar</button>
    </form>

    <div class="text-center mt-3">
      <small>Sudah punya akun? <a href="login.php" class="text-decoration-none text-purple">Login</a></small>
    </div>
  </div>

</body>
</html>
 