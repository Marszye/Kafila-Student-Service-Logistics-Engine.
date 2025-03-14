<?php
include 'config.php';

$nama = $_GET['nama'];
$sql = "SELECT COUNT(*) FROM peminjaman WHERE nama = :nama AND status = 'Dipinjam'";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':nama', $nama, PDO::PARAM_STR);
$stmt->execute();
$count = $stmt->fetchColumn();

if ($count > 0) {
    echo json_encode(['error' => 'Nama sudah ada dalam transaksi yang sedang berlangsung.']);
} else {
    echo json_encode(['success' => 'Nama tersedia untuk peminjaman.']);
}
?>