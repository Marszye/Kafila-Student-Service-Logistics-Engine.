<?php
session_start();
include '../peminjaman/config.php'; // Sesuaikan path dengan struktur folder Anda

try {
    // Query: ambil data dari tabel peminjaman hanya untuk method 'debt'
    $sql = "SELECT nama, kelas, bayar AS hutang FROM peminjaman WHERE method = 'debt' ORDER BY nama ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $listHutang = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['danger'] = "Gagal mengambil data: " . $e->getMessage();
    $listHutang = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>List Hutang</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    html, body {
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    /* Tabel hitam putih (monochromic) */
    table {
      border-collapse: collapse;
      width: 100%;
    }
    table, th, td {
      border: 1px solid #000;
    }
    th, td {
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
    <h1 class="text-2xl font-bold mb-4">Daftar Hutang</h1>
    <table>
      <thead>
        <tr>
          <th>Nama</th>
          <th>Kelas</th>
          <th>Hutang</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($listHutang)): ?>
          <?php foreach ($listHutang as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['nama']) ?></td>
              <td><?= htmlspecialchars($row['kelas']) ?></td>
              <td><?= htmlspecialchars($row['hutang']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="3" class="text-center">Tidak ada data hutang.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
