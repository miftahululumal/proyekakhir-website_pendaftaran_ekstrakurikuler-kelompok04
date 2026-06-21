<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "ekstrakurikuler"
);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>