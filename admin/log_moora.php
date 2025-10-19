<?php
session_start();
include '../config/koneksi.php';

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil data log moora
$result = mysqli_query($koneksi, "SELECT * FROM log_moora ORDER BY score DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Log MOORA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 40px 0;
            color: #444;
        }

        .card {
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .card-header {
            background: #4e73df;
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            display: flex;
            align-items: center;
        }

        .card-header i {
            margin-right: 12px;
            font-size: 1.8rem;
        }

        .btn-back {
            background-color: #6c757d;
            border-radius: 50px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .btn-back:hover {
            background-color: #5a6268;
            color: #fff;
        }

        table.dataTable thead {
            background-color: #2e3e75;
            color: #fff;
        }

        table.dataTable tbody tr:hover {
            background-color: #dceeff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        /* Badge warna kelas */
        .badge-ipa {
            background-color: #a8e6a3;
            color: #1b4d1b;
            font-weight: 700;
            padding: 0.45em 0.85em;
            border-radius: 12px;
        }

        .badge-ips {
            background-color: #a3c9f1;
            color: #103e72;
            font-weight: 700;
            padding: 0.45em 0.85em;
            border-radius: 12px;
        }

        .badge-belum {
            background-color: #6c757d;
            color: #f8f9fa;
            font-weight: 600;
            padding: 0.4em 0.8em;
            border-radius: 12px;
        }

        /* Badge status */
        .badge-success {
            background-color: #28a745;
            font-weight: 700;
            padding: 0.45em 0.85em;
            border-radius: 12px;
            color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            font-weight: 700;
            padding: 0.45em 0.85em;
            border-radius: 12px;
            color: white;
        }

        /* Table fonts */
        table.dataTable tbody td {
            font-size: 0.9rem;
            vertical-align: middle !important;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-chart-line"></i>
            Log Perhitungan MOORA
        </div>
        <div class="card-body">
            <a href="data_santri.php" class="btn btn-back btn-sm mb-4">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>

            <div class="table-responsive">
                <table id="logTable" class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Santri</th>
                            <th>Akademik</th>
                            <th>Non Akademik</th>
                            <th>IQ</th>
                            <th>Agama</th>
                            <th><i class="fas fa-star"></i> Skor MOORA</th>
                            <th>Kelas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td><?= number_format($row['r_akademik'], 4) ?></td>
                            <td><?= number_format($row['r_non_akademik'], 4) ?></td>
                            <td><?= number_format($row['r_iq'], 4) ?></td>
                            <td><?= number_format($row['r_agama'], 4) ?></td>
                            <td><strong><?= number_format($row['score'], 4) ?></strong></td>
                            <td>
                                <?php if ($row['kelas'] == 'IPA') : ?>
                                    <span class="badge badge-ipa">IPA</span>
                                <?php elseif ($row['kelas'] == 'IPS') : ?>
                                    <span class="badge badge-ips">IPS</span>
                                <?php else : ?>
                                    <span class="badge badge-belum">Belum</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $row['status'] == 'lulus' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function () {
        $('#logTable').DataTable({
            "pageLength": 10,
            "lengthChange": false,
            "language": {
                "search": "Cari:",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Tidak ada data",
                "paginate": {
                    "previous": "Sebelumnya",
                    "next": "Berikutnya"
                }
            }
        });
    });
</script>
</body>
</html>
