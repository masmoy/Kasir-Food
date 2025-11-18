<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Koneksi Database
$host = 'sql211.infinityfree.com'; $user = 'if0_39547231'; $pass = 'dermagaadmin'; $db = 'if0_39547231_kasir';
$koneksi = mysqli_connect($host, $user, $pass, $db);
if (!$koneksi) { die("Koneksi gagal: " . mysqli_connect_error()); }

// --- BAGIAN BARU: Logika untuk Hapus Item Menu ---
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM menu WHERE id = '$id_hapus'");
    header("Location: kelola_menu.php");
    exit;
}
// --- AKHIR BAGIAN BARU ---


// Proses Tambah Item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['simpan'])) {
    $nama_item = mysqli_real_escape_string($koneksi, $_POST['nama_item']);
    $harga = mysqli_real_escape_string($koneksi, $_POST['harga']);
    $sql = "INSERT INTO menu (nama_item, harga) VALUES ('$nama_item', '$harga')";
    mysqli_query($koneksi, $sql);
    header("Location: kelola_menu.php"); // Refresh halaman
    exit;
}

// Ambil Daftar Menu
$daftar_menu = mysqli_query($koneksi, "SELECT * FROM menu ORDER BY nama_item ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; margin: 0; padding: 1.5rem; }
        .container {
            background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 600px; margin: auto; animation: slideInUp 0.5s ease-out;
        }
        h2 { text-align: center; color: #333; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input[type="text"], input[type="number"] {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;
            box-sizing: border-box; font-family: 'Poppins', sans-serif;
        }
        .btn-container { display: flex; gap: 1rem; }
        .btn {
            flex: 1; padding: 12px; border: none; border-radius: 8px; cursor: pointer;
            font-size: 1rem; font-weight: 600; transition: background 0.3s;
        }
        .btn-simpan { background: #2ecc71; color: white; }
        .btn-simpan:hover { background: #27ae60; }
        .btn-kembali { background: #3498db; color: white; text-align: center; text-decoration: none; }
        .btn-kembali:hover { background: #2980b9; }
        hr { border: 0; border-top: 1px solid #eee; margin: 2rem 0; }
        .menu-list { list-style: none; padding: 0; }
        .menu-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 15px; background: #f9f9f9; border-radius: 8px; margin-bottom: 0.5rem;
        }
        /* --- STYLE BARU UNTUK TOMBOL HAPUS --- */
        .menu-info { flex-grow: 1; }
        .btn-hapus-menu {
            background: none; border: none; color: #e74c3c;
            cursor: pointer; font-size: 1.2rem; padding: 5px;
            text-decoration: none;
        }
        .btn-hapus-menu:hover { color: #c0392b; }
        /* --- AKHIR STYLE BARU --- */

    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-book-open"></i> KELOLA MENU</h2>
        <form action="kelola_menu.php" method="post">
            <div class="form-group">
                <label for="nama_item">Nama Item</label>
                <input type="text" id="nama_item" name="nama_item" required>
            </div>
            <div class="form-group">
                <label for="harga">Harga</label>
                <input type="number" id="harga" name="harga" required>
            </div>
            <div class="btn-container">
                <button type="submit" name="simpan" class="btn btn-simpan"><i class="fas fa-save"></i> Simpan</button>
                <a href="dashboard.php" class="btn btn-kembali"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
        <hr>
        <h3><i class="fas fa-list-ul"></i> Daftar Menu</h3>
        <ul class="menu-list">
            <?php while ($item = mysqli_fetch_assoc($daftar_menu)): ?>
                <li class="menu-item">
                    <div class="menu-info">
                        <span><?php echo htmlspecialchars($item['nama_item']); ?></span>
                        <br>
                        <small>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></small>
                    </div>
                    <a href="kelola_menu.php?hapus=<?php echo $item['id']; ?>" class="btn-hapus-menu" onclick="return confirm('Anda yakin ingin menghapus menu ini?')">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
