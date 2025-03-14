<?php
include 'config.php';

$hp = $_GET['hp'];
$sql = "SELECT COUNT(*) FROM peminjaman WHERE hp = :hp AND status = 'Dipinjam'";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':hp', $hp, PDO::PARAM_STR);
$stmt->execute();
$count = $stmt->fetchColumn();

if ($count > 0) {
    echo json_encode(['error' => 'HP sedang dipinjam.']);
} else {
    echo json_encode(['success' => 'HP tersedia.']);
}
?>