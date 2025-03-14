<?php
include 'config.php';

if ($conn) {
    echo "Koneksi database berhasil!";
} else {
    echo "Koneksi database gagal: " . mysqli_connect_error();
}