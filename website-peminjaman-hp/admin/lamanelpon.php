<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../peminjaman/config.php'; // Pastikan path sesuai dengan struktur folder Anda

// Ambil data dari tabel peminjaman selama 14 hari terakhir dan yang sudah selesai (waktu_kembali tidak null)
try {
    $sql = "SELECT nama, kelas, waktu_pinjam, waktu_kembali 
            FROM peminjaman 
            WHERE waktu_pinjam >= DATE_SUB(NOW(), INTERVAL 14 DAY)
              AND waktu_kembali IS NOT NULL";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $calls = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['danger'] = "Gagal mengambil data: " . $e->getMessage();
    $calls = [];
}

// Pengelompokan data berdasarkan nama (dan kelas)
$grouped = [];
$now = new DateTime();

foreach ($calls as $call) {
    $nama = $call['nama'];
    $kelas = $call['kelas'];
    $waktuPinjam = new DateTime($call['waktu_pinjam']);
    $waktuKembali = new DateTime($call['waktu_kembali']);
    $durasi = ($waktuKembali->getTimestamp() - $waktuPinjam->getTimestamp()) / 60; // dalam menit

    // Hitung biaya total nelpon per panggilan
    if ($durasi <= 16) {
        $callFee = 1000;
        $terlambat = 0; // tidak terlambat
    } else {
        $callFee = 1000 + (floor(($durasi - 16) / 5) * 1000);
        $terlambat = $durasi - 16; // selisih menit
    }

    // Grouping berdasarkan nama dan kelas
    $key = $nama . "||" . $kelas; // key unik untuk grouping
    if (!isset($grouped[$key])) {
        $grouped[$key] = [
            'nama' => $nama,
            'kelas' => $kelas,
            'total_terlambat' => 0, // dalam menit
            'total_nelpon' => 0     // dalam rupiah
        ];
    }
    $grouped[$key]['total_terlambat'] += $terlambat;
    $grouped[$key]['total_nelpon'] += $callFee;
}

// Mengonversi hasil grouping ke array dan mengurutkan berdasarkan total nelpon secara menurun
$groupedData = array_values($grouped);
usort($groupedData, function($a, $b) {
    return $b['total_nelpon'] - $a['total_nelpon'];
});
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lama Nelpon</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    html, body {
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    /* Tabel hitam putih */
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
  </style>
</head>
<body class="bg-white text-black">
  <div class="container mx-auto p-4">
    <!-- Tombol Kembali -->
    <div class="mb-4">
      <a href="/website-peminjaman-hp/peminjaman/index.php" class="inline-block px-4 py-2 rounded border border-black text-black hover:bg-black hover:text-white transition-all">
        &larr; Kembali ke Beranda
      </a>
    </div>
    <h1 class="text-2xl font-bold mb-4">Lama Nelpon</h1>
    <p class="text-sm mb-6 text-gray-600">Data nelpon dari dua minggu terakhir (data sebelum dua minggu direset dihapus).</p>
    <table>
      <thead>
        <tr>
          <th>Nama</th>
          <th>Kelas</th>
          <th>Terlambat (menit)</th>
          <th>Total Nelpon (Rp)</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($groupedData)): ?>
          <?php foreach ($groupedData as $data): ?>
            <tr>
              <td><?= htmlspecialchars($data['nama']) ?></td>
              <td><?= htmlspecialchars($data['kelas']) ?></td>
              <td><?= number_format($data['total_terlambat'], 0) ?></td>
              <td>Rp <?= number_format($data['total_nelpon'], 0) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="text-center">Tidak ada data nelpon.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
