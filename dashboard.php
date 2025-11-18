<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; margin: 0; padding: 1.5rem; }
        .container {
            background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 500px; margin: auto; animation: slideInUp 0.5s ease-out;
        }
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h2 { text-align: center; color: #333; }
        .menu-btn {
            display: block; width: 100%; padding: 15px; margin-bottom: 1rem; background: #2ecc71; color: white;
            text-decoration: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600;
            transition: background 0.3s, transform 0.2s; box-sizing: border-box; text-align: center;
        }
        .menu-btn:hover { background: #27ae60; transform: translateY(-2px); }
        .menu-btn i { margin-right: 10px; }
        .footer-nav { display: flex; justify-content: space-between; margin-top: 2rem; }
        .nav-btn {
            flex: 1; padding: 12px; text-align: center; text-decoration: none; border-radius: 8px;
            font-weight: 600; transition: background 0.3s;
        }
        .btn-kembali { background: #3498db; color: white; margin-right: 0.5rem; }
        .btn-kembali:hover { background: #2980b9; }
        .btn-logout { background: #e74c3c; color: white; margin-left: 0.5rem; }
        .btn-logout:hover { background: #c0392b; }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-tachometer-alt"></i> DASHBOARD</h2>
        <a href="kelola_menu.php" class="menu-btn"><i class="fas fa-book-open"></i> KELOLA MENU</a>
        <a href="riwayat.php" class="menu-btn"><i class="fas fa-history"></i> RIWAYAT</a>
        <a href="laporan.php" class="menu-btn"><i class="fas fa-chart-line"></i> LAPORAN</a>
        <div class="footer-nav">
            <a href="index.php" class="nav-btn btn-kembali"><i class="fas fa-arrow-left"></i> KEMBALI</a>
            <a href="logout.php" class="nav-btn btn-logout"><i class="fas fa-sign-out-alt"></i> LOGOUT</a>
        </div>
    </div>
</body>
</html>
