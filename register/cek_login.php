<?php
session_start();

include '../database/koneksi.php';

if(!isset($_SESSION['login'])){

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
        'logout.php'
    ];

    if(!in_array($currentPage, $allowedAdmin)){

        header("Location: ../admin/dashboard_admin.php");
        exit;

    }

}

elseif($role == 'user'){

    $allowedUser = [
        'dashboard_user.php',
        'form.php',
        'riwayat.php',
        'detail_ekskul.php',
        'logout.php'
    ];

    if(!in_array($currentPage, $allowedUser)){

        header("Location: ../user/dashboard_user.php");
        exit;

    }

}
?>