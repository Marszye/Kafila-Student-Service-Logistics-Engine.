<?php
// Return true jika boleh pinjam
function cekHari($kelas) {
    $hari = date('l');
    $ajaib = [
        'Monday'    => ['8A', '8B', '8C'],
        'Tuesday'   => ['9A'],
        'Wednesday' => ['10A', '10B'],
        'Thursday'  => ['11A', '11B', '11C'],
        'Friday'    => ['12B', '12C'],
        'Saturday'  => ['7A', '7B', '7C'],
        'Sunday'    => ['']
    ];
    if ($hari === 'Sunday') {
        return true;
    }
    return isset($ajaib[$hari]) && in_array($kelas, $ajaib[$hari]);
}
?>