<?php
session_start();
include 'config.php'; // Koneksi PDO sesuai kode lama

// Ambil data santri untuk autocomplete nama (datalist)
$sql = "SELECT nama FROM santri ORDER BY nama";
$stmt = $conn->prepare($sql);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung status peminjaman
$countDipinjam   = 0;
$countBatal      = 0;
$countTerlambat  = 0;
$countTepatWaktu = 0;

try {
    $sqlStatus = "SELECT status, bayar FROM peminjaman";
    $stmtStatus = $conn->prepare($sqlStatus);
    $stmtStatus->execute();
    $rows = $stmtStatus->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        $mainStatus = strtolower($r['status']);
        $bayar      = (int)($r['bayar'] ?? 0);

        if ($mainStatus === 'dipinjam') {
            $countDipinjam++;
        } elseif ($mainStatus === 'batal') {
            $countBatal++;
        } elseif ($mainStatus === 'selesai') {
            // Jika bayar>0 => Terlambat, else Tepat Waktu
            if ($bayar > 0) {
                $countTerlambat++;
            } else {
                $countTepatWaktu++;
            }
        }
    }
} catch (Exception $e) {
    error_log("Error menghitung status peminjaman: " . $e->getMessage());
    // Anda dapat menambahkan notifikasi $_SESSION['danger'] di sini jika mau
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>IZIN NELPON ORTU PRAKTIS</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <!-- Bootstrap CSS (untuk modal, toast) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    /* Gunakan font Plus Jakarta Sans di seluruh halaman */
    html, body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #f8f8f8; /* latar abu muda */
      color: #222;
    }
    /* Header dan navigasi */
    .header {
      background-color: #333;
      color: #fff;
    }
    .header-title {
      font-size: 1.25rem;
      font-weight: 600;
    }
    .header-subtitle {
      font-size: 0.75rem;
    }
    .header-nav a {
      background-color: #444;
      color: #fff;
      border: 1px solid #444;
      transition: all 0.2s ease;
    }
    .header-nav a:hover {
      background-color: #222;
      border-color: #222;
    }
    /* Tombol default (kecuali PINJAM): latar abu terang, teks lebih gelap */
    .btn-inverse {
      background-color: #e0e0e0;
      color: #333;
      border: 1px solid #999;
      transition: all 0.3s ease;
    }
    .btn-inverse:hover {
      background-color: #333;
      color: #fff;
      border-color: #333;
    }
    /* Tombol PINJAM: latar #333, teks putih, tanpa hover */
    .btn-pinjam {
      background-color: #333;
      color: #fff;
      border: 1px solid #333;
    }
    /* Modal header warna #333, teks putih */
    .modal-header-black {
      background-color: #333;
      color: #fff;
      border-bottom: 1px solid #333;
    }
    .modal-header-black:hover {
      background-color: #333;
      color: #fff;
    }
    /* Tombol aksi (Selesai/Batal) */
    .btn-auto {
      border-radius: 0.4rem;
      padding: 0.5rem 1rem;
      margin: 0 0.2rem;
      width: 6rem;
    }
    /* Box styling untuk form dan tabel */
    .box-shadow {
      background-color: #fff;
      border: 1px solid #ccc;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    /* Tabel border */
    table.min-w-full {
      border: 1px solid #aaa;
    }
    table.min-w-full th, table.min-w-full td {
      border: 1px solid #ccc;
      padding: 8px;
    }
    /* Status color coding */
    .status-dipinjam {
      background-color: #FFF8E3;
    }
    .status-terlambat {
      background-color: #FFEAE3;
    }
    .status-tepat-waktu {
      background-color: #E3FFE4;
    }
    /* Button-group di history: tombol batal muncul saat hover */
    .button-group .cancel-btn {
      display: none;
      margin-left: 0.3rem;
    }
    .button-group:hover .cancel-btn {
      display: inline-block;
    }
  </style>
</head>
<body>
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

  <!-- Header -->
  <header class="header px-4 py-2 flex justify-between items-center">
    <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 5px;">
    <h1 style="font-size: 2rem; font-weight: bold;">IZIN NELPON ORTU PRAKTIS</h1>        
    <p style="font-size: 0.5rem;">V2 FROM PURNOMO INC</p>
    </div>
    <nav class="header-nav space-x-2">
      <a href="/website-peminjaman-hp/admin/login.php" class="px-3 py-1 rounded">ADMIN</a>
      <a href="/website-peminjaman-hp/admin/lamanelpon.php" class="px-3 py-1 rounded">NELPON TERLAMA</a>
      <a href="/website-peminjaman-hp/admin/listhutang.php" class="px-3 py-1 rounded">HUTANG</a>
      <a href="/website-peminjaman-hp/admin/askmeirs.php" class="px-3 py-1 rounded">ASKMEIRS</a>
      <a href="/website-peminjaman-hp/admin/masukan_anon.php" class="px-3 py-1 rounded">MASUKAN</a>
    </nav>
  </header>

  <main class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row gap-6">
      <!-- Form Peminjaman (kiri) -->
      <div class="mt-4 w-full md:w-1/4">
        <div class="box-shadow rounded p-4">
          <form id="pinjamForm" method="POST" action="proses_peminjaman.php">
            <!-- Nama: Input text dengan datalist untuk autocomplete -->
            <div class="mb-4">
              <label class="block mb-1">Nama</label>
              <input type="text" list="namaList" id="nama" name="nama" class="w-full p-2 border rounded" placeholder="Ketik nama..." required>
              <datalist id="namaList">
                <?php
                  foreach ($students as $row) {
                    echo '<option value="' . htmlspecialchars($row['nama']) . '"></option>';
                  }
                ?>
              </datalist>
            </div>
            <!-- Kelas Santri -->
            <div class="mb-4">
              <label class="block mb-1">Kelas</label>
              <input type="text" id="kelas" name="kelas" class="w-full p-2 border rounded" readonly />
            </div>
            <!-- Jenis HP -->
            <div class="mb-4">
              <label class="block mb-1">Jenis HP</label>
              <select class="w-full p-2 border rounded" name="hp" id="hp" required>
                <option value="">Pilih HP</option>
              </select>
            </div>
            <!-- Tombol Submit PINJAM -->
            <button type="submit" id="submitBtn" class="w-full py-2 rounded btn-pinjam" disabled>
              PINJAM
            </button>
            <p class="text-center text-xs mt-4 text-gray-500">&copy; DEVELOPER BY MARSZYE</p>          </form>
        </div>

        <!-- Card Status di bawah Form -->
        <div class="mt-4 p-4 box-shadow rounded">
          <h2 class="text-lg font-bold mb-2">Status Peminjaman</h2>
          <?php
            // Hitung status
            // (Kode sudah di atas, kita sudah punya $countDipinjam, $countBatal, $countTerlambat, $countTepatWaktu)
            $countSelesai = $countTerlambat + $countTepatWaktu;
          ?>
          <div class="flex flex-col gap-2">
            <!-- Card Peminjaman -->
            <div class="p-2 border rounded flex justify-between items-center">
              <span> Dipinjam</span>
              <span class="font-bold text-xl"><?= $countDipinjam ?></span>
            </div>
            <!-- Card Selesai -->
            <div class="p-2 border rounded flex flex-col">
              <div class="flex justify-between items-center">
                <span>Selesai</span>
                <span class="font-bold text-xl">
                  <?= $countSelesai ?>
                </span>
              </div>
            </div>
            <!-- Card Batal -->
            <div class="p-2 border rounded flex justify-between items-center">
              <span>Batal</span>
              <span class="font-bold text-xl"><?= $countBatal ?></span>
            </div>
          </div>
        </div>
        <!-- End Card Status -->
      </div>

      <!-- History (Tabel dengan scroll & garis, di kanan) -->
      <div class="mt-4 w-full md:w-3/4">
        <div class="box-shadow rounded p-2 overflow-auto max-h-[665px]">
          <table class="min-w-full border-collapse">
            <thead class="bg-gray-200">
              <tr>
                <th class="px-2 py-2 text-left">No</th>
                <th class="px-2 py-2 text-left">Nama Santri</th>
                <th class="px-2 py-2 text-left">Kelas</th>
                <th class="px-2 py-2 text-left">HP</th>
                <th class="px-2 py-2 text-left">Waktu Pinjam</th>
                <th class="px-2 py-2 text-left">Waktu Kembali</th>
                <th class="px-2 py-2 text-left">Status</th>
                <th class="px-2 py-2 text-left">Total Bayar</th>
                <th class="px-2 py-2 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php include 'history.php'; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Modal Konfirmasi Pembayaran -->
  <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <!-- Modal Header warna #333, teks putih -->
        <div class="modal-header modal-header-black">
          <h5 class="modal-title">Konfirmasi Pembayaran</h5>
        </div>
        <!-- Modal Body -->
        <div class="modal-body">
          <h4>Total Biaya: <span id="modalFee" class="fw-bold"></span></h4>
          <p class="text-sm mt-2">Pilih Metode Pembayaran:</p>
          <div class="mt-2">
            <label class="inline-flex items-center me-4">
              <input type="radio" class="form-radio" name="paymentMethod" value="cash" checked />
              <span class="ms-2">Cash</span>
            </label>
            <label class="inline-flex items-center">
              <input type="radio" class="form-radio" name="paymentMethod" value="debt" />
              <span class="ms-2">Debt</span>
            </label>
          </div>
        </div>
        <!-- Modal Footer -->
        <div class="modal-footer d-flex justify-content-end">
          <button type="button" class="btn-inverse btn-auto" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn-inverse btn-auto ms-2" id="finishPayment">Selesai</button>
        </div>
        <!-- Form Konfirmasi (tersembunyi) -->
        <form id="completeForm" method="POST" action="complete.php" class="d-none">
          <input type="hidden" name="id" id="modalId" />
          <input type="hidden" name="method" id="modalMethod" />
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts: jQuery, Bootstrap -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function () {
      // AJAX: Ambil kelas, cek aturan, dan daftar HP
      $('#nama').on('change', function () {
        const nama = $(this).val();
        if (nama) {
          // Cek apakah nama sudah ada dalam transaksi yang sedang berlangsung
          $.ajax({
            url: 'cek_nama.php',
            type: 'GET',
            data: { nama: nama },
            success: function (response) {
              const result = JSON.parse(response);
              if (result.error) {
                alert(result.error);
                $('#nama').val('');
                $('#hp').empty().append('<option value="">Pilih HP</option>');
                $('#submitBtn').prop('disabled', true);
              }
            },
            error: function () {
              alert('Maaf, server tidak merespons.');
            }
          });

          $.ajax({
            url: 'get_kelas.php',
            type: 'GET',
            data: { nama: nama },
            success: function (response) {
              $('#kelas').val(response.trim());
            },
            error: function () {
              $('#kelas').val('Kelas tidak ditemukan');
            }
          });
          $.ajax({
            url: 'cek_aturan.php',
            type: 'GET',
            data: { nama: nama },
            success: function (response) {
              const result = JSON.parse(response);
              if (result.error) {
                alert(result.error);
                $('#nama').val('');
                $('#hp').empty().append('<option value="">Pilih HP</option>');
                $('#submitBtn').prop('disabled', true);
              }
            },
            error: function () {
              alert('Maaf, server tidak merespons.');
            }
          });
          $.ajax({
            url: 'get_available_hps.php',
            type: 'GET',
            success: function (response) {
              const availableHps = JSON.parse(response);
              const $hpSelect = $('#hp');
              $hpSelect.empty();
              $hpSelect.append('<option value="">Pilih HP</option>');
              availableHps.forEach(hp => {
                $hpSelect.append(`<option value="${hp}">${hp}</option>`);
              });
            },
            error: function () {
              alert('Gagal mendapatkan HP yang tersedia.');
            }
          });
        } else {
          $('#kelas').val('');
          $('#hp').empty().append('<option value="">Pilih HP</option>');
          $('#submitBtn').prop('disabled', true);
        }
      });

      // Validasi pemilihan HP
      $('#hp').on('change', function () {
        const hp = $(this).val();
        if (hp) {
          $.ajax({
            url: 'cek_hp.php',
            type: 'GET',
            data: { hp: hp },
            success: function (response) {
              const result = JSON.parse(response);
              if (result.error) {
                alert(result.error);
                $('#hp').val('');
                $('#submitBtn').prop('disabled', true);
              } else {
                $('#submitBtn').prop('disabled', false);
              }
            },
            error: function () {
              alert('Tidak dapat memeriksa ketersediaan HP.');
              $('#submitBtn').prop('disabled', true);
            }
          });
        } else {
          $('#submitBtn').prop('disabled', true);
        }
      });
      
      // Cegah submit jika HP belum dipilih
      $('#pinjamForm').on('submit', function (e) {
        if ($('#hp').val() === '') {
          alert('Silahkan pilih HP terlebih dahulu.');
          e.preventDefault();
        }
      });

      // Tampilkan modal konfirmasi pembayaran saat tombol "Selesai" ditekan
      $(document).on('click', '.btn-selesai', function () {
        const id = $(this).data('id');
        const biaya = $(this).data('biaya');
        $('#modalFee').text('Rp ' + biaya);
        $('#modalId').val(id);
        $('#confirmModal').modal('show');
      });
      
      // Saat tombol "Selesai" di modal ditekan, ambil metode pembayaran dan submit form
      $('#finishPayment').on('click', function(){
        const method = $('input[name="paymentMethod"]:checked').val();
        $('#modalMethod').val(method);
        $('#completeForm').submit();
      });
    });
  </script>
</body>
</html>