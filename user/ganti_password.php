<?php
session_start();
include '../database/koneksi.php'; 

if (!isset($_SESSION['nisn'])) {
    header("Location: ../login.php");
    exit;
}

$error = "";
$pesan_sukses = "";

if (isset($_POST['simpan'])) {
    $pass_baru = trim($_POST['password_baru'] ?? '');

    if (empty($pass_baru) || strlen($pass_baru) < 8) {
        $error = "Password wajib diisi dan minimal 8 karakter!";
    } else {
        $pass_hash = password_hash($pass_baru, PASSWORD_DEFAULT);
        $nisn = $_SESSION['nisn'];
        $update = mysqli_query($conn, "UPDATE siswa SET password='$pass_hash', updated_sts=NOW() WHERE nisn='$nisn'");

        if ($update) {

            header("Location: ../user/dashboard_user.php?pesan=password_sukses");
            exit;
        } else {
            $error = "Gagal memperbarui password, silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password - SMK KAMAL 1</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeUp { animation: fadeUp 0.5s ease-out forwards; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 antialiased">

    <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-md border border-gray-100 animate-fadeUp">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-emerald-200 shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h2 class="text-2xl lg:text-3xl font-black text-emerald-950 tracking-tight">Ganti Password</h2>
            
            <div id="notif-container">
                <?php if ($error) { ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl mb-6 shadow-sm text-sm font-bold flex items-center animate-fadeUp">
                        <span class="mr-2">⚠️</span> <?= $error ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <form action="" method="POST" class="space-y-6">
            <div class="relative">
                <label class="block text-sm font-bold text-gray-700 mb-2">Password Baru</label>
                <div class="relative flex items-center">
                    <input type="password" id="passwordField" name="password_baru" required minlength="8"
                        class="w-full px-4 py-3.5 pr-14 rounded-xl border border-gray-200 bg-gray-50/50 focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none transition-all text-gray-800 placeholder-gray-400"
                        placeholder="password minimal 8 karakter">
                    
                    <button type="button" onclick="togglePassword()" class="absolute right-0 p-2 text-gray-400 hover:text-emerald-600 transition-colors">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path id="eyePath" stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" name="simpan" 
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3.5 rounded-xl transition-all shadow-lg shadow-emerald-200 active:scale-[0.98] text-base lg:text-lg tracking-wide">
                Simpan Password Baru
            </button>

            <a href="../user/dashboard_user.php" class="block text-center text-sm text-gray-400 hover:text-emerald-700 font-semibold transition-colors mt-8 py-2">
                &larr; Kembali ke Dashboard
            </a>
        </form>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById("passwordField");
            const eyePath = document.getElementById("eyePath");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyePath.setAttribute("d", "M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.7 9.7 0 012.26-3.444M15 12a3 3 0 11-6 0m3.75 3.75l-7.5-7.5M9.105 14.895l7.5 7.5");
            } else {
                passwordField.type = "password";
                eyePath.setAttribute("d", "M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z");
            }
        }
    </script>
</body>
</html>