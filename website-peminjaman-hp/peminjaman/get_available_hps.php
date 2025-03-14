<?php
include 'config.php';

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ambil semua HP dari tabel hp_list
    $sql = "SELECT hp FROM hp_list";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Ambil hasil dalam bentuk array kolom
    $available_hps = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    error_log("Database error in get_available_hps.php: " . $e->getMessage());
    $available_hps = [];
}

echo json_encode($available_hps);

// Tutup koneksi
$stmt = null;
$conn = null;
?>