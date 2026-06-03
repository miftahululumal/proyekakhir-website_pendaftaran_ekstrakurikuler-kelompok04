<?php
session_start();
include '../database/koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../user/dashboard_user.php");
    exit;
}

if(!isset($_GET['id'])){
    header("Location: dashboard_admin.php");
    exit;
}

$id = $_GET['id'];

$queryEkskul = "
    SELECT e.*, j.hari, j.jam_mulai, j.jam_selesai 
    FROM ekskul e 
    LEFT JOIN jadwal_ekskul j ON e.id_jadwal = j.id_jadwal 
    WHERE e.id_ekskul = '$id'";
$resultEkskul = mysqli_query($conn, $queryEkskul);
$data = mysqli_fetch_assoc($resultEkskul);

if(!$data){
    header("Location: dashboard_admin.php");
    exit;
}

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

$error = "";
$success = "";

if(isset($_POST['update'])){
    $nama           = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $nip_pembimbing = mysqli_real_escape_string($conn, trim($_POST['nip_pembimbing']));
    $id_jadwal      = isset($_POST['id_jadwal']) ? mysqli_real_escape_string($conn, trim($_POST['id_jadwal'])) : '';
    
    $deskripsi      = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $visi           = mysqli_real_escape_string($conn, trim($_POST['visi']));
    $misi           = mysqli_real_escape_string($conn, trim($_POST['misi']));
    $program_kerja  = mysqli_real_escape_string($conn, trim($_POST['program_kerja']));
    $prestasi       = mysqli_real_escape_string($conn, trim($_POST['prestasi']));

    $foto = $_FILES['foto']['name'];

    if(
        empty($nama) || empty($nip_pembimbing) || empty($id_jadwal) || 
        empty($deskripsi) || empty($visi) || empty($misi) || empty($program_kerja) || empty($prestasi)
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
                  AND e.id_ekskul != '$id'
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
                if(!empty($foto)){
                    $tmp = $_FILES['foto']['tmp_name'];
                    $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
                    $allowed = ['jpg','jpeg','png'];

                    if(!in_array($ext, $allowed)){
                        $error = "Format foto harus JPG, JPEG, atau PNG!";
                    } else {
                        $namaFoto = time().'_'.$foto;
                        move_uploaded_file($tmp, "../gambar/".$namaFoto);

                        $updateQuery = "UPDATE ekskul SET
                                            nama_ekskul='$nama', nip_pembimbing='$nip_pembimbing', id_jadwal='$id_jadwal',
                                            deskripsi='$deskripsi', visi='$visi', misi='$misi', program_kerja='$program_kerja',
                                            prestasi='$prestasi', foto='$namaFoto'
                                        WHERE id_ekskul='$id'";
                        
                        if(mysqli_query($conn, $updateQuery)){ $success = "Ekskul berhasil diupdate!"; } 
                        else { $error = "Gagal mengupdate database: " . mysqli_error($conn); }
                    }
                } else {
                    $updateQuery = "UPDATE ekskul SET
                                        nama_ekskul='$nama', nip_pembimbing='$nip_pembimbing', id_jadwal='$id_jadwal',
                                        deskripsi='$deskripsi', visi='$visi', misi='$misi', program_kerja='$program_kerja',
                                        prestasi='$prestasi'
                                    WHERE id_ekskul='$id'";

                    if(mysqli_query($conn, $updateQuery)){ $success = "Ekskul berhasil diupdate!"; } 
                    else { $error = "Gagal mengupdate database: " . mysqli_error($conn); }
                }

                $resultEkskul = mysqli_query($conn, $queryEkskul);
                $data = mysqli_fetch_assoc($resultEkskul);
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
    <title>Edit Ekskul</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/animation.css">
    <style>
        .glass { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(20px); 
            border-radius: 24px; 
            border: 1px solid rgba(230, 230, 230, 0.6); 
        }
        .label { 
            font-weight: 800; 
            color: #065f46; 
            margin-bottom: 6px; 
            display: block; 
            font-size: 0.875rem; /* text-sm */
        }
        .input { 
            width: 100%; 
            padding: 12px 14px; 
            border-radius: 14px; 
            border: 1px solid #d1d5db; 
            outline: none; 
            background: white; 
            transition: all 0.3s; 
            font-size: 0.875rem;
        }
        .input:focus { 
            border-color: #10b981; 
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); 
        }
        .input-disabled { 
            background-color: #f3f4f6; 
            color: #4b5563; 
            font-weight: 600; 
            cursor: not-allowed; 
            border: 1px solid #e5e7eb; 
        }
        .btn { 
            width: 100%; 
            padding: 14px; 
            border-radius: 14px; 
            font-weight: 800; 
            color: white; 
            background: linear-gradient(135deg, #10b981, #059669); 
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }
        .btn:active {
            transform: scale(0.98);
        }
    </style>
</head>

<body class="py-4 sm:py-10 bg-gray-50 antialiased text-gray-800">
<div class="max-w-4xl mx-auto px-3 sm:px-6">
    <div class="glass p-5 sm:p-8 shadow-xl animate-fadeUp">
        
        <div class="border-b border-gray-200/60 pb-4 mb-5">
            <h2 class="text-xl sm:text-2xl font-black bg-gradient-to-r from-emerald-800 to-emerald-600 bg-clip-text text-transparent">
                Formulir Perubahan Data Ekskul
            </h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Sesuaikan info, jadwal, beserta guru pembina kelompok ekstrakurikuler.</p>
        </div>

        <?php if($error){ echo "<div class='bg-rose-50 text-rose-700 border border-rose-100 p-4 rounded-xl mb-5 text-xs sm:text-sm font-semibold'>⚠️ $error</div>"; } ?>
        <?php if($success){ echo "<div class='bg-emerald-50 text-emerald-700 border border-emerald-100 p-4 rounded-xl mb-5 text-xs sm:text-sm font-semibold'>✅ $success</div>"; } ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4 sm:space-y-5">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Nama Ekskul</label>
                    <input type="text" class="input" name="nama" value="<?= htmlspecialchars(isset($_POST['nama']) ? $_POST['nama'] : ($data['nama_ekskul'] ?? '')) ?>" placeholder="Masukkan nama ekskul...">
                </div>

                <div>
                    <label class="label">Jadwal Latihan Saat Ini</label>
                    <?php 
                        $jam_mulai_asli = isset($data['jam_mulai']) ? date('H:i', strtotime($data['jam_mulai'])) : '';
                        $jam_selesai_asli = isset($data['jam_selesai']) ? date('H:i', strtotime($data['jam_selesai'])) : '';
                        $teksJadwalAktif = !empty($data['hari']) ? "{$data['hari']} ({$jam_mulai_asli} - {$jam_selesai_asli})" : "Belum ada jadwal";
                    ?>
                    <input type="text" class="input input-disabled" value="<?= htmlspecialchars($teksJadwalAktif) ?>" readonly>
                </div>
            </div>

            <div>
                <label class="label">Guru Pembimbing</label>
                <select class="input" name="nip_pembimbing">
                    <option value="">-- Pilih Guru Pembimbing --</option>
                    <?php 
                    mysqli_data_seek($guruQuery, 0);
                    while($guru = mysqli_fetch_assoc($guruQuery)) { 
                        $selectedNip = isset($_POST['nip_pembimbing']) ? $_POST['nip_pembimbing'] : ($data['nip_pembimbing'] ?? '');
                    ?>
                        <option value="<?= $guru['nip'] ?>" <?= ($guru['nip'] == $selectedNip) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($guru['nama_guru']) ?>
                            <?php if(!empty($guru['ekskul_diikuti'])) { ?> (Mengajar: <?= htmlspecialchars($guru['ekskul_diikuti']) ?>) <?php } ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Ubah Hari Latihan</label>
                    <select id="pilih_hari" name="hari_latihan" class="input" onchange="filterJamLatihan()">
                        <option value="">-- Pilih Hari --</option>
                        <?php 
                        $hariArray = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                        $hariDefault = isset($_POST['hari_latihan']) ? $_POST['hari_latihan'] : ($data['hari'] ?? '');
                        foreach($hariArray as $h) {
                            $selectedHari = ($hariDefault == $h) ? 'selected' : '';
                            echo "<option value='$h' $selectedHari>$h</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="label">Ubah Jam Latihan</label>
                    <select id="pilih_jam" name="id_jadwal" class="input" disabled>
                        <option value="">-- Pilih Hari Dahulu --</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="label">Deskripsi Ekskul</label>
                <textarea class="input resize-none" name="deskripsi" rows="3" placeholder="Gambarkan kelompok ekskul ini..."><?= htmlspecialchars(isset($_POST['deskripsi']) ? $_POST['deskripsi'] : ($data['deskripsi'] ?? '')) ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Visi</label>
                    <textarea class="input resize-none" name="visi" rows="3" placeholder="Visi ekskul..."><?= htmlspecialchars(isset($_POST['visi']) ? $_POST['visi'] : ($data['visi'] ?? '')) ?></textarea>
                </div>
                <div>
                    <label class="label">Misi</label>
                    <textarea class="input resize-none" name="misi" rows="3" placeholder="Misi ekskul..."><?= htmlspecialchars(isset($_POST['misi']) ? $_POST['misi'] : ($data['misi'] ?? '')) ?></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Program Kerja</label>
                    <textarea class="input resize-none" name="program_kerja" rows="3" placeholder="Rencana program kerja..."><?= htmlspecialchars(isset($_POST['program_kerja']) ? $_POST['program_kerja'] : ($data['program_kerja'] ?? '')) ?></textarea>
                </div>
                <div>
                    <label class="label">Prestasi Ekskul</label>
                    <textarea class="input resize-none" name="prestasi" rows="3" placeholder="Daftar kejuaraan/prestasi..."><?= htmlspecialchars(isset($_POST['prestasi']) ? $_POST['prestasi'] : ($data['prestasi'] ?? '')) ?></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end pt-2">
                <div>
                    <label class="label">Foto Saat Ini</label>
                    <?php if(!empty($data['foto'])): ?>
                        <img src="../gambar/<?= $data['foto'] ?>" class="w-full h-40 md:h-48 object-cover rounded-xl shadow-sm border border-gray-200">
                    <?php else: ?>
                        <div class="w-full h-40 bg-gray-100 border border-dashed border-gray-300 rounded-xl flex items-center justify-center text-xs text-gray-400 font-medium">Belum ada foto terupload</div>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="label">Ganti Foto (Opsional)</label>
                    <input type="file" name="foto" class="input file:mr-4 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>
            </div>

            <div class="pt-3">
                <button type="submit" name="update" class="btn text-sm">Update Ekskul</button>
            </div>
        </form>

        <div class="text-center mt-5">
            <a href="dashboard_admin.php" class="inline-block text-xs sm:text-sm text-emerald-700 font-bold hover:underline transition-all">← Kembali ke Dashboard</a>
        </div>
    </div>
</div>

<script>
const masterJadwal = <?= json_encode($listJadwal) ?>;
const idJadwalTerpilihLama = "<?= isset($_POST['id_jadwal']) ? $_POST['id_jadwal'] : ($data['id_jadwal'] ?? '') ?>";

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
                
                if(idJadwalTerpilihLama == j.id_jadwal) {
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