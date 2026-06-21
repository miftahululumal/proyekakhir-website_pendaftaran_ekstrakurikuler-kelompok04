<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../register/cek_login.php';
include '../database/koneksi.php';

$pesan_sukses = "";
if (isset($_GET['pesan']) && $_GET['pesan'] == 'password_sukses') {
    $pesan_sukses = "Password berhasil diubah!";
}

$query = mysqli_query($conn, "SELECT updated_sts FROM siswa WHERE nisn = '$_SESSION[nisn]'");
$data = mysqli_fetch_assoc($query);

if(!isset($_SESSION['nisn']) || !isset($_SESSION['nama'])){
    die("SESSION TIDAK DITEMUKAN. SILAKAN LOGIN ULANG.");
}

$nisn   = $_SESSION['nisn'];
$nama   = $_SESSION['nama'];
$kelas  = $_SESSION['kelas'] ?? '-'; 
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

$ekskulQuery = mysqli_query($conn, "SELECT * FROM ekskul ORDER BY id_ekskul DESC");

$riwayatQuery = mysqli_query($conn,
    "SELECT p.*, e.nama_ekskul
     FROM pendaftaran p
     LEFT JOIN ekskul e ON p.id_ekskul = e.id_ekskul
     WHERE p.nisn = '$nisn'
     ORDER BY p.id_pendaftaran DESC"
);

