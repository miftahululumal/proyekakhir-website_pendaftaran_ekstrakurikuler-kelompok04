<?php
include '../register/cek_login.php';
include '../database/koneksi.php';

if($_SESSION['role'] != 'admin'){
    header("Location: ../user/dashboard_user.php");
    exit;
}

if(!isset($_GET['id'])){
    header("Location: data_pendaftaran.php");
    exit;
}

$id = $_GET['id'];

$query = mysqli_query($conn,"
    UPDATE pendaftaran
    SET status='Diterima'
    WHERE id_pendaftaran='$id'
");

header("Location: data_pendaftaran.php");
exit;
?>