<?php
include 'config.php';
include 'komponen_aturan_peminjaman.php';

$nama = $_GET['nama'];

// Ambil kelas santri
$sql = "SELECT kelas FROM santri WHERE nama = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nama);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Santri tidak ditemukan']);
} else {
    $kelas = $result->fetch_assoc()['kelas'];
    $hasil = cekHari($kelas);
    if ($hasil) {
        echo json_encode(['success' => 'Peminjaman diperbolehkan']);
    } else {
        echo json_encode(['error' => 'Peminjaman tidak diperbolehkan hari ini']);
    }
}
?>