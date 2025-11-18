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

// Logika Hapus (menghapus dari kedua tabel)
if (isset($_GET['hapus'])) {
    $id_pesanan_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM detail_pesanan WHERE id_pesanan = '$id_pesanan_hapus'");
    mysqli_query($koneksi, "DELETE FROM pesanan WHERE id_pesanan = '$id_pesanan_hapus'");
    header("Location: riwayat.php");
    exit;
}

// --- LOGIKA PAGINASI BARU ---
$limit = 10; // Jumlah item per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Halaman saat ini, default 1
$offset = ($page - 1) * $limit; // Hitung offset untuk query

// Ambil total jumlah pesanan untuk menghitung total halaman
$total_result = mysqli_query($koneksi, "SELECT COUNT(id_pesanan) AS total FROM pesanan");
$total_rows = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_rows / $limit);
// --- AKHIR LOGIKA PAGINASI ---


// Ambil data pesanan utama dengan LIMIT dan OFFSET
$query_pesanan = "SELECT * FROM pesanan ORDER BY tanggal_pesanan DESC LIMIT $offset, $limit";
$hasil_pesanan = mysqli_query($koneksi, $query_pesanan);

$semua_pesanan = [];
while($pesanan = mysqli_fetch_assoc($hasil_pesanan)) {
    $query_detail = "SELECT * FROM detail_pesanan WHERE id_pesanan = '{$pesanan['id_pesanan']}'";
    $hasil_detail = mysqli_query($koneksi, $query_detail);
    $detail_items = [];
    while($item = mysqli_fetch_assoc($hasil_detail)) {
        $detail_items[] = $item;
    }
    $pesanan['detail_items'] = $detail_items;
    $semua_pesanan[] = $pesanan;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; margin: 0; padding: 1.5rem; }
        .container { background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); max-width: 800px; margin: auto; }
        h2 { text-align: center; color: #333; margin-bottom: 1.5rem; }
        .pesanan-list { list-style: none; padding: 0; }
        .pesanan-item { background: #f9f9f9; border-left: 5px solid #3498db; padding: 15px; border-radius: 8px; margin-bottom: 1rem; }
        .pesanan-header { display: flex; justify-content: space-between; align-items: flex-start; font-weight: 600; }
        .pesanan-id { color: #2980b9; }
        .pesanan-total { color: #2ecc71; }
        .pesanan-body { margin-top: 10px; font-size: 0.9rem; color: #555; }
        .detail-item-list { padding-left: 20px; list-style-type: circle; }
        .pesanan-actions { margin-top: 15px; display: flex; gap: 10px; }
        .action-btn { padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer; color: white; font-size: 0.8rem; text-decoration: none; display: inline-flex; align-items: center; }
        .action-btn i { margin-right: 5px; }
        .btn-print { background: #f39c12; }
        .btn-hapus { background: #e74c3c; }
        .btn-kembali { display: inline-block; padding: 12px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 1.5rem; }
        
        /* --- CSS BARU UNTUK PAGINASI --- */
        .pagination { text-align: center; margin-top: 2rem; }
        .pagination a {
            color: #3498db;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
            border-radius: 5px;
            transition: background-color .3s;
        }
        .pagination a.active {
            background-color: #3498db;
            color: white;
            border: 1px solid #3498db;
        }
        .pagination a:hover:not(.active) { background-color: #f0f2f5; }
        /* --- AKHIR CSS BARU --- */

        /* Sembunyikan area struk secara default */
        #struk-area { display: none; }
        
        @media print {
            @page { size: auto; margin: 0mm; }
            body > *:not(#struk-area) { display: none; }
            #struk-area {
                display: block; position: absolute; left: 0; top: 0;
                width: 58mm; font-family: 'Courier New', Courier, monospace;
                font-size: 8pt; color: #000;
            }
            #struk-area h4, #struk-area p { margin: 2px 0; }
            #struk-area .header, #struk-area .footer { text-align: center; }
            #struk-area .item-row { display: flex; justify-content: space-between; }
            #struk-area .item-details { text-align: left; }
            #struk-area .total { text-align: right; font-weight: bold; margin-top: 5px; border-top: 1px dashed #000; padding-top: 5px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-history"></i> RIWAYAT PESANAN</h2>
        <ul class="pesanan-list">
            <?php if(!empty($semua_pesanan)): ?>
                <?php foreach ($semua_pesanan as $pesanan): ?>
                    <li class="pesanan-item">
                        <div class="pesanan-header">
                            <div>
                                <span class="pesanan-id"><?php echo htmlspecialchars($pesanan['id_pesanan']); ?></span>
                                <p style="margin:2px 0;"><small><?php echo date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])); ?></small></p>
                            </div>
                            <span class="pesanan-total">Rp <?php echo number_format($pesanan['total_keseluruhan'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="pesanan-body">
                            <p><strong>Detail Item:</strong></p>
                            <ul class="detail-item-list">
                                <?php foreach ($pesanan['detail_items'] as $item): ?>
                                    <li><?php echo $item['qty']; ?>x <?php echo htmlspecialchars($item['nama_item']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($pesanan['jenis_customer']); ?> | <strong>Bayar:</strong> <?php echo htmlspecialchars($pesanan['metode_bayar']); ?></p>
                        </div>
                        <div class="pesanan-actions">
                            <button class="action-btn btn-print" onclick="printStruk(this)" 
                                data-pesanan='<?php echo htmlspecialchars(json_encode($pesanan), ENT_QUOTES, 'UTF-8'); ?>'>
                                <i class="fas fa-print"></i> Print
                            </button>
                            <a href="riwayat.php?hapus=<?php echo $pesanan['id_pesanan']; ?>" class="action-btn btn-hapus" onclick="return confirm('Yakin ingin menghapus pesanan ini?')"><i class="fas fa-trash"></i> Hapus</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;">Belum ada riwayat pesanan.</p>
            <?php endif; ?>
        </ul>

        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="riwayat.php?page=<?php echo $i; ?>" class="<?php if($page == $i) { echo 'active'; } ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <a href="dashboard.php" class="btn-kembali"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
    </div>

    <div id="struk-area"></div>

    <script>
    function printStruk(btn) {
        const pesanan = JSON.parse(btn.dataset.pesanan);
        let itemsHtml = '';
        pesanan.detail_items.forEach(item => {
            itemsHtml += `
                <div class="item-details">${item.qty}x ${item.nama_item}</div>
                <div class="item-row">
                    <span>@${parseInt(item.harga).toLocaleString('id-ID')}</span>
                    <span>${parseInt(item.subtotal).toLocaleString('id-ID')}</span>
                </div>
            `;
        });
        const strukContent = `
            <div class="header"><h4>DERMAGA Street Food</h4><p>-------------------------</p></div>
            <p>ID: ${pesanan.id_pesanan}</p>
            <p>Tgl: ${new Date(pesanan.tanggal_pesanan).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' }).replace(/\./g, ':')}</p>
            <p>-------------------------</p>
            ${itemsHtml}
            <p>-------------------------</p>
            <div class="total">
                <p>TOTAL: Rp ${parseInt(pesanan.total_keseluruhan).toLocaleString('id-ID')}</p>
            </div>
            <div class="footer"><p>-------------------------</p><p>Terima Kasih!</p></div>
        `;
        
        document.getElementById('struk-area').innerHTML = strukContent;
        window.print();
    }
    </script>
</body>
</html>