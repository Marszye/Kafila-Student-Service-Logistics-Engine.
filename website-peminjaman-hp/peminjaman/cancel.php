<?php
session_start();
include 'config.php';

try {
    $conn->beginTransaction(); // Use PDO transaction

    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];

        // Ambil status transaksi
        $sqlCheckStatus = "SELECT status FROM peminjaman WHERE id = :id";
        $stmtCheckStatus = $conn->prepare($sqlCheckStatus);
        $stmtCheckStatus->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtCheckStatus->execute();
        $resultCheckStatus = $stmtCheckStatus->fetch(PDO::FETCH_ASSOC);

        if (empty($resultCheckStatus)) {
            throw new Exception('Transaksi tidak ditemukan.');
        }

        $currentStatus = $resultCheckStatus['status'] ?? '';
        if (strtolower($currentStatus) !== 'dipinjam') {
            throw new Exception('Transaksi sudah selesai atau dibatalkan.');
        }

        // Update status ke 'Batal' dan kosongkan bayar dan method
        $sqlCancel = "UPDATE peminjaman SET status = 'Batal', bayar = NULL, method = NULL WHERE id = :id";
        $stmtCancel = $conn->prepare($sqlCancel);
        $stmtCancel->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtCancel->execute();

        if ($stmtCancel->rowCount() > 0) {
            $conn->commit();
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Transaksi berhasil dibatalkan.'
            ];
            header('Location: index.php');
            exit;
        } else {
            throw new Exception('Tidak ada perubahan terdeteksi.');
        }
    } else {
        throw new Exception('ID transaksi tidak valid.');
    }

} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['message'] = [
        'type' => 'danger',
        'text' => $e->getMessage()
    ];
    error_log('Cancel.php error: ' . $e->getMessage());
} finally {
    $conn = null; // Close the database connection
}