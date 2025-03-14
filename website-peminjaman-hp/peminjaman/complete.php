<?php
session_start();
include 'config.php';

try {
    $conn->beginTransaction();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $method = filter_input(INPUT_POST, 'method', FILTER_SANITIZE_STRING);

        if (!$id || !$method) {
            throw new Exception("Parameter transaksi tidak lengkap.");
        }

        // Validasi: Pastikan transaksi ada dan statusnya masih 'Dipinjam'
        $sqlCheck = "SELECT id FROM peminjaman WHERE id = :id AND status = 'Dipinjam'";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        
        if ($stmtCheck->rowCount() === 0) {
            throw new Exception("Transaksi tidak ditemukan atau sudah diselesaikan.");
        }

        // Ambil waktu saat ini untuk perhitungan
        $now = new DateTime();
        
        // Hitung durasi peminjaman
        $sqlDurasi = "SELECT waktu_pinjam FROM peminjaman WHERE id = :id";
        $stmtDurasi = $conn->prepare($sqlDurasi);
        $stmtDurasi->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtDurasi->execute();
        $waktuPinjam = $stmtDurasi->fetchColumn();
        
        $waktuPinjam = new DateTime($waktuPinjam);
        $menitTotal = ($now->getTimestamp() - $waktuPinjam->getTimestamp()) / 60;

        // Hitung biaya
        if ($menitTotal <= 15) {
            $biaya = 1000;
        } else {
            $selisih = $menitTotal - 15;
            $blok = ceil($selisih / 5);
            $biaya = 1000 + ($blok * 1000);
        }

        // Update transaksi
        $sql = "UPDATE peminjaman 
                SET status='Selesai', 
                    waktu_kembali=NOW(), 
                    method=:method, 
                    bayar=:biaya
                WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':method', $method, PDO::PARAM_STR);
        $stmt->bindParam(':biaya', $biaya, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new Exception("Transaksi sudah selesai atau tidak ditemukan.");
        }

        // Jika method adalah cash, catat ke tabel cashflow
        if ($method === 'cash') {
            $sqlCashflow = "INSERT INTO cashflow (tanggal, nama, kelas, nominal, type, rincian)
                            SELECT NOW(), nama, kelas, :biaya, 'in', 'Bayar Telpon'
                            FROM peminjaman
                            WHERE id = :id";
            $stmtCashflow = $conn->prepare($sqlCashflow);
            $stmtCashflow->bindParam(':biaya', $biaya, PDO::PARAM_INT);
            $stmtCashflow->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtCashflow->execute();
        }

        $conn->commit();
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Transaksi berhasil diselesaikan.'
        ];
        header("Location: index.php");
        exit;

    } else {
        throw new Exception("Metode request tidak valid.");
    }

} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['message'] = [
        'type' => 'danger',
        'text' => $e->getMessage()
    ];
    error_log("Complete.php error: " . $e->getMessage());
    header("Location: index.php");
    exit;
} finally {
    $conn = null;
}
?>