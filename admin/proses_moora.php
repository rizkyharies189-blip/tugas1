<?php
session_start();
include '../config/koneksi.php';

// Cek apakah user admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil data santri dan nilai
$result = mysqli_query($koneksi, "
    SELECT u.id, u.nama_lengkap, n.akademik, n.non_akademik, n.iq, n.agama
    FROM users u
    INNER JOIN nilai n ON u.id = n.user_id
    WHERE u.role = 'santri'
");

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

if (count($data) == 0) {
    die("Tidak ada data santri untuk diproses.");
}

// --- Langkah 1: Hitung penyebut normalisasi (akar dari jumlah kuadrat) ---
$sum_akademik = $sum_non = $sum_iq = $sum_agama = 0;
foreach ($data as $d) {
    $sum_akademik += pow($d['akademik'], 2);
    $sum_non += pow($d['non_akademik'], 2);
    $sum_iq += pow($d['iq'], 2);
    $sum_agama += pow($d['agama'], 2);
}

$norm_akademik = sqrt($sum_akademik);
$norm_non = sqrt($sum_non);
$norm_iq = sqrt($sum_iq);
$norm_agama = sqrt($sum_agama);

// --- Bobot tiap kriteria ---
$w_akademik = 0.25;
$w_non = 0.15;
$w_iq = 0.25;
$w_agama = 0.35;

// (Opsional) Bersihkan log lama jika hanya ingin simpan log terbaru
mysqli_query($koneksi, "DELETE FROM log_moora");

// --- Langkah 2: Hitung nilai MOORA dan log untuk masing-masing santri ---
foreach ($data as &$d) {
    $r_akademik = ($d['akademik'] / $norm_akademik) * $w_akademik;
    $r_non = ($d['non_akademik'] / $norm_non) * $w_non;
    $r_iq = ($d['iq'] / $norm_iq) * $w_iq;
    $r_agama = ($d['agama'] / $norm_agama) * $w_agama;

    $score = $r_akademik + $r_non + $r_iq + $r_agama;
    $d['moora_score'] = $score;

    // Klasifikasi berdasarkan score
    if ($score >= 0.32) {
        $kelas = 'IPA';
        $status = 'lulus';
    } elseif ($score >= 0.19) {
        $kelas = 'IPS';
        $status = 'lulus';
    } else {
        $kelas = '-';
        $status = 'tidak_lulus';
    }

    // Update ke tabel nilai
    mysqli_query($koneksi, "
        UPDATE nilai SET moora_score = '$score' WHERE user_id = {$d['id']}
    ");

    // Update ke tabel users
    mysqli_query($koneksi, "
        UPDATE users SET kelas = '$kelas', status = '$status' WHERE id = {$d['id']}
    ");

    // Simpan log ke log_moora
    mysqli_query($koneksi, "
        INSERT INTO log_moora (
            user_id, nama_lengkap,
            r_akademik, r_non_akademik, r_iq, r_agama,
            score, kelas, status
        ) VALUES (
            '{$d['id']}', '{$d['nama_lengkap']}',
            '$r_akademik', '$r_non', '$r_iq', '$r_agama',
            '$score', '$kelas', '$status'
        )
    ");
}

header("Location: data_santri.php?pesan=moora_sukses");
exit;
?>
