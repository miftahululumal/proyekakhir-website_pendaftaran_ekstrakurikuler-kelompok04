<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../register/cek_login.php';
include '../database/koneksi.php';

if($_SESSION['role'] != 'admin'){
    header("Location: ../user/dashboard_user.php");
    exit;
}

$username = $_SESSION['username'];
$pesan_sukses = isset($_SESSION['pesan_sukses']) ? $_SESSION['pesan_sukses'] : "";
unset($_SESSION['pesan_sukses']);

$error_tambah = "";
$error_reset = "";

$nisn = isset($_POST['nisn']) ? $_POST['nisn'] : "";
$nama = isset($_POST['nama']) ? $_POST['nama'] : "";
$email = isset($_POST['email']) ? $_POST['email'] : "";
$password = isset($_POST['password']) ? $_POST['password'] : "";
$alamat = isset($_POST['alamat']) ? $_POST['alamat'] : "";
$jenis_kelamin = isset($_POST['jenis_kelamin']) ? $_POST['jenis_kelamin'] : "Laki-laki";
$kelas = isset($_POST['kelas']) ? $_POST['kelas'] : "10";
$err_fields = ['nisn' => false, 'nama' => false, 'email' => false, 'password' => false, 'alamat' => false];

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    mysqli_query($conn, "DELETE FROM siswa WHERE nisn = '$id'");
    $_SESSION['pesan_sukses'] = "Data siswa berhasil dihapus!";
    header("Location: data_siswa.php"); exit;
}

