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

$id = intval($_GET['id']);
$queryEkskul = "SELECT * FROM ekskul WHERE id_ekskul = '$id'";
$resultEkskul = mysqli_query($conn, $queryEkskul);
$data = mysqli_fetch_assoc($resultEkskul);

if(!$data){
    header("Location: dashboard_admin.php");
    exit;
}

$guruQuery = mysqli_query($conn, "
    SELECT g.nip, g.nama_guru, e.nama_ekskul 
    FROM guru g
    LEFT JOIN ekskul e ON g.nip = e.nip_pembimbing
    ORDER BY g.nama_guru ASC
");

$jadwalQuery = mysqli_query($conn, "
    SELECT id_jadwal, hari, jam_mulai, jam_selesai 
    FROM jadwal_ekskul 
    WHERE id_ekskul = '$id'
    ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), jam_mulai ASC
");

$error = "";
$success = "";

if(isset($_POST['update'])){
    $nama           = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $nip_pembimbing = mysqli_real_escape_string($conn, trim($_POST['nip_pembimbing']));
    $deskripsi      = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $visi           = mysqli_real_escape_string($conn, trim($_POST['visi']));
    $misi           = mysqli_real_escape_string($conn, trim($_POST['misi']));
    $program_kerja  = mysqli_real_escape_string($conn, trim($_POST['program_kerja']));
    $prestasi       = mysqli_real_escape_string($conn, trim($_POST['prestasi'])); // Sekarang boleh kosong
    
    $jadwal_input = isset($_POST['jadwal']) ? $_POST['jadwal'] : [];

    if(empty($nama) || empty($deskripsi) || empty($visi) || empty($misi) || empty($program_kerja)){
        $error = "Semua kolom informasi ekskul wajib diisi! (Kecuali Prestasi Ekskul)";
    } else {
        
        $pembimbingBentrok = false;
        $pesanBentrokDetail = "";

        if(!empty($nip_pembimbing)) {
            $namaGuruQuery = mysqli_query($conn, "SELECT nama_guru FROM guru WHERE nip = '$nip_pembimbing'");
            $dataGuru = mysqli_fetch_assoc($namaGuruQuery);
            $nama_guru_terpilih = $dataGuru['nama_guru'] ?? 'Guru';

            foreach($jadwal_input as $id_jadwal_form => $j_data) {
                $hari_baru = mysqli_real_escape_string($conn, $j_data['hari']);
                $jam_mulai_baru = mysqli_real_escape_string($conn, $j_data['jam_mulai']);
                $jam_selesai_baru = mysqli_real_escape_string($conn, $j_data['jam_selesai']);

                if($jam_mulai_baru >= $jam_selesai_baru) {
                    $pembimbingBentrok = true;
                    $pesanBentrokDetail = "Jam selesai latihan harus lebih besar dari jam mulai latihan!";
                    break;
                }

                $queryCekGuru = mysqli_query($conn, "
                    SELECT e.nama_ekskul, j.hari, j.jam_mulai, j.jam_selesai 
                    FROM ekskul e
                    JOIN jadwal_ekskul j ON e.id_ekskul = j.id_ekskul
                    WHERE e.nip_pembimbing = '$nip_pembimbing' 
                    AND j.hari = '$hari_baru'
                    AND e.id_ekskul != '$id'
                ");

                while($row = mysqli_fetch_assoc($queryCekGuru)) {
                    if(($jam_mulai_baru < $row['jam_selesai']) && ($jam_selesai_baru > $row['jam_mulai'])) {
                        $pembimbingBentrok = true;
                        $jam_format = substr($row['jam_mulai'], 0, 5) . " - " . substr($row['jam_selesai'], 0, 5);
                        $pesanBentrokDetail = "Guru pembimbing <b>" . htmlspecialchars($nama_guru_terpilih) . "</b> sudah mengajar di ekskul <b>" . htmlspecialchars($row['nama_ekskul']) . "</b> pada hari <b>" . htmlspecialchars($row['hari']) . "</b> jam <b>" . $jam_format . " WIB</b>.";
                        break 2;
                    }
                }
            }
        }

        if ($pembimbingBentrok) {
            $error = "Gagal menyimpan! " . $pesanBentrokDetail;
        } else {
            $foto = $_FILES['foto']['name'];
            $namaFoto = $data['foto']; // fallback ke foto lama jika tidak ganti
            $uploadOk = true;

            if(!empty($foto)){
                $tmp = $_FILES['foto']['tmp_name'];
                $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png'];

                if(!in_array($ext, $allowed)){
                    $error = "Format foto harus JPG, JPEG, atau PNG!";
                    $uploadOk = false;
                } else {
                    $namaFoto = time().'_'.$foto;
                    move_uploaded_file($tmp, "../gambar/".$namaFoto);
                }
            }

            if($uploadOk) {
                $nip_db = empty($nip_pembimbing) ? "NULL" : "'$nip_pembimbing'";
                $updateQuery = "UPDATE ekskul SET
                                    nama_ekskul='$nama', 
                                    nip_pembimbing=$nip_db,
                                    deskripsi='$deskripsi', 
                                    visi='$visi', 
                                    misi='$misi', 
                                    program_kerja='$program_kerja',
                                    prestasi='$prestasi', 
                                    foto='$namaFoto'
                                WHERE id_ekskul='$id'";
                
                if(mysqli_query($conn, $updateQuery)){
                    
                    foreach($jadwal_input as $id_jadwal => $j_data) {
                        $hari = mysqli_real_escape_string($conn, $j_data['hari']);
                        $jam_mulai = mysqli_real_escape_string($conn, $j_data['jam_mulai']);
                        $jam_selesai = mysqli_real_escape_string($conn, $j_data['jam_selesai']);
                        
                        mysqli_query($conn, "
                            UPDATE jadwal_ekskul SET 
                                hari = '$hari', 
                                jam_mulai = '$jam_mulai', 
                                jam_selesai = '$jam_selesai' 
                            WHERE id_jadwal = '$id_jadwal' AND id_ekskul = '$id'
                        ");
                    }
                    
                    $success = "Data ekskul beserta jadwalnya berhasil diperbarui!";
                    $jadwalQuery = mysqli_query($conn, "SELECT id_jadwal, hari, jam_mulai, jam_selesai FROM jadwal_ekskul WHERE id_ekskul = '$id' ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), jam_mulai ASC");
                    $resultEkskul = mysqli_query($conn, $queryEkskul);
                    $data = mysqli_fetch_assoc($resultEkskul);
                    $guruQuery = mysqli_query($conn, "SELECT g.nip, g.nama_guru, e.nama_ekskul FROM guru g LEFT JOIN ekskul e ON g.nip = e.nip_pembimbing ORDER BY g.nama_guru ASC");
                } else {
                    $error = "Gagal memperbarui database: " . mysqli_error($conn);
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
    <title>Admin - Edit Ekskul & Jadwal Internal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); border-radius: 24px; border: 1px solid rgba(220, 220, 220, 0.7); }
        .label { font-weight: 800; color: #065f46; margin-bottom: 6px; display: block; font-size: 0.875rem; }
        .input { width: 100%; padding: 12px 14px; border-radius: 14px; border: 1px solid #d1d5db; outline: none; background: white; transition: all 0.3s; font-size: 0.875rem; }
        .input:focus { border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); }
        .btn { width: 100%; padding: 14px; border-radius: 14px; font-weight: 800; color: white; background: linear-gradient(135deg, #10b981, #059669); transition: all 0.3s; cursor: pointer; box-shadow: 0 4px 12px rgba(16,185,129,0.2); }
    </style>
</head>

<body class="py-4 sm:py-10 bg-gray-50/50 antialiased text-gray-800">
<div class="max-w-4xl mx-auto px-3 sm:px-6">
    <div class="glass p-5 sm:p-8 shadow-xl">
        
        <div class="border-b border-gray-200/60 pb-4 mb-5">
            <h2 class="text-xl sm:text-2xl font-black bg-gradient-to-r from-emerald-800 to-emerald-600 bg-clip-text text-transparent">
                Formulir Edit Ekskul & Waktu Latihan
            </h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Mengubah data informasi ekskul beserta detail 3 jam latihan internal milik ekskul ini.</p>
        </div>

        <?php if($error){ echo "<div class='bg-rose-50 text-rose-700 border border-rose-100 p-4 rounded-xl mb-5 text-xs sm:text-sm font-semibold'>⚠️ $error</div>"; } ?>
        <?php if($success){ echo "<div class='bg-emerald-50 text-emerald-700 border border-emerald-100 p-4 rounded-xl mb-5 text-xs sm:text-sm font-semibold'>✅ $success</div>"; } ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-5">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Nama Ekskul</label>
                    <input type="text" class="input" name="nama" value="<?= htmlspecialchars($data['nama_ekskul'] ?? '') ?>">
                </div>

                <div>
                    <label class="label">Guru Pembimbing</label>
                    <select class="input" name="nip_pembimbing">
                        <option value="">-- Tanpa Pembimbing --</option>
                        <?php while($guru = mysqli_fetch_assoc($guruQuery)) { 
                            $status_mengajar = !empty($guru['nama_ekskul']) ? " (Mengajar: " . htmlspecialchars($guru['nama_ekskul']) . ")" : " (Belum Ada Ekskul)";
                            $selected = ($guru['nip'] == $data['nip_pembimbing']) ? 'selected' : '';
                        ?>
                            <option value="<?= $guru['nip'] ?>" <?= $selected ?>><?= htmlspecialchars($guru['nama_guru']) . $status_mengajar ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="bg-gray-100/80 p-4 rounded-2xl border border-gray-200">
                <label class="label mb-3 text-emerald-900 font-black text-base">📅 Pengaturan Waktu Latihan Ekskul</label>
                
                <?php if(mysqli_num_rows($jadwalQuery) > 0) { ?>
                    <div class="space-y-3">
                        <?php 
                        $no = 1;
                        while($jadwal = mysqli_fetch_assoc($jadwalQuery)) { 
                            $id_j = $jadwal['id_jadwal'];
                        ?>
                            <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-3 items-center">
                                <div>
                                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Jadwal Ke-<?= $no++ ?>: Hari</span>
                                    <select name="jadwal[<?= $id_j ?>][hari]" class="w-full bg-gray-50 border p-2 rounded-lg text-sm font-semibold text-gray-700 focus:border-emerald-500 outline-none">
                                        <?php 
                                        $hari_list = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
                                        foreach($hari_list as $h) {
                                            $sel = ($jadwal['hari'] == $h) ? 'selected' : '';
                                            echo "<option value='$h' $sel>$h</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Jam Mulai</span>
                                    <input type="time" name="jadwal[<?= $id_j ?>][jam_mulai]" value="<?= substr($jadwal['jam_mulai'],0,5) ?>" class="w-full bg-gray-50 border p-2 rounded-lg text-sm font-semibold text-gray-700 focus:border-emerald-500 outline-none">
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Jam Selesai</span>
                                    <input type="time" name="jadwal[<?= $id_j ?>][jam_selesai]" value="<?= substr($jadwal['jam_selesai'],0,5) ?>" class="w-full bg-gray-50 border p-2 rounded-lg text-sm font-semibold text-gray-700 focus:border-emerald-500 outline-none">
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <p class="text-xs text-gray-400 italic">Ekskul ini belum memiliki jadwal latihan sama sekali di database.</p>
                <?php } ?>
            </div>

            <div>
                <label class="label">Deskripsi Ekskul</label>
                <textarea class="input resize-none" name="deskripsi" rows="3"><?= htmlspecialchars($data['deskripsi'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Visi</label>
                    <textarea class="input resize-none" name="visi" rows="3"><?= htmlspecialchars($data['visi'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="label">Misi</label>
                    <textarea class="input resize-none" name="misi" rows="3"><?= htmlspecialchars($data['misi'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label">Program Kerja</label>
                    <textarea class="input resize-none" name="program_kerja" rows="3"><?= htmlspecialchars($data['program_kerja'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="label">Prestasi Ekskul <span class="text-gray-400 font-normal italic">(Opsional)</span></label>
                    <textarea class="input resize-none" name="prestasi" rows="3" placeholder="Kosongkan jika belum memiliki prestasi..."><?= htmlspecialchars($data['prestasi'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end pt-1">
                <div>
                    <label class="label">Pratinjau Foto Sampul</label>
                    <?php if(!empty($data['foto'])): ?>
                        <img src="../gambar/<?= htmlspecialchars($data['foto']) ?>" class="w-full h-40 object-cover rounded-xl border border-gray-200">
                    <?php else: ?>
                        <div class="w-full h-40 bg-gray-100 border border-dashed rounded-xl flex items-center justify-center text-xs text-gray-400 font-medium">Belum ada berkas gambar terunggah</div>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="label">Ganti File Foto (Opsional)</label>
                    <input type="file" name="foto" class="input file:mr-4 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" name="update" class="btn text-sm">Simpan Seluruh Perubahan</button>
            </div>
        </form>

        <div class="text-center mt-5">
            <a href="dashboard_admin.php" class="inline-block text-xs sm:text-sm text-emerald-700 font-bold hover:underline">← Kembali ke Dashboard Utama</a>
        </div>
    </div>
</div>
</body>
</html>