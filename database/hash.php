<?php
include 'koneksi.php';

$dataAdmin = mysqli_query($conn, "SELECT * FROM admin");

while($a = mysqli_fetch_assoc($dataAdmin)){
    $id = $a['id_admin'];
    $passwordLama = $a['password'];
    if(password_get_info($passwordLama)['algo'] == 0){

        $hash = password_hash($passwordLama, PASSWORD_DEFAULT);

        mysqli_query($conn,"
            UPDATE admin
            SET password='$hash'
            WHERE id_admin='$id'
        ");

        echo "Password admin ID $id berhasil di hash <br>";
    }
}

$dataSiswa = mysqli_query($conn, "SELECT * FROM siswa");

while($s = mysqli_fetch_assoc($dataSiswa)){

    $nisn = $s['nisn'];
    $passwordLama = $s['password'];

    if(password_get_info($passwordLama)['algo'] == 0){

        $hash = password_hash($passwordLama, PASSWORD_DEFAULT);

        mysqli_query($conn,"
            UPDATE siswa
            SET password='$hash'
            WHERE nisn='$nisn'
        ");

        echo "Password siswa $nisn berhasil di hash <br>";
    }
}

echo "<br>SELESAI!";
?>