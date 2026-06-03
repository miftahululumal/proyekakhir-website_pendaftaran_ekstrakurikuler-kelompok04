<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../register/cek_login.php';
include '../database/koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'user'){
    header("Location: ../admin/dashboard_admin.php");
    exit;
}

if(!isset($_GET['id'])){
    header("Location: dashboard_user.php");
    exit;
}

$id = $_GET['id'];
$query = mysqli_query($conn, "
    SELECT e.*, g.nama_guru AS pembimbing 
    FROM ekskul e
    LEFT JOIN guru g ON e.nip_pembimbing = g.nip 
    WHERE e.id_ekskul = '$id'
");
$data = mysqli_fetch_assoc($query);

$jadwalQuery = mysqli_query($conn,"
    SELECT *
    FROM jadwal_ekskul
    WHERE id_ekskul='$id'
    ORDER BY hari, jam_mulai
");

if(!$data){
    header("Location: dashboard_user.php");
    exit;
}

function safe($text){
    return nl2br(htmlspecialchars($text ?? ''));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Ekskul - <?= htmlspecialchars($data['nama_ekskul']) ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/animation.css">
    <style>
        .glass::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 24px;
            padding: 1px;
            background: linear-gradient(
                135deg,
                rgba(16, 185, 129, 0.4),
                rgba(255, 255, 255, 0.2),
                rgba(16, 185, 129, 0.1)
            );
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }
    </style>
</head>

<body class="text-gray-800 bg-gray-50/60 antialiased">
<div class="max-w-4xl mx-auto px-4 py-4 sm:py-10">
    
    <div class="relative rounded-[2rem] overflow-hidden shadow-xl animate-fadeUp">
        <img src="../gambar/<?= htmlspecialchars($data['foto']) ?>"
             class="w-full h-[220px] sm:h-[380px] object-cover">

        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent">
            <div class="absolute bottom-0 inset-x-0 p-5 sm:p-8 text-white">
                <p class="text-[10px] tracking-[3px] uppercase opacity-75 font-bold">
                    Ekstrakurikuler Sekolah
                </p>
                <h1 class="text-2xl sm:text-4xl font-black mt-1 leading-tight">
                    <?= htmlspecialchars($data['nama_ekskul']) ?>
                </h1>
                <span class="inline-flex items-center gap-1.5 mt-3 bg-emerald-500/90 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-bold tracking-wide shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Aktif
                </span>
            </div>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden animate-fadeUp">
        <div class="flex gap-1 min-w-max sm:justify-center bg-gray-200/70 p-1.5 rounded-2xl backdrop-blur-md shadow-inner">
            
            <button class="tab-btn px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm font-extrabold bg-emerald-500 text-white shadow-[0_4px_12px_rgba(16,185,129,0.2)] transition-all duration-300 whitespace-nowrap snap-center"
                    onclick="openTab(this, 'deskripsi')">Deskripsi</button>

            <button class="tab-btn px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm font-extrabold text-gray-600 hover:text-emerald-600 transition-all duration-300 whitespace-nowrap snap-center"
                    onclick="openTab(this, 'visi')">Visi</button>

            <button class="tab-btn px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm font-extrabold text-gray-600 hover:text-emerald-600 transition-all duration-300 whitespace-nowrap snap-center"
                    onclick="openTab(this, 'misi')">Misi</button>

            <button class="tab-btn px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm font-extrabold text-gray-600 hover:text-emerald-600 transition-all duration-300 whitespace-nowrap snap-center"
                    onclick="openTab(this, 'program')">Program Kerja</button>

            <button class="tab-btn px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm font-extrabold text-gray-600 hover:text-emerald-600 transition-all duration-300 whitespace-nowrap snap-center"
                    onclick="openTab(this, 'prestasi')">Prestasi</button>
        </div>
    </div>

    <div class="mt-4 glass p-5 sm:p-8 bg-white/75 backdrop-blur-md rounded-[24px] relative shadow-[0_10px_30px_rgba(0,0,0,0.04),inset_0_1px_0_rgba(255,255,255,0.6)] transition duration-300 md:hover:-translate-y-1 md:hover:shadow-[0_18px_45px_rgba(0,0,0,0.08),inset_0_1px_0_rgba(255,255,255,0.8)] animate-fadeUp">
        <div id="deskripsi" class="tab-content block transition-opacity duration-300">
            <h2 class="text-xl font-black text-emerald-800 mb-2">Deskripsi</h2>
            <p class="text-gray-600 text-sm sm:text-base leading-relaxed"><?= safe($data['deskripsi']) ?></p>
        </div>
        
        <div id="visi" class="tab-content hidden transition-opacity duration-300">
            <h2 class="text-xl font-black text-emerald-800 mb-2">Visi</h2>
            <p class="text-gray-600 text-sm sm:text-base leading-relaxed"><?= safe($data['visi']) ?></p>
        </div>

        <div id="misi" class="tab-content hidden transition-opacity duration-300">
            <h2 class="text-xl font-black text-emerald-800 mb-2">Misi</h2>
            <p class="text-gray-600 text-sm sm:text-base leading-relaxed"><?= safe($data['misi']) ?></p>
        </div>

        <div id="program" class="tab-content hidden transition-opacity duration-300">
            <h2 class="text-xl font-black text-emerald-800 mb-2">Program Kerja</h2>
            <p class="text-gray-600 text-sm sm:text-base leading-relaxed"><?= safe($data['program_kerja']) ?></p>
        </div>

        <div id="prestasi" class="tab-content hidden transition-opacity duration-300">
            <h2 class="text-xl font-black text-emerald-800 mb-2">Prestasi</h2>
            <p class="text-gray-600 text-sm sm:text-base leading-relaxed"><?= safe($data['prestasi']) ?></p>
        </div>
    </div>

    <div class="mt-5 space-y-4 animate-fadeUp">
        <div class="glass p-5 bg-white/75 backdrop-blur-md rounded-[24px] relative shadow-[0_10px_30px_rgba(0,0,0,0.04),inset_0_1px_0_rgba(255,255,255,0.6)] transition duration-300 md:hover:-translate-y-1 md:hover:shadow-[0_18px_45px_rgba(0,0,0,0.08),inset_0_1px_0_rgba(255,255,255,0.8)]">
            <h2 class="text-base font-black text-emerald-800 mb-3">📅 Jadwal Latihan</h2>
            <?php if(mysqli_num_rows($jadwalQuery) > 0){ ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <?php while($jadwal = mysqli_fetch_assoc($jadwalQuery)){ ?>
                        <div class="bg-gradient-to-br from-emerald-50/60 to-emerald-100/40 border border-emerald-100 rounded-xl p-3">
                            <p class="font-bold text-emerald-800 text-sm sm:text-base">
                                <?= htmlspecialchars($jadwal['hari']) ?>
                            </p>
                            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                                Jam <?= substr($jadwal['jam_mulai'],0,5) ?> - <?= substr($jadwal['jam_selesai'],0,5) ?> WIB
                            </p>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <p class="text-xs sm:text-sm text-gray-400 italic">Belum ada jadwal pengerjaan latihan.</p>
            <?php } ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="glass p-5 flex flex-col justify-center bg-white/75 backdrop-blur-md rounded-[24px] relative shadow-[0_10px_30px_rgba(0,0,0,0.04),inset_0_1px_0_rgba(255,255,255,0.6)] transition duration-300 md:hover:-translate-y-1 md:hover:shadow-[0_18px_45px_rgba(0,0,0,0.08),inset_0_1px_0_rgba(255,255,255,0.8)]">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Guru Pembimbing</h2>
                <p class="text-base sm:text-lg font-bold text-emerald-800">
                    <?= !empty($data['pembimbing']) ? safe($data['pembimbing']) : 'Belum Ditentukan' ?> 
                </p>
            </div>

            <div class="glass p-5 bg-white/75 backdrop-blur-md rounded-[24px] relative shadow-[0_10px_30px_rgba(0,0,0,0.04),inset_0_1px_0_rgba(255,255,255,0.6)] transition duration-300 md:hover:-translate-y-1 md:hover:shadow-[0_18px_45px_rgba(0,0,0,0.08),inset_0_1px_0_rgba(255,255,255,0.8)]">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Informasi Instansi</h2>
                <div class="space-y-2 text-xs sm:text-sm">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-1.5">
                        <span class="text-gray-500 font-medium">Kategori</span>
                        <span class="font-bold text-gray-700">Ekstrakurikuler</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-medium">Pendaftaran</span>
                        <span class="bg-emerald-50 text-emerald-700 font-bold px-2 py-0.5 rounded text-[11px]">Terbuka</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3 animate-fadeUp">
        <a href="form.php?id=<?= $id ?>"
           class="w-full bg-gradient-to-r from-emerald-600 to-emerald-500 text-white py-3.5 rounded-xl font-extrabold text-sm shadow-md hover:opacity-95 transition text-center block active:scale-[0.98]">
            Daftar Sekarang
        </a>
        <a href="dashboard_user.php"
           class="w-full bg-white border border-gray-200 text-emerald-700 py-3.5 rounded-xl font-extrabold text-sm shadow-sm hover:bg-gray-50 transition text-center block active:scale-[0.98]">
            ← Kembali ke Dashboard
        </a>
    </div>

</div>

<script>
function openTab(buttonElement, tabId){
    let contents = document.querySelectorAll('.tab-content');
    let buttons = document.querySelectorAll('.tab-btn');
    contents.forEach(c => {
        c.classList.remove('block');
        c.classList.add('hidden');
    });
    
    buttons.forEach(b => {
        b.className = "tab-btn px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm font-extrabold text-gray-600 hover:text-emerald-600 transition-all duration-300 whitespace-nowrap snap-center";
    });

    const activeContent = document.getElementById(tabId);
    activeContent.classList.remove('hidden');
    activeContent.classList.add('block');
    buttonElement.className = "tab-btn px-4 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm font-extrabold bg-emerald-500 text-white shadow-[0_4px_12px_rgba(16,185,129,0.2)] transition-all duration-300 whitespace-nowrap snap-center";
    buttonElement.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
}
</script>
</body>
</html>