<?php
include '../register/cek_login.php';
include '../database/koneksi.php';

if (isset($_GET['baca']) && $_GET['baca'] == 1) {
    $_SESSION['last_read_admin'] = date('Y-m-d H:i:s');
    header("Location: data_pendaftaran.php");
    exit;
}

if($_SESSION['role'] != 'admin'){
    header("Location: ../user/dashboard_user.php");
    exit;
}

if(isset($_POST['submit_tolak'])){
    $id = $_POST['id_pendaftaran'];
    $alasan = trim($_POST['alasan']);

    mysqli_query($conn,"
        UPDATE pendaftaran
        SET
            status='Ditolak',
            alasan_ditolak='$alasan'
        WHERE id_pendaftaran='$id'
    ");

    header("Location: data_pendaftaran.php");
    exit;
}

if(isset($_POST['submit_keluarkan'])){
    $id = $_POST['id_pendaftaran'];
    $alasan = trim($_POST['alasan_keluar']);

    mysqli_query($conn,"
        UPDATE pendaftaran
        SET
            status='Dikeluarkan',
            alasan_dikeluarkan='$alasan'
        WHERE id_pendaftaran='$id'
    ");

    header("Location: data_pendaftaran.php");
    exit;
}

if(isset($_GET['aksi']) && $_GET['aksi'] == "terima"){
    $id = $_GET['id'];

    $getData = mysqli_query($conn,"
        SELECT *
        FROM pendaftaran
        WHERE id_pendaftaran='$id'
    ");

    $dataPendaftaran = mysqli_fetch_assoc($getData);
    $nisn = $dataPendaftaran['nisn'];

    $cekDiterima = mysqli_query($conn,"
        SELECT *
        FROM pendaftaran
        WHERE nisn='$nisn'
        AND status='Diterima'
    ");

    $totalDiterima = mysqli_num_rows($cekDiterima);

    if($totalDiterima >= 3){
        mysqli_query($conn,"
            UPDATE pendaftaran
            SET status='Sudah Max'
            WHERE id_pendaftaran='$id'
        ");
    } else {
        mysqli_query($conn,"
            UPDATE pendaftaran
            SET status='Diterima'
            WHERE id_pendaftaran='$id'
        ");

        $cekUlang = mysqli_query($conn,"
            SELECT *
            FROM pendaftaran
            WHERE nisn='$nisn'
            AND status='Diterima'
        ");

        $totalBaru = mysqli_num_rows($cekUlang);

        if($totalBaru >= 3){
            mysqli_query($conn,"
                UPDATE pendaftaran
                SET status='Sudah Max'
                WHERE nisn='$nisn'
                AND status='Menunggu'
            ");
        }
    }

    header("Location: data_pendaftaran.php");
    exit;
}

if(isset($_GET['aksi']) && $_GET['aksi'] == "hapus"){
    $id = $_GET['id'];
    mysqli_query($conn,"
        DELETE FROM pendaftaran
        WHERE id_pendaftaran='$id'
    ");
    header("Location: data_pendaftaran.php");
    exit;
}

$search = $_GET['search'] ?? '';
$kelas  = $_GET['kelas'] ?? '';

$where = "WHERE 1=1";

if($search != ''){
    $where .= " AND (
        s.nisn LIKE '%$search%'
        OR s.nama LIKE '%$search%'
    )";
}

if($kelas != ''){
    $where .= " AND s.kelas='$kelas'";
}

$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if($halaman < 1){ $halaman = 1; }

$mulai = ($halaman - 1) * $batas;

$totalDataQuery = mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM pendaftaran p
    JOIN siswa s ON p.nisn = s.nisn
    JOIN ekskul e ON p.id_ekskul = e.id_ekskul
    LEFT JOIN jadwal_ekskul j ON p.id_jadwal = j.id_jadwal
    $where
");

$totalData = mysqli_fetch_assoc($totalDataQuery)['total'];
$totalHalaman = ceil($totalData / $batas);

$data = mysqli_query($conn,"
    SELECT
        p.id_pendaftaran, p.no_hp, p.foto_diri, p.status, p.alasan_ditolak, p.alasan_dikeluarkan,
        s.nisn, s.nama, s.kelas,
        e.nama_ekskul,
        g.nama_guru AS pembimbing,
        j.hari, j.jam_mulai, j.jam_selesai
    FROM pendaftaran p
    JOIN siswa s ON p.nisn = s.nisn
    JOIN ekskul e ON p.id_ekskul = e.id_ekskul
    LEFT JOIN guru g ON e.nip_pembimbing = g.nip
    LEFT JOIN jadwal_ekskul j ON p.id_jadwal = j.id_jadwal
    $where
    ORDER BY p.id_pendaftaran DESC
    LIMIT $mulai, $batas
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pendaftaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/animation.css">
</head>
<body class="bg-gray-50 min-h-screen antialiased text-gray-800">

<div class="max-w-[1600px] mx-auto px-4 py-6 sm:p-8">
    
    <div class="animate-fadeUp bg-white/70 backdrop-blur-xl p-5 sm:p-6 rounded-[24px] border border-gray-200/50 shadow-md">
        <div class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
            <div class="bg-emerald-50 p-3 rounded-2xl border border-emerald-100 shadow-inner shrink-0">
                <img src="../gambar/logo.png" class="w-16 h-16 sm:w-20 sm:h-20 object-contain rounded-xl" alt="Logo">
            </div>
            <div>
                <h1 class="text-2xl sm:text-4xl font-black bg-gradient-to-r from-emerald-800 via-emerald-600 to-green-500 bg-clip-text text-transparent">
                    Data Pendaftaran Siswa
                </h1>
                <p class="text-sm sm:text-base text-gray-500 mt-1">
                    Kelola seluruh data pendaftaran ekstrakurikuler siswa dengan mudah.
                </p>
            </div>
        </div>
    </div>

    <form method="GET" class="mt-6 bg-white border border-gray-200 rounded-[20px] shadow-sm p-4 flex flex-col lg:flex-row gap-3 items-center animate-fadeUp">
        <div class="w-full lg:flex-1">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari berdasarkan NISN atau Nama..." class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white text-sm transition-all">
        </div>
        <div class="w-full lg:w-48">
            <select name="kelas" class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white text-sm transition-all">
                <option value="">Semua Kelas</option>
                <option value="10" <?= $kelas=="10" ? 'selected' : '' ?>>Kelas 10</option>
                <option value="11" <?= $kelas=="11" ? 'selected' : '' ?>>Kelas 11</option>
                <option value="12" <?= $kelas=="12" ? 'selected' : '' ?>>Kelas 12</option>
            </select>
        </div>
        <div class="w-full lg:w-auto flex gap-2">
            <button class="w-full lg:w-auto flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl font-bold text-sm shadow transition-all active:scale-95">Cari</button>
            <a href="data_pendaftaran.php" class="w-full lg:w-auto flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-bold text-sm shadow transition-all active:scale-95 text-center">Reset</a>
        </div>
    </form>

    <div class="mt-6 animate-fadeUp">
        
        <?php 
        $rowsData = [];
        while($r = mysqli_fetch_assoc($data)){
            $rowsData[] = $r;
        }
        ?>

        <div class="hidden md:block bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-700 font-bold">
                            <th class="p-4 text-center">Foto</th>
                            <th class="p-4">NISN</th>
                            <th class="p-4">Nama</th>
                            <th class="p-4 text-center">Kelas</th>
                            <th class="p-4">Ekskul</th>
                            <th class="p-4">No HP</th>
                            <th class="p-4">Jadwal</th>
                            <th class="p-4">Pembimbing</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <?php if(count($rowsData) == 0): ?>
                        <tr><td colspan="10" class="p-8 text-center text-gray-400 font-medium">Tidak ada data pendaftaran ditemukan.</td></tr>
                    <?php endif; ?>
                    <?php foreach($rowsData as $row){ ?>
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="p-4 text-center">
                                <?php if(!empty($row['foto_diri'])){ ?>
                                    <img src="../gambar/<?= $row['foto_diri'] ?>" class="w-12 h-12 object-cover rounded-xl mx-auto shadow-sm border border-gray-200">
                                <?php } else { echo "-"; } ?>
                            </td>
                            <td class="p-4 font-bold text-gray-600"><?= $row['nisn'] ?></td>
                            <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($row['nama']) ?></td>
                            <td class="p-4 text-center"><span class="px-2.5 py-1 bg-gray-100 rounded-md text-xs font-semibold text-gray-600">Kls <?= $row['kelas'] ?></span></td>
                            <td class="p-4 font-bold text-emerald-700"><?= htmlspecialchars($row['nama_ekskul']) ?></td>
                            <td class="p-4 text-gray-600"><?= htmlspecialchars($row['no_hp']) ?></td>
                            <td class="p-4">
                                <?php if(!empty($row['hari'])){ ?>
                                    <span class="block text-xs font-medium text-gray-700 bg-emerald-50 border border-emerald-100 rounded-md px-2 py-1 w-max">
                                        <?= htmlspecialchars($row['hari']) ?> (<?= substr($row['jam_mulai'],0,5) ?> - <?= substr($row['jam_selesai'],0,5) ?>)
                                    </span>
                                <?php } else { echo "<span class='text-gray-400'>-</span>"; } ?>
                            </td>
                            <td class="p-4 text-gray-600 text-xs font-semibold"><?= htmlspecialchars($row['pembimbing'] ?? 'Belum Ada Pembimbing') ?></td>
                            <td class="p-4 text-center">
                                <?php if($row['status']=="Menunggu"){ ?>
                                    <span class="bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1 rounded-full text-xs font-bold">Menunggu</span>
                                <?php } elseif($row['status']=="Diterima"){ ?>
                                    <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-full text-xs font-bold">Diterima</span>
                                <?php } elseif($row['status']=="Ditolak"){ ?>
                                    <span class="bg-rose-50 text-rose-700 border border-rose-200 px-3 py-1 rounded-full text-xs font-bold">Ditolak</span>
                                <?php } elseif($row['status']=="Dikeluarkan"){ ?>
                                    <span class="bg-gray-800 text-white px-3 py-1 rounded-full text-xs font-bold">Dikeluarkan</span>
                                <?php } elseif($row['status']=="Sudah Max"){ ?>
                                    <span class="bg-purple-50 text-purple-700 border border-purple-200 px-3 py-1 rounded-full text-xs font-bold">Sudah Max</span>
                                <?php } ?>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center gap-1.5">
                                    <?php if($row['status'] == "Menunggu"){ ?>
                                        <a href="?aksi=terima&id=<?= $row['id_pendaftaran'] ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">Terima</a>
                                        <button onclick="openTolak(<?= $row['id_pendaftaran'] ?>)" class="bg-rose-500 hover:bg-rose-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">Tolak</button>
                                    <?php } elseif($row['status'] == "Diterima"){ ?>
                                        <button onclick="openKeluarkan(<?= $row['id_pendaftaran'] ?>)" class="bg-gray-900 hover:bg-gray-800 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">Keluarkan</button>
                                    <?php } else { ?>
                                        <a href="?aksi=hapus&id=<?= $row['id_pendaftaran'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="bg-rose-100 hover:bg-rose-200 text-rose-700 px-3 py-1.5 rounded-lg text-xs font-bold transition">Hapus</a>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="block md:hidden">
            <div class="grid grid-cols-2 gap-3">
                <?php if(count($rowsData) == 0): ?>
                    <div class="col-span-2 bg-white p-8 rounded-2xl text-center text-gray-400 border border-gray-200 text-sm">
                        Tidak ada data pendaftaran ditemukan.
                    </div>
                <?php endif; ?>
                
                <?php foreach($rowsData as $row){ ?>
                    <div class="bg-white border border-gray-200 rounded-2xl p-3 shadow-sm flex flex-col justify-between space-y-2.5">
                        <div class="space-y-2">
                            <div class="flex items-start justify-between gap-1">
                                <?php if(!empty($row['foto_diri'])): ?>
                                    <img src="../gambar/<?= $row['foto_diri'] ?>" class="w-9 h-9 object-cover rounded-lg border border-gray-100 shrink-0">
                                <?php else: ?>
                                    <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center text-[10px] text-gray-400 shrink-0">No Pix</div>
                                <?php endif; ?>
                                
                                <div class="text-right">
                                    <?php if($row['status']=="Menunggu"){ ?>
                                        <span class="inline-block bg-amber-50 text-amber-700 border border-amber-200 px-1.5 py-0.5 rounded-md text-[9px] font-bold">Menunggu</span>
                                    <?php } elseif($row['status']=="Diterima"){ ?>
                                        <span class="inline-block bg-emerald-50 text-emerald-700 border border-emerald-200 px-1.5 py-0.5 rounded-md text-[9px] font-bold">Diterima</span>
                                    <?php } elseif($row['status']=="Ditolak"){ ?>
                                        <span class="inline-block bg-rose-50 text-rose-700 border border-rose-200 px-1.5 py-0.5 rounded-md text-[9px] font-bold">Ditolak</span>
                                    <?php } elseif($row['status']=="Dikeluarkan"){ ?>
                                        <span class="inline-block bg-gray-800 text-white px-1.5 py-0.5 rounded-md text-[9px] font-bold">Keluar</span>
                                    <?php } else { ?>
                                        <span class="inline-block bg-purple-50 text-purple-700 border border-purple-200 px-1.5 py-0.5 rounded-md text-[9px] font-bold">Max</span>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="font-bold text-gray-800 text-xs tracking-tight line-clamp-1" title="<?= htmlspecialchars($row['nama']) ?>">
                                    <?= htmlspecialchars($row['nama']) ?>
                                </h4>
                                <p class="text-[10px] text-gray-400 truncate">Kls <?= $row['kelas'] ?> • <?= $row['nisn'] ?></p>
                            </div>
                        </div>

                        <div class="space-y-1.5 text-[11px] bg-gray-50/70 p-2 rounded-xl border border-gray-100/70">
                            <div>
                                <span class="text-[9px] text-gray-400 block">Ekskul</span>
                                <span class="font-bold text-emerald-700 block truncate"><?= htmlspecialchars($row['nama_ekskul']) ?></span>
                            </div>
                            <div>
                                <span class="text-[9px] text-gray-400 block">Jadwal</span>
                                <span class="font-medium text-gray-700 block text-[10px] truncate">
                                    📅 <?= htmlspecialchars($row['hari'] ?? '-') ?> (<?= !empty($row['jam_mulai']) ? substr($row['jam_mulai'],0,5) : '-' ?>)
                                </span>
                            </div>
                            <div>
                                <span class="text-[9px] text-gray-400 block">No. HP</span>
                                <span class="font-medium text-gray-600 block text-[10px] truncate"><?= htmlspecialchars($row['no_hp']) ?></span>
                            </div>
                        </div>

                        <div class="pt-1 mt-auto">
                            <?php if($row['status'] == "Menunggu"){ ?>
                                <div class="grid grid-cols-2 gap-1">
                                    <a href="?aksi=terima&id=<?= $row['id_pendaftaran'] ?>" class="bg-emerald-600 text-center text-white py-1.5 rounded-lg font-bold text-[10px] shadow-sm active:bg-emerald-700 block">Terima</a>
                                    <button onclick="openTolak(<?= $row['id_pendaftaran'] ?>)" class="bg-rose-500 text-white py-1.5 rounded-lg font-bold text-[10px] shadow-sm active:bg-rose-600 w-full">Tolak</button>
                                </div>
                            <?php } elseif($row['status'] == "Diterima"){ ?>
                                <button onclick="openKeluarkan(<?= $row['id_pendaftaran'] ?>)" class="w-full bg-gray-900 text-white py-1.5 rounded-lg font-bold text-[10px] shadow-sm active:bg-black">Keluarkan</button>
                            <?php } else { ?>
                                <a href="?aksi=hapus&id=<?= $row['id_pendaftaran'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="w-full block bg-rose-50 border border-rose-100 text-rose-700 text-center py-1.5 rounded-lg font-bold text-[10px] active:bg-rose-100">Hapus</a>
                            <?php } ?>
                        </div>

                    </div>
                <?php } ?>
            </div>
        </div>

    </div>

    <div class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-4">
        <div class="w-full sm:w-auto">
            <a href="dashboard_admin.php" class="w-full sm:w-auto inline-flex justify-center items-center bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-5 py-3 rounded-xl font-bold text-sm shadow-sm transition active:scale-95">← Kembali Ke Menu</a>
        </div>
        
        <?php if($totalHalaman > 1): ?>
        <div class="flex items-center gap-1.5 flex-wrap justify-center">
            <?php if($halaman > 1){ ?>
                <a href="?search=<?= urlencode($search) ?>&kelas=<?= $kelas ?>&halaman=<?= $halaman-1 ?>" class="bg-white border border-gray-200 px-3.5 py-2 rounded-xl text-xs font-bold hover:bg-gray-50 transition shadow-sm">Prev</a>
            <?php } ?>

            <?php for($i=1; $i <= $totalHalaman; $i++){ ?>
                <a href="?search=<?= urlencode($search) ?>&kelas=<?= $kelas ?>&halaman=<?= $i ?>" class="px-3.5 py-2 rounded-xl text-xs font-bold shadow-sm transition <?= $i == $halaman ? 'bg-emerald-600 text-white scale-105' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' ?>"><?= $i ?></a>
            <?php } ?>

            <?php if($halaman < $totalHalaman){ ?>
                <a href="?search=<?= urlencode($search) ?>&kelas=<?= $kelas ?>&halaman=<?= $halaman+1 ?>" class="bg-white border border-gray-200 px-3.5 py-2 rounded-xl text-xs font-bold hover:bg-gray-50 transition shadow-sm">Next</a>
            <?php } ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div id="modalTolak" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-4 transition-all">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-xl w-full max-w-md animate-fadeUp">
        <h3 class="text-lg font-black text-rose-700 mb-2">Alasan Menolak Siswa</h3>
        <p class="text-xs text-gray-400 mb-4">Berikan alasan penolakan yang akan dikirimkan kepada sistem siswa.</p>
        <form method="POST" action="">
            <input type="hidden" name="id_pendaftaran" id="id_pendaftaran_tolak">
            <textarea name="alasan" rows="4" required placeholder="Tuliskan alasan penolakan di sini..." class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl outline-none focus:ring-2 focus:ring-rose-400 focus:bg-white resize-none text-sm mb-4"></textarea>
            <div class="flex justify-end gap-2 text-xs">
                <button type="button" onclick="closeTolak()" class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl font-bold transition">Batal</button>
                <button type="submit" name="submit_tolak" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 rounded-xl font-bold shadow transition">Simpan & Tolak</button>
            </div>
        </form>
    </div>
</div>

<div id="modalKeluarkan" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden p-4 transition-all">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-xl w-full max-w-md animate-fadeUp">
        <h3 class="text-lg font-black text-gray-900 mb-2">Alasan Mengeluarkan Siswa</h3>
        <p class="text-xs text-gray-400 mb-4">Tuliskan alasan resmi pencabutan status keanggotaan ekstrakurikuler siswa.</p>
        <form method="POST" action="">
            <input type="hidden" name="id_pendaftaran" id="id_pendaftaran_keluar">
            <textarea name="alasan_keluar" rows="4" required placeholder="Tuliskan alasan mengeluarkan siswa di sini..." class="w-full bg-gray-50 border border-gray-200 p-3 rounded-xl outline-none focus:ring-2 focus:ring-gray-400 focus:bg-white resize-none text-sm mb-4"></textarea>
            <div class="flex justify-end gap-2 text-xs">
                <button type="button" onclick="closeKeluarkan()" class="bg-gray-100 text-gray-600 px-4 py-2.5 rounded-xl font-bold transition">Batal</button>
                <button type="submit" name="submit_keluarkan" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2.5 rounded-xl font-bold shadow transition">Simpan & Keluarkan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTolak(id) {
    document.getElementById('id_pendaftaran_tolak').value = id;
    document.getElementById('modalTolak').classList.remove('hidden');
}
function closeTolak() {
    document.getElementById('modalTolak').classList.add('hidden');
}
function openKeluarkan(id) {
    document.getElementById('id_pendaftaran_keluar').value = id;
    document.getElementById('modalKeluarkan').classList.remove('hidden');
}
function closeKeluarkan() {
    document.getElementById('modalKeluarkan').classList.add('hidden');
}
</script>
</body>
</html>