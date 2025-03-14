<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../peminjaman/config.php';

// Jika ada parameter GET nama dan kelas, update status method menjadi 'cash'
if (isset($_GET['nama']) && isset($_GET['kelas'])) {
    $nama = $_GET['nama'];
    $kelas = $_GET['kelas'];
    try {
        $sql = "UPDATE peminjaman SET method = 'cash' 
                WHERE nama = :nama 
                AND kelas = :kelas 
                AND method = 'debt'";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':nama' => $nama, ':kelas' => $kelas]);
        $_SESSION['message'] = ['type' => 'Sukses', 'text' => 'Status berhasil diubah menjadi cash untuk '.$nama];
    } catch (Exception $e) {
        $_SESSION['danger'] = "Gagal mengubah status: " . $e->getMessage();
    }
    header("Location: kelolahutang.php");
    exit;
}

// Ambil data hutang yang sudah digabungkan
try {
    $sql = "SELECT 
                nama, 
                kelas, 
                SUM(bayar) as total_hutang 
            FROM peminjaman 
            WHERE method = 'debt' 
            GROUP BY nama, kelas 
            ORDER BY nama ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $hutangList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['danger'] = "Gagal mengambil data: " . $e->getMessage();
    $hutangList = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Hutang</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    html, body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #fff;
      color: #000;
    }
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
    .btn-inverse {
      background-color: #fff;
      color: #000;
      border: 1px solid #000;
      padding: 0.5rem 1rem;
      transition: all 0.3s ease;
    }
    .btn-inverse:hover {
      background-color: #000;
      color: #fff;
    }
  </style>
</head>
<body>
  <div class="container mx-auto p-4">
    <div class="mb-4">
      <a href="dashboard.php" class="inline-block btn-inverse rounded">
        &larr; Dashboard Admin 
      </a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
      <div class="mb-4 p-3 border border-black rounded text-center">
        <strong><?= htmlspecialchars($_SESSION['message']['type'] ?? '') ?>:</strong> 
        <?= htmlspecialchars($_SESSION['message']['text'] ?? '') ?>
      </div>
      <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['danger'])): ?>
      <div class="mb-4 p-3 border border-black rounded text-center bg-red-200">
        <strong><?= htmlspecialchars($_SESSION['danger'] ?? '') ?></strong>
      </div>
      <?php unset($_SESSION['danger']); ?>
    <?php endif; ?>

    <h1 class="text-2xl font-bold mb-4">Kelola Hutang</h1>
    <table>
      <thead>
        <tr>
          <th>Nama</th>
          <th>Kelas</th>
          <th>Total Hutang (Rp)</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($hutangList)): ?>
          <?php foreach ($hutangList as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['nama'] ?? '') ?></td>
              <td><?= htmlspecialchars($row['kelas'] ?? '') ?></td>
              <td><?= number_format($row['total_hutang'] ?? 0) ?></td>
              <td>
                <a href="kelolahutang.php?nama=<?= urlencode($row['nama'] ?? '') ?>&kelas=<?= urlencode($row['kelas'] ?? '') ?>" 
                   class="btn-inverse rounded" 
                   onclick="return confirm('Ubah semua hutang <?= htmlspecialchars($row['nama'] ?? '') ?> menjadi cash?')">
                   Ubah ke Cash
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="text-center">Tidak ada data hutang.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>