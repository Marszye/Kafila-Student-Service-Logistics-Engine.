<?php
include 'config.php';

$nama = $_GET['nama'];
$sql = "SELECT kelas FROM santri WHERE nama = :nama";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':nama', $nama, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result && isset($result['kelas'])) {
    echo htmlspecialchars($result['kelas']);
} else {
    echo 'Kelas tidak ditemukan';
}
$stmt = null;
$conn = null;
?>