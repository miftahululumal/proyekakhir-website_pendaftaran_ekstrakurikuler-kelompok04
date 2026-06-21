<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../register/cek_login.php';
include '../database/koneksi.php';

$username = $_SESSION['username'];
$error = "";
$pesan_sukses = ""; 
$showModal = false;

$nip = "";
$nama_guru = "";
$no_hp = "";
$email = "";
$err_fields = [
    'nip' => false, 'nama_guru' => false, 'no_hp' => false, 'email' => false
];

if (isset($_GET['pesan']) && $_GET['pesan'] == 'sukses') {
    $pesan_sukses = "Data guru berhasil ditambahkan!";
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $nip_hapus = mysqli_real_escape_string($conn, $_GET['id']); 
    $hapusQuery = mysqli_query($conn, "DELETE FROM guru WHERE nip = '$nip_hapus'");
    if ($hapusQuery) {
        $pesan_sukses = "Data guru berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data!";
    }
}

if (isset($_POST['submit_tambah'])) {
    $nip       = trim($_POST['nip'] ?? '');
    $nama_guru = trim($_POST['nama_guru'] ?? '');
    $no_hp     = trim($_POST['no_hp'] ?? '');
    $email     = trim($_POST['email'] ?? '');

    if (empty($nip) || empty($nama_guru) || empty($no_hp) || empty($email)) {
        $error = "Semua field wajib diisi!";
        $showModal = true;
        if(empty($nip))       $err_fields['nip'] = true;
        if(empty($nama_guru)) $err_fields['nama_guru'] = true;
        if(empty($no_hp))     $err_fields['no_hp'] = true;
        if(empty($email))     $err_fields['email'] = true;
    }
    
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Gagal menyimpan! Format <b>Email tidak valid</b>.";
        $showModal = true;
        $err_fields['email'] = true;
    }
    
    elseif (!isset($nip[17]) || isset($nip[18])) {
        $error = "Gagal menyimpan! NIP <b>wajib berjumlah tepat 18 digit</b>.";
        $showModal = true;
        $err_fields['nip'] = true;
    }
    
    elseif (!str_starts_with($no_hp, '08') && !str_starts_with($no_hp, '+62')) {
        $error = "Nomor HP tidak valid! Awalan harus menggunakan format <b>08</b> atau <b>+62</b>.";
        $showModal = true;
        $err_fields['no_hp'] = true;
    }
    
    elseif (!preg_match('/^[0-9]+$/', str_replace('+', '', $no_hp))) {
        $error = "Nomor HP hanya boleh berisi angka murni (tanpa spasi, huruf, atau tanda strip)!";
        $showModal = true;
        $err_fields['no_hp'] = true;
    }
    
    elseif (strlen($no_hp) < 10 || strlen($no_hp) > 14) {
        $error = "Jumlah digit nomor HP tidak sesuai! Pastikan panjang nomor antara 10 hingga 14 karakter.";
        $showModal = true;
        $err_fields['no_hp'] = true;
    }
    
    else {
        $nip_clean   = mysqli_real_escape_string($conn, $nip);
        $email_clean = mysqli_real_escape_string($conn, $email);
        $cekGuru = mysqli_query($conn, "SELECT * FROM guru WHERE nip='$nip_clean' OR email='$email_clean'");
        
        if (mysqli_num_rows($cekGuru) > 0) {
            $error = "Gagal menyimpan! NIP atau Email sudah terdaftar di sistem.";
            $showModal = true;
            $err_fields['nip'] = true;
            $err_fields['email'] = true;
        } else {
            $nama_clean  = mysqli_real_escape_string($conn, $nama_guru);
            $no_hp_clean = mysqli_real_escape_string($conn, $no_hp);
            $tambahQuery = "INSERT INTO guru (nip, nama_guru, no_hp, email) 
                            VALUES ('$nip_clean', '$nama_clean', '$no_hp_clean', '$email_clean')";
            
            if (mysqli_query($conn, $tambahQuery)) {
                header("Location: data_guru.php?pesan=sukses");
                exit;
            } else {
                $error = "Terjadi kesalahan database saat menyimpan data!";
                $showModal = true;
            }
        }
    }
}

