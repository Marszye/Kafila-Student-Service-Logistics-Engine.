<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../peminjaman/config.php'; // Pastikan path sudah benar sesuai struktur folder

// Jika form disubmit, simpan ke database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesan = trim($_POST['pesan'] ?? '');
    if (!empty($pesan)) {
        try {
            $sql = "INSERT INTO masukan_anon (pesan) VALUES (:pesan)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':pesan' => $pesan]);
            $_SESSION['message'] = ['type' => 'Sukses', 'text' => 'Pesan anonim berhasil dikirim.'];
        } catch (Exception $e) {
            $_SESSION['danger'] = "Gagal menyimpan masukan: " . $e->getMessage();
        }
    } else {
        $_SESSION['danger'] = "Pesan tidak boleh kosong.";
    }
    header("Location: masukan_anon.php");
    exit;
}

// Ambil semua masukan anonim
try {
    $sql = "SELECT id, pesan, created_at FROM masukan_anon ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $listPesan = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $listPesan = [];
    $_SESSION['danger'] = "Gagal mengambil data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masukan Anonim</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Bootstrap CSS (untuk Toast) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    html, body {
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    body {
      background-color: #fff;
      color: #000;
    }
    /* Tombol default (hitam-putih terbalik) */
    .btn-inverse {
      background-color: #fff;
      color: #000;
      border: 1px solid #000;
      transition: all 0.3s ease;
    }
    .btn-inverse:hover {
      background-color: #000;
      color: #fff;
    }
    /* Style untuk bubble pesan */
    .bubble {
      background-color: #fff;
      border: 1px solid #000;
      color: #000;
      border-radius: 1rem;
      padding: 0.75rem 1rem;
      max-width: 70%;
      margin-bottom: 0.5rem;
      position: relative;
    }
    .bubble::before {
      content: "";
      position: absolute;
      left: -8px;
      top: 10px;
      width: 0;
      height: 0;
      border-top: 8px solid transparent;
      border-bottom: 8px solid transparent;
      border-right: 8px solid #000;
    }
    .bubble::after {
      content: "";
      position: absolute;
      left: -7px;
      top: 10px;
      width: 0;
      height: 0;
      border-top: 7px solid transparent;
      border-bottom: 7px solid transparent;
      border-right: 7px solid #fff;
    }
  </style>
</head>
<body class="bg-white text-black">
  <!-- Toast Notifikasi Danger -->
  <?php if (isset($_SESSION['danger'])): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 2000;">
      <div class="toast show align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            <?= htmlspecialchars($_SESSION['danger']) ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>
    <?php unset($_SESSION['danger']); ?>
  <?php endif; ?>

  <!-- Toast Notifikasi Success/Info -->
  <?php if (isset($_SESSION['message'])): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 2000;">
      <div class="toast show align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            <strong><?= htmlspecialchars($_SESSION['message']['type']) ?>:</strong>
            <?= htmlspecialchars($_SESSION['message']['text']) ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>
    <?php unset($_SESSION['message']); ?>
  <?php endif; ?>

  <div class="container mx-auto p-4">
    <!-- Tombol Kembali -->
    <div class="mb-4">
      <a href="/website-peminjaman-hp/peminjaman/index.php" class="inline-block px-4 py-2 rounded btn-inverse">&larr; Kembali ke Beranda</a>
    </div>
    <h1 class="text-2xl font-bold mb-4">Masukan Anonim</h1>
    <p class="text-sm mb-6 text-gray-600">Tinggalkan pesan atau kritik Anda di sini tanpa perlu menyebutkan nama.</p>

    <!-- Form Masukan -->
    <div class="border border-black rounded p-4 mb-6">
      <form method="POST" action="">
        <div class="mb-4">
          <label class="block mb-1">Pesan Anda (Anonim)</label>
          <textarea name="pesan" class="w-full p-2 border border-black rounded" rows="3" placeholder="Tulis pesan anonim di sini..." required></textarea>
        </div>
        <button type="submit" class="btn-inverse px-4 py-2 rounded">Kirim</button>
      </form>
    </div>

    <!-- Daftar Pesan -->
    <div class="border border-black rounded p-4 max-h-[500px] overflow-auto">
      <?php if (!empty($listPesan)): ?>
        <?php foreach ($listPesan as $row): ?>
          <div class="bubble">
            <p class="text-sm whitespace-pre-line"><?= nl2br(htmlspecialchars($row['pesan'])) ?></p>
            <span class="block text-xs text-gray-500 mt-1">
              <?= htmlspecialchars($row['created_at']) ?>
            </span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-sm text-gray-500">Belum ada masukan anonim yang diposting.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
