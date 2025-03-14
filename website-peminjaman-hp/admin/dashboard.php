<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard Admin</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts: Plus Jakarta Sans (opsional) -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Font Awesome (ikon) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <style>
    html, body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #fff;
      color: #000;
    }
    /* Hover efek sederhana: ikon dan teks jadi kebalik warna */
    .icon-card:hover {
      background-color: #000;
      color: #fff;
      transition: all 0.3s ease;
    }
  </style>
</head>
<body>
  <!-- Tombol Kembali -->
  <div class="m-4">
    <a href="/website-peminjaman-hp/peminjaman/index.php" class="inline-block px-4 py-2 border border-black text-black hover:bg-black hover:text-white transition-all rounded">
      &larr; Kembali
    </a>
  </div>

  <!-- Judul Dashboard -->
  <div class="text-center my-6">
    <h1 class="text-3xl font-bold">DASHBOARD ADMIN</h1>
  </div>

  <!-- Container Ikon -->
  <div class="max-w-4xl mx-auto flex flex-col md:flex-row items-center justify-center gap-8">
    <!-- Card Ikon 1 - Kelola Hutang -->
    <a href="kelolahutang.php" class="icon-card w-48 h-48 border border-black flex flex-col items-center justify-center rounded-lg p-4 text-center cursor-pointer">
      <i class="fas fa-coins text-6xl mb-2"></i>
      <span class="text-sm">Kelola Hutang</span>
    </a>

    <!-- Card Ikon 2 - Kelola HP -->
    <a href="kelolahp.php" class="icon-card w-48 h-48 border border-black flex flex-col items-center justify-center rounded-lg p-4 text-center cursor-pointer">
      <i class="fas fa-mobile-alt text-6xl mb-2"></i>
      <span class="text-sm">Kelola HP</span>
    </a>

    <!-- Card Ikon 3 - Cashflow -->
    <a href="cashflow.php" class="icon-card w-48 h-48 border border-black flex flex-col items-center justify-center rounded-lg p-4 text-center cursor-pointer">
      <i class="fas fa-money-bill-wave text-6xl mb-2"></i>
      <span class="text-sm">Transaksi Pembayaran</span>
    </a>
  </div>
</body>
</html>