<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../peminjaman/config.php'; // Sesuaikan path config.php

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (!empty($username) && !empty($password)) {
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: dashboard.php"); // Redirect ke dashboard.php
            exit;
        } else {
            $_SESSION['danger'] = "Username atau password salah.";
            header("Location: login.php");
            exit;
        }
    } else {
        $_SESSION['danger'] = "Username dan password harus diisi.";
        header("Location: login.php");
        exit;
    }
}

// Proses pendaftaran (trigger dari modal registrasi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $regUsername = trim($_POST['reg_username'] ?? '');
    $regPassword = trim($_POST['reg_password'] ?? '');
    
    if (!empty($regUsername) && !empty($regPassword)) {
        $hash = password_hash($regPassword, PASSWORD_DEFAULT);
        try {
            $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':username' => $regUsername, ':password' => $hash]);
            $_SESSION['message'] = ['type' => 'Sukses', 'text' => 'Pendaftaran berhasil. Silakan login.'];
            header("Location: login.php");
            exit;
        } catch (Exception $e) {
            $_SESSION['danger'] = "Gagal mendaftar: " . $e->getMessage();
            header("Location: login.php");
            exit;
        }
    } else {
        $_SESSION['danger'] = "Semua field harus diisi.";
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Asrama</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <!-- Google Fonts: Plus Jakarta Sans -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS (untuk modal, toast) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    html, body {
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    /* Tema Monochrome */
    .btn-black {
      background-color: #000;
      color: #fff;
      border: 1px solid #000;
    }
    .btn-black:hover {
      background-color: #fff;
      color: #000;
    }
    /* Link tanpa background */
    .text-link {
      color: #000;
      text-decoration: underline;
      cursor: pointer;
    }
    .text-link:hover {
      color: #000;
    }
    /* Modal Lock dan Registrasi */
    .modal-content {
      border: 1px solid #000;
    }
    /* Ikon silang */
    .close-icon {
      position: absolute;
      top: 10px;
      right: 10px;
      cursor: pointer;
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
            <strong><?= htmlspecialchars($_SESSION['message']['type']) ?>:</strong> <?= htmlspecialchars($_SESSION['message']['text']) ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>
    <?php unset($_SESSION['message']); ?>
  <?php endif; ?>

  <!-- Kontainer Utama -->
  <div class="flex items-center justify-center min-h-screen bg-white">
    <div class="w-full max-w-sm p-8 space-y-6 border rounded-lg shadow-md relative">
      <!-- Ikon silang untuk kembali ke index.php -->
      <a href="/website-peminjaman-hp/peminjaman/index.php" class="close-icon">
        <i class="fas fa-times text-xl"></i>
      </a>
      
      <h2 class="text-2xl font-bold text-center">LOGIN ASRAMA</h2>
      <form method="POST" class="space-y-4">
        <div>
          <input type="text" name="username" placeholder="USERNAME" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
        </div>
        <div>
          <input type="password" name="password" placeholder="PASSWORD" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
        </div>
        <div>
          <button type="submit" name="login" class="w-full px-4 py-2 btn-black rounded-lg">LOGIN</button>
        </div>
      </form>
      <div class="text-center">
        <span class="text-sm">Belum punya akun?</span>
        <span id="openLock" class="text-link text-sm font-bold">Daftar Sekarang</span>
      </div>
    </div>
  </div>

  <!-- Modal Lock (untuk membuka akses pendaftaran) -->
  <div id="modalLock" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-black text-white rounded-lg p-6 w-80">
      <h3 class="text-lg font-bold mb-4">What Global Boarding School App?</h3>
      <input type="password" id="lockPassword" class="w-full px-4 py-2 border rounded-lg mb-4 bg-gray-800 text-white focus:outline-none" placeholder="Masukkan jawaban">
      <div class="flex justify-between">
        <button id="cancelBtn" class="px-4 py-2 text-white border border-white rounded w-1/2">Batal</button>
        <button id="unlockBtn" class="px-4 py-2 text-white border border-white rounded w-1/2">Unlock</button>
      </div>
    </div>
  </div>

  <!-- Modal Registrasi (untuk pendaftaran) -->
  <div id="modalRegister" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white text-black rounded-lg p-6 w-80">
      <h3 class="text-lg font-bold mb-4">Daftar Akun</h3>
      <form method="POST">
        <div class="mb-4">
          <input type="text" name="reg_username" placeholder="Username Baru" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
        </div>
        <div class="mb-4">
          <input type="password" name="reg_password" placeholder="Password Baru" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
        </div>
        <div class="flex justify-between">
          <button type="submit" name="register" class="px-4 py-2 btn-black rounded">Daftar</button>
          <span id="closeRegister" class="text-link self-center">Kembali ke Login</span>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts: jQuery, Bootstrap -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    $(document).ready(function(){
      // Buka modal lock ketika klik "Daftar Sekarang"
      $('#openLock').on('click', function(){
        $('#modalLock').removeClass('hidden');
      });
      
      // Buka modal registrasi setelah unlock
      $('#unlockBtn').on('click', function(){
        var pass = $('#lockPassword').val();
        if(pass === 'kormaapps'){
          $('#modalLock').addClass('hidden');
          $('#modalRegister').removeClass('hidden');
        } else {
          alert('Jawaban salah. Coba lagi.');
        }
      });
      
      // Tutup modal lock dan tidak melanjutkan ke registrasi
      $('#cancelBtn').on('click', function(){
        $('#modalLock').addClass('hidden');
      });
      
      // Tutup modal registrasi dan kembali ke login
      $('#closeRegister').on('click', function(){
        $('#modalRegister').addClass('hidden');
      });
    });
  </script>
</body>
</html>