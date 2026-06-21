<?php
session_start();
$current_url = rtrim($_SERVER['REQUEST_URI'], '/');
$current_folder = basename(__DIR__);

if (preg_match("/\/$current_folder$/", $current_url)) {
    echo "
    <div style='background-color: #ffebee; border: 5px solid #d32f2f; padding: 30px; margin: 50px auto; max-width: 800px; text-align: center; font-family: Arial, sans-serif; border-radius: 10px; box-shadow: 0px 10px 20px rgba(0,0,0,0.19);'>
        <h1 style='color: #d32f2f; font-size: 3rem; text-transform: uppercase; font-weight: 900; margin: 0 0 15px 0; letter-spacing: 2px;'>
            ⚠️ PERINGATAN
        </h1>
        <p style='color: #333; font-size: 1.5rem; font-weight: bold; margin: 0;'>
            LINK YANG ANDA MASUKKAN SALAH ATAU SUDAH TIDAK BERLAKU!
        </p>
    </div>
    ";
    exit();
}

if (!isset($_SESSION['sudah_login']) || $_SESSION['sudah_login'] !== true) {
    header("Location: ../register/login.php"); 
    exit();
}

?>