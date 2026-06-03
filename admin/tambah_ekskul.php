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

$jadwalMasterQuery = mysqli_query($conn, "SELECT id_jadwal, hari, jam_mulai, jam_selesai FROM jadwal_ekskul ORDER BY jam_mulai ASC");
$listJadwal = [];
while($row = mysqli_fetch_assoc($jadwalMasterQuery)) {
    $listJadwal[] = [
        'id_jadwal'   => $row['id_jadwal'],
        'hari'        => $row['hari'],
        'jam_mulai'   => date('H:i', strtotime($row['jam_mulai'])),
        'jam_selesai' => date('H:i', strtotime($row['jam_selesai']))
    ];
}

if(isset($_POST['simpan'])){

    $nama           = mysqli_real_escape_string($conn, trim($_POST['nama_ekskul']));
    $nip_pembimbing = mysqli_real_escape_string($conn, trim($_POST['nip_pembimbing']));
    $id_jadwal      = isset($_POST['id_jadwal']) ? mysqli_real_escape_string($conn, trim($_POST['id_jadwal'])) : '';
    
    $deskripsi      = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $visi           = mysqli_real_escape_string($conn, trim($_POST['visi']));
    $misi           = mysqli_real_escape_string($conn, trim($_POST['misi']));
    $program_kerja  = mysqli_real_escape_string($conn, trim($_POST['program_kerja']));
    $prestasi       = mysqli_real_escape_string($conn, trim($_POST['prestasi']));
    
    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];

    if(
        empty($nama) || empty($nip_pembimbing) || empty($id_jadwal) || 
        empty($deskripsi) || empty($visi) || empty($misi) || 
        empty($program_kerja) || empty($prestasi) || empty($foto)
    ){
        $error = "Semua data termasuk Hari dan Jam Latihan wajib diisi!";
    } else {

        $queryJadwalPilihan = mysqli_query($conn, "SELECT hari, jam_mulai, jam_selesai FROM jadwal_ekskul WHERE id_jadwal = '$id_jadwal'");
        
        if(mysqli_num_rows($queryJadwalPilihan) > 0) {
            $jadwalPilihan = mysqli_fetch_assoc($queryJadwalPilihan);
            $hariPilihan   = $jadwalPilihan['hari'];
            $mulaiPilihan  = $jadwalPilihan['jam_mulai'];
            $selesaiPilihan = $jadwalPilihan['jam_selesai'];

            $queryCekBentrok = "
                SELECT e.nama_ekskul, j.hari, j.jam_mulai, j.jam_selesai 
                FROM ekskul e
                JOIN jadwal_ekskul j ON e.id_jadwal = j.id_jadwal
                WHERE e.nip_pembimbing = '$nip_pembimbing' 
                  AND j.hari = '$hariPilihan'
                  AND j.jam_mulai = '$mulaiPilihan'
                  AND j.jam_selesai = '$selesaiPilihan'
            ";
            
            $cekBentrok = mysqli_query($conn, $queryCekBentrok);
            
            if(mysqli_num_rows($cekBentrok) > 0) {
                $dataBentrok = mysqli_fetch_assoc($cekBentrok);
                $error = "Tidak bisa memilih jadwal ini! Pembimbing tersebut sudah mengajar di Ekskul '" . 
                         htmlspecialchars($dataBentrok['nama_ekskul']) . "' pada hari " . 
                         htmlspecialchars($dataBentrok['hari']) . " jam " .
                         date('H:i', strtotime($dataBentrok['jam_mulai'])) . " - " . 
                         date('H:i', strtotime($dataBentrok['jam_selesai'])) . ".";
            } else {

                $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png'];

                if(!in_array($ext, $allowed)){
                    $error = "Format foto harus JPG, JPEG, atau PNG!";
                } else {

                    $namaFoto = time()."_".$foto;
                    move_uploaded_file($tmp, "../gambar/".$namaFoto);
                    
                    $queryInsert = "INSERT INTO ekskul 
                                    (nama_ekskul, deskripsi, foto, visi, misi, program_kerja, prestasi, nip_pembimbing, id_jadwal) 
                                    VALUES 
                                    ('$nama', '$deskripsi', '$namaFoto', '$visi', '$misi', '$program_kerja', '$prestasi', '$nip_pembimbing', '$id_jadwal')";

                    if(mysqli_query($conn, $queryInsert)){
                        $success = "Tambah ekstrakurikuler berhasil!";
                        
                        $guruQuery = mysqli_query($conn, "
                            SELECT g.nip, g.nama_guru, GROUP_CONCAT(e.nama_ekskul SEPARATOR ', ') AS ekskul_diikuti 
                            FROM guru g
                            LEFT JOIN ekskul e ON g.nip = e.nip_pembimbing
                            GROUP BY g.nip
                            ORDER BY g.nama_guru ASC
                        ");
                    } else {
                        $error = "Gagal menyimpan ke database: " . mysqli_error($conn);
                    }
                }
            }
        } else {
            $error = "ID Jadwal yang dipilih tidak valid!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Ekskul</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/animation.css">
    <style>
        .input-style {
            width: 100%; 
            padding: 14px; 
            border-radius: 16px; 
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid #d1d5db; 
            outline: none; 
            transition: all 0.3s;
            font-size: 0.875rem; /* text-sm */
        }
        .input-style:focus {
            border-color: #10b981; 
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); 
            background: white;
        }
        .glass-container {
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(20px); 
            border: 1px solid rgba(229, 231, 235, 0.6);
        }
    </style>
</head>

<body class="text-gray-800 bg-gray-50 antialiased">
<div class="max-w-4xl mx-auto py-6 sm:py-10 px-3 sm:px-6">
    
    <div class="text-center animate-fadeUp mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-4xl font-black bg-gradient-to-r from-emerald-800 to-emerald-600 bg-clip-text text-transparent">
            Tambah Ekskul Baru
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1">Daftarkan kelompok ekstrakurikuler serta konfigurasikan detailnya.</p>
    </div>

    <div class="glass-container rounded-3xl shadow-xl p-4 sm:p-8 animate-fadeUp">

        <?php if($error){ echo "<div class='bg-rose-50 text-rose-700 border border-rose-100 p-4 rounded-xl mb-5 text-xs sm:text-sm font-semibold'>⚠️ $error</div>"; } ?>
        <?php if($success){ echo "<div class='bg-emerald-50 text-emerald-700 border border-emerald-100 p-4 rounded-xl mb-5 text-xs sm:text-sm font-semibold'>✅ $success</div>"; } ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4 sm:space-y-5">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1.5 font-bold text-xs sm:text-sm text-emerald-800">Nama Ekskul</label>
                    <input type="text" name="nama_ekskul" class="input-style" placeholder="Contoh: Pramuka, Basket" value="<?= isset($_POST['nama_ekskul']) && !$success ? htmlspecialchars($_POST['nama_ekskul']) : '' ?>">
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block mb-1.5 font-bold text-xs sm:text-sm text-emerald-800">Hari Latihan</label>
                    <select id="pilih_hari" name="hari_latihan" class="input-style" onchange="filterJamLatihan()">
                        <option value="">-- Pilih Hari --</option>
                        <?php 
                        $hariArray = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                        foreach($hariArray as $h) {
                            $selectedHari = (isset($_POST['hari_latihan']) && $_POST['hari_latihan'] == $h && !$success) ? 'selected' : '';
                            echo "<option value='$h' $selectedHari>$h</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block mb-1.5 font-bold text-xs sm:text-sm text-emerald-800">Jam Latihan</label>
                    <select id="pilih_jam" name="id_jadwal" class="input-style" disabled>
                        <option value="">-- Pilih Hari Dahulu --</option>
                    </select>
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

            <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                <div>
                    <label class="block mb-1.5 font-bold text-xs sm:text-sm text-emerald-800">Program Kerja</label>
                    <textarea name="program_kerja" rows="3" class="input-style resize-none" placeholder="Agenda rencana program kerja..."><?= isset($_POST['program_kerja']) && !$success ? htmlspecialchars($_POST['program_kerja']) : '' ?></textarea>
                </div>
            </div>

            <div>
                <label class="block mb-1.5 font-bold text-xs sm:text-sm text-emerald-800">Upload Foto Utama</label>
                <div class="border-2 border-dashed border-gray-300 rounded-2xl p-4 text-center bg-white/40">
                    <input type="file" name="foto" class="w-full text-xs sm:text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3">
                <button type="submit" name="simpan" class="w-full bg-gradient-to-r from-emerald-600 to-emerald-500 text-white py-3.5 rounded-xl font-extrabold text-sm shadow-md hover:opacity-95 transition active:scale-[0.98]">
                    Simpan Ekskul
                </button>
                <a href="dashboard_admin.php" class="w-full bg-white border border-gray-200 text-gray-700 py-3.5 rounded-xl text-center font-extrabold text-sm shadow-sm hover:bg-gray-50 transition block active:scale-[0.98]">
                    ← Kembali ke Dashboard
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const masterJadwal = <?= json_encode($listJadwal) ?>;
const idJadwalTerpilihLama = "<?= isset($_POST['id_jadwal']) ? $_POST['id_jadwal'] : '' ?>";

function filterJamLatihan() {
    const hariTerpilih = document.getElementById('pilih_hari').value;
    const selectJam = document.getElementById('pilih_jam');
    
    selectJam.innerHTML = '<option value="">-- Pilih Jam Latihan --</option>';
    
    if (hariTerpilih === "") {
        selectJam.disabled = true;
        selectJam.innerHTML = '<option value="">-- Pilih Hari Dahulu --</option>';
        return;
    }

    const jadwalSesuaiHari = masterJadwal.filter(j => j.hari === hariTerpilih);

    if (jadwalSesuaiHari.length === 0) {
        selectJam.disabled = true;
        selectJam.innerHTML = '<option value="">Tidak ada jam tersedia di hari ini</option>';
    } else {
        selectJam.disabled = false;
        const jamSudahAda = new Set();

        jadwalSesuaiHari.forEach(j => {
            const teksJam = `${j.jam_mulai} - ${j.jam_selesai}`;
            
            if (!jamSudahAda.has(teksJam)) {
                jamSudahAda.add(teksJam);
                
                const option = document.createElement('option');
                option.value = j.id_jadwal;
                option.textContent = teksJam;
                
                if(idJadwalTerpilihLama === j.id_jadwal) {
                    option.selected = true;
                }
                
                selectJam.appendChild(option);
            }
        });
    }
}

window.addEventListener('DOMContentLoaded', () => {
    if(document.getElementById('pilih_hari').value !== "") {
        filterJamLatihan();
    }
});
</script>
</body>
</html>