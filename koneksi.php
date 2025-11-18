<?php
$host = 'sql211.infinityfree.com';
$user = 'if0_39547231';
$pass = 'dermagaadmin';
$db   = 'if0_39547231_kasir';

$koneksi = mysqli_connect($host, $user, $pass, $db);
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
