<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = $_POST['judul'];
    $kategori = $_POST['kategori'];
    $penerbit = $_POST['penerbit'];
    $th_terbit = $_POST['th_terbit'];
    $tgl_beli = $_POST['tgl_beli'];
    $harga = $_POST['harga'];
    
    // Handle upload gambar
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "uploads/"; // Pastikan folder ini ada dan writable
        
        // Buat folder uploads jika belum ada
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION));
        $allowed_extensions = array("jpg", "jpeg", "png", "gif");
        
        // Validasi ekstensi file
        if (in_array($file_extension, $allowed_extensions)) {
            // Generate nama file unik
            $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Validasi ukuran file (maksimal 5MB)
            if ($_FILES["gambar"]["size"] <= 5000000) {
                if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                    $gambar = $new_filename; // Simpan hanya nama file
                } else {
                    echo "<div class='alert alert-danger'>Gagal mengupload gambar.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Ukuran file terlalu besar. Maksimal 5MB.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Format file tidak didukung. Hanya JPG, JPEG, PNG, dan GIF yang diizinkan.</div>";
        }
    }

    // Simpan ke database
    $sql = "INSERT INTO buku_2401010444 (judul, kategori, penerbit, th_terbit, tgl_beli, harga, gambar) 
            VALUES ('$judul', '$kategori', '$penerbit', '$th_terbit', '$tgl_beli', '$harga', '$gambar')";

    if (mysqli_query($conn, $sql)) {
        header("Location: admin.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Gagal menyimpan data: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Data Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .preview-container {
            margin-top: 10px;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card p-4">
            <h1>I Putu Mesha krisna Wiguna - 2401010444</h1>
            <h3>Form Data Buku</h3>
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="judul" class="form-label">Judul</label>
                    <input type="text" class="form-control" id="judul" name="judul" required>
                </div>
                <div class="mb-3">
                    <label for="kategori" class="form-label">Kategori</label>
                    <select class="form-select" id="kategori" name="kategori" required>
                        <option selected disabled value="">Pilih Kategori</option>
                        <option value="Cerpen">Cerpen</option>
                        <option value="Komik">Komik</option>
                        <option value="Novel">Novel</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="penerbit" class="form-label">Penerbit</label>
                    <input type="text" class="form-control" id="penerbit" name="penerbit" required>
                </div>
                <div class="mb-3">
                    <label for="th_terbit" class="form-label">Tahun Terbit</label>
                    <input type="text" class="form-control" id="th_terbit" name="th_terbit" required>
                </div>
                <div class="mb-3">
                    <label for="tgl_beli" class="form-label">Tanggal Beli</label>
                    <input type="date" class="form-control" id="tgl_beli" name="tgl_beli" required>
                </div>
                <div class="mb-3">
                    <label for="harga" class="form-label">Harga</label>
                    <input type="number" class="form-control" id="harga" name="harga" required>
                </div>
                <div class="mb-3">
                    <label for="gambar" class="form-label">Gambar Buku</label>
                    <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" onchange="previewImage(this)">
                    <div class="form-text">Format yang didukung: JPG, JPEG, PNG, GIF. Maksimal 5MB.</div>
                    <div class="preview-container">
                        <img id="preview" class="preview-image" style="display: none;">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="admin.php" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>