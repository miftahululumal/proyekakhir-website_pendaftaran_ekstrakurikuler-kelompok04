<?php
session_start();
include '../database/koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../user/dashboard_user.php");
    exit;
}

if(!isset($_GET['id'])){
    header("Location: ../admin/dashboard_admin.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

$query = mysqli_query(
    $conn,
    "SELECT * FROM ekskul WHERE id_ekskul='$id'"
);

$data = mysqli_fetch_assoc($query);

if($data){

    $file = "../gambar/" . $data['foto'];

    if(!empty($data['foto']) && file_exists($file)){
        unlink($file);
    }

    mysqli_query(
        $conn,
        "DELETE FROM ekskul WHERE id_ekskul='$id'"
    );
}

header("Location: ../admin/dashboard_admin.php");
exit;
?>