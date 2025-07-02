<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

$sql = "SELECT * FROM buku_2401010444";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Buku</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #e0c3fc, #8ec5fc);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }

        .sidebar {
            background-color: #343a40;
        }

        .sidebar h5 {
            color: #fff;
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .nav-link {
            color: #ccc;
        }

        .nav-link:hover, .nav-link.active {
            background-color: #495057;
            color: #fff;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 1rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .btn-success {
            background-color: #6c63ff;
            border: none;
        }

        .btn-success:hover {
            background-color: #5a52d4;
        }

        .table thead {
            background-color: #6c63ff;
            color: #fff;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.5rem;
            border: 1px solid #ccc;
            padding: 0.3rem 0.6rem;
        }

        .btn-sm i {
            vertical-align: middle;
        }

        .btn-warning {
            background-color: #ffc107;
            border: none;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .logout-btn {
            background-color: #dc3545;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f6f6f6;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse min-vh-100">
            <div class="position-sticky pt-3">
                <h5 class="text-center">📘 Perpustakaan</h5>
                <ul class="nav flex-column px-2">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">
                            <i class="bi bi-house-door-fill me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">
                            <i class="bi bi-journal-bookmark-fill me-2"></i>Data Buku
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="peminjam.php">
                            <i class="bi bi-people-fill me-2"></i>Data Peminjam
                        </a>
                    </li>
                </ul>
                <hr class="text-secondary mx-3">
                <div class="px-3">
                    <a href="logout.php" class="btn logout-btn w-100 text-white">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4 text-dark">📚 Data Buku</h2>
                <a href="form.php" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i>Tambah Buku
                </a>
            </div>

            <div class="card mb-5">
                <div class="card-body">
                    <table id="bukuTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Judul Buku</th>
                                <th>Kategori</th>
                                <th>Penerbit</th>
                                <th>Tahun</th>
                                <th>Tanggal Pembelian</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['judul']) ?></td>
                                    <td><?= htmlspecialchars($row['kategori']) ?></td>
                                    <td><?= htmlspecialchars($row['penerbit']) ?></td>
                                    <td><?= htmlspecialchars($row['th_terbit']) ?></td>
                                    <td>
                                        <?php
                                        $tgl_beli = $row['tgl_beli'];
                                        $hari_ini = date('Y-m-d');
                                        $kemarin = date('Y-m-d', strtotime('-1 day'));
                                        echo ($tgl_beli == $hari_ini) ? "hari ini" :
                                             (($tgl_beli == $kemarin) ? "kemarin" : htmlspecialchars($tgl_beli));
                                        ?>
                                    </td>
                                    <td><?= 'Rp. ' . number_format($row['harga'], 0, ',', '.') ?></td>
                                    <td>
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('#bukuTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            }
        });
    });
</script>
</body>
</html>