if (isset($_POST['submit_reset'])) {
    $nisn_reset = mysqli_real_escape_string($conn, $_POST['nisn_reset']);
    $pass_baru  = $_POST['password_baru'];
    
    if (strlen($pass_baru) < 8) {
        $error_reset = "Password minimal 8 karakter!";
    } else {
        $hash = password_hash($pass_baru, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE siswa SET password='$hash', updated_sts=NULL WHERE nisn='$nisn_reset'");
        $_SESSION['pesan_sukses'] = "Password berhasil direset!";
        header("Location: data_siswa.php"); 
        exit;
    }
}

if (isset($_POST['submit_tambah'])) {
    if (empty($nisn) || empty($nama) || empty($email) || empty($password) || empty($alamat)) {
        $error_tambah = "Semua field wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_tambah = "Format Email tidak valid!"; $err_fields['email'] = true;
    } elseif (strlen($nisn) != 10) {
        $error_tambah = "NISN wajib 10 digit!"; $err_fields['nisn'] = true;
    } elseif (strlen($password) < 8) {
        $error_tambah = "Password minimal 8 karakter!"; $err_fields['password'] = true;
    } else {
        $nisn_clean = mysqli_real_escape_string($conn, $nisn);
        $email_clean = mysqli_real_escape_string($conn, $email);
        $cek = mysqli_query($conn, "SELECT * FROM siswa WHERE nisn='$nisn_clean' OR email='$email_clean'");
        
        if (mysqli_num_rows($cek) > 0) {
            $error_tambah = "NISN atau Email sudah terdaftar!";
        } else {
            $nama_clean = mysqli_real_escape_string($conn, $nama);
            $alamat_clean = mysqli_real_escape_string($conn, $alamat);
            $hash = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO siswa (nisn, nama, email, password, alamat, jenis_kelamin, kelas) VALUES ('$nisn_clean', '$nama_clean', '$email_clean', '$hash', '$alamat_clean', '$jenis_kelamin', '$kelas')");
            $_SESSION['pesan_sukses'] = "Data siswa berhasil ditambahkan!";
            header("Location: data_siswa.php"); exit;
        }
    }
}
// Ganti baris 89 menjadi:
$siswaQuery = mysqli_query($conn, "SELECT nisn, nama, kelas, jenis_kelamin, email, alamat FROM siswa ORDER BY nisn DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - Admin</title>
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
                <a href="data_guru.php" class="block bg-white/10 hover:bg-white/20 p-2.5 rounded-xl text-center text-xs lg:text-sm transition-colors">Data Guru</a>
                <a href="data_siswa.php" class="block bg-white text-emerald-700 p-2.5 rounded-xl font-black text-center text-xs lg:text-sm shadow-md">Data Siswa</a>
                <a href="../register/logout.php" class="block bg-red-500 hover:bg-red-600 transition-colors p-2.5 rounded-xl text-center font-black mt-6 text-xs lg:text-sm">Logout</a>
            </div>
        </aside>

        <div id="overlay" class="fixed inset-0 bg-black/40 backdrop-blur-md z-40 hidden lg:hidden transition-all duration-300"></div>
        <main class="flex-1 lg:ml-64 p-4 lg:p-8">
           <?php if ($pesan_sukses) { ?>
                <div id="pesan-sukses" class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl mb-6 text-sm font-bold flex items-center shadow-sm animate-fadeUp">
                    ✅ <?= $pesan_sukses ?>
                </div>
            <?php } ?>

            <div class="flex justify-between items-center mb-6 animate-fadeUp">
                <div>
                    <h2 class="text-xl lg:text-4xl font-black text-emerald-700">Data Siswa</h2>
                    <p class="text-gray-500 text-xs lg:text-sm">Kelola dan lihat rincian data seluruh siswa pendaftar</p>
                </div>
                <button onclick="openModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl font-bold text-xs lg:text-sm shadow-md transition-colors">
                    + Tambah Siswa
                </button>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-fadeUp">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1000px]">
                        <thead>
                            <tr class="bg-emerald-900 text-white text-xs lg:text-sm">
                                <th class="p-4 w-12">No</th>
                                <th class="p-4 w-28">NISN</th>
                                <th class="p-4">Nama Siswa</th>
                                <th class="p-4 w-20">Kelas</th>
                                <th class="p-4 w-28">Gender</th>
                                <th class="p-4">Email</th>
                                <th class="p-4 max-w-xs">Alamat</th>
                                <th class="p-4 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs lg:text-sm text-gray-600 divide-y divide-gray-100">
                            <?php 
                            $no = 1;
                            while($siswa = mysqli_fetch_assoc($siswaQuery)){ 
                            ?>
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="p-4"><?= $no++; ?></td>
                                <td class="p-4 font-mono"><?= $siswa['nisn']; ?></td>
                                <td class="p-4 font-bold text-emerald-800"><?= $siswa['nama']; ?></td>
                                <td class="p-4 text-center"><span class="bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-md font-semibold"><?= $siswa['kelas']; ?></span></td>
                                <td class="p-4"><?= $siswa['jenis_kelamin']; ?></td>
                                <td class="p-4 break-all text-gray-500"><?= $siswa['email']; ?></td>
                                <td class="p-4 max-w-xs break-words text-gray-500"><?= $siswa['alamat']; ?></td>
                                <td class="p-4 text-center">
                                    <div class="flex flex-col lg:flex-row gap-1 justify-center">
                                        <button type="button" onclick="openResetModal('<?= $siswa['nisn']; ?>')" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg font-bold text-xs transition-colors">Reset</button>
                                        <a href="data_siswa.php?aksi=hapus&id=<?= $siswa['nisn']; ?>" onclick="return confirm('Yakin ingin menghapus siswa ini?')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg font-bold text-xs transition-colors">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                            <?php } if(mysqli_num_rows($siswaQuery) == 0) { ?>
                                <tr><td colspan="9" class="p-4 text-center text-gray-400 italic">Belum ada data siswa.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        </div>
    </div>

    <div id="siswaModal" class="<?= $showModal ? '' : 'hidden opacity-0' ?> fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center transition-opacity duration-300">
        <div class="bg-white p-6 rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md mx-4 transform transition-transform duration-300 max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-black text-emerald-700 mb-3">Tambah Siswa Baru</h3>
            
            <?php if(!empty($error_tambah)){ ?>
                <div class="bg-red-50 border border-red-100 text-red-700 p-3 rounded-xl mb-4 text-xs font-medium">
                    ⚠️ <?= $error_tambah ?>
                </div>
            <?php } ?>

            <form action="" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1 <?= $err_fields['nisn'] ? 'text-red-500' : 'text-gray-500' ?>">NISN (10 digit)</label>
                    <input type="text" name="nisn" maxlength="10" value="<?= htmlspecialchars($nisn) ?>" 
                           class="w-full p-3 rounded-xl border text-sm font-medium transition duration-200 <?= $err_fields['nisn'] ? 'border-red-500 bg-red-50 text-red-900 focus:ring-red-100 focus:border-red-500' : 'border-gray-200 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 text-gray-800' ?>">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1 <?= $err_fields['nama'] ? 'text-red-500' : 'text-gray-500' ?>">Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($nama) ?>" 
                           class="w-full p-3 rounded-xl border text-sm font-medium transition duration-200 <?= $err_fields['nama'] ? 'border-red-500 bg-red-50 text-red-900 focus:ring-red-100 focus:border-red-500' : 'border-gray-200 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 text-gray-800' ?>">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1 <?= $err_fields['email'] ? 'text-red-500' : 'text-gray-500' ?>">Email</label>
                    <input type="text" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="contoh@gmail.com"
                           class="w-full p-3 rounded-xl border text-sm font-medium transition duration-200 <?= $err_fields['email'] ? 'border-red-500 bg-red-50 text-red-900 focus:ring-red-100 focus:border-red-500' : 'border-gray-200 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 text-gray-800' ?>">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1 <?= $err_fields['password'] ? 'text-red-500' : 'text-gray-500' ?>">Password (Min. 8 Karakter)</label>
                    <input type="text" name="password" value="<?= htmlspecialchars($password) ?>" placeholder="Minimal 8 karakter"
                           class="w-full p-3 rounded-xl border text-sm font-medium transition duration-200 <?= $err_fields['password'] ? 'border-red-500 bg-red-50 text-red-900 focus:ring-red-100 focus:border-red-500' : 'border-gray-200 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 text-gray-800' ?>">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Kelas</label>
                        <select name="kelas" required class="w-full p-3 rounded-xl border bg-white text-sm font-medium border-gray-200 text-gray-700 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500">
                            <option value="10" <?= $kelas == '10' ? 'selected' : '' ?>>10</option>
                            <option value="11" <?= $kelas == '11' ? 'selected' : '' ?>>11</option>
                            <option value="12" <?= $kelas == '12' ? 'selected' : '' ?>>12</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Gender</label>
                        <select name="jenis_kelamin" required class="w-full p-3 rounded-xl border bg-white text-sm font-medium border-gray-200 text-gray-700 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500">
                            <option value="Laki-laki" <?= $jenis_kelamin == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= $jenis_kelamin == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide mb-1 <?= $err_fields['alamat'] ? 'text-red-500' : 'text-gray-500' ?>">Alamat</label>
                    <textarea name="alamat" rows="2" placeholder="Tulis alamat lengkap..." 
                              class="w-full p-3 rounded-xl border text-sm font-medium transition duration-200 <?= $err_fields['alamat'] ? 'border-red-500 bg-red-50 text-red-900 focus:ring-red-100 focus:border-red-500' : 'border-gray-200 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 text-gray-800' ?>"><?= htmlspecialchars($alamat) ?></textarea>
                </div>
                <div class="flex gap-2 pt-2 justify-end">
                    <button type="button" onclick="closeModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl font-bold text-xs transition-colors">Batal</button>
                    <button type="submit" name="submit_tambah" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition-colors">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <div id="resetModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-2xl shadow-2xl border border-gray-100 w-full max-w-sm mx-4">
            <h3 class="text-xl font-black text-amber-600 mb-3">Reset Password</h3>
            
            <?php if(!empty($error_reset)){ ?>
                <div class="bg-red-50 border border-red-100 text-red-700 p-3 rounded-xl mb-4 text-xs font-medium">
                    ⚠️ <?= $error_reset ?>
                </div>
            <?php } ?>

            <form action="" method="POST">
                <input type="hidden" name="nisn_reset" id="nisn_reset">
                
                <div class="mb-4 relative">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Password Baru</label>
                    <div class="relative">
                        <input type="password" name="password_baru" id="pass_reset" required 
                            class="w-full p-3 pr-10 rounded-xl border border-gray-200 text-sm focus:ring-4 focus:ring-amber-100">
                        <button type="button" onclick="togglePassword('pass_reset', this)" 
                            class="absolute right-3 top-3 text-gray-400 hover:text-amber-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="closeResetModal()" class="w-full bg-gray-100 p-2.5 rounded-xl font-bold text-xs">Batal</button>
                    <button type="submit" name="submit_reset" class="w-full bg-amber-600 text-white p-2.5 rounded-xl font-bold text-xs">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const menuBtn = document.getElementById('menuBtn');
        const closeBtn = document.getElementById('closeBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const modal = document.getElementById('siswaModal');

        function openSidebar() { sidebar.classList.remove('-translate-x-full'); overlay.classList.remove('hidden'); }
        function closeSidebar() { sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); }
        
        if(menuBtn) menuBtn.addEventListener('click', openSidebar);
        if(closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if(overlay) overlay.addEventListener('click', closeSidebar);

        function openModal() {
            modal.classList.remove('hidden');
            setTimeout(() => { modal.classList.remove('opacity-0'); }, 10);
        }
        function closeModal() {
            modal.classList.add('opacity-0');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }

        function openResetModal(nisn) {
            document.getElementById('nisn_reset').value = nisn;
            document.getElementById('resetModal').classList.remove('hidden');
        }
        function closeResetModal() {
            document.getElementById('resetModal').classList.add('hidden');
        }

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('svg');
            
            if (input.type === "password") {
                input.type = "text";
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.7 9.7 0 012.26-3.834m5.58 5.58l-1.32 1.32m3.66-3.66l-1.32 1.32m3.66-3.66l-1.32 1.32m3.66-3.66L13.875 18.825z"></path>';
            } else {
                input.type = "password";
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12 a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            }
        }

        setTimeout(function() {
            const alertSukses = document.getElementById('alert-sukses');
            if (alertSukses) {
                alertSukses.style.transition = "opacity 0.8s ease, height 0.5s ease, margin 0.5s ease, padding 0.5s ease";
                alertSukses.style.opacity = "0";
                alertSukses.style.height = "0";
                alertSukses.style.margin = "0";
                alertSukses.style.padding = "0";
                alertSukses.style.overflow = "hidden";
                setTimeout(() => alertSukses.remove(), 800); 
            }
        }, 5000); 

        <?php if(!empty($error_tambah)) echo "openModal();"; ?>
        <?php if(!empty($error_reset)) echo "document.getElementById('resetModal').classList.remove('hidden');"; ?>
    </script>
</body>
</html>