<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../peminjaman/config.php'; // Pastikan path config.php sesuai

// Hitung total dari peminjaman.method='cash'
try {
    $sql = "SELECT COALESCE(SUM(bayar),0) AS totalTelponCash FROM peminjaman WHERE method='cash'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalTelponCash = $row ? $row['totalTelponCash'] : 0;
} catch (Exception $e) {
    $totalTelponCash = 0;
    $_SESSION['danger'] = "Gagal hitung total telpon cash: " . $e->getMessage();
}

// Hitung total pemasukan (cashflow.type='in')
try {
    $sqlIn = "SELECT COALESCE(SUM(nominal),0) AS totalIn FROM cashflow WHERE type='in'";
    $stmtIn = $conn->prepare($sqlIn);
    $stmtIn->execute();
    $rowIn = $stmtIn->fetch(PDO::FETCH_ASSOC);
    $totalIn = $rowIn ? $rowIn['totalIn'] : 0;
} catch (Exception $e) {
    $totalIn = 0;
    $_SESSION['danger'] = "Gagal hitung total pemasukan: " . $e->getMessage();
}

// Hitung total pengeluaran (cashflow.type='out')
try {
    $sqlOut = "SELECT COALESCE(SUM(nominal),0) AS totalOut FROM cashflow WHERE type='out'";
    $stmtOut = $conn->prepare($sqlOut);
    $stmtOut->execute();
    $rowOut = $stmtOut->fetch(PDO::FETCH_ASSOC);
    $totalOut = $rowOut ? $rowOut['totalOut'] : 0;
} catch (Exception $e) {
    $totalOut = 0;
    $_SESSION['danger'] = "Gagal hitung total pengeluaran: " . $e->getMessage();
}

// TOTAL UANG = TelponCash + In - Out
$totalCash = $totalTelponCash + $totalIn - $totalOut;

// Proses penambahan data (uang masuk / keluar) dari modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitFlow'])) {
    $type    = $_POST['flowType']; // 'in' atau 'out'
    $nama    = trim($_POST['nama'] ?? '');
    $kelas   = trim($_POST['kelas'] ?? '');
    $nominal = (int)($_POST['nominal'] ?? 0);
    $rincian = trim($_POST['rincian'] ?? '');

    if ($nominal > 0) {
        try {
            $sqlFlow = "INSERT INTO cashflow (tanggal, nama, kelas, nominal, type, rincian)
                        VALUES (NOW(), :nama, :kelas, :nominal, :type, :rincian)";
            $stmtFlow = $conn->prepare($sqlFlow);
            $stmtFlow->execute([
                ':nama'    => $nama,
                ':kelas'   => $kelas,
                ':nominal' => $nominal,
                ':type'    => $type,
                ':rincian' => $rincian
            ]);
            $_SESSION['message'] = [
                'type' => 'Sukses',
                'text' => ($type === 'in' ? 'Uang masuk' : 'Uang keluar') . ' berhasil dicatat.'
            ];
        } catch (Exception $e) {
            $_SESSION['danger'] = "Gagal mencatat data: " . $e->getMessage();
        }
    } else {
        $_SESSION['danger'] = "Nominal harus lebih dari 0.";
    }
    header("Location: cashflow.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Keuntungan (Cashflow)</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Font: Plus Jakarta Sans -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS (untuk modal, toast) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    html, body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #fff;
      color: #000;
    }
    /* Card total: tanpa hover effect */
    .card-total {
      border: 1px solid #000;
      background-color: #fff;
      /* no hover effect */
    }
    /* Tombol Admin & lain-lain */
    .btn-inverse {
      background-color: #fff;
      color: #000;
      border: 1px solid #000;
      transition: all 0.3s ease;
      padding: 0.5rem 1rem;
    }
    .btn-inverse:hover {
      background-color: #000;
      color: #fff;
    }
  </style>
</head>
<body>
  <div class="container mx-auto p-4">
    <!-- Tombol Kembali ke Dashboard -->
    <div class="mb-4">
      <a href="dashboard.php" class="inline-block btn-inverse rounded">&larr; Dashboard Admin</a>
    </div>

    <!-- Notifikasi -->
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

    <h1 class="text-2xl font-bold mb-6">KELOLA KEUNTUNGAN (CASHFLOW)</h1>

    <!-- Card Uang + Tiga Tombol -->
    <div class="card-total p-6 rounded mb-8">
      <div class="text-xl mb-2">TOTAL UANG (HASIL TELPON)</div>
      <div class="text-4xl font-bold mb-4">Rp <?= number_format($totalCash, 0) ?></div>
      <div class="flex gap-2">
        <!-- Tombol Uang Masuk -->
        <button class="btn-inverse rounded" id="btnUangMasuk">UANG MASUK</button>
        <!-- Tombol Uang Keluar -->
        <button class="btn-inverse rounded" id="btnUangKeluar">UANG KELUAR</button>
        <!-- Tombol Riwayat -->
        <a href="riwayatcashflow.php" class="btn-inverse rounded">RIWAYAT</a>
      </div>
    </div>
  </div>

  <!-- Modal UANG MASUK -->
  <div class="modal fade" id="modalMasuk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border:1px solid #000;">
        <div class="modal-header" style="background-color:#000; color:#fff;">
          <h5 class="modal-title">UANG MASUK</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" class="modal-body">
          <input type="hidden" name="flowType" value="in" />
          <div class="mb-3">
            <label class="form-label">Jumlah Uang (Angka)</label>
            <input type="number" name="nominal" class="form-control" min="1" required />
          </div>
          <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" name="nama" class="form-control" placeholder="Opsional" />
          </div>
          <div class="mb-3">
            <label class="form-label">Kelas</label>
            <input type="text" name="kelas" class="form-control" placeholder="Opsional" />
          </div>
          <div class="mb-3">
            <label class="form-label">Rincian</label>
            <input type="text" name="rincian" class="form-control" placeholder="Misal: Bayar Telpon" />
          </div>
          <div class="text-end">
            <button type="submit" name="submitFlow" class="btn-inverse rounded">KONFIRMASI</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal UANG KELUAR -->
  <div class="modal fade" id="modalKeluar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" style="border:1px solid #000;">
        <div class="modal-header" style="background-color:#000; color:#fff;">
          <h5 class="modal-title">UANG KELUAR</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" class="modal-body">
          <input type="hidden" name="flowType" value="out" />
          <div class="mb-3">
            <label class="form-label">Jumlah Uang (Angka)</label>
            <input type="number" name="nominal" class="form-control" min="1" required />
          </div>
          <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" name="nama" class="form-control" placeholder="Opsional" />
          </div>
          <div class="mb-3">
            <label class="form-label">Kelas</label>
            <input type="text" name="kelas" class="form-control" placeholder="Opsional" />
          </div>
          <div class="mb-3">
            <label class="form-label">Rincian</label>
            <input type="text" name="rincian" class="form-control" placeholder="Misal: Bayar Tagihan Listrik" />
          </div>
          <div class="text-end">
            <button type="submit" name="submitFlow" class="btn-inverse rounded">KONFIRMASI</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function(){
      $('#btnUangMasuk').on('click', function(){
        $('#modalMasuk').modal('show');
      });
      $('#btnUangKeluar').on('click', function(){
        $('#modalKeluar').modal('show');
      });
    });
  </script>
</body>
</html>