$jadwalQuery = mysqli_query($conn, "
    SELECT 
        e.nama_ekskul, 
        g.nama_guru AS pembimbing,
        j.hari, 
        j.jam_mulai, 
        j.jam_selesai
    FROM pendaftaran p
    JOIN ekskul e ON p.id_ekskul = e.id_ekskul
    JOIN siswa s ON p.nisn = s.nisn
    JOIN jadwal_ekskul j ON p.id_jadwal = j.id_jadwal
    LEFT JOIN guru g ON e.nip_pembimbing = g.nip
    WHERE p.nisn = '$nisn' AND p.status = 'Diterima'
");

$queryTotalDiterima = "SELECT COUNT(DISTINCT id_ekskul) AS total_diterima 
                       FROM pendaftaran 
                       WHERE nisn = '$nisn' AND status = 'Diterima'";
$resultTotal = mysqli_query($conn, $queryTotalDiterima);
$rowTotal = mysqli_fetch_assoc($resultTotal);
$totalEkskulDiterima = $rowTotal['total_diterima'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/animation.css">
</head>
<body class="bg-gray-50 min-h-screen overflow-x-hidden antialiased">

    <div class="lg:hidden bg-emerald-900 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
        <div class="flex items-center gap-2">
            <img src="../gambar/logo.png" class="w-7 h-7 object-cover rounded-md">
            <span class="font-black text-sm tracking-wide">SMK KAMAL 1</span>
        </div>

        <button id="menuBtn" class="p-2 focus:outline-none hover:bg-emerald-800 rounded-lg transition-colors block">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <div class="flex flex-col lg:flex-row min-h-screen">
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-2/3 sm:w-1/2 lg:w-72 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out backdrop-blur-2xl bg-emerald-900/95 border-r border-white/10 text-white p-6 shadow-2xl overflow-y-auto h-screen">
            <div class="lg:hidden flex justify-end mb-2">
                <button id="closeBtn" class="p-1.5 hover:bg-white/10 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="animate-fadeUp">
                <div class="flex justify-center">
                    <div class="bg-white/10 backdrop-blur-xl p-3 rounded-2xl border border-white/20 shadow-xl">
                        <img src="../gambar/logo.png" class="w-14 lg:w-24 h-14 lg:h-24 object-cover rounded-xl">
                    </div>
                </div>
                <div class="text-center mt-4">
                    <h1 class="text-base lg:text-2xl font-black tracking-wide">SMK KAMAL 1</h1>
                </div>
            </div>

            <div class="mt-6 bg-white/10 backdrop-blur-xl rounded-xl p-3 border border-white/20 text-center animate-fadeUp">
                <p class="text-[10px] lg:text-xs text-white/60 uppercase tracking-wider font-semibold">Mode Siswa</p>
                <h2 class="text-sm lg:text-xl font-bold truncate mt-1"><?= htmlspecialchars($nama) ?></h2>
            </div>

            <div class="mt-8 space-y-3 animate-fadeUp">
                <a href="dashboard_user.php" class="block bg-white text-emerald-700 p-3 rounded-xl font-black text-center text-xs lg:text-base shadow-md transition-all hover:scale-[1.02]">Dashboard</a>
                <a href="riwayat.php" class="block bg-white/10 hover:bg-white/20 p-3 rounded-xl text-center text-xs lg:text-base font-medium transition-all">Riwayat</a>
                <a href="../register/logout.php" class="block bg-red-500 hover:bg-red-600 transition-colors p-3 rounded-xl text-center font-black mt-8 text-xs lg:text-base">Logout</a>
            </div>
        </aside>

        <div id="overlay" class="fixed inset-0 bg-black/40 backdrop-blur-md z-40 hidden lg:hidden transition-all duration-300"></div>

        <section class="main flex-1 lg:ml-72 p-5 lg:p-10">
            <div class="animate-fadeUp">
               <?php if ($data['updated_sts'] === NULL || $data['updated_sts'] == "") { ?>
                    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg shadow-sm mb-6 flex items-center justify-between animate-fadeUp">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <h4 class="text-amber-800 font-bold text-sm">Keamanan Akun</h4>
                                <p class="text-amber-700 text-xs">Password Anda masih standar. Segera ganti password untuk menjaga keamanan data.</p>
                            </div>
                        </div>
                        <a href="ganti_password.php" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-bold text-xs transition-colors shadow-md">
                            Ganti Sekarang
                        </a>
                    </div>
                <?php } ?>

                <?php if ($pesan_sukses) { ?>
                    <div id="notif-sukses" class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-2xl mb-6 text-sm font-bold flex items-center shadow-sm animate-fadeUp">
                        ✅ <?= $pesan_sukses ?>
                    </div>
                <?php } ?>

                <h1 class="text-2xl lg:text-4xl xl:text-5xl font-black bg-gradient-to-r from-emerald-700 via-green-500 to-emerald-400 bg-clip-text text-transparent leading-tight">
                    Selamat Datang
                </h1>
                <p class="text-gray-500 mt-2 text-xs lg:text-lg font-medium">
                    Pilih ekstrakurikuler sesuai minat dan bakatmu
                </p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mt-6 lg:mt-10">
                <div class="bg-white p-4 lg:p-8 rounded-2xl lg:rounded-[30px] shadow-sm border border-gray-100 card-hover glow floating flex flex-col justify-center items-center">
                    <p class="font-bold text-gray-400 text-xs lg:text-base uppercase tracking-wider">NISN</p>
                    <h2 class="text-lg lg:text-4xl xl:text-5xl font-black text-emerald-700 mt-2 text-center break-all leading-tight">
                        <?= htmlspecialchars($nisn) ?>
                    </h2>
                </div>

                <div class="bg-white p-4 lg:p-8 rounded-2xl lg:rounded-[30px] shadow-sm border border-gray-100 card-hover glow floating flex flex-col justify-center items-center">
                    <p class="font-bold text-gray-400 text-xs lg:text-base uppercase tracking-wider">Kelas</p>
                    <h2 class="text-xl lg:text-5xl xl:text-6xl font-black text-cyan-500 mt-2">
                        <?= htmlspecialchars($kelas) ?>
                    </h2>
                </div>

                <div class="col-span-2 lg:col-span-1 bg-white p-4 lg:p-8 rounded-2xl lg:rounded-[30px] shadow-sm border border-gray-100 card-hover glow floating flex flex-col justify-center items-center">
                    <p class="font-bold text-gray-400 text-xs lg:text-base uppercase tracking-wider text-center">Total Ekskul Diterima</p>
                    <h2 class="text-xl lg:text-5xl xl:text-6xl font-black text-orange-500 mt-2">
                        <?= $totalEkskulDiterima ?>
                    </h2>
                </div>
            </div>

            <?php if (mysqli_num_rows($jadwalQuery) > 0) { ?>
            <div class="mt-12 bg-white/70 backdrop-blur-xl rounded-[24px] lg:rounded-[30px] p-5 lg:p-8 shadow-sm border border-gray-100 animate-fadeUp relative overflow-hidden">
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="flex h-2.5 w-2.5 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            </span>
                            <h2 class="text-base lg:text-2xl font-black text-emerald-950 tracking-wide">
                                Jadwal Latihan Ekskul Kamu
                            </h2>
                        </div>
                        <p class="text-[11px] lg:text-sm text-gray-500 mt-1 font-medium">
                            Kamu telah resmi bergabung. Silakan ikuti latihan sesuai jadwal berikut:
                        </p>
                    </div>
                    
                    <div class="self-start sm:self-center bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100 text-[11px] lg:text-sm font-bold text-emerald-800 flex items-center gap-1.5">
                        <span>⚽</span> <?= mysqli_num_rows($jadwalQuery); ?> Ekskul Aktif
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-100 shadow-sm bg-white">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gradient-to-r from-emerald-800 to-emerald-700 text-white text-[10px] lg:text-sm tracking-wider uppercase">
                                <th class="p-3 lg:p-5 font-black">Nama Ekstrakurikuler</th>
                                <th class="p-3 lg:p-5 font-black">Hari Latihan</th>
                                <th class="p-3 lg:p-5 font-black">Jam Latihan</th>
                                <th class="p-3 lg:p-5 font-black">Guru Pembimbing</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs lg:text-base text-gray-700 font-medium">
                            <?php while ($jadwal = mysqli_fetch_assoc($jadwalQuery)) { 
                                $jam_mulai   = date('H:i', strtotime($jadwal['jam_mulai']));
                                $jam_selesai = date('H:i', strtotime($jadwal['jam_selesai']));
                            ?>
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="p-3 lg:p-5">
                                    <div class="flex items-center gap-2 lg:gap-3">
                                        <div class="w-1.5 h-6 lg:h-8 bg-emerald-600 rounded-full"></div>
                                        <span class="font-black text-emerald-900 text-xs lg:text-lg">
                                            <?= htmlspecialchars($jadwal['nama_ekskul']) ?>
                                        </span>
                                    </div>
                                </td>
                                
                                <td class="p-3 lg:p-5">
                                    <span class="inline-flex items-center bg-emerald-50 text-emerald-800 px-2 lg:px-3 py-1 rounded-lg text-[10px] lg:text-sm font-bold border border-emerald-100">
                                        📆 <?= htmlspecialchars($jadwal['hari']) ?>
                                    </span>
                                </td>

                                <td class="p-3 lg:p-5 text-gray-600 font-semibold text-[11px] lg:text-base">
                                    <div class="flex items-center gap-1">
                                        <span>⏱️</span> <?= $jam_mulai ?> - <?= $jam_selesai ?> WIB
                                    </div>
                                </td>
                                
                                <td class="p-3 lg:p-5">
                                    <div class="text-gray-600 font-bold text-[11px] lg:text-base flex items-center gap-1">
                                        <span>👨‍🏫</span> <?= htmlspecialchars($jadwal['pembimbing'] ?? 'Belum Ada Pembimbing') ?>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php } else { ?>
            <div class="mt-12 bg-white/70 backdrop-blur-xl rounded-[24px] lg:rounded-[30px] p-6 lg:p-10 shadow-sm border border-gray-100 text-center animate-fadeUp">
                <div class="max-w-md mx-auto flex flex-col items-center">
                    <div class="w-16 lg:w-20 h-16 lg:h-20 bg-amber-50 border border-amber-100 rounded-2xl flex items-center justify-center text-2xl lg:text-4xl mb-4 shadow-sm animate-bounce" style="animation-duration: 3s;">
                        🔔
                    </div>
                    <h3 class="text-base lg:text-2xl font-black text-emerald-950 tracking-wide">
                        Belum Ada Jadwal Aktif
                    </h3>
                    <p class="text-xs lg:text-base text-gray-500 mt-2 leading-relaxed">
                        Jadwal latihan hanya akan muncul di sini apabila pendaftaran ekstrakurikuler kamu sudah disetujui atau berstatus <span class="text-emerald-600 font-bold">Diterima</span>
                    </p>
                </div>
            </div>
            <?php } ?>

            <div class="mt-14 animate-fadeUp">
                <h2 class="text-lg lg:text-3xl font-black text-emerald-700">
                    Daftar Ekstrakurikuler
                </h2>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-8 mt-4 lg:mt-6">
                <?php while($row = mysqli_fetch_assoc($ekskulQuery)){ ?>
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 card-hover glow animate-fadeUp overflow-hidden flex flex-col justify-between">
                    <div>
                        <img src="../gambar/<?= $row['foto'] ?>" class="w-full h-32 lg:h-64 object-cover image-hover">
                        <div class="p-4 lg:p-6">
                            <h2 class="text-xs lg:text-2xl font-black text-center text-emerald-700 break-words line-clamp-2 leading-snug">
                                <?= htmlspecialchars($row['nama_ekskul']) ?>
                            </h2>
                        </div>
                    </div>

                    <div class="p-4 lg:p-6 pt-0">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <a href="detail_ekskul.php?id=<?= $row['id_ekskul'] ?>" class="flex-1 text-center bg-white border border-emerald-500 text-emerald-700 py-2 rounded-lg font-bold text-[11px] lg:text-base shadow-sm transition-colors hover:bg-emerald-50">
                                Detail
                            </a>
                            <a href="form.php?id=<?= $row['id_ekskul'] ?>" class="flex-1 text-center bg-gradient-to-r from-emerald-600 to-green-500 text-white py-2 rounded-lg font-bold text-[11px] lg:text-base shadow-md transition-transform hover:scale-[1.02]">
                                Daftar
                            </a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const menuBtn = document.getElementById('menuBtn');
            const closeBtn = document.getElementById('closeBtn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }

            if(menuBtn) menuBtn.addEventListener('click', openSidebar);
            if(closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if(overlay) overlay.addEventListener('click', closeSidebar);

            const alerts = ['successAlert', 'notif-sukses'];
            setTimeout(function() {
                alerts.forEach(id => {
                    let alertBox = document.getElementById(id);
                    if (alertBox) {
                        alertBox.style.transition = "opacity 0.5s ease";
                        alertBox.style.opacity = "0";
                        setTimeout(() => alertBox.remove(), 500);
                    }
                });
            }, 5000);
        });
    </script>
</body>
</html>