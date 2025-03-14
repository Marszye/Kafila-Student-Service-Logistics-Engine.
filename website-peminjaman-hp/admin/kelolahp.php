<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../peminjaman/config.php';

// Proses penambahan HP via form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_hp'])) {
    $newHp = trim($_POST['hp_name'] ?? '');
    if (!empty($newHp)) {
        try {
            $sql = "INSERT INTO hp_list (hp) VALUES (:hp)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':hp' => $newHp]);
            $_SESSION['message'] = ['type' => 'Sukses', 'text' => "HP '$newHp' berhasil ditambahkan."];
        } catch (Exception $e) {
            $_SESSION['danger'] = "Gagal menambah HP: " . $e->getMessage();
        }
    } else {
        $_SESSION['danger'] = "Nama HP tidak boleh kosong.";
    }
    header("Location: kelolahp.php");
    exit;
}

// Proses penghapusan HP
if (isset($_GET['hapus'])) {
    $hpId = $_GET['hapus'];
    try {
        $sql = "DELETE FROM hp_list WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $hpId]);
        $_SESSION['message'] = ['type' => 'Sukses', 'text' => "HP berhasil dihapus."];
    } catch (Exception $e) {
        $_SESSION['danger'] = "Gagal menghapus HP: " . $e->getMessage();
    }
    header("Location: kelolahp.php");
    exit;
}

// Ambil data HP dari tabel hp_list
try {
    $sql = "SELECT id, hp FROM hp_list ORDER BY hp ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $finalHps = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['danger'] = "Gagal mengambil data HP: " . $e->getMessage();
    $finalHps = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola HP</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <style>
    html, body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #fff;
      color: #000;
    }
    .hp-card {
      border: 1px solid #000;
      background-color: #fff;
      transition: all 0.3s ease;
      position: relative;
      padding: 1.5rem;
    }
    .hp-card:hover {
      background-color: #000;
      color: #fff;
    }
    /* Style tombol silang tanpa lingkaran */
    .delete-btn {
      position: absolute;
      top: 5px;
      right: 5px;
      cursor: pointer;
      font-size: 1.25rem;
      line-height: 1;
      transition: color 0.3s ease;
    }
    .delete-btn:hover {
      color: #ff0000;
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
        <strong><?= htmlspecialchars($_SESSION['message']['type']) ?>:</strong> <?= htmlspecialchars($_SESSION['message']['text']) ?>
      </div>
      <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['danger'])): ?>
      <div class="mb-4 p-3 border border-black rounded text-center bg-red-200">
        <strong><?= htmlspecialchars($_SESSION['danger']) ?></strong>
      </div>
      <?php unset($_SESSION['danger']); ?>
    <?php endif; ?>

    <h1 class="text-2xl font-bold mb-4">KELOLA HANDPHONE</h1>
    <p class="text-sm text-gray-600 mb-6">Daftar HP yang tersimpan dalam database</p>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
      <?php if (!empty($finalHps)): ?>
        <?php foreach ($finalHps as $hp): ?>
          <div class="hp-card flex flex-col items-center justify-center rounded text-center">
            <!-- Tombol Hapus Silang -->
            <a href="?hapus=<?= $hp['id'] ?>" 
               class="delete-btn"
               onclick="return confirm('Yakin hapus HP ini?')">
              &times;
            </a>
            
            <i class="fas fa-mobile-alt text-4xl mb-2"></i>
            <span class="text-sm"><?= htmlspecialchars($hp['hp']) ?></span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-sm text-gray-600 col-span-full">Belum ada data HP.</p>
      <?php endif; ?>
    </div>

    <div class="mt-8">
      <h2 class="text-lg font-semibold mb-2">TAMBAH HP</h2>
      <form method="POST" class="flex gap-2 items-center">
        <input type="text" name="hp_name" class="p-2 border border-black rounded w-48"
               placeholder="Contoh: HP 05" required>
        <button type="submit" name="tambah_hp" class="btn-inverse rounded">Tambah</button>
      </form>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
</body>
</html>