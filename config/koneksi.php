<?php
$koneksi = mysqli_connect("localhost", "root", "", "db_pesantren");
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
