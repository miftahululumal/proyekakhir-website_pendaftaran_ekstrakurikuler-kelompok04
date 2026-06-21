<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../register/cek_login.php';
include '../database/koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'user'){
    header("Location: ../admin/dashboard_admin.php");
    exit;
}

$nisn  = $_SESSION['nisn'] ?? '';
$kelas = $_SESSION['kelas'] ?? '';
$nama  = $_SESSION['nama'] ?? '';

$id_ekskul = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id_ekskul <= 0){
    header("Location: dashboard_user.php");
    exit;
}

$id_ekskul_clean = mysqli_real_escape_string($conn, $id_ekskul);
$dataEkskul = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM ekskul WHERE id_ekskul='$id_ekskul_clean'
"));

if(!$dataEkskul){
    header("Location: dashboard_user.php");
    exit;
}

$cekTotal = mysqli_query($conn, "
    SELECT * FROM pendaftaran
    WHERE nisn='$nisn'
    AND status='Diterima'
");

$totalDaftar = mysqli_num_rows($cekTotal);

$error = "";
$success = "";
$showForm = false;
$no_hp = "";
$id_jadwal = "";

$err_fields = [
    'id_jadwal' => false,
    'no_hp'     => false,
    'foto'      => false,
    'surat'     => false
];

if(isset($_POST['daftar'])){

    $id_jadwal = trim($_POST['id_jadwal'] ?? '');
    $no_hp     = trim($_POST['no_hp'] ?? '');
    $foto      = $_FILES['foto']['name'] ?? '';
    $tmp_foto  = $_FILES['foto']['tmp_name'] ?? '';
    $size_foto = $_FILES['foto']['size'] ?? 0;
    $err_foto  = $_FILES['foto']['error'] ?? 0;
    $surat      = $_FILES['surat']['name'] ?? '';
    $tmp_surat  = $_FILES['surat']['tmp_name'] ?? '';
    $size_surat = $_FILES['surat']['size'] ?? 0;
    $err_surat  = $_FILES['surat']['error'] ?? 0;

    if(empty($id_jadwal) || empty($no_hp) || empty($foto) || empty($surat)){
        $error = "Semua field wajib diisi termasuk dokumen lampiran!";
        $showForm = true;
        if(empty($id_jadwal)) $err_fields['id_jadwal'] = true;
        if(empty($no_hp))     $err_fields['no_hp'] = true;
        if(empty($foto))      $err_fields['foto'] = true;
        if(empty($surat))     $err_fields['surat'] = true;
    }

    elseif (!str_starts_with($no_hp, '08') && !str_starts_with($no_hp, '+62')) {
        $error = "Nomor HP tidak valid! Awalan harus menggunakan format <b>08</b> atau <b>+62</b>.";
        $showForm = true;
        $err_fields['no_hp'] = true;
    }

    elseif (!preg_match('/^[0-9]+$/', str_replace('+', '', $no_hp))) {
        $error = "Nomor HP hanya boleh berisi angka murni (tanpa spasi, huruf, atau tanda strip)!";
        $showForm = true;
        $err_fields['no_hp'] = true;
    }

    elseif (strlen($no_hp) < 10 || strlen($no_hp) > 14) {
        $error = "Jumlah digit nomor HP tidak sesuai! Pastikan panjang nomor antara 10 hingga 14 karakter.";
        $showForm = true;
        $err_fields['no_hp'] = true;
    } 
    
    elseif($totalDaftar >= 3){
        $error = "Kamu sudah diterima di 3 ekstrakurikuler!";
        $showForm = true;
    }
    else{
        $cekEkskul = mysqli_query($conn, "
            SELECT * FROM pendaftaran
            WHERE nisn='$nisn'
            AND id_ekskul='$id_ekskul_clean'
        ");

        if(mysqli_num_rows($cekEkskul) > 0){
            $error = "Kamu sudah mendaftar ekskul ini!";
            $showForm = true;
        } else {
            $ekstensi_foto  = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
            $ekstensi_surat = strtolower(pathinfo($surat, PATHINFO_EXTENSION));
            
            $allowed_foto   = ['jpg','jpeg','png'];

            if(!in_array($ekstensi_foto, $allowed_foto)){
                $error = "Format foto harus JPG, JPEG, atau PNG!";
                $showForm = true;
                $err_fields['foto'] = true;
            }
            elseif($err_foto !== 0){
                $error = "Upload pas foto gagal!";
                $showForm = true;
                $err_fields['foto'] = true;
            }
            elseif($size_foto > 2 * 1024 * 1024){
                $error = "Ukuran pas foto maksimal 2 MB!";
                $showForm = true;
                $err_fields['foto'] = true;
            }
            elseif($ekstensi_surat !== 'pdf'){
                $error = "Format berkas surat pernyataan harus berupa <b>PDF</b>!";
                $showForm = true;
                $err_fields['surat'] = true;
            }
            elseif($err_surat !== 0){
                $error = "Upload berkas surat panyataan gagal!";
                $showForm = true;
                $err_fields['surat'] = true;
            }
            elseif($size_surat > 2 * 1024 * 1024){
                $error = "Ukuran berkas surat pernyataan maksimal 2 MB!";
                $showForm = true;
                $err_fields['surat'] = true;
            }
            else{
                $id_jadwal_clean = mysqli_real_escape_string($conn, $id_jadwal);
                $jadwalBaru = mysqli_fetch_assoc(mysqli_query($conn, "
                    SELECT * FROM jadwal_ekskul
                    WHERE id_jadwal='$id_jadwal_clean'
                "));

                $cekDiterima = mysqli_query($conn, "
                    SELECT p.*, e.nama_ekskul, j.hari, j.jam_mulai, j.jam_selesai
                    FROM pendaftaran p
                    JOIN ekskul e ON p.id_ekskul = e.id_ekskul
                    JOIN jadwal_ekskul j ON p.id_jadwal = j.id_jadwal
                    WHERE p.nisn = '$nisn'
                ");

                $jadwalBentrok = false;
                $ekskulBentrokNama = "";

                while($d = mysqli_fetch_assoc($cekDiterima)){
                    if($d['hari'] == $jadwalBaru['hari']){
                        if(($jadwalBaru['jam_mulai'] < $d['jam_selesai']) && ($jadwalBaru['jam_selesai'] > $d['jam_mulai'])){
                            $jadwalBentrok = true;
                            $ekskulBentrokNama = $d['nama_ekskul'];
                            break;
                        }
                    }
                }

                if($jadwalBentrok){
                    $error = "Jadwal bentrok! Jam pilihanmu bertabrakan dengan ekskul <b>" . htmlspecialchars($ekskulBentrokNama) . "</b> pada hari yang sama.";
                    $showForm = true;
                    $err_fields['id_jadwal'] = true;
                }else{
                    $namaFoto  = time().'_foto_'.$foto;
                    $namaSurat = time().'_surat_pernyataan_'.$surat;

                    if(move_uploaded_file($tmp_foto, "../gambar/".$namaFoto)){
                        if(move_uploaded_file($tmp_surat, "../surat_pernyataan/".$namaSurat)){
                            
                            $insert = mysqli_query($conn, "
                                INSERT INTO pendaftaran
                                (nisn, id_ekskul, id_jadwal, no_hp, foto_diri, surat_pernyataan, tanggal_daftar, status)
                                VALUES
                                ('$nisn', '$id_ekskul_clean', '$id_jadwal_clean', '".mysqli_real_escape_string($conn, $no_hp)."', '$namaFoto', '$namaSurat', CURDATE(), 'Menunggu')
                            ");

                            if($insert){
                                $_SESSION['success'] = "Pendaftaran berhasil dikirim!";
                                header("Location: dashboard_user.php");
                                exit;
                            }else{
                                $error = "Pendaftaran gagal disimpan ke database! Periksa kembali struktur tabel Anda.";
                                $showForm = true;
                            }
                        } else {
                            $error = "Gagal memindahkan berkas surat pernyataan PDF ke folder surat_pernyataan. Pastikan foldernya sudah dibuat!";
                            $showForm = true;
                            $err_fields['surat'] = true;
                        }
                    } else {
                        $error = "Gagal memindahkan file pas foto ke folder gambar.";
                        $showForm = true;
                        $err_fields['foto'] = true;
                    }
                }
            }
        }
    }
}
$isFormActive = ($showForm || $error || $success);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pendaftaran - <?= htmlspecialchars($dataEkskul['nama_ekskul']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/animation.css">
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

<div class="max-w-4xl mx-auto px-4 py-6 sm:py-10">

    <div id="sectionPersyaratan" class="<?= $isFormActive ? 'hidden' : '' ?> bg-white/90 backdrop-blur-xl rounded-[2rem] border border-gray-200/60 shadow-xl p-5 sm:p-8 animate-fadeUp">
        <div class="text-center">
            <h1 class="text-2xl sm:text-3xl font-black bg-gradient-to-r from-emerald-700 to-green-600 bg-clip-text text-transparent">
                Persyaratan Pendaftaran
            </h1>
            <p class="text-gray-500 mt-2 text-xs sm:text-sm max-w-xl mx-auto">
                Bacalah dan pahami seluruh persyaratan terlebih dahulu sebelum melakukan pendaftaran ekstrakurikuler.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
            <div class="bg-gray-50/50 rounded-2xl border border-gray-100 p-5 shadow-sm">
                <h3 class="font-extrabold text-emerald-800 text-sm sm:text-base mb-3">✅ Syarat Siswa</h3>
                <ul class="space-y-2 text-xs sm:text-sm text-gray-600">
                    <li class="flex items-center gap-2"><span>🎓</span> Siswa aktif sekolah</li>
                    <li class="flex items-center gap-2"><span>❤️</span> Memiliki minat tinggi</li>
                    <li class="flex items-center gap-2"><span>🧑‍🏫</span> Taat aturan ekskul</li>
                    <li class="flex items-center gap-2"><span>⏰</span> Hadir tepat waktu</li>
                    <li class="flex items-center gap-2"><span>🚫</span> Maksimal diterima di 3 ekskul</li>
                </ul>
            </div>

            <div class="bg-gray-50/50 rounded-2xl border border-gray-100 p-5 shadow-sm">
                <h3 class="font-extrabold text-emerald-800 text-sm sm:text-base mb-3">📂 Persiapan Dokumen</h3>
                <ul class="space-y-2 text-xs sm:text-sm text-gray-600">
                    <li class="flex items-center gap-2"><span>📸</span> Foto formal ukuran 3x4</li>
                    <li class="flex items-center gap-2"><span>🖼️</span> Format JPG / JPEG / PNG</li>
                    <li class="flex flex-col items-start gap-1">
                        <div class="flex items-center gap-2"><span>📄</span> Surat Pernyataan Orang Tua (PDF)</div>
                        <a href="https://drive.google.com/drive/folders/16w_zU3tWvTSvISbDgDTjRaioYC0pLnIt" target="_blank" class="ml-6 inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 hover:text-emerald-800 hover:underline bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 transition-colors mt-0.5">
                            📥 Unduh Template Surat Di Sini
                        </a>
                    </li>
                    <li class="flex items-center gap-2"><span>📱</span> Nomor WhatsApp aktif</li>
                </ul>
            </div>
        </div>

        <div class="mt-5 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50/60 border border-amber-100 p-4">
            <div class="flex gap-3">
                <div class="text-xl">⚠️</div>
                <div>
                    <h4 class="font-extrabold text-amber-800 text-sm">Perhatian Penting</h4>
                    <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">
                        Pastikan seluruh persyaratan sudah dibaca. Setelah menekan tombol lanjutkan, kamu dianggap memahami seluruh ketentuan pendaftaran ekstrakurikuler.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-5 bg-gray-50 p-3.5 rounded-xl border border-gray-100">
            <label class="flex items-start gap-3 cursor-pointer select-none">
                <input type="checkbox" id="setuju" class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300">
                <span class="text-xs sm:text-sm font-semibold text-gray-600 leading-tight">Saya sudah membaca, memahami, dan menyetujui seluruh persyaratan di atas.</span>
            </label>
        </div>

        <button type="button" id="btnDaftar" disabled onclick="tampilkanForm()" 
                class="mt-4 w-full bg-gray-300 text-gray-500 py-3.5 rounded-xl font-black text-sm transition-all duration-300 cursor-not-allowed shadow-sm">
            Lanjutkan Pendaftaran
        </button>
    </div>
    
    <div id="formPendaftaran" class="<?= $isFormActive ? '' : 'hidden' ?> bg-white/90 backdrop-blur-xl rounded-[2rem] border border-gray-200/60 shadow-xl p-5 sm:p-8 animate-fadeUp">
        
        <div class="flex flex-col sm:flex-row items-center gap-4 border-b border-gray-100 pb-5 mb-5">
            <div class="bg-gray-50 p-2.5 rounded-2xl border border-gray-200/60 shadow-sm">
                <img src="../gambar/logo.png" class="w-12 h-12 object-cover rounded-xl" onerror="this.src='https://via.placeholder.com/150'">
            </div>
            <div class="text-center sm:text-left">
                <h1 class="text-xl sm:text-2xl font-black bg-gradient-to-r from-emerald-800 via-emerald-600 to-green-500 bg-clip-text text-transparent">
                    Formulir Pendaftaran
                </h1>
                <p class="text-gray-400 text-xs mt-0.5">
                    Lengkapilah data diri pendaftaran ekstrakurikuler pilihanmu
                </p>
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-600 to-emerald-500 text-white p-4 sm:p-5 rounded-2xl shadow-md mb-5">
            <div class="flex justify-between items-center gap-4">
                <div class="space-y-0.5">
                    <h2 class="text-base sm:text-lg font-bold">Informasi Pendaftaran</h2>
                    <p class="text-emerald-100 text-xs leading-relaxed max-w-sm sm:max-w-md">
                        Kamu bebas mendaftar beberapa ekskul, namun batas maksimal hanya bisa <b>diterima di 3 ekstrakurikuler saja</b>.
                    </p>
                </div>
                <div class="bg-white/15 px-4 py-2.5 rounded-xl text-center min-w-[85px] backdrop-blur-sm">
                    <p class="text-[10px] text-emerald-100 font-medium uppercase tracking-wider">Diterima</p>
                    <h1 class="text-xl sm:text-2xl font-black mt-0.5"><?= $totalDaftar ?>/3</h1>
                </div>
            </div>
        </div>

        <?php if($error){ ?>
            <div class="bg-red-50 border border-red-100 text-red-700 p-3.5 rounded-xl mb-4 text-xs sm:text-sm font-medium">
                ⚠️ <?= $error ?>
            </div>
        <?php } ?>

        <?php if($success){ ?>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-800 p-3.5 rounded-xl mb-4 text-xs sm:text-sm font-medium">
                ✅ <?= $success ?>
            </div>
        <?php } ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wide">Nama Lengkap</label>
                    <input type="text" value="<?= htmlspecialchars($nama) ?>" readonly class="w-full p-3 rounded-xl bg-gray-100 border border-gray-200 text-gray-500 font-medium text-sm focus:outline-none">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wide">Kelas / Tingkat</label>
                    <input type="text" value="<?= htmlspecialchars($kelas) ?>" readonly class="w-full p-3 rounded-xl bg-gray-100 border border-gray-200 text-gray-500 font-medium text-sm focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wide">NISN Siswa</label>
                    <input type="text" value="<?= htmlspecialchars($nisn) ?>" readonly class="w-full p-3 rounded-xl bg-gray-100 border border-gray-200 text-gray-500 font-medium text-sm focus:outline-none">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wide">Ekskul Tujuan</label>
                    <input type="text" value="<?= htmlspecialchars($dataEkskul['nama_ekskul']); ?>" readonly class="w-full p-3 rounded-xl bg-gray-100 border border-gray-200 text-emerald-800 font-bold text-sm focus:outline-none">
                </div>
            </div>
            
            <div class="space-y-1">
                <label class="text-xs font-bold uppercase tracking-wide <?= $err_fields['no_hp'] ? 'text-red-500' : 'text-gray-500' ?>">
                    Nomor WhatsApp Aktif
                </label>
                <input type="text" name="no_hp" value="<?= htmlspecialchars($no_hp) ?>" required
                       class="w-full p-3 rounded-xl border text-sm font-medium transition duration-200 placeholder:text-gray-300 <?= $err_fields['no_hp'] ? 'border-red-500 bg-red-50 text-red-900 focus:ring-red-100 focus:border-red-500' : 'border-gray-200 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 text-gray-800' ?>" 
                       placeholder="Contoh: 081234567890">
            </div>

            <div class="space-y-1">
                <label class="text-xs font-bold uppercase tracking-wide <?= $err_fields['id_jadwal'] ? 'text-red-500' : 'text-gray-500' ?>">
                    Pilih Opsi Jadwal Latihan
                </label>
                <select name="id_jadwal" class="w-full p-3 rounded-xl border bg-white text-sm font-medium transition duration-200 <?= $err_fields['id_jadwal'] ? 'border-red-500 bg-red-50 text-red-900 focus:ring-red-100 focus:border-red-500' : 'border-gray-200 text-gray-700 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500' ?>" required>
                    <option value="" disabled selected>-- Pilih Salah Satu Jadwal --</option>
                    <?php
                    $jadwalQuery = mysqli_query($conn,"
                        SELECT * FROM jadwal_ekskul WHERE id_ekskul='$id_ekskul_clean'
                    ");
                    while($j = mysqli_fetch_assoc($jadwalQuery)){
                        $selected = ($id_jadwal == $j['id_jadwal']) ? 'selected' : '';
                    ?>
                        <option value="<?= $j['id_jadwal'] ?>" <?= $selected ?>>
                            <?= htmlspecialchars($j['hari']) ?>  |  Jam <?= substr($j['jam_mulai'],0,5) ?> - <?= substr($j['jam_selesai'],0,5) ?> WIB
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold uppercase tracking-wide <?= $err_fields['foto'] ? 'text-red-500' : 'text-gray-500' ?>">
                        Lampiran Pas Foto
                    </label>
                    <div class="border-2 border-dashed rounded-2xl p-4 text-center backdrop-blur-sm relative transition duration-200 min-h-[125px] flex flex-col justify-center items-center <?= $err_fields['foto'] ? 'border-red-300 bg-red-50/40 hover:bg-red-50/60' : 'border-emerald-200 bg-emerald-50/20 hover:bg-emerald-50/40' ?>">
                        <input type="file" name="foto" accept=".jpg,.jpeg,.png" required class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:text-white file:cursor-pointer cursor-pointer <?= $err_fields['foto'] ? 'file:bg-red-600 hover:file:bg-red-700' : 'file:bg-emerald-600 hover:file:bg-emerald-700' ?>">
                        <p class="text-[10px] mt-2 <?= $err_fields['foto'] ? 'text-red-400' : 'text-gray-400' ?>">Mendukung: <b>JPG, JPEG, PNG</b> (Maks 2 MB)</p>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold uppercase tracking-wide <?= $err_fields['surat'] ? 'text-red-500' : 'text-gray-500' ?>">
                        Surat Pernyataan Orang Tua
                    </label>
                    <div class="border-2 border-dashed rounded-2xl p-4 text-center backdrop-blur-sm relative transition duration-200 min-h-[125px] flex flex-col justify-center items-center <?= $err_fields['surat'] ? 'border-red-300 bg-red-50/40 hover:bg-red-50/60' : 'border-emerald-200 bg-emerald-50/20 hover:bg-emerald-50/40' ?>">
                        <input type="file" name="surat" accept=".pdf" required class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:text-white file:cursor-pointer cursor-pointer <?= $err_fields['surat'] ? 'file:bg-red-600 hover:file:bg-red-700' : 'file:bg-emerald-600 hover:file:bg-emerald-700' ?>">
                        <p class="text-[10px] mt-2 <?= $err_fields['surat'] ? 'text-red-400' : 'text-gray-400' ?>">Mendukung berkas: <b>PDF Saja</b> (Ukuran Maks 2 MB)</p>
                    </div>
                </div>
            </div>

            <div class="pt-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button type="submit" name="daftar" 
                        class="w-full bg-gradient-to-r from-emerald-600 to-emerald-500 text-white py-3.5 rounded-xl font-extrabold text-sm shadow-md hover:opacity-95 transition duration-200 active:scale-[0.99] order-1 sm:order-2">
                    Kirim Form Pendaftaran
                </button>
                <a href="dashboard_user.php" 
                   class="w-full bg-white border border-gray-200 text-emerald-700 py-3.5 rounded-xl font-extrabold text-sm shadow-sm hover:bg-gray-50 transition duration-200 text-center block active:scale-[0.99] order-2 sm:order-1">
                    ← Batalkan & Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const checkbox = document.getElementById('setuju');
const tombol = document.getElementById('btnDaftar');
const sectionPersyaratan = document.getElementById('sectionPersyaratan');

checkbox.addEventListener('change', function(){
    if(this.checked){
        tombol.disabled = false;
        tombol.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
        tombol.classList.add('bg-gradient-to-r', 'from-emerald-600', 'to-emerald-500', 'text-white', 'hover:opacity-95', 'shadow-md');
    }else{
        tombol.disabled = true;
        tombol.classList.remove('bg-gradient-to-r', 'from-emerald-600', 'to-emerald-500', 'text-white', 'hover:opacity-95', 'shadow-md');
        tombol.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
    }
});

function tampilkanForm(){
    const form = document.getElementById('formPendaftaran');
    if(sectionPersyaratan) {
        sectionPersyaratan.classList.add('hidden');
    }
    form.classList.remove('hidden');
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

<?php if($isFormActive) { ?>
    window.addEventListener('DOMContentLoaded', () => {
        const formElement = document.getElementById('formPendaftaran');
        if (formElement) {
            formElement.scrollIntoView({ behavior: 'auto', block: 'start' });
        }
    });
<?php } ?>
</script>
</body>
</html>