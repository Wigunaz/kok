<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); 
    exit();
}
// Get all books
$sql = "SELECT * FROM buku_2401010444";
$result = $conn->query($sql);

// Get user information from database
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : 1; // Default user ID 1 for demo
$user_sql = "SELECT username, role FROM users WHERE id = ?";

if ($user_stmt = $conn->prepare($user_sql)) {
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $username = $user_data['username'];
        $level = $user_data['role'];
    } else {
        // Default values if user not found
        $username = 'Guest';
        $level = 'user';
    }
    $user_stmt->close();
} else {
    // If prepare fails, use default values
    $username = 'Guest User';
    $level = 'user';
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan MC - Digital Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }

        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
        }

        .nav-link:hover {
            color: white !important;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-radius: 15px;
            padding: 1rem;
        }

        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8rem 0 4rem;
            margin-bottom: 3rem;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            opacity: 0.9;
        }

        .book-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            border: none;
            height: 100%;
        }

        .book-card:hover {
            transform: translateY(-5px);
        }

        .card-img-top {
            height: 400px;
            object-fit: cover;
            background: linear-gradient(135deg, #e3e3e3 0%, #f5f5f5 100%);
            border-radius: 15px 15px 0 0;
        }

        .book-image-placeholder {
            height: 400px;
            background: linear-gradient(135deg, #e3e3e3 0%, #f5f5f5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px 15px 0 0;
        }

        .card-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .card-publisher {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .card-year {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .btn-detail {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            border-radius: 25px;
            padding: 0.6rem 1.2rem;
            color: white;
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        .stats-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            color: #7f8c8d;
            margin-top: 0.5rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
            color: #2c3e50;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .empty-icon {
            font-size: 4rem;
            color: #bdc3c7;
            margin-bottom: 1rem;
        }

        .price-tag {
            background: linear-gradient(135deg, #ff9a56 0%, #ff6b35 100%);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            margin-top: 0.5rem;
        }

        .book-info {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .book-info i {
            width: 16px;
            text-align: center;
            margin-right: 0.5rem;
            color: #7f8c8d;
        }
        
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-book-open me-2"></i>
                Perpustakaan MC
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-home me-1"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="form_peminjaman.php"><i class="fa fa-pencil fa-fw"></i>Pinjam</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#catalog"><i class="fas fa-book me-1"></i>Katalog</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            Account
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <div class="dropdown-item-text">
                                    <div class="fw-bold"><?= htmlspecialchars($username) ?></div>
                                    <small class="text-muted">Role:
                                    <br><span class="badge <?= $level == 'admin' ? 'bg-danger' : 'bg-success' ?>"><?= ucfirst(htmlspecialchars($level)) ?></span>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-heart me-2"></i>Favorites</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="?logout=1" onclick="return confirm('Yakin ingin logout?')">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="hero-title">Selamat Datang Di</h1>
            <h2 class="hero-title">Perpustakaan MC</h2>
            <p class="hero-subtitle">Temukan ribuan buku terbaik untuk memperkaya pengetahuan Anda</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Statistics -->
        <?php if ($result && $result->num_rows > 0): ?>
        <div class="stats-section">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="stat-number"><?= $result->num_rows ?></div>
                    <div class="stat-label">Total Buku</div>
                </div>
                <div class="col-md-4">
                    <div class="stat-number">3</div>
                    <div class="stat-label">Kategori</div>
                </div>
                <div class="col-md-4">
                    <div class="stat-number">1.2K</div>
                    <div class="stat-label">Members</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Book Catalog -->
        <section id="catalog">
            <h2 class="section-title">Katalog Buku</h2>
            
            <div class="row g-4">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                            <div class="card book-card">
                                <?php if (!empty($row['gambar']) && file_exists('uploads/' . $row['gambar'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($row['gambar']) ?>" 
                                         alt="Cover <?= htmlspecialchars($row['judul']) ?>" 
                                         class="card-img-top"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="book-image-placeholder" style="display: none;">
                                        <i class="fas fa-book fa-3x text-muted"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="book-image-placeholder">
                                        <i class="fas fa-book fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($row['judul']) ?></h5>
                                    
                                    <?php if (!empty($row['kategori'])): ?>
                                        <div class="book-info">
                                            <i class="fas fa-tag"></i>
                                            <span><?= htmlspecialchars($row['kategori']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="book-info">
                                        <i class="fas fa-building"></i>
                                        <span><?= htmlspecialchars($row['penerbit']) ?></span>
                                    </div>
                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <?php if (!empty($row['th_terbit'])): ?>
                                            <span class="card-year">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= htmlspecialchars($row['th_terbit']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mt-3 d-flex justify-content-between">
                                        <a href="#" class="btn-detail">
                                            <i class="bi bi-backpack3"></i>
                                           Pinjam Buku
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-books empty-icon"></i>
                            <h3>Belum Ada Buku</h3>
                            <p class="text-muted">Koleksi buku sedang dalam proses penambahan.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-book-open me-2"></i>Perpustakaan MC</h5>
                    <p class="mb-0">Membangun budaya membaca untuk masa depan yang lebih cerah</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; 2024 Perpustakaan MC. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
if (isset($conn)) {
    $conn->close();
}
?>