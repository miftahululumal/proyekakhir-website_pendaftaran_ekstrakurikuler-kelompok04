<?php
include '../register/cek_login.php';
include '../database/koneksi.php';

if($_SESSION['role'] != 'user'){
    header("Location: ../admin/dashboard_admin.php");
    exit;
}

$nisn = $_SESSION['nisn'];
$riwayatQuery = mysqli_query($conn,
    "SELECT
        p.*,
        e.nama_ekskul,
        g.nama_guru AS pembimbing,
        j.hari,
        j.jam_mulai,
        j.jam_selesai
     FROM pendaftaran p
     LEFT JOIN ekskul e
        ON p.id_ekskul = e.id_ekskul
     LEFT JOIN guru g
        ON e.nip_pembimbing = g.nip
     LEFT JOIN jadwal_ekskul j
        ON p.id_jadwal = j.id_jadwal
     WHERE p.nisn = '$nisn'
     ORDER BY p.id_pendaftaran DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pendaftaran - Sistem Ekskul</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/animation.css">
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
<div class="max-w-5xl mx-auto px-4 py-6 sm:py-10">
    
    <div class="animate-fadeUp mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
            <div class="bg-white p-3 rounded-2xl border border-gray-200/60 shadow-sm">
                <img src="../gambar/logo.png" class="w-14 h-14 sm:w-16 sm:h-16 object-cover rounded-xl" onerror="this.src='https://via.placeholder.com/150'">
            </div>
            <div>
                <h1 class="text-2xl sm:text-4xl font-black bg-gradient-to-r from-emerald-800 via-emerald-600 to-green-500 bg-clip-text text-transparent">
                    Riwayat Pendaftaran
                </h1>
                <p class="text-gray-400 text-xs sm:text-sm mt-0.5">
                    Pantau status penerimaan dan riwayat berkas pendaftaran ekstrakurikuler milikmu
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white/90 backdrop-blur-xl rounded-[2rem] border border-gray-200/60 shadow-xl p-4 sm:p-6 md:p-8 animate-fadeUp">
        
        <div class="overflow-x-auto rounded-2xl border border-gray-100 shadow-sm bg-white">
            <table class="min-w-[650px] w-full text-left border-collapse">
                <thead class="bg-gradient-to-r from-emerald-700 to-emerald-600 text-white text-xs sm:text-sm uppercase tracking-wider font-bold">
                    <tr>
                        <th class="p-4 text-center w-[100px]">Foto</th>
                        <th class="p-4">Nama Ekstrakurikuler</th>
                        <th class="p-4 w-[160px] text-center">Tanggal Daftar</th>
                        <th class="p-4 w-[180px] text-center">Status Kelulusan</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 text-sm">
                <?php if(mysqli_num_rows($riwayatQuery) > 0){ ?>
                    <?php while($r = mysqli_fetch_assoc($riwayatQuery)){ ?>
                        <tr class="hover:bg-gray-50/80 transition duration-150">
                            
                            <td class="p-4 text-center">
                                <?php if(!empty($r['foto_diri'])){ ?>
                                    <img src="../gambar/<?= $r['foto_diri'] ?>"
                                         class="w-11 h-11 sm:w-12 sm:h-12 object-cover rounded-xl mx-auto border border-gray-100 shadow-sm">
                                <?php } else { ?>
                                    <span class="text-gray-300 text-xs italic">No Foto</span>
                                <?php } ?>
                            </td>

                            <td class="p-4">
                                <p class="font-bold text-gray-700 text-sm sm:text-base">
                                    <?= htmlspecialchars($r['nama_ekskul']) ?>
                                </p>
                                <p class="text-xs text-emerald-700 font-medium mt-0.5">
                                    🗓️ <?= htmlspecialchars($r['hari']) ?> (Jam <?= substr($r['jam_mulai'],0,5) ?> WIB)
                                </p>
                            </td>

                            <td class="p-4 text-center text-xs sm:text-sm font-medium text-gray-500">
                                <?= date('d M Y', strtotime($r['tanggal_daftar'])) ?>
                            </td>

                            <td class="p-4 text-center">
                                <?php if($r['status'] == "Menunggu"){ ?>
                                    <span class="inline-block bg-amber-50 text-amber-700 px-3 py-1.5 rounded-full text-xs font-extrabold tracking-wide border border-amber-100">
                                        ⏳ Menunggu
                                    </span>
                                <?php } ?>

                                <?php if($r['status'] == "Diterima"){ ?>
                                    <span class="inline-block bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-full text-xs font-extrabold tracking-wide border border-emerald-100">
                                        ✅ Diterima
                                    </span>
                                <?php } ?>

                                <?php if($r['status'] == "Ditolak"){ ?>
                                    <div class="flex flex-col sm:flex-row justify-center items-center gap-1.5">
                                        <span class="inline-block bg-red-50 text-red-700 px-3 py-1.5 rounded-full text-xs font-extrabold tracking-wide border border-red-100">
                                            ❌ Ditolak
                                        </span>
                                        <button onclick="openModal('<?= htmlspecialchars($r['alasan_ditolak']) ?>','Alasan Berkas Ditolak')"
                                                class="bg-gray-800 hover:bg-gray-900 text-white px-2.5 py-1 rounded-lg text-[11px] font-bold shadow-sm transition">
                                            Alasan
                                        </button>
                                    </div>
                                <?php } ?>

                                <?php if($r['status'] == "Dikeluarkan"){ ?>
                                    <div class="flex flex-col sm:flex-row justify-center items-center gap-1.5">
                                        <span class="inline-block bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-xs font-extrabold tracking-wide border border-gray-200">
                                            🚫 Dikeluarkan
                                        </span>
                                        <button onclick="openModal('<?= htmlspecialchars($r['alasan_dikeluarkan']) ?>','Alasan Dikeluarkan')"
                                                class="bg-gray-500 hover:bg-gray-600 text-white px-2.5 py-1 rounded-lg text-[11px] font-bold shadow-sm transition">
                                            Alasan
                                        </button>
                                    </div>
                                <?php } ?>
                            </td>

                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-400 italic font-medium">
                             Belum ada riwayat pendaftaran ekstrakurikuler yang terekam.
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="mt-5 sm:mt-6 border-t border-gray-100 pt-4 flex justify-start">
            <a href="dashboard_user.php"
               class="inline-flex items-center gap-2 bg-white border border-gray-200 text-emerald-700 px-5 py-3 rounded-xl font-extrabold text-sm shadow-sm hover:bg-gray-50 transition active:scale-[0.99]">
                 ← Kembali ke Dashboard
            </a>
        </div>

    </div>
</div>

<div id="modalAlasan" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4 transition-all duration-300 animate-fadeIn">
    <div class="bg-white w-full max-w-md rounded-2xl p-5 sm:p-6 shadow-2xl transform scale-100 transition-transform">
        
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-50 border border-red-100 mb-3 text-red-600 text-xl">
                ℹ️
            </div>
            <h2 id="judulModal" class="text-lg sm:text-xl font-black text-gray-800 leading-tight">
                </h2>
            <div id="isiAlasan" class="mt-3 bg-gray-50 border border-gray-100 p-4 rounded-xl text-left text-gray-600 text-xs sm:text-sm leading-relaxed">
                </div>
        </div>

        <button onclick="closeModal()" 
                class="w-full mt-5 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white py-3 rounded-xl font-bold text-sm shadow-md hover:opacity-95 transition">
            Pahami & Tutup
        </button>
    </div>
</div>

<script>
function openModal(alasan, judul){
    const modal = document.getElementById('modalAlasan');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('isiAlasan').innerHTML = alasan;
    document.getElementById('judulModal').innerHTML = judul;
}

function closeModal(){
    const modal = document.getElementById('modalAlasan');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

window.onclick = function(event) {
    const modal = document.getElementById('modalAlasan');
    if (event.target == modal) {
        closeModal();
    }
}
</script>
</body>
</html>