<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// Handle form submission
if ($_POST) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $buku_id = mysqli_real_escape_string($conn, $_POST['buku_id']);
    $tanggal_pinjam = mysqli_real_escape_string($conn, $_POST['tanggal_pinjam']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    
    // Validasi input
    $errors = [];
    
    if (empty($user_id)) {
        $errors[] = "Peminjam harus dipilih";
    }
    
    if (empty($buku_id)) {
        $errors[] = "Buku harus dipilih";
    }
    
    if (empty($tanggal_pinjam)) {
        $errors[] = "Tanggal pinjam harus diisi";
    }
    
    // Cek apakah buku sudah dipinjam
    if (!empty($buku_id)) {
        $check_sql = "SELECT * FROM peminjam WHERE buku_id = ? AND status = 'dipinjam'";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "i", $buku_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $errors[] = "Buku ini sedang dipinjam oleh orang lain";
        }
    }
    
    // Jika tidak ada error, simpan data
    if (empty($errors)) {
        $insert_sql = "INSERT INTO peminjam (user_id, buku_id, tanggal_pinjam, status, catatan, created_at) VALUES (?, ?, ?, 'dipinjam', ?, NOW())";
        $insert_stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, "iiss", $user_id, $buku_id, $tanggal_pinjam, $catatan);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $_SESSION['success_message'] = "Data peminjaman berhasil disimpan";
            header("Location: peminjam.php");
            exit;
        } else {
            $errors[] = "Gagal menyimpan data peminjaman";
        }
    }
}

// Ambil data users (role = 'user')
$users_sql = "SELECT id, username FROM users WHERE role = 'user' ORDER BY username ASC";
$users_result = mysqli_query($conn, $users_sql);

// Ambil data buku yang belum dipinjam
$books_sql = "SELECT b.id, b.judul, b.penerbit, b.kategori 
              FROM buku_2401010444 b 
              LEFT JOIN peminjam p ON b.id = p.buku_id AND p.status = 'dipinjam'
              WHERE p.id IS NULL 
              ORDER BY b.judul ASC";