$guruQuery = mysqli_query($conn, "SELECT * FROM guru ORDER BY nip DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru - Admin</title>
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
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <a href="dashboard_admin.php" class="block bg-white/10 hover:bg-white/20 p-2.5 rounded-xl text-center text-xs lg:text-sm transition-colors">Dashboard</a>
                <a href="tambah_ekskul.php" class="block bg-white/10 hover:bg-white/20 p-2.5 rounded-xl text-center text-xs lg:text-sm transition-colors">+ Tambah Ekskul</a>
                <a href="data_pendaftaran.php" class="block bg-white/10 hover:bg-white/20 p-2.5 rounded-xl text-center text-xs lg:text-sm transition-colors">Data Pendaftaran</a>
                <a href="data_guru.php" class="block bg-white text-emerald-700 p-2.5 rounded-xl font-black text-center text-xs lg:text-sm shadow-md">Data Guru</a>
                <a href="data_siswa.php" class="block bg-white/10 hover:bg-white/20 p-2.5 rounded-xl text-center text-xs lg:text-sm transition-colors">Data Siswa</a>
                <a href="../register/logout.php" class="block bg-red-500 hover:bg-red-600 transition-colors p-2.5 rounded-xl text-center font-black mt-6 text-xs lg:text-sm">Logout</a>
            </div>
        </aside>

        <div id="overlay" class="fixed inset-0 bg-black/40 backdrop-blur-md z-40 hidden lg:hidden transition-all duration-300"></div>
        <main class="flex-1 lg:ml-64 p-4 lg:p-8">
            <div class="flex justify-between items-center mb-6 animate-fadeUp">
                <div>
                    <h2 class="text-xl lg:text-4xl font-black text-emerald-700">Data Guru</h2>
                    <p class="text-gray-500 text-xs lg:text-sm">Kelola data seluruh pengajar & pembina ekstrakurikuler</p>
                </div>
                <button onclick="openModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl font-bold text-xs lg:text-sm shadow-md transition-colors">
                    + Tambah Guru
                </button>
            </div>

            <?php if ($pesan_sukses) { ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl mb-6 text-sm font-bold flex items-center shadow-sm animate-fadeUp">
                    ✅ <?= $pesan_sukses ?>
                </div>
            <?php } ?>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-fadeUp">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[950px]">
                        <thead>
                            <tr class="bg-emerald-900 text-white text-xs lg:text-sm">
                                <th class="p-4 w-12">No</th>
                                <th class="p-4 w-44">NIP</th>
                                <th class="p-4">Nama Guru</th>
                                <th class="p-4 w-40">No. HP</th>
                                <th class="p-4">Email</th>
                                <th class="p-4">Ekskul & Jadwal</th>
                                <th class="p-4 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs lg:text-sm text-gray-600 divide-y divide-gray-100">
                            <?php 
                            $no = 1;
                            while($guru = mysqli_fetch_assoc($guruQuery)){ 
                            ?>
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="p-4"><?= $no++; ?></td>
                                <td class="p-4 font-mono"><?= $guru['nip']; ?></td>
                                <td class="p-4 font-bold text-emerald-800"><?= $guru['nama_guru']; ?></td>
                                <td class="p-4"><?= $guru['no_hp']; ?></td>
                                <td class="p-4 text-gray-500"><?= $guru['email']; ?></td>
                                
                                <td class="p-4">
                                    <?php 
                                    $nip_guru = $guru['nip'];
                                    $queryEkskulGuru = mysqli_query($conn, "
                                        SELECT id_ekskul, nama_ekskul 
                                        FROM ekskul 
                                        WHERE nip_pembimbing = '$nip_guru'
                                    ");

                                    if (mysqli_num_rows($queryEkskulGuru) > 0) {
                                        while ($ekskul = mysqli_fetch_assoc($queryEkskulGuru)) {
                                            $id_ekskul = $ekskul['id_ekskul'];                                        
                                            echo "<div class='mb-2.5 last:mb-0 border-l-2 border-emerald-500 pl-2'>";
                                            echo "<span class='block text-emerald-700 font-bold text-xs lg:text-sm'>" . htmlspecialchars($ekskul['nama_ekskul']) . "</span>";
                                            
                                            $queryJadwal = mysqli_query($conn, "
                                                SELECT hari, jam_mulai, jam_selesai 
                                                FROM jadwal_ekskul 
                                                WHERE id_ekskul = '$id_ekskul'
                                                ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), jam_mulai ASC
                                            ");

                                            if (mysqli_num_rows($queryJadwal) > 0) {
                                                echo "<span class='block text-[11px] text-gray-500 italic font-medium mt-0.5'>";
                                                
                                                $array_jadwal = [];
                                                while ($jadwal = mysqli_fetch_assoc($queryJadwal)) {
                                                   
                                                    $jam_m = substr($jadwal['jam_mulai'], 0, 5);
                                                    $jam_s = substr($jadwal['jam_selesai'], 0, 5);
                                                    $array_jadwal[] = $jadwal['hari'] . " " . $jam_m . "-" . $jam_s;
                                                }

                                                echo htmlspecialchars(implode(', ', $array_jadwal));
                                                echo "</span>";
                                            } else {
                                                echo "<span class='block text-[11px] text-gray-400 italic mt-0.5'>Jadwal belum ditentukan</span>";
                                            }
                                            echo "</div>";
                                        }
                                    } else { 
                                        echo "<span class='text-gray-400 italic bg-gray-100 px-2 py-1 rounded-md text-xs'>Belum bimbing ekskul</span>";
                                    } 
                                    ?>
                                </td>

                                <td class="p-4 text-center">
                                    <a href="data_guru.php?aksi=hapus&id=<?= $guru['nip']; ?>" 
                                       onclick="return confirm('Yakin ingin menghapus guru ini?')" 
                                       class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg font-bold text-xs transition-colors inline-block">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php } if(mysqli_num_rows($guruQuery) == 0) { ?>
                                <tr>
                                    <td colspan="7" class="p-4 text-center text-gray-400 italic">Belum ada data guru.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="guruModal" class="<?= $showModal ? '' : 'hidden opacity-0' ?> fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center transition-opacity duration-300">
        <div class="bg-white p-6 rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md mx-4 transform transition-transform duration-300 max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-black text-emerald-700 mb-3">Tambah Guru Baru</h3>
            
            <?php if($showModal && !empty($error)){ ?>
                <div class="bg-red-50 border border-red-100 text-red-700 p-3 rounded-xl mb-4 text-xs font-medium">
                    ⚠️ <?= $error ?>
                </div>
            <?php } ?>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1 <?= $err_fields['nip'] ? 'text-red-500' : 'text-gray-500' ?>">NIP (18 Karakter)</label>
                    <input type="text" name="nip" maxlength="18" value="<?= htmlspecialchars($nip) ?>" 
                           class="w-full p-3 rounded-xl border text-sm font-medium transition duration-200 <?= $err_fields['nip'] ? 'border-red-500 bg-red-50 text-red-900 focus:ring-red-100 focus:border-red-500' : 'border-gray-200 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 text-gray-800' ?>">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1 <?= $err_fields['nama_guru'] ? 'text-red-500' : 'text-gray-500' ?>">Nama Lengkap</label>
                    <input type="text" name="nama_guru" value="<?= htmlspecialchars($nama_guru) ?>" 
                           class="w-full p-3 rounded-xl border text-sm font-medium transition duration-200 <?= $err_fields['nama_guru'] ? 'border-red-500 bg-red-50 text-red-900 focus:ring-red-100 focus:border-red-500' : 'border-gray-200 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 text-gray-800' ?>">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1 <?= $err_fields['no_hp'] ? 'text-red-500' : 'text-gray-500' ?>">No. HP</label>
                    <input type="text" name="no_hp" value="<?= htmlspecialchars($no_hp) ?>" 
                           class="w-full p-3 rounded-xl border text-sm font-medium transition duration-200 <?= $err_fields['no_hp'] ? 'border-red-500 bg-red-50 text-red-900 focus:ring-red-100 focus:border-red-500' : 'border-gray-200 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 text-gray-800' ?>">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1 <?= $err_fields['email'] ? 'text-red-500' : 'text-gray-500' ?>">Email</label>
                    <input type="text" name="email" value="<?= htmlspecialchars($email) ?>" 
                           class="w-full p-3 rounded-xl border text-sm font-medium transition duration-200 <?= $err_fields['email'] ? 'border-red-500 bg-red-50 text-red-900 focus:ring-red-100 focus:border-red-500' : 'border-gray-200 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 text-gray-800' ?>">
                </div>
                <div class="flex gap-2 pt-2 justify-end">
                    <button type="button" onclick="closeModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl font-bold text-xs transition-colors">Batal</button>
                    <button type="submit" name="submit_tambah" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition-colors">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const closeBtn = document.getElementById('closeBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const modal = document.getElementById('guruModal');

        function openSidebar() { sidebar.classList.remove('-translate-x-full'); overlay.classList.remove('hidden'); }
        function closeSidebar() { sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); }
        menuBtn.addEventListener('click', openSidebar);
        closeBtn.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);

        function openModal() {
            modal.classList.remove('hidden');
            setTimeout(() => { modal.classList.remove('opacity-0'); }, 10);
        }
        function closeModal() {
            modal.classList.add('opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }
        setTimeout(function() {
            const successAlert = document.querySelector('.bg-emerald-50');
            if (successAlert) {
                successAlert.style.transition = "opacity 0.8s ease";
                successAlert.style.opacity = "0";
                setTimeout(() => successAlert.remove(), 800);
            }
        }, 5000);
    </script>
</body>
</html>