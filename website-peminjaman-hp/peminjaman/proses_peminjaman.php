<?php
session_start();
include 'config.php';
include 'komponen_aturan_peminjaman.php';

try {
    $conn->beginTransaction();

    $nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_SPECIAL_CHARS);
    $hp = filter_input(INPUT_POST, 'hp', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($nama) || empty($hp)) {
        throw new Exception('Nama dan HP harus diisi.');
    }

    // Validasi nama tidak ada dalam transaksi yang sedang berlangsung
    $sqlCheckNama = "SELECT COUNT(*) FROM peminjaman WHERE nama = :nama AND status = 'Dipinjam'";
    $stmtCheckNama = $conn->prepare($sqlCheckNama);
    $stmtCheckNama->bindParam(':nama', $nama, PDO::PARAM_STR);
    $stmtCheckNama->execute();
    $countNama = $stmtCheckNama->fetchColumn();

    if ($countNama > 0) {
        throw new Exception('Nama sudah ada dalam transaksi yang sedang berlangsung.');
    }

    // Dapatkan kelas santri dari database
    $sqlKelas = "SELECT kelas FROM santri WHERE nama = :nama";
    $stmtKelas = $conn->prepare($sqlKelas);
    $stmtKelas->bindParam(':nama', $nama, PDO::PARAM_STR);
    $stmtKelas->execute();
    $kelas = $stmtKelas->fetchColumn();

    if ($kelas === false) {
        throw new Exception('Santri tidak ditemukan.');
    }

    // Cek aturan harian
    if (!cekHari($kelas)) {
        throw new Exception('Peminjaman tidak diperbolehkan hari ini untuk kelas Anda.');
    }

    // Cek ketersediaan HP
    $sqlCheckHP = "SELECT COUNT(*) FROM peminjaman WHERE hp = :hp AND status = 'Dipinjam'";
    $stmtCheckHP = $conn->prepare($sqlCheckHP);
    $stmtCheckHP->bindParam(':hp', $hp, PDO::PARAM_STR);
    $stmtCheckHP->execute();
    $count = $stmtCheckHP->fetchColumn();

    if ($count > 0) {
        throw new Exception('HP sedang dipinjam.');
    }

    // Cek jam peminjaman (format HHmm, antara 08:00 dan 22:00)
    $current_time = date('Hi');
    $allowedTime = ($current_time >= 800 && $current_time <= 2200);
    if (!$allowedTime) {
        throw new Exception('Luar jam peminjaman (08:00 - 22:00).');
    }

    // Tentukan method peminjaman (di sini kita set default sebagai 'cash')
    $method = 'cash';

    // Buat transaksi baru di tabel peminjaman
    $sqlInsert = "INSERT INTO peminjaman (nama, kelas, hp, status, waktu_pinjam, method) 
                  VALUES (:nama, :kelas, :hp, 'Dipinjam', NOW(), :method)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bindParam(':nama', $nama, PDO::PARAM_STR);
    $stmtInsert->bindParam(':kelas', $kelas, PDO::PARAM_STR);
    $stmtInsert->bindParam(':hp', $hp, PDO::PARAM_STR);
    $stmtInsert->bindParam(':method', $method, PDO::PARAM_STR);
    $stmtInsert->execute();

    if ($stmtInsert->rowCount() === 0) {
        throw new Exception('Gagal membuat transaksi baru.');
    }

    // Jika method adalah cash, hitung total bayar dan catat ke tabel cashflow.
    // Karena pada saat peminjaman, waktu_kembali belum tercatat, kita asumsikan total bayar default = Rp1.000.
    if ($method === 'cash') {
        $totalBayar = 1000; // Default fee Rp1.000
        $sql2 = "INSERT INTO cashflow (tanggal, nama, kelas, nominal, type, rincian)
                 VALUES (NOW(), :nama, :kelas, :nominal, 'in', 'Bayar Telpon')";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute([
            ':nama'    => $nama,
            ':kelas'   => $kelas,
            ':nominal' => $totalBayar
        ]);
    }

    $conn->commit();
    $_SESSION['message'] = [
        'type' => 'success',
        'text' => 'Transaksi peminjaman berhasil!'
    ];
    header("Location: index.php");
    exit;

} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['message'] = [
        'type' => 'danger',
        'text' => $e->getMessage()
    ];
    error_log('Proses peminjaman error: ' . $e->getMessage());
    header("Location: index.php");
    exit;
} finally {
    $conn = null;
}
?>