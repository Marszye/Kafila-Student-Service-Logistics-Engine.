<?php
include_once 'config.php';

try {
    $sql = "SELECT id, nama, kelas, hp, waktu_pinjam, waktu_kembali, status, bayar, method 
            FROM peminjaman 
            ORDER BY waktu_pinjam DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $now = new DateTime();
    $counter = 1;

    foreach ($result as $row) {
        $mainStatus = strtolower($row['status']);
        $buttons = '';
        $totalBayar = '-';
        $statusClass = '';
        $statusText = $row['status'];

        // Format waktu
        $waktuPinjamDisp = (new DateTime($row['waktu_pinjam']))->format("H:i");
        $waktuKembaliDisp = $row['waktu_kembali'] ? (new DateTime($row['waktu_kembali']))->format("H:i") : '-';

        if ($mainStatus === 'dipinjam') {
            // Hitung durasi peminjaman
            $waktuPinjam = new DateTime($row['waktu_pinjam']);
            $menitTotal = ($now->getTimestamp() - $waktuPinjam->getTimestamp()) / 60;

            // Hitung biaya
            if ($menitTotal <= 15) {
                $measureBiaya = 1000;
            } else {
                $selisih = $menitTotal - 15;
                $blok = ceil($selisih / 5);
                $measureBiaya = 1000 + ($blok * 1000);
            }

            // Tombol aksi
            $buttons = '
                <div class="button-group inline-block">
                    <button type="button" 
                            class="btn-inverse btn-auto btn-selesai"
                            data-id="' . htmlspecialchars($row['id']) . '"
                            data-biaya="' . number_format($measureBiaya) . '">
                        Selesai
                    </button>
                    <a href="cancel.php?id=' . htmlspecialchars($row['id']) . '"
                       class="cancel-btn btn-inverse btn-auto"
                       onclick="return confirm(\'Yakin batalkan transaksi?\')">
                       Batal
                    </a>
                </div>';
            
            $totalBayar = 'Rp ' . number_format($measureBiaya);
            $statusClass = 'status-dipinjam';
            $statusText = 'Dipinjam';
            
        } else {
            // Handle status selesai/batal
            if ($mainStatus === 'selesai') {
                $statusClass = ($row['bayar'] > 1000) ? 'status-terlambat' : 'status-tepat-waktu';
                $statusText = ($row['bayar'] > 1000) ? 'Terlambat' : 'Tepat Waktu';
                
                if (!empty($row['bayar'])) {
                    $totalBayar = 'Rp ' . number_format($row['bayar']);
                }
            } elseif ($mainStatus === 'batal') {
                $statusClass = 'status-batal';
                $totalBayar = 'Rp 0'; // Menampilkan Rp 0 untuk transaksi dibatalkan
            }
            
            // Tambahkan method pembayaran jika ada dan transaksi tidak dibatalkan
            if (!empty($row['method']) && $mainStatus !== 'batal') {
                $totalBayar .= ' (' . htmlspecialchars($row['method']) . ')';
            }
        }

        echo '<tr>
                <td class="px-2 py-2">' . $counter++ . '</td>
                <td class="px-2 py-2">' . htmlspecialchars($row['nama'] ?? '') . '</td>
                <td class="px-2 py-2">' . htmlspecialchars($row['kelas'] ?? '') . '</td>
                <td class="px-2 py-2">' . htmlspecialchars($row['hp'] ?? '') . '</td>
                <td class="px-2 py-2">' . $waktuPinjamDisp . '</td>
                <td class="px-2 py-2">' . $waktuKembaliDisp . '</td>
                <td class="px-2 py-2 ' . $statusClass . '">' . htmlspecialchars($statusText) . '</td>
                <td class="px-2 py-2">' . $totalBayar . '</td>
                <td class="px-2 py-2 text-center">' . $buttons . '</td>
              </tr>';
    }

} catch (Exception $e) {
    error_log('Error in history.php: ' . $e->getMessage());
    echo '<tr><td colspan="9">Error loading data</td></tr>';
}
?>