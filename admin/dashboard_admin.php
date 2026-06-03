<?php
include '../register/cek_login.php';
include '../database/koneksi.php';

if($_SESSION['role'] != 'admin'){
    header("Location: ../user/dashboard_user.php");
    exit;
}

$username = $_SESSION['username'];
$ekskulQuery = mysqli_query($conn,"SELECT * FROM ekskul ORDER BY id_ekskul DESC");
$total = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM pendaftaran"));
$menunggu = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM pendaftaran WHERE status='Menunggu'"));
$diterima = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM pendaftaran WHERE status='Diterima'"));
$ditolak = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM pendaftaran WHERE status='Ditolak'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/animation.css">
</head>
<body class="bg-gray-50 min-h-screen overflow-x-hidden">

    <div class="lg:hidden bg-emerald-900 text-white p-3.5 flex justify-between items-center sticky top-0 z-30 shadow-md">
        <div class="flex items-center gap-2">
            <img src="../gambar/logo.png" class="w-6 h-6 object-cover rounded-md">
            <span class="font-black text-sm tracking-wide">SMK KAMAL 1</span>
        </div>
        <button id="menuBtn" class="p-1.5 focus:outline-none hover:bg-emerald-800 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <div class="flex flex-col lg:flex-row min-h-screen">
        
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-2/3 sm:w-1/2 lg:w-64 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out backdrop-blur-2xl bg-emerald-900/95 border-r border-white/10 text-white p-5 shadow-2xl overflow-y-auto h-screen">
            
            <div class="lg:hidden flex justify-end mb-2">
                <button id="closeBtn" class="p-1.5 hover:bg-white/10 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="animate-fadeUp">
                <div class="flex justify-center">
                    <div class="bg-white/10 backdrop-blur-xl p-2.5 rounded-2xl border border-white/20 shadow-xl">
                        <img src="../gambar/logo.png" class="w-14 lg:w-20 h-14 lg:h-20 object-cover rounded-xl">
                    </div>
                </div>
                <div class="text-center mt-3">
                    <h1 class="text-base lg:text-xl font-black tracking-wide">SMK KAMAL 1</h1>
                </div>
            </div>

            <div class="mt-5 bg-white/10 backdrop-blur-xl rounded-xl p-2.5 border border-white/20 text-center animate-fadeUp">
                <p class="text-[10px] text-white/60 uppercase tracking-wider">Mode Admin</p>
                <h2 class="text-sm lg:text-lg font-bold truncate mt-0.5"><?= $username ?></h2>
            </div>

            <div class="mt-5 space-y-2 animate-fadeUp">
                <a href="dashboard_admin.php" class="block bg-white text-emerald-700 p-2.5 rounded-xl font-black text-center text-xs lg:text-sm shadow-md">Dashboard</a>
                <a href="tambah_ekskul.php" class="block bg-white/10 hover:bg-white/20 p-2.5 rounded-xl text-center text-xs lg:text-sm transition-colors">+ Tambah Ekskul</a>
                <a href="data_pendaftaran.php" class="block bg-white/10 hover:bg-white/20 p-2.5 rounded-xl text-center text-xs lg:text-sm transition-colors">Data Pendaftaran</a>
                <a href="../register/logout.php" class="block bg-red-500 hover:bg-red-600 transition-colors p-2.5 rounded-xl text-center font-black mt-6 text-xs lg:text-sm">Logout</a>
            </div>
        </aside>

        <div id="overlay" class="fixed inset-0 bg-black/40 backdrop-blur-md z-40 hidden lg:hidden transition-all duration-300"></div>

        <section class="main flex-1 lg:ml-64 p-4 lg:p-8">

            <div class="animate-fadeUp">
                <h2 class="text-xl lg:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-700 via-green-500 to-emerald-400 leading-tight">
                    Dashboard Admin
                </h2>
                <p class="text-gray-500 mt-1 text-xs lg:text-base">
                    Kelola seluruh ekstrakurikuler sekolah dengan mudah
                </p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-6 lg:mt-8">
                <div class="bg-white p-3.5 lg:p-6 rounded-xl lg:rounded-[30px] shadow-sm border border-gray-100 card-hover glow floating">
                    <p class="text-center font-medium text-gray-400 text-[10px] lg:text-sm">Total Daftar</p>
                    <h1 class="text-xl lg:text-5xl text-center font-black text-emerald-700 mt-0.5"><?= $total ?></h1>
                </div>

                <div class="bg-white p-3.5 lg:p-6 rounded-xl lg:rounded-[30px] shadow-sm border border-gray-100 card-hover glow floating">
                    <p class="text-center font-medium text-gray-400 text-[10px] lg:text-sm">Menunggu</p>
                    <h1 class="text-xl lg:text-5xl text-center font-black text-yellow-500 mt-0.5"><?= $menunggu ?></h1>
                </div>

                <div class="bg-white p-3.5 lg:p-6 rounded-xl lg:rounded-[30px] shadow-sm border border-gray-100 card-hover glow floating">
                    <p class="text-center font-medium text-gray-400 text-[10px] lg:text-sm">Diterima</p>
                    <h1 class="text-xl lg:text-5xl text-center font-black text-green-500 mt-0.5"><?= $diterima ?></h1>
                </div>

                <div class="bg-white p-3.5 lg:p-6 rounded-xl lg:rounded-[30px] shadow-sm border border-gray-100 card-hover glow floating">
                    <p class="text-center font-medium text-gray-400 text-[10px] lg:text-sm">Ditolak</p>
                    <h1 class="text-xl lg:text-5xl text-center font-black text-red-500 mt-0.5"><?= $ditolak ?></h1>
                </div>
            </div>

            <div class="mt-10 lg:mt-14 animate-fadeUp">
                <h2 class="text-lg lg:text-3xl font-black text-emerald-700">Daftar Ekstrakurikuler</h2>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-6 mt-4 lg:mt-6">
                <?php while($row = mysqli_fetch_assoc($ekskulQuery)){ ?>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 card-hover glow animate-fadeUp overflow-hidden flex flex-col justify-between">
                    <div>
                        <img src="../gambar/<?= $row['foto'] ?>" class="w-full h-28 lg:h-56 object-cover image-hover">
                        <div class="p-3 lg:p-5">
                            <h2 class="text-xs lg:text-xl font-black text-center text-emerald-700 break-words line-clamp-2">
                                <?= $row['nama_ekskul'] ?>
                            </h2>
                        </div>
                    </div>

                    <div class="p-3 lg:p-5 pt-0">
                        <div class="flex flex-col sm:flex-row gap-1.5 sm:gap-2">
                            <a href="edit_ekskul.php?id=<?= $row['id_ekskul'] ?>" class="flex-1 bg-emerald-600 hover:bg-emerald-700 transition-colors text-white py-1.5 rounded-lg text-center font-bold text-[10px] lg:text-sm">Edit</a>
                            <a href="../admin/hapus.php?id=<?= $row['id_ekskul'] ?>" 
                               onclick="return confirm('Yakin hapus data?')"
                               class="flex-1 bg-red-500 hover:bg-red-600 transition-colors text-white py-1.5 rounded-lg text-center font-bold text-[10px] lg:text-sm">Hapus</a>
                        </div>
                    </div>
                </div>

                <?php } ?>
            </div>
        </section>
    </div>

    <script>
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

        menuBtn.addEventListener('click', openSidebar);
        closeBtn.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);
    </script>
</body>
</html>