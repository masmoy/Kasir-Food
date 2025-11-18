<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Koneksi Database
$host = 'sql211.infinityfree.com'; 
$user = 'if0_39547231'; 
$pass = 'dermagaadmin'; 
$db = 'if0_39547231_kasir';
$koneksi = mysqli_connect($host, $user, $pass, $db);
if (!$koneksi) { 
    die("Koneksi gagal: " . mysqli_connect_error()); 
}

// --- PROSES FORM PEMBAYARAN (SETELAH KERANJANG DI-SUBMIT) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['keranjang_json'])) {
    // BARU: Atur zona waktu server ke Jakarta (WIB)
    date_default_timezone_set('Asia/Jakarta');

    $keranjang = json_decode($_POST['keranjang_json'], true);
    $jenis_customer = $_POST['jenis_customer'];
    $metode_bayar = $_POST['metode_bayar'];
    $total_keseluruhan = 0;
    
    // BARU: Dapatkan tanggal dan waktu saat ini sesuai format database
    $tanggal_sekarang = date('Y-m-d H:i:s'); 

    if (!empty($keranjang)) {
        // Hitung total keseluruhan dari keranjang
        foreach($keranjang as $item) {
            $total_keseluruhan += $item['subtotal'];
        }

        // Generate ID Pesanan unik berdasarkan jenis customer dan tanggal
        $prefixMap = ['Customer Langsung'=>'CL', 'Gofood'=>'GJF', 'Grabfood'=>'GRF', 'Shopeefood'=>'SPF'];
        $prefix = $prefixMap[$jenis_customer];
        $tanggal_id = date('Ymd'); // Tanggal untuk ID, bukan timestamp lengkap
        $query_last_id = "SELECT id_pesanan FROM pesanan WHERE id_pesanan LIKE '$prefix$tanggal_id%' ORDER BY id_pesanan DESC LIMIT 1";
        $result_last_id = mysqli_query($koneksi, $query_last_id);
        
        $next_num = 1;
        if (mysqli_num_rows($result_last_id) > 0) {
            $last_id = mysqli_fetch_assoc($result_last_id)['id_pesanan'];
            $last_num = (int)substr($last_id, -3);
            $next_num = $last_num + 1;
        }
        $id_pesanan = $prefix . $tanggal_id . sprintf('%03d', $next_num);
        
        // DIUBAH: Simpan ke tabel 'pesanan' dengan menyertakan tanggal_pesanan
        $stmt_pesanan = $koneksi->prepare("INSERT INTO pesanan (id_pesanan, jenis_customer, metode_bayar, total_keseluruhan, tanggal_pesanan) VALUES (?, ?, ?, ?, ?)");
        
        // DIUBAH: Tambahkan "s" untuk tipe data string tanggal dan variabel $tanggal_sekarang
        $stmt_pesanan->bind_param("sssis", $id_pesanan, $jenis_customer, $metode_bayar, $total_keseluruhan, $tanggal_sekarang);
        $stmt_pesanan->execute();
        
        // Simpan setiap item ke tabel 'detail_pesanan'
        $stmt_detail = $koneksi->prepare("INSERT INTO detail_pesanan (id_pesanan, nama_item, harga, qty, subtotal) VALUES (?, ?, ?, ?, ?)");
        foreach ($keranjang as $item) {
            $stmt_detail->bind_param("ssiii", $id_pesanan, $item['nama'], $item['harga'], $item['qty'], $item['subtotal']);
            $stmt_detail->execute();
        }

        $stmt_pesanan->close();
        $stmt_detail->close();
        
        // Arahkan ke halaman riwayat setelah berhasil
        header("Location: riwayat.php");
        exit;
    }
}

