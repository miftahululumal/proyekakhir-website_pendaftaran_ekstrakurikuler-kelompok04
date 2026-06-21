<?php
// Pastikan tidak ada spasi atau baris kosong sebelum tag ini
session_start();
include '../database/koneksi.php';

$error = "";

if(isset($_POST['login'])){

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if(empty($username) || empty($password)){
        $error = "Username / NISN dan password wajib diisi!";
    } else {

        $queryAdmin = "SELECT * FROM admin WHERE username=?";
        $stmtAdmin = mysqli_prepare($conn, $queryAdmin);
        mysqli_stmt_bind_param($stmtAdmin, "s", $username);
        mysqli_stmt_execute($stmtAdmin);
        $resultAdmin = mysqli_stmt_get_result($stmtAdmin);
        $admin = mysqli_fetch_assoc($resultAdmin);

        if($admin && password_verify($password, $admin['password'])){
            $_SESSION['login'] = true;
            $_SESSION['id_admin'] = $admin['id_admin'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['nama_admin'] = $admin['nama_admin'];
            $_SESSION['role'] = 'admin';

            header("Location: ../admin/dashboard_admin.php");
            exit;
        }

        $querySiswa = "SELECT * FROM siswa WHERE nisn=?";
        $stmtSiswa = mysqli_prepare($conn, $querySiswa);
        mysqli_stmt_bind_param($stmtSiswa, "s", $username);
        mysqli_stmt_execute($stmtSiswa);
        $resultSiswa = mysqli_stmt_get_result($stmtSiswa);
        $siswa = mysqli_fetch_assoc($resultSiswa);

        if($siswa && password_verify($password, $siswa['password'])){
            $_SESSION['login'] = true;
            $_SESSION['nisn'] = $siswa['nisn'];
            $_SESSION['nama'] = $siswa['nama'];
            $_SESSION['email'] = $siswa['email'];
            $_SESSION['kelas'] = $siswa['kelas'];
            $_SESSION['role'] = 'user';

            header("Location: ../user/dashboard_user.php");
            exit;
        }

        $error = "Username / NISN atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Ekstrakurikuler</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body{
    background-image: url('../gambar/sekolah.jpeg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
}
</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
<div class="fixed inset-0 bg-black/60"></div>
<div class="relative w-full max-w-6xl z-10">
    <div class="grid md:grid-cols-2 overflow-hidden rounded-3xl shadow-2xl">
        <div class="bg-emerald-700/80 backdrop-blur-md p-8 md:p-12 text-white flex flex-col justify-center">
            <span class="inline-block bg-white/20 px-4 py-2 rounded-full text-sm mb-6">Sistem Ekstrakurikuler Sekolah</span>
            <h1 class="text-4xl md:text-5xl font-black leading-tight mb-6">Pendaftaran<br>Ekstrakurikuler</h1>
            <p class="text-white/90 leading-relaxed mb-8">Selamat datang di sistem pendaftaran ekstrakurikuler sekolah. Website ini digunakan untuk membantu siswa memilih dan mendaftar kegiatan ekstrakurikuler sesuai minat dan bakat secara mudah, cepat, dan online.</p>
            <div class="space-y-5">
                <div class="flex gap-4"><div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-xl">📚</div><div><h3 class="font-bold">Beragam Pilihan Ekskul</h3><p class="text-sm text-white/80">Temukan kegiatan sesuai minat dan bakatmu.</p></div></div>
                <div class="flex gap-4"><div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-xl">📝</div><div><h3 class="font-bold">Pendaftaran Online</h3><p class="text-sm text-white/80">Daftar kapan saja tanpa harus datang langsung.</p></div></div>
                <div class="flex gap-4"><div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-xl">🚀</div><div><h3 class="font-bold">Mudah dan Cepat</h3><p class="text-sm text-white/80">Sistem digital yang praktis dan efisien.</p></div></div>
            </div>
        </div>
        <div class="bg-white/15 backdrop-blur-xl p-8 md:p-10 text-white flex flex-col justify-center">
            <div class="text-center mb-8">
                <img src="../gambar/logo.png" alt="Logo Sekolah" class="w-24 h-24 mx-auto object-contain mb-4">
                <h1 class="text-xl md:text-2xl font-bold uppercase tracking-wider">SMK KAMAL 1</h1>
                <div class="w-16 h-1 bg-emerald-400 mx-auto rounded-full my-4"></div>
                <h2 class="text-4xl font-black">LOGIN</h2>
                <p class="text-white/80 mt-2 text-sm">Masuk ke akun Anda</p>
            </div>
            <?php if($error != ""){ ?>
            <div class="bg-red-500/80 text-white p-3 rounded-xl mb-5 text-center">
                <?= $error ?>
            </div>
            <?php } ?>
            <form method="POST" class="space-y-5">
                <div>
                    <label class="block mb-2 text-sm text-white/80">Username / NISN</label>
                    <input type="text" name="username" required placeholder="Masukkan username atau NISN" class="w-full p-4 rounded-xl bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-emerald-400">
                </div>
                <div class="relative w-full">
                    <label class="block mb-2 text-sm text-white/80">Password</label>
                    <div class="relative flex items-center">
                        <input 
                            type="password" 
                            id="passwordField" 
                            name="password" 
                            required 
                            placeholder="Masukkan password" 
                            class="w-full p-4 pr-14 rounded-xl bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                        >
                        
                        <button 
                            type="button" 
                            onclick="togglePassword()" 
                            class="absolute right-4 flex items-center justify-center p-2 text-white hover:text-emerald-300 transition-colors"
                        >
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path id="eyePath" stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" name="login" class="w-full bg-emerald-500 hover:bg-emerald-600 py-4 rounded-xl font-bold text-lg transition duration-300">Masuk</button>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById("passwordField");
    const eyePath = document.getElementById("eyePath");
    
    if (passwordField.type === "password") {
        passwordField.type = "text";
        // Ikon Mata Tertutup (dengan garis silang)
        eyePath.setAttribute("d", "M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.7 9.7 0 012.26-3.444M15 12a3 3 0 11-6 0m3.75 3.75l-7.5-7.5M9.105 14.895l7.5 7.5");
    } else {
        passwordField.type = "password";
        // Ikon Mata Terbuka
        eyePath.setAttribute("d", "M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z");
    }
}
</script>
</body>
</html>