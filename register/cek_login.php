<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../database/koneksi.php';

if(!isset($_SESSION['login']) || $_SESSION['login'] !== true){
    header("Location: ../register/login.php");
    exit;
}

$role = $_SESSION['role'];
$currentPage = basename($_SERVER['PHP_SELF']);

if($role == 'admin'){

    $allowedAdmin = [
        'dashboard_admin.php',
        'tambah_ekskul.php',
        'edit_ekskul.php',
        'hapus_ekskul.php',
        'data_pendaftaran.php',
        'laporan.php',
        'logout.php',
        'terima.php',
        'tolak.php',
        'data_siswa.php',
        'data_guru.php'
    ];

    if(!in_array($currentPage, $allowedAdmin)){

        if (file_exists('dashboard_admin.php')) {
            header("Location: dashboard_admin.php");
        } else {
            header("Location: ../admin/dashboard_admin.php");
        }
        exit;
    }

}

elseif($role == 'user'){

    $allowedUser = [
        'dashboard_user.php',
        'form.php',
        'riwayat.php',
        'detail_ekskul.php',
        'logout.php',
        'ganti_password.php'
    ];

    if(!in_array($currentPage, $allowedUser)){

        if (file_exists('dashboard_user.php')) {
            header("Location: dashboard_user.php");
        } else {
            header("Location: ../user/dashboard_user.php");
        }
        exit;
    }

}
?>