// Ambil daftar menu untuk dropdown
$menu_items = mysqli_query($koneksi, "SELECT id, nama_item, harga FROM menu");
$menu_data = [];
while($row = mysqli_fetch_assoc($menu_items)) {
    $menu_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRANSAKSI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; margin: 0; padding: 1.5rem; }
        .container { background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); max-width: 600px; margin: auto; }
        h2 { text-align: center; color: #333; margin-bottom: 1.5rem; }
        .form-group, .add-item-form, .pembayaran-info { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .add-item-form { display: flex; gap: 10px; align-items: flex-end; }
        .item-selector { flex: 3; }
        .qty-selector { flex: 1; }
        .btn-tambah { padding: 10px; background: #5c67f2; color: white; border: none; border-radius: 8px; cursor: pointer; }
        #keranjang { margin-top: 1.5rem; border-top: 2px solid #eee; padding-top: 1.5rem; }
        #keranjang h3 {text-align:center; color:#777; margin:0;}
        .keranjang-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 4px; border-bottom: 1px solid #f0f0f0; }
        .keranjang-item:last-child { border-bottom: none; }
        .item-info { flex-grow: 1; }
        .btn-hapus-item { background: none; border: none; color: #e74c3c; cursor: pointer; font-size: 1.1rem; }
        .total-harga { background: #e9ecef; font-size: 1.2rem; font-weight: bold; text-align: center; padding: 15px; border-radius: 8px; margin-top: 1.5rem; }
        .btn-container { display: flex; gap: 1rem; margin-top: 1.5rem; }
        .btn { flex: 1; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; font-weight: 600; text-align: center; text-decoration: none; color: white; display:flex; align-items:center; justify-content:center; gap: 8px;}
        .btn-bayar { background: #2ecc71; }
        .btn-kembali { background: #3498db; }
        .btn-logout { background: #e74c3c; margin-top: 1rem; display: block; }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-shopping-cart"></i> PENJUALAN</h2>

        <div class="add-item-form">
            <div class="item-selector">
                <label for="item">Pilih Item</label>
                <select id="item">
                    <?php foreach ($menu_data as $item): ?>
                        <option value="<?php echo $item['id']; ?>" data-harga="<?php echo $item['harga']; ?>" data-nama="<?php echo htmlspecialchars($item['nama_item']); ?>">
                            <?php echo htmlspecialchars($item['nama_item']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="qty-selector">
                <label for="qty">Qty</label>
                <input type="number" id="qty" value="1" min="1">
            </div>
            <button type="button" class="btn-tambah" onclick="tambahKeKeranjang()"><i class="fas fa-plus"></i></button>
        </div>

        <div id="keranjang">
            <h3>KERANJANG</h3>
        </div>

        <form action="transaksi.php" method="post" id="form-pembayaran">
            <div class="pembayaran-info">
                <div class="form-group">
                    <label for="jenis_customer">Jenis Customer</label>
                    <select id="jenis_customer" name="jenis_customer" required>
                        <option>Customer Langsung</option>
                        <option>Gofood</option>
                        <option>Grabfood</option>
                        <option>Shopeefood</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="metode_bayar">Metode Bayar</label>
                    <select id="metode_bayar" name="metode_bayar" required>
                        <option>TUNAI</option>
                        <option>NON TUNAI</option>
                        <option>QRIS</option>
                    </select>
                </div>
            </div>
            
            <div class="total-harga">
                TOTAL: <span id="total_harga_display">Rp 0</span>
            </div>

            <input type="hidden" name="keranjang_json" id="keranjang_json">
            
            <div class="btn-container">
                <button type="submit" name="bayar" class="btn btn-bayar"><i class="fas fa-money-bill-wave"></i> BAYAR</button>
                <a href="dashboard.php" class="btn btn-kembali"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
         <a href="logout.php" class="btn btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <script>
        let keranjang = [];

        // Panggil renderKeranjang saat halaman pertama kali dimuat
        document.addEventListener('DOMContentLoaded', function() {
            renderKeranjang();
        });

        function tambahKeKeranjang() {
            const itemSelect = document.getElementById('item');
            const selectedOption = itemSelect.options[itemSelect.selectedIndex];
            const qtyInput = document.getElementById('qty');

            const item = {
                id: selectedOption.value,
                nama: selectedOption.dataset.nama,
                harga: parseInt(selectedOption.dataset.harga),
                qty: parseInt(qtyInput.value)
            };
            item.subtotal = item.harga * item.qty;

            // Cek jika item sudah ada di keranjang, update qty
            const itemAda = keranjang.find(i => i.id === item.id);
            if (itemAda) {
                itemAda.qty += item.qty;
                itemAda.subtotal = itemAda.harga * itemAda.qty;
            } else {
                keranjang.push(item);
            }
            renderKeranjang();
            qtyInput.value = 1; // Reset qty
        }

        function renderKeranjang() {
            const keranjangDiv = document.getElementById('keranjang');
            keranjangDiv.innerHTML = '<h3 style="text-align:center; color:#777;">KERANJANG</h3>'; // Reset dengan judul
            let totalKeseluruhan = 0;

            if (keranjang.length === 0) {
                keranjangDiv.innerHTML += '<p style="text-align:center; color:#999;">Keranjang masih kosong</p>';
            } else {
                keranjang.forEach((item, index) => {
                    totalKeseluruhan += item.subtotal;
                    keranjangDiv.innerHTML += `
                        <div class="keranjang-item">
                            <div class="item-info">
                                <strong>${item.nama}</strong><br>
                                <small>${item.qty} x Rp ${item.harga.toLocaleString('id-ID')} = Rp ${item.subtotal.toLocaleString('id-ID')}</small>
                            </div>
                            <button type="button" class="btn-hapus-item" onclick="hapusItem(${index})"><i class="fas fa-times-circle"></i></button>
                        </div>
                    `;
                });
            }
            document.getElementById('total_harga_display').textContent = 'Rp ' + totalKeseluruhan.toLocaleString('id-ID');
            document.getElementById('keranjang_json').value = JSON.stringify(keranjang);
        }

        function hapusItem(index) {
            keranjang.splice(index, 1);
            renderKeranjang();
        }
    </script>
</body>
</html>