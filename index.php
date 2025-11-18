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
    <title>Halaman Utama</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .logo {
            margin-bottom: 1.5rem;
        }
        /* BARU: Aturan CSS untuk mengatur ukuran gambar logo */
        .logo img {
            max-width: 120px; /* Atur lebar logo di sini */
            height: auto;
        }
        .menu-btn {
            display: block;
            width: 100%;
            padding: 15px;
            margin-bottom: 1rem;
            background: #4a90e2;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background 0.3s, transform 0.2s;
            box-sizing: border-box;
        }
        .menu-btn:hover {
            background: #357abd;
            transform: translateY(-2px);
        }
        .btn-logout {
            background: #e74c3c;
        }
        .btn-logout:hover {
            background: #c0392b;
        }
        .menu-btn i {
            margin-right: 10px;
        }
        /* BARU: Aturan CSS untuk copyright */
        .copyright {
            margin-top: 2rem;
            font-size: 0.8rem;
            color: #888;
        }
        .copyright a {
            color: #666;
            text-decoration: none;
            font-weight: 600;
        }
        .copyright a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="logo.png" alt="Logo Toko">
        </div>
        <h2>MENU UTAMA</h2>
        <a href="transaksi.php" class="menu-btn"><i class="fas fa-calculator"></i> TRANSAKSI</a>
        <a href="dashboard.php" class="menu-btn"><i class="fas fa-tachometer-alt"></i> DASHBOARD</a>
        <a href="logout.php" class="menu-btn btn-logout"><i class="fas fa-sign-out-alt"></i> LOGOUT</a>

        <div class="copyright">
            DERMAGA Street Food | Made with ❤️ by 
            <a href="https://ngabdulmuhyi.my.id" target="_blank">MasMoy</a>
        </div>
    </div>
</body>
</html>
