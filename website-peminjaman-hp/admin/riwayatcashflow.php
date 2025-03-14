<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../peminjaman/config.php'; // Pastikan path config.php sesuai

// Ambil data dari tabel cashflow, diurutkan dari terbaru
try {
    // Dapatkan nilai filter tanggal dari form
    $tanggalMulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : '';
    $tanggalAkhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';
    
    $sql = "SELECT id, tanggal, nama, kelas, nominal, type, rincian FROM cashflow";
    $params = [];
    
    if (!empty($tanggalMulai) || !empty($tanggalAkhir)) {
        $sql .= " WHERE ";
        $conditions = [];
        
        if (!empty($tanggalMulai)) {
            $conditions[] = "tanggal >= :tanggal_mulai";
            $params[':tanggal_mulai'] = $tanggalMulai . ' 00:00:00';
        }
        
        if (!empty($tanggalAkhir)) {
            $conditions[] = "tanggal <= :tanggal_akhir";
            $params[':tanggal_akhir'] = $tanggalAkhir . ' 23:59:59';
        }
        
        $sql .= implode(' AND ', $conditions);
    }
    
    $sql .= " ORDER BY tanggal DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $flows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['danger'] = "Gagal mengambil data cashflow: " . $e->getMessage();
    $flows = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Cashflow</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <style>
    html, body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #fff;
      color: #000;
    }
    /* Tabel Monochrome */
    table {
      border-collapse: collapse;
      width: 100%;
    }
    th, td {
      border: 1px solid #000;
      padding: 8px;
      text-align: left;
    }
    th {
      background-color: #fff;
      color: #000;
    }
    /* Tombol Kembali */
    .btn-inverse {
      background-color: #fff;
      color: #000;
      border: 1px solid #000;
      padding: 0.5rem 1rem;
      transition: all 0.3s ease;
      border-radius: 5px;
      display: inline-block;
      text-align: center;
    }
    .btn-inverse:hover {
      background-color: #000;
      color: #fff;
    }
    /* Card styling */
    .box-shadow {
      background-color: #fff;
      border: 1px solid #ccc;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    /* Filter form styling */
    .filter-form {
      padding: 15px;
      background-color: #f8f8f8;
      border-radius: 5px;
    }
    /* Layout styling */
    .content-layout {
      display: flex;
      gap: 20px;
    }
    .filter-column {
      flex: 1;
      min-width: 300px;
    }
    .table-column {
      flex: 3;
    }
    /* Tombol dengan border radius */
    .btn-rounded {
      border-radius: 5px;
      text-align: center;
      padding: 0.5rem 1rem;
    }
    /* Spasi antara elemen */
    .mt-4 {
      margin-top: 1rem;
    }
    .mb-4 {
      margin-bottom: 1rem;
    }
    .mr-4 {
      margin-right: 1rem;
    }
    .ml-4 {
      margin-left: 1rem;
    }
    .p-4 {
      padding: 1rem;
    }
    /* Judul dan narasi */
    .judul {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    .narasi {
      font-size: 0.9rem;
      color: #666;
      margin-bottom: 1.5rem;
    }
  </style>
</head>
<body>
  <div class="container mx-auto p-4">
    <!-- Header -->
    <header class="mb-4 flex justify-between items-center">
      <a href="cashflow.php" class="btn-inverse btn-rounded mr-4">&larr; Kelola Uang</a>
    </header>
    
    <!-- Judul dan Narasi -->
    <div class="mb-4">
      <h1 class="judul">RIWAYAT CASHFLOW</h1>
      <p class="narasi">Laporan transaksi cashflow yang mencatat semua pemasukan dan pengeluaran dana dalam sistem peminjaman HP.</p>
    </div>
    
    <!-- Konten Utama dengan Layout 2 kolom -->
    <div class="content-layout">
      <!-- Kolom Tabel Cashflow -->
      <div class="table-column">
        <!-- Card dengan Tabel -->
        <div class="box-shadow rounded p-4 overflow-hidden">
          <div class="overflow-auto max-h-[600px]" style="overflow-x: auto;">
            <table>
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Nama</th>
                  <th>Kelas</th>
                  <th>Rincian</th>
                  <th>Uang Masuk (Cash)</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($flows)): ?>
                  <?php foreach ($flows as $flow): ?>
                    <tr>
                      <td><?= htmlspecialchars($flow['tanggal']) ?></td>
                      <td><?= htmlspecialchars($flow['nama'] ?: '-') ?></td>
                      <td><?= htmlspecialchars($flow['kelas'] ?: '-') ?></td>
                      <td><?= htmlspecialchars($flow['rincian'] ?: '-') ?></td>
                      <td>
                        <?php if ($flow['type'] === 'in'): ?>
                          Rp <?= number_format($flow['nominal']) ?>
                        <?php else: ?>
                          -Rp <?= number_format($flow['nominal']) ?>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="text-center">Belum ada riwayat cashflow.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Kolom Filter -->
      <div class="filter-column">
        <div class="filter-form">
          <h2 class="text-lg font-bold mb-3">Filter Berdasarkan Tanggal</h2>
          <form method="GET">
            <div class="mb-3">
              <label class="block mb-1">Tanggal Mulai</label>
              <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="w-full px-2 py-1 border rounded" value="<?= $tanggalMulai ?>">
            </div>
            <div class="mb-3">
              <label class="block mb-1">Tanggal Akhir</label>
              <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="w-full px-2 py-1 border rounded" value="<?= $tanggalAkhir ?>">
            </div>
            <div class="flex flex-col">
              <button type="submit" class="btn-inverse btn-rounded mb-2 w-full">Filter</button>
              <a href="riwayatcashflow.php" class="btn-inverse btn-rounded w-full">Reset Filter</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>