$books_result = mysqli_query($conn, $books_sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Peminjaman Buku - Perpustakaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #e0c3fc, #8ec5fc);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }

        .main-card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin: 2rem 0;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 2rem 2rem 0 0 !important;
            padding: 2rem;
            text-align: center;
        }

        .form-control, .form-select {
            border-radius: 1rem;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 1rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            border-radius: 1rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .alert {
            border-radius: 1rem;
            border: none;
            padding: 1rem 1.5rem;
        }

        .book-info {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 1rem;
            padding: 1rem;
            margin-top: 0.5rem;
            display: none;
        }

        .book-info h6 {
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .book-info p {
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .select2-container--default .select2-selection--single {
            border-radius: 1rem !important;
            border: 2px solid #e9ecef !important;
            height: 45px !important;
            padding: 0.75rem 1rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px !important;
            padding-left: 0 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 43px !important;
            right: 10px !important;
        }

        .select2-dropdown {
            border-radius: 1rem !important;
            border: 2px solid #e9ecef !important;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .stats-card h4 {
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
        }

        .stats-card p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }

        .breadcrumb {
            background: none;
            padding: 0;
            margin-bottom: 2rem;
        }

        .breadcrumb-item a {
            color: #667eea;
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="pt-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="admin_dashboard.php">
                    <i class="bi bi-house-door-fill me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="peminjam.php">
                    <i class="bi bi-people-fill me-1"></i>Data Peminjaman
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="bi bi-plus-circle me-1"></i>Form Peminjaman
            </li>
        </ol>
    </nav>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-4">
            <div class="stats-card">
                <?php
                $total_buku = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM buku_2401010444"));
                ?>
                <h4><?= $total_buku ?></h4>
                <p><i class="bi bi-book me-1"></i>Total Buku</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <?php
                $buku_tersedia = mysqli_num_rows($books_result);
                mysqli_data_seek($books_result, 0); // Reset pointer
                ?>
                <h4><?= $buku_tersedia ?></h4>
                <p><i class="bi bi-check-circle me-1"></i>Buku Tersedia</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <?php
                $total_users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE role = 'user'"));
                ?>
                <h4><?= $total_users ?></h4>
                <p><i class="bi bi-people me-1"></i>Total Anggota</p>
            </div>
        </div>
    </div>

    <!-- Main Form -->
    <div class="main-card">
        <div class="card-header">
            <h3 class="mb-0">
                <i class="bi bi-journal-plus me-2"></i>Form Peminjaman Buku Baru
            </h3>
            <p class="mb-0 mt-2">Silakan isi form di bawah untuk menambah data peminjaman buku</p>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" id="peminjamanForm">
                <div class="row">
                    <!-- Pilih Peminjam -->
                    <div class="col-md-6 mb-4">
                        <label for="user_id" class="form-label">
                            <i class="bi bi-person-fill me-2"></i>Pilih Peminjam
                        </label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">-- Pilih Peminjam --</option>
                            <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                                <option value="<?= $user['id'] ?>" <?= (isset($_POST['user_id']) && $_POST['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['username']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Tanggal Pinjam -->
                    <div class="col-md-6 mb-4">
                        <label for="tanggal_pinjam" class="form-label">
                            <i class="bi bi-calendar-event me-2"></i>Tanggal Pinjam
                        </label>
                        <input type="date" class="form-control" id="tanggal_pinjam" name="tanggal_pinjam" 
                               value="<?= isset($_POST['tanggal_pinjam']) ? $_POST['tanggal_pinjam'] : date('Y-m-d') ?>" required>
                    </div>
                </div>

                <!-- Pilih Buku -->
                <div class="mb-4">
                    <label for="buku_id" class="form-label">
                        <i class="bi bi-book me-2"></i>Pilih Buku
                    </label>
                    <select class="form-select" id="buku_id" name="buku_id" required>
                        <option value="">-- Pilih Buku --</option>
                        <?php while ($book = mysqli_fetch_assoc($books_result)): ?>
                            <option value="<?= $book['id'] ?>" 
                                    data-penerbit="<?= htmlspecialchars($book['penerbit']) ?>"
                                    data-kategori="<?= htmlspecialchars($book['kategori']) ?>"
                                    <?= (isset($_POST['buku_id']) && $_POST['buku_id'] == $book['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($book['judul']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    
                    <!-- Info Buku -->
                    <div class="book-info" id="bookInfo">
                        <h6><i class="bi bi-info-circle me-1"></i>Informasi Buku</h6>
                        <p><strong>Penerbit:</strong> <span id="bookPenerbit">-</span></p>
                        <p><strong>Kategori:</strong> <span id="bookKategori">-</span></p>
                    </div>
                </div>

                <!-- Catatan -->
                <div class="mb-4">
                    <label for="catatan" class="form-label">
                        <i class="bi bi-chat-left-text me-2"></i>Catatan (Opsional)
                    </label>
                    <textarea class="form-control" id="catatan" name="catatan" rows="3" 
                              placeholder="Masukkan catatan peminjaman jika diperlukan..."><?= isset($_POST['catatan']) ? htmlspecialchars($_POST['catatan']) : '' ?></textarea>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="peminjam.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Simpan Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('#user_id').select2({
        placeholder: "-- Pilih Peminjam --",
        allowClear: true,
        width: '100%'
    });
    
    $('#buku_id').select2({
        placeholder: "-- Pilih Buku --",
        allowClear: true,
        width: '100%'
    });
    
    // Show book info when book is selected
    $('#buku_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var penerbit = selectedOption.data('penerbit');
        var kategori = selectedOption.data('kategori');
        
        if ($(this).val()) {
            $('#bookPenerbit').text(penerbit || '-');
            $('#bookKategori').text(kategori || '-');
            $('#bookInfo').slideDown();
        } else {
            $('#bookInfo').slideUp();
        }
    });
    
    // Trigger change event if book is already selected (for form validation errors)
    if ($('#buku_id').val()) {
        $('#buku_id').trigger('change');
    }
    
    // Set minimum date to today
    var today = new Date().toISOString().split('T')[0];
    $('#tanggal_pinjam').attr('min', today);
    
    // Form validation
    $('#peminjamanForm').on('submit', function(e) {
        var isValid = true;
        var errorMessage = '';
        
        // Check if user is selected
        if (!$('#user_id').val()) {
            isValid = false;
            errorMessage += '• Peminjam harus dipilih\n';
        }
        
        // Check if book is selected
        if (!$('#buku_id').val()) {
            isValid = false;
            errorMessage += '• Buku harus dipilih\n';
        }
        
        // Check if date is selected
        if (!$('#tanggal_pinjam').val()) {
            isValid = false;
            errorMessage += '• Tanggal pinjam harus diisi\n';
        }
        
        if (!isValid) {
            alert('Terjadi kesalahan:\n' + errorMessage);
            e.preventDefault();
            return false;
        }
        
        // Confirmation
        var userName = $('#user_id option:selected').text();
        var bookTitle = $('#buku_id option:selected').text();
        var confirmMessage = `Apakah Anda yakin ingin meminjamkan buku ini`;
        
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
    });
    
    // Real-time validation feedback
    $('.form-control, .form-select').on('blur', function() {
        if ($(this).prop('required') && !$(this).val()) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }
    });
});
</script>

</body>
</html>