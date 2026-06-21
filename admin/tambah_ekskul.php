<?php
session_start();
include '../database/koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../user/dashboard_user.php");
    exit;
}

$error = "";
$success = "";
$guruQuery = mysqli_query($conn, "
    SELECT g.nip, g.nama_guru, GROUP_CONCAT(e.nama_ekskul SEPARATOR ', ') AS ekskul_diikuti 
    FROM guru g
    LEFT JOIN ekskul e ON g.nip = e.nip_pembimbing
    GROUP BY g.nip
    ORDER BY g.nama_guru ASC
");

if(isset($_POST['simpan'])){

    $nama           = mysqli_real_escape_string($conn, trim($_POST['nama_ekskul']));
    $nip_pembimbing = mysqli_real_escape_string($conn, trim($_POST['nip_pembimbing']));
    
    $deskripsi      = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $visi           = mysqli_real_escape_string($conn, trim($_POST['visi']));
    $misi           = mysqli_real_escape_string($conn, trim($_POST['misi']));
    $program_kerja  = mysqli_real_escape_string($conn, trim($_POST['program_kerja']));
    
    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];
    $jadwal_inputs = isset($_POST['jadwal']) ? $_POST['jadwal'] : [];

    if(
        empty($nama) || empty($deskripsi) || empty($visi) || 
        empty($misi) || empty($program_kerja) || empty($foto)
    ){
        $error = "Semua data informasi ekskul dan foto wajib diisi!";
    } else {
        
        $jadwal_valid = true;
        foreach($jadwal_inputs as $index => $j) {
            if(empty($j['hari']) || empty($j['jam_mulai']) || empty($j['jam_selesai'])) {
                $jadwal_valid = false;
                break;
            }
        }

        if(!$jadwal_valid) {
            $error = "Seluruh baris input untuk 3 jadwal wajib diisi lengkap (Hari, Jam Mulai, Jam Selesai)!";
        } else {
            
            $bentrok = false;
            if(!empty($nip_pembimbing)) {
                foreach($jadwal_inputs as $j) {
                    $hari_cek = mysqli_real_escape_string($conn, $j['hari']);
                    $mulai_cek = mysqli_real_escape_string($conn, $j['jam_mulai']);
                    $selesai_cek = mysqli_real_escape_string($conn, $j['jam_selesai']);

                    $queryCekBentrok = "
                        SELECT e.nama_ekskul, j.hari, j.jam_mulai, j.jam_selesai 
                        FROM jadwal_ekskul j
                        JOIN ekskul e ON j.id_ekskul = e.id_ekskul
                        WHERE e.nip_pembimbing = '$nip_pembimbing' 
                          AND j.hari = '$hari_cek'
                          AND j.jam_mulai = '$mulai_cek'
                          AND j.jam_selesai = '$selesai_cek'
                    ";
                    $cekBentrok = mysqli_query($conn, $queryCekBentrok);
                    
                    if(mysqli_num_rows($cekBentrok) > 0) {
                        $dataBentrok = mysqli_fetch_assoc($cekBentrok);
                        $error = "Gagal! Pembimbing tersebut bentrok dengan jadwal Ekskul '" . 
                                 htmlspecialchars($dataBentrok['nama_ekskul']) . "' pada hari " . 
                                 htmlspecialchars($dataBentrok['hari']) . " jam " . 
                                 substr($dataBentrok['jam_mulai'], 0, 5) . " - " . substr($dataBentrok['jam_selesai'], 0, 5) . ".";
                        $bentrok = true;
                        break;
                    }
                }
            }

            if(!$bentrok) {
                $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png'];

                if(!in_array($ext, $allowed)){
                    $error = "Format foto harus JPG, JPEG, atau PNG!";
                } else {
                    $namaFoto = time()."_".$foto;
                    if(move_uploaded_file($tmp, "../gambar/".$namaFoto)) {
                        
                        $nip_db = empty($nip_pembimbing) ? "NULL" : "'$nip_pembimbing'";
                        $queryInsertEkskul = "INSERT INTO ekskul 
                            (nama_ekskul, deskripsi, foto, visi, misi, program_kerja, nip_pembimbing, id_jadwal) 
                            VALUES 
                            ('$nama', '$deskripsi', '$namaFoto', '$visi', '$misi', '$program_kerja', $nip_db, NULL)";

                        if(mysqli_query($conn, $queryInsertEkskul)){

                            $new_id_ekskul = mysqli_insert_id($conn);
                            foreach($jadwal_inputs as $j) {
                                $hari        = mysqli_real_escape_string($conn, $j['hari']);
                                $jam_mulai   = mysqli_real_escape_string($conn, $j['jam_mulai']);
                                $jam_selesai = mysqli_real_escape_string($conn, $j['jam_selesai']);

                                mysqli_query($conn, "INSERT INTO jadwal_ekskul 
                                    (id_ekskul, hari, jam_mulai, jam_selesai) 
                                    VALUES 
                                    ('$new_id_ekskul', '$hari', '$jam_mulai', '$jam_selesai')");
                            }

                            $success = "Tambah ekstrakurikuler baru beserta 3 jadwal latihan berhasil disave!";
                            $guruQuery = mysqli_query($conn, "
                                SELECT g.nip, g.nama_guru, GROUP_CONCAT(e.nama_ekskul SEPARATOR ', ') AS ekskul_diikuti 
                                FROM guru g
                                LEFT JOIN ekskul e ON g.nip = e.nip_pembimbing
                                GROUP BY g.nip
                                ORDER BY g.nama_guru ASC
                            ");
                        } else {
                            $error = "Gagal menyimpan data ekskul: " . mysqli_error($conn);
                        }
                    } else {
                        $error = "Gagal mengunggah berkas gambar ke server.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Ekskul - Multi Jadwal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .input-style { width: 100%; padding: 14px; border-radius: 16px; background: rgba(255, 255, 255, 0.8); border: 1px solid #d1d5db; outline: none; transition: all 0.3s; font-size: 0.875rem; }
        .input-style:focus { border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); background: white; }
        .glass-container { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); border: 1px solid rgba(229, 231, 235, 0.6); }
    </style>
</head>

<body class="text-gray-800 bg-gray-50 antialiased">
<div class="max-w-4xl mx-auto py-6 sm:py-10 px-3 sm:px-6">
    
    <div class="text-center mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-4xl font-black bg-gradient-to-r from-emerald-800 to-emerald-600 bg-clip-text text-transparent">
            Tambah Ekskul Baru
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1">Daftarkan ekstrakurikuler baru serta inputkan langsung 3 jadwal latihan aktif.</p>
    </div>

    <div class="glass-container rounded-3xl shadow-xl p-4 sm:p-8">

        <?php if($error){ echo "<div class='bg-rose-50 text-rose-700 border border-rose-100 p-4 rounded-xl mb-5 text-xs sm:text-sm font-semibold'>⚠️ $error</div>"; } ?>
        <?php if($success){ echo "<div class='bg-emerald-50 text-emerald-700 border border-emerald-100 p-4 rounded-xl mb-5 text-xs sm:text-sm font-semibold'>✅ $success</div>"; } ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4 sm:space-y-5">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1.5 font-bold text-xs sm:text-sm text-emerald-800">Nama Ekskul</label>
                    <input type="text" name="nama_ekskul" class="input-style" placeholder="Contoh: Futsal, Musik" value="<?= isset($_POST['nama_ekskul']) && !$success ? htmlspecialchars($_POST['nama_ekskul']) : '' ?>">
                </div>

                <div>
                    <label class="block mb-1.5 font-bold text-xs sm:text-sm text-emerald-800">Guru Pembimbing</label>
                    <select name="nip_pembimbing" class="input-style">
                        <option value="">-- Pilih Guru Pembimbing --</option>
                        <?php 
                        mysqli_data_seek($guruQuery, 0);
                        while($guru = mysqli_fetch_assoc($guruQuery)) { 
                        ?>
                            <option value="<?= $guru['nip'] ?>" <?= (isset($_POST['nip_pembimbing']) && $_POST['nip_pembimbing'] == $guru['nip'] && !$success) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($guru['nama_guru']) ?> 
                                <?php if(!empty($guru['ekskul_diikuti'])) { ?> (Mengajar: <?= htmlspecialchars($guru['ekskul_diikuti']) ?>) <?php } ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="bg-gray-100/70 p-4 rounded-2xl border border-gray-200">
                <label class="block mb-3 text-sm font-black text-emerald-900 uppercase tracking-wide">📅 Input Kontrak 3 Jadwal Latihan Ekskul</label>
                
                <div class="space-y-3">
                    <?php for($i = 1; $i <= 3; $i++) { ?>
                        <div class="bg-white p-3 rounded-xl border border-gray-200/80 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
                            
                            <div>
                                <span class="block text-[11px] font-bold text-gray-400 uppercase mb-1">Jadwal Ke-<?= $i ?>: Hari</span>
                                <select name="jadwal[<?= $i ?>][hari]" class="w-full bg-gray-50 border p-2.5 rounded-lg text-sm font-semibold text-gray-700 outline-none focus:border-emerald-500">
                                    <option value="">-- Pilih Hari --</option>
                                    <?php 
                                    $hariArray = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                    foreach($hariArray as $h) {
                                        $selected = (isset($_POST['jadwal'][$i]['hari']) && $_POST['jadwal'][$i]['hari'] == $h && !$success) ? 'selected' : '';
                                        echo "<option value='$h' $selected>$h</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <span class="block text-[11px] font-bold text-gray-400 uppercase mb-1">Jam Mulai</span>
                                <input type="time" name="jadwal[<?= $i ?>][jam_mulai]" value="<?= isset($_POST['jadwal'][$i]['jam_mulai']) && !$success ? htmlspecialchars($_POST['jadwal'][$i]['jam_mulai']) : '' ?>" class="w-full bg-gray-50 border p-2.5 rounded-lg text-sm font-semibold text-gray-700 outline-none focus:border-emerald-500">
                            </div>

                            <div>
                                <span class="block text-[11px] font-bold text-gray-400 uppercase mb-1">Jam Selesai</span>
                                <input type="time" name="jadwal[<?= $i ?>][jam_selesai]" value="<?= isset($_POST['jadwal'][$i]['jam_selesai']) && !$success ? htmlspecialchars($_POST['jadwal'][$i]['jam_selesai']) : '' ?>" class="w-full bg-gray-50 border p-2.5 rounded-lg text-sm font-semibold text-gray-700 outline-none focus:border-emerald-500">
                            </div>

                        </div>
                    <?php } ?>
                </div>
            </div>

            <div>
                <label class="block mb-1.5 font-bold text-xs sm:text-sm text-emerald-800">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="input-style resize-none" placeholder="Gambarkan garis besar kelompok ekstrakurikuler ini..."><?= isset($_POST['deskripsi']) && !$success ? htmlspecialchars($_POST['deskripsi']) : '' ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1.5 font-bold text-xs sm:text-sm text-emerald-800">Visi</label>
                    <textarea name="visi" rows="3" class="input-style resize-none" placeholder="Visi ekskul..."><?= isset($_POST['visi']) && !$success ? htmlspecialchars($_POST['visi']) : '' ?></textarea>
                </div>
                <div>
                    <label class="block mb-1.5 font-bold text-xs sm:text-sm text-emerald-800">Misi</label>
                    <textarea name="misi" rows="3" class="input-style resize-none" placeholder="Misi ekskul..."><?= isset($_POST['misi']) && !$success ? htmlspecialchars($_POST['misi']) : '' ?></textarea>
                </div>
            </div>

            <div>
                <label class="block mb-1.5 font-bold text-xs sm:text-sm text-emerald-800">Program Kerja</label>
                <textarea name="program_kerja" rows="3" class="input-style resize-none" placeholder="Agenda rencana program kerja..."><?= isset($_POST['program_kerja']) && !$success ? htmlspecialchars($_POST['program_kerja']) : '' ?></textarea>
            </div>

            <div>
                <label class="block mb-1.5 font-bold text-xs sm:text-sm text-emerald-800">Upload Foto Utama</label>
                <div class="border-2 border-dashed border-gray-300 rounded-2xl p-4 text-center bg-white/40">
                    <input type="file" name="foto" class="w-full text-xs sm:text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3">
                <button type="submit" name="simpan" class="w-full bg-gradient-to-r from-emerald-600 to-emerald-500 text-white py-3.5 rounded-xl font-extrabold text-sm shadow-md hover:opacity-95 transition">
                    Simpan Ekskul & Jadwal
                </button>
                <a href="dashboard_admin.php" class="w-full bg-white border border-gray-200 text-gray-700 py-3.5 rounded-xl text-center font-extrabold text-sm shadow-sm hover:bg-gray-50 transition block">
                    ← Kembali ke Dashboard
                </a>
            </div>
        </form>
    </div>
</div>
</body>
</html>