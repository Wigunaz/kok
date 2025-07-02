<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// Query untuk data peminjaman - diperbaiki sesuai struktur database
$sql_peminjaman = "SELECT p.id, u.username as nama_peminjam, b.judul, p.tanggal_pinjam, p.tanggal_kembali, p.status 
                   FROM peminjam p 
                   JOIN buku_2401010444 b ON p.buku_id = b.id 
                   JOIN users u ON p.user_id = u.id
                   ORDER BY p.tanggal_pinjam DESC";
$result_peminjaman = mysqli_query($conn, $sql_peminjaman);

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $id = $_POST['id'];
        $action = $_POST['action'];
        
        if ($action == 'kembalikan') {
            $sql_update = "UPDATE peminjam SET status = 'dikembalikan', tanggal_kembali = NOW(), updated_at = NOW() WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
        }
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Peminjaman - Perpustakaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }

        .btn-success {
            background-color: #6c63ff;
            border: none;
        }

        .btn-success:hover {
            background-color: #5a52d4;
        }

        .logout-btn {
            background-color: #dc3545;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #6c63ff;
            color: white;
            border: none;
            font-weight: 600;
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        .table tbody tr:hover {
            background-color: rgba(108, 99, 255, 0.05);
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
        }

        .badge-dipinjam {
            background-color: #ffc107;
            color: #000;
        }

        .badge-dikembalikan {
            background-color: #28a745;
            color: #fff;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
            border-radius: 0.5rem;
        }

        .search-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .search-section .form-control {
            border-radius: 0.5rem;
            border: none;
            padding: 0.75rem 1rem;
        }

        .search-section .btn {
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
        }

        .stats-mini {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
            margin-top: 1rem;
        }

        .stats-mini h4 {
            margin: 0;
            font-size: 1.5rem;
        }

        .stats-mini p {
            margin: 0;
            opacity: 0.8;
            font-size: 0.9rem;
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
                        <a class="nav-link active" href="peminjam.php">
                            <i class="bi bi-people-fill me-2"></i>Data Peminjaman
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
            <!-- Header Section -->
            <div class="search-section">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2">
                            <i class="bi bi-book me-2"></i>Manajemen Peminjaman Buku
                        </h2>
                        <p class="mb-3">Kelola data peminjaman dan pengembalian buku perpustakaan</p>
                        
                        <!-- Search Form -->
                        <form method="GET" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama peminjam atau judul buku..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            <button type="submit" class="btn btn-light">
                                <i class="bi bi-search"></i>
                            </button>
                            <a href="form_peminjaman.php" class="btn btn-success">
                                <i class="bi bi-plus-circle me-1"></i>Pinjam Buku
                            </a>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <?php
                        // Mini statistics - diperbaiki query
                        $total_dipinjam = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM peminjam WHERE status = 'dipinjam'"));
                        $total_dikembalikan = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM peminjam WHERE status = 'dikembalikan'"));
                        ?>
                        <div class="row">
                            <div class="col-6">
                                <div class="stats-mini">
                                    <h4><?= $total_dipinjam ?></h4>
                                    <p>Dipinjam</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stats-mini">
                                    <h4><?= $total_dikembalikan ?></h4>
                                    <p>Dikembalikan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-list-task me-2"></i>Data Peminjaman
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group">
                                <a href="?filter=dipinjam" class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-hourglass-split me-1"></i>Dipinjam
                                </a>
                                <a href="?filter=dikembalikan" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-check-circle me-1"></i>Dikembalikan
                                </a>
                                <a href="?" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-list me-1"></i>Semua
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Peminjam</th>
                                    <th>Judul Buku</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Status</th>
                                    <th>Catatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Filter query based on search and filter parameters - diperbaiki JOIN
                                $where_clause = "1=1";
                                
                                if (isset($_GET['search']) && !empty($_GET['search'])) {
                                    $search = mysqli_real_escape_string($conn, $_GET['search']);
                                    $where_clause .= " AND (u.username LIKE '%$search%' OR b.judul LIKE '%$search%')";
                                }
                                
                                if (isset($_GET['filter']) && !empty($_GET['filter'])) {
                                    $filter = mysqli_real_escape_string($conn, $_GET['filter']);
                                    $where_clause .= " AND p.status = '$filter'";
                                }
                                
                                $sql_filtered = "SELECT p.id, u.username as nama_peminjam, b.judul, p.tanggal_pinjam, p.tanggal_kembali, p.status, p.catatan
                                               FROM peminjam p 
                                               JOIN buku_2401010444 b ON p.buku_id = b.id 
                                               JOIN users u ON p.user_id = u.id
                                               WHERE $where_clause
                                               ORDER BY p.tanggal_pinjam DESC";
                                
                                $result_filtered = mysqli_query($conn, $sql_filtered);
                                $no = 1;
                                
                                if (mysqli_num_rows($result_filtered) > 0):
                                    while($row = mysqli_fetch_assoc($result_filtered)): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                                        <?= strtoupper(substr($row['nama_peminjam'], 0, 1)) ?>
                                                    </div>
                                                    <strong><?= htmlspecialchars($row['nama_peminjam']) ?></strong>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-book text-primary me-2"></i>
                                                    <?= htmlspecialchars($row['judul']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    <?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($row['tanggal_kembali'] && $row['tanggal_kembali'] != '0000-00-00 00:00:00'): ?>
                                                    <small class="text-success">
                                                        <i class="bi bi-calendar-check me-1"></i>
                                                        <?= date('d/m/Y', strtotime($row['tanggal_kembali'])) ?>
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-muted">
                                                        <i class="bi bi-dash-circle me-1"></i>
                                                        Belum dikembalikan
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['status'] == 'dipinjam'): ?>
                                                    <span class="badge badge-dipinjam">
                                                        <i class="bi bi-hourglass-split me-1"></i>Dipinjam
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-dikembalikan">
                                                        <i class="bi bi-check-circle me-1"></i>Dikembalikan
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['catatan'])): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($row['catatan']) ?></small>
                                                <?php else: ?>
                                                    <small class="text-muted">-</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['status'] == 'dipinjam'): ?>
                                                    <form method="POST" style="display: inline;" id="return-form-<?= $row['id'] ?>">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <input type="hidden" name="action" value="kembalikan">
                                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Yakin ingin mengembalikan buku ini?')">
                                                            <i class="bi bi-arrow-return-left me-1"></i>Kembalikan
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary btn-sm" disabled>
                                                        <i class="bi bi-check-circle me-1"></i>Selesai
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <a href="edit_peminjaman.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm ms-1">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <a href="hapus_peminjaman.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm ms-1" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                <p>Tidak ada data peminjaman yang ditemukan.</p>
                                                <a href="form_peminjaman.php" class="btn btn-primary">
                                                    <i class="bi bi-plus-circle me-1"></i>Tambah Peminjaman
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Auto-refresh setiap 30 detik untuk update real-time
setTimeout(function(){
    location.reload();
}, 30000);

// Konfirmasi sebelum mengembalikan buku
function confirmReturn(id, nama, judul) {
    if (confirm(`Yakin ingin mengembalikan buku "${judul}" yang dipinjam oleh ${nama}?`)) {
        document.getElementById('return-form-' + id).submit();
    }
}
</script>

</body>
</html>