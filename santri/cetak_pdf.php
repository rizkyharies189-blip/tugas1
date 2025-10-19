<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
use Mpdf\Mpdf;

// Cek login
if (!isset($_SESSION['id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

$user_id = $_SESSION['id'];

// Koneksi database
$koneksi = new mysqli("localhost", "root", "", "db_pesantren");
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// Ambil data user, nilai, dan skor MOORA
$sql = "
    SELECT u.nama_lengkap, u.kelas, u.status,
           n.akademik, n.non_akademik, n.iq, n.agama,
           lm.score
    FROM users u
    JOIN nilai n ON u.id = n.user_id
    LEFT JOIN log_moora lm ON u.id = lm.user_id
    WHERE u.id = ?
";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $html = '
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
            }
            h2, h3, h4 {
                text-align: center;
                margin-bottom: 5px;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th, td {
                border: 1px solid #333;
                padding: 8px 12px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
                width: 30%;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h2>Laporan Nilai Tes Santri</h2>
            <h3>Pondok Pesantren Moderat At-Thohiriyah</h3>
            <h4>Tahun Ajaran 2025/2026</h4>
            <hr>
        </div>
        <table>
            <tr><th>Nama Lengkap</th><td>' . htmlspecialchars($row['nama_lengkap']) . '</td></tr>
            <tr><th>Nilai Akademik</th><td>' . htmlspecialchars($row['akademik']) . '</td></tr>
            <tr><th>Nilai Non Akademik</th><td>' . htmlspecialchars($row['non_akademik']) . '</td></tr>
            <tr><th>Nilai Agama</th><td>' . htmlspecialchars($row['agama']) . '</td></tr>
            <tr><th>IQ</th><td>' . htmlspecialchars($row['iq']) . '</td></tr>
            <tr><th>Skor MOORA</th><td>' . ($row['score'] !== null ? number_format($row['score'], 4) : 'Belum diproses') . '</td></tr>
            <tr><th>Status Kelulusan</th><td>' . htmlspecialchars($row['status']) . '</td></tr>
            <tr><th>Jurusan / Kelas</th><td>' . htmlspecialchars($row['kelas']) . '</td></tr>
        </table>
        <br><br>
    </body>
    </html>
    ';

    $mpdf = new Mpdf([
        'format' => 'A4',
        'margin_top' => 20,
        'margin_bottom' => 20,
    ]);
    $mpdf->WriteHTML($html);
    $mpdf->Output('laporan_nilai_saya.pdf', 'I'); // tampilkan di browser
} else {
    echo "Data tidak ditemukan untuk user ini.";
}
