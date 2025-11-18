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

$jenis_laporan = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$data_laporan = null;
$judul_laporan = '';

if ($jenis_laporan == 'harian') {
    $judul_laporan = "Laporan Penjualan Hari Ini (" . date('d M Y') . ")";
    // DIUBAH: Query mengambil dari tabel 'pesanan'
    $query = "SELECT SUM(total_keseluruhan) as total, COUNT(id) as jumlah FROM pesanan WHERE DATE(tanggal_pesanan) = CURDATE()";
    $result = mysqli_query($koneksi, $query);
    $data_laporan = mysqli_fetch_assoc($result);
    
    // DIUBAH: Query mengambil detail item dari pesanan hari ini
    $query_detail = "SELECT dp.* FROM detail_pesanan dp JOIN pesanan p ON dp.id_pesanan = p.id_pesanan WHERE DATE(p.tanggal_pesanan) = CURDATE() ORDER BY p.tanggal_pesanan ASC";
    $detail_transaksi = mysqli_query($koneksi, $query_detail);

} elseif ($jenis_laporan == 'bulanan') {
    $judul_laporan = "Laporan Penjualan Bulan Ini (" . date('F Y') . ")";
    // DIUBAH: Query mengambil dari tabel 'pesanan'
    $query = "SELECT SUM(total_keseluruhan) as total, COUNT(id) as jumlah FROM pesanan WHERE MONTH(tanggal_pesanan) = MONTH(CURDATE()) AND YEAR(tanggal_pesanan) = YEAR(CURDATE())";
    $result = mysqli_query($koneksi, $query);
    $data_laporan = mysqli_fetch_assoc($result);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; margin: 0; padding: 1.5rem; }
        .container {
            background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 500px; margin: auto; animation: fadeIn 0.5s;
        }
        h2 { text-align: center; color: #333; margin-bottom: 1.5rem; }
        .menu-btn {
            display: block; width: 100%; padding: 15px; margin-bottom: 1rem; background: #9b59b6; color: white;
            text-decoration: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600;
            transition: background 0.3s, transform 0.2s; box-sizing: border-box; text-align: center;
        }
        .menu-btn:hover { background: #8e44ad; transform: translateY(-2px); }
        .btn-kembali { background: #3498db; color: white; margin-top: 1rem; }
        .btn-kembali:hover { background: #2980b9; }
        .laporan-box {
            background: #f9f9f9; padding: 1.5rem; border-radius: 8px; text-align: center;
        }
        .laporan-box h3 { margin-top: 0; }
        .laporan-box p { font-size: 1.5rem; font-weight: 600; color: #2ecc71; margin: 0.5rem 0; }
        .btn-print { background: #f39c12; color: white; padding: 10px 15px; border: none; border-radius: 8px; cursor: pointer; margin-top: 1rem; }
        .btn-print:hover { background: #e67e22; }

        /* Struk untuk Print 58mm */
        @media print {
            body * { visibility: hidden; }
            #struk, #struk * { visibility: visible; }
            #struk {
                position: absolute; left: 0; top: 0; width: 58mm;
                font-family: 'Courier New', Courier, monospace; font-size: 8pt; color: #000;
            }
            #struk h4, #struk p { margin: 2px 0; }
            #struk .header { text-align: center; margin-bottom: 10px; }
            #struk .item { display: flex; justify-content: space-between; }
            #struk .total { text-align: right; font-weight: bold; margin-top: 10px; border-top: 1px dashed #000; padding-top: 5px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container no-print">
        <h2><i class="fas fa-chart-line"></i> LAPORAN PENJUALAN</h2>
        <?php if (!$jenis_laporan): ?>
            <a href="laporan.php?jenis=harian" class="menu-btn"><i class="fas fa-calendar-day"></i> Penjualan Hari Ini</a>
            <a href="laporan.php?jenis=bulanan" class="menu-btn"><i class="fas fa-calendar-alt"></i> Penjualan Bulan Ini</a>
            <a href="dashboard.php" class="menu-btn btn-kembali"><i class="fas fa-arrow-left"></i> Kembali</a>
        <?php else: ?>
            <div class="laporan-box">
                <h3><?php echo $judul_laporan; ?></h3>
                <p>Rp <?php echo number_format($data_laporan['total'] ?? 0, 0, ',', '.'); ?></p>
                <small>Total dari <?php echo $data_laporan['jumlah'] ?? 0; ?> pesanan</small>
                <?php if ($jenis_laporan == 'harian' && isset($data_laporan['jumlah']) && $data_laporan['jumlah'] > 0): ?>
                    <button onclick="window.print()" class="btn-print no-print"><i class="fas fa-print"></i> Print Laporan Harian</button>
                <?php endif; ?>
            </div>
             <a href="laporan.php" class="menu-btn btn-kembali"><i class="fas fa-arrow-left"></i> Kembali ke Menu Laporan</a>
        <?php endif; ?>
    </div>

    <?php if ($jenis_laporan == 'harian' && isset($detail_transaksi) && mysqli_num_rows($detail_transaksi) > 0): ?>
        <div id="struk">
            <div class="header">
                <h4>LAPORAN PENJUALAN</h4>
                <p><?php echo date('d M Y'); ?></p>
                <p>-------------------------</p>
            </div>
            <?php mysqli_data_seek($detail_transaksi, 0); // Reset pointer ?>
            <?php while($trx = mysqli_fetch_assoc($detail_transaksi)): ?>
                <div class="item">
                    <span><?php echo $trx['nama_item'] . ' ('.$trx['qty'].'x)'; ?></span>
                    <span><?php echo number_format($trx['subtotal']); ?></span>
                </div>
            <?php endwhile; ?>
            <div class="total">
                <p>-------------------------</p>
                TOTAL: Rp <?php echo number_format($data_laporan['total']); ?>
            </div>
            <div class="footer" style="text-align: center; margin-top: 10px;">
                <p>Terima Kasih</p>
            </div>
        </div>
    <?php endif; ?>

</body>
</html>
