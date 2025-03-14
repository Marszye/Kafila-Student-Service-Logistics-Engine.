<?php
session_start();
include 'config.php';

try {
    $conn->beginTransaction();
    
    // Ambil data dari form
    $nama = $_POST['nama'];
    $hp = $_POST['hp'];

    // Validate inputs
    if (empty($nama) || empty($hp)) {
        throw new Exception("Nama atau HP belum diisi.");
    }

    // Check if device is currently rented
    $sqlCheck = "SELECT id FROM peminjaman WHERE nama = ? AND hp = ? AND status = 'Dipinjam'";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bindParam(':nama', $nama, PDO::PARAM_STR);
    $stmtCheck->bindParam(':hp', $hp, PDO::PARAM_STR);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows === 0) {
        throw new Exception("HP yang dimaksud sedang tidak dipinjam atau telah dikembalikan.");
    }

    // Get the ID of the current rent
    $rentalId = $resultCheck->fetch_assoc()['id'];

    // Update status to 'Batal'
    $sqlUpdate = "UPDATE peminjaman SET status = 'Batal' WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("i", $rentalId);
    $stmtUpdate->execute();

    if ($stmtUpdate->affected_rows > 0) {
        $conn->commit();
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Peminjaman berhasil dibatalkan.'
        ];
        header("Location: index.php");
        exit;
    } else {
        throw new Exception("Tidak ada perubahan terdeteksi.");
    }

} catch (Exception $e) {
    $conn->rollback(); 
    $_SESSION['message'] = [
        'type' => 'danger',
        'text' => $e->getMessage()
    ];
    error_log("Batal telepon error: " . $e->getMessage());
} finally {
    $conn->autocommit(true);
    if (isset($stmtCheck)) $stmtCheck->close();
    if (isset($stmtUpdate)) $stmtUpdate->close();
    $conn->close();
}
?>