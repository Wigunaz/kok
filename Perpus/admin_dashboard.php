<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// Query untuk statistik dashboard
$sql_total_buku = "SELECT COUNT(*) as total FROM buku_2401010444";
$result_total_buku = mysqli_query($conn, $sql_total_buku);
$total_buku = mysqli_fetch_assoc($result_total_buku)['total'];

// Asumsi tabel users untuk total user (sesuaikan dengan nama tabel yang ada)
$sql_total_user = "SELECT COUNT(*) as total FROM users";
$result_total_user = mysqli_query($conn, $sql_total_user);
$total_user = mysqli_fetch_assoc($result_total_user)['total'];

// Asumsi tabel peminjaman untuk buku yang dipinjam (sesuaikan dengan struktur database)
$sql_total_dipinjam = "SELECT COUNT(*) as total FROM peminjam WHERE status = 'dipinjam'";
$result_total_dipinjam = mysqli_query($conn, $sql_total_dipinjam);
$total_dipinjam = mysqli_fetch_assoc($result_total_dipinjam)['total'];

// Buku tersedia = total buku - total dipinjam
$buku_tersedia = $total_buku - $total_dipinjam;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Perpustakaan</title>
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
            transform: translateY(-5px);
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

        .stat-card {
            border-left: 4px solid;
            height: 120px;
        }

        .stat-card.books {
            border-left-color: #6c63ff;
        }

        .stat-card.users {
            border-left-color: #28a745;
        }

        .stat-card.borrowed {
            border-left-color: #ffc107;
        }

        .stat-card.available {
            border-left-color: #17a2b8;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            font-size: 3rem;
            opacity: 0.2;
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
        }

        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .quick-actions .btn {
            border-radius: 0.8rem;
            padding: 0.8rem 1.5rem;
            font-weight: 500;
        }

        .recent-activity {
            max-height: 300px;
            overflow-y: auto;
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
                        <a class="nav-link active" href="admin_dashboard.php">
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
            <!-- Welcome Section -->
            <div class="card welcome-card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-2">Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>! 👋</h3>
                            <p class="mb-0 opacity-75">Kelola perpustakaan Anda dengan mudah melalui dashboard ini.</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="bi bi-graph-up-arrow" style="font-size: 4rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card books">
                        <div class="card-body position-relative">
                            <div class="stat-number"><?= number_format($total_buku) ?></div>
                            <div class="stat-label">Total Buku</div>
                            <i class="bi bi-book-fill stat-icon"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card users">
                        <div class="card-body position-relative">
                            <div class="stat-number"><?= number_format($total_user) ?></div>
                            <div class="stat-label">Total User</div>
                            <i class="bi bi-people-fill stat-icon"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card borrowed">
                        <div class="card-body position-relative">
                            <div class="stat-number"><?= number_format($total_dipinjam) ?></div>
                            <div class="stat-label">Buku Dipinjam</div>
                            <i class="bi bi-arrow-up-circle-fill stat-icon"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card available">
                        <div class="card-body position-relative">
                            <div class="stat-number"><?= number_format($buku_tersedia) ?></div>
                            <div class="stat-label">Buku Tersedia</div>
                            <i class="bi bi-check-circle-fill stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-lightning-charge-fill me-2"></i>Aksi Cepat
                            </h5>
                        </div>
                        <div class="card-body quick-actions">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <a href="form.php" class="btn btn-success w-100">
                                        <i class="bi bi-plus-circle me-2"></i>Tambah Buku Baru
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="admin.php" class="btn btn-primary w-100">
                                        <i class="bi bi-list-ul me-2"></i>Lihat Semua Buku
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="peminjam.php" class="btn btn-info w-100">
                                        <i class="bi bi-person-check me-2"></i>Kelola Peminjam
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="laporan.php" class="btn btn-warning w-100">
                                        <i class="bi bi-file-earmark-text me-2"></i>Laporan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock-history me-2"></i>Aktivitas Terbaru
                            </h5>
                        </div>
                        <div class="card-body recent-activity">
                            <?php
                            // Query untuk aktivitas terbaru (sesuaikan dengan struktur database)
                            $sql_recent = "SELECT judul, 'Buku ditambahkan' as aktivitas, tgl_beli as tanggal 
                                          FROM buku_2401010444 
                                          ORDER BY tgl_beli DESC 
                                          LIMIT 5";
                            $result_recent = mysqli_query($conn, $sql_recent);
                            
                            if (mysqli_num_rows($result_recent) > 0):
                                while($row = mysqli_fetch_assoc($result_recent)): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3">
                                            <i class="bi bi-book text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold"><?= htmlspecialchars($row['judul']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($row['aktivitas']) ?></small>
                                            <div>
                                                <small class="text-muted">
                                                    <?php
                                                    $tgl = $row['tanggal'];
                                                    $hari_ini = date('Y-m-d');
                                                    $kemarin = date('Y-m-d', strtotime('-1 day'));
                                                    echo ($tgl == $hari_ini) ? "Hari ini" :
                                                         (($tgl == $kemarin) ? "Kemarin" : date('d/m/Y', strtotime($tgl)));
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile;
                            else: ?>
                                <p class="text-muted text-center">Belum ada aktivitas terbaru.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>