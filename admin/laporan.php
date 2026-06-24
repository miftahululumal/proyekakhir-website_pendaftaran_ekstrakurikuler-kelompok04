<?php
// SINKRONISASI WAKTU AGAR TIDAK TELAT 1 HARI
date_default_timezone_set('Asia/Jakarta');

include '../register/cek_login.php';
include '../database/koneksi.php';

if($_SESSION['role'] != 'admin'){
    header("Location: ../user/dashboard_user.php");
    exit;
}

// Inisialisasi variabel filter dari URL (Method GET)
$filter_ekskul   = isset($_GET['ekskul']) ? mysqli_real_escape_string($conn, $_GET['ekskul']) : '';
$filter_status   = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$tgl_mulai       = isset($_GET['tgl_mulai']) ? mysqli_real_escape_string($conn, $_GET['tgl_mulai']) : '';
$tgl_selesai     = isset($_GET['tgl_selesai']) ? mysqli_real_escape_string($conn, $_GET['tgl_selesai']) : '';

// Ambil data untuk pilihan dropdown filter Ekskul
$list_ekskul = mysqli_query($conn, "SELECT id_ekskul, nama_ekskul FROM ekskul ORDER BY nama_ekskul ASC");

// Flag untuk mengecek apakah user sudah melakukan filter
$is_filtered = (!empty($filter_ekskul) || !empty($filter_status) || !empty($tgl_mulai) || !empty($tgl_selesai));

$total_pendaftar = 0;
$ekskul = null;

if ($is_filtered) {
    // 1. Ambil info Ekskul jika memilih ekskul tertentu
    if (!empty($filter_ekskul)) {
        $ekskulQuery = mysqli_query($conn, "
            SELECT ekskul.*, guru.nama_guru 
            FROM ekskul 
            LEFT JOIN guru ON ekskul.nip_pembimbing = guru.nip 
            WHERE ekskul.id_ekskul = '$filter_ekskul'
        ");
        $ekskul = mysqli_fetch_assoc($ekskulQuery);
    }

    // 2. Bangun Query pendaftaran dinamis
    $query_string = "
        SELECT 
            pendaftaran.*, 
            siswa.nama AS nama_siswa, 
            siswa.kelas, 
            jadwal_ekskul.hari, 
            jadwal_ekskul.jam_mulai, 
            jadwal_ekskul.jam_selesai,
            ekskul.nama_ekskul
        FROM pendaftaran 
        INNER JOIN siswa ON pendaftaran.nisn = siswa.nisn 
        INNER JOIN jadwal_ekskul ON pendaftaran.id_jadwal = jadwal_ekskul.id_jadwal
        INNER JOIN ekskul ON pendaftaran.id_ekskul = ekskul.id_ekskul
        WHERE 1=1
    ";

    if (!empty($filter_ekskul)) {
        $query_string .= " AND pendaftaran.id_ekskul = '$filter_ekskul'";
    }
    if (!empty($filter_status)) {
        $query_string .= " AND pendaftaran.status = '$filter_status'";
    }
    
    // Perbaikan logika filter tanggal
    if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
        $query_string .= " AND DATE(pendaftaran.tanggal_daftar) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
    } elseif (!empty($tgl_mulai) && empty($tgl_selesai)) {
        $query_string .= " AND DATE(pendaftaran.tanggal_daftar) = '$tgl_mulai'";
    } elseif (empty($tgl_mulai) && !empty($tgl_selesai)) {
        $query_string .= " AND DATE(pendaftaran.tanggal_daftar) <= '$tgl_selesai'";
    }

    $query_string .= " ORDER BY pendaftaran.id_pendaftaran DESC";
    
    $pendaftaranQuery = mysqli_query($conn, $query_string);
    $total_pendaftar = mysqli_num_rows($pendaftaranQuery);
}

// Array nama bulan Indonesia hanya untuk penanggalan tanda tangan di bawah
$nama_bulan = [
    "01" => "Januari", "02" => "Februari", "03" => "Maret", "04" => "April", 
    "05" => "Mei", "06" => "Juni", "07" => "Juli", "08" => "Agustus", 
    "09" => "September", "10" => "Oktober", "11" => "November", "12" => "Desember"
];

// Fungsi format tanggal Indonesia untuk sub-judul laporan
function tgl_indo($tanggal){
    if(empty($tanggal)) return '';
    $bulan_indo = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan_indo[ (int)$split[1] ] . ' ' . $split[0];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Ekskul - SMK KAMAL 1</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/animation.css">
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 1.2s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }

        .animation-delay-200 { animation-delay: 250ms; }
        .animation-delay-400 { animation-delay: 500ms; }
        .animation-delay-600 { animation-delay: 750ms; }

        @media print {
            .no-print { display: none !important; }
            body { background-color: white; padding: 0; }
            .print-container { box-shadow: none !important; border: none !important; max-width: 100% !important; padding: 0 !important; }
            .animate-fade-in-up { animation: none !important; opacity: 1 !important; transform: none !important; }
        }
        @media (max-width: 640px) {
            .table-responsive { font-size: 0.75rem; }
            .table-responsive th, .table-responsive td { padding: 8px 4px !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-2 lg:p-8">

    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-200 p-4 lg:p-8 print-container">
        
        <div class="no-print bg-gray-50 p-6 rounded-xl border border-green-200 mb-6 animate-fade-in-up opacity-0 bg-gradient-to-r from-emerald-50 to-emerald-100">
            
            <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                <a href="dashboard_admin.php" class="inline-flex items-center gap-2 text-sm font-bold text-gray-600 hover:text-emerald-700 transition-all duration-200 bg-white border border-gray-300 rounded-lg px-4 py-2 shadow-sm hover:shadow-md transform hover:-translate-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Dashboard
                </a>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Menu Administrator</span>
            </div>

            <h3 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-wider">Filter Laporan Pendaftaran</h3>
            <form method="GET" action="" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Pilih Ekstrakurikuler</label>
                    <select name="ekskul" class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:border-emerald-600 transition-all">
                        <option value="">-- Semua Ekskul --</option>
                        <?php while($row = mysqli_fetch_assoc($list_ekskul)): ?>
                            <option value="<?= $row['id_ekskul'] ?>" <?= $filter_ekskul == $row['id_ekskul'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['nama_ekskul']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Dari Tanggal</label>
                    <input type="date" name="tgl_mulai" value="<?= $tgl_mulai ?>" class="w-full bg-white border border-gray-300 rounded-lg p-1.5 text-sm focus:outline-none focus:border-emerald-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Sampai Tanggal</label>
                    <input type="date" name="tgl_selesai" value="<?= $tgl_selesai ?>" class="w-full bg-white border border-gray-300 rounded-lg p-1.5 text-sm focus:outline-none focus:border-emerald-600 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Pilih Status</label>
                    <select name="status" class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm focus:outline-none focus:border-emerald-600 transition-all">
                        <option value="">-- Semua Status --</option>
                        <option value="Diterima" <?= $filter_status == 'Diterima' ? 'selected' : '' ?>>Diterima</option>
                        <option value="Ditolak" <?= $filter_status == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                        <option value="Dikeluarkan" <?= $filter_status == 'Dikeluarkan' ? 'selected' : '' ?>>Dikeluarkan</option>
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-4 flex gap-2 justify-end mt-2">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-2 rounded-lg text-sm transition-all duration-200 hover:shadow-lg active:scale-95">
                        Filter Data
                    </button>
                    <button type="button" onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2 rounded-lg text-sm transition-all duration-200 hover:shadow-lg active:scale-95">
                        Cetak PDF
                    </button>
                </div>
            </form>
        </div>

        <?php if (!$is_filtered): ?>
            <div class="no-print text-center py-12 text-gray-400 border border-dashed border-gray-300 rounded-xl animate-fade-in-up animation-delay-200 opacity-0">
                <p class="italic text-sm">Silakan tentukan kriteria filter di atas terlebih dahulu, kemudian klik "Filter Data" untuk memunculkan dokumen cetak laporan.</p>
            </div>
        <?php else: ?>

            <div class="flex flex-col items-center justify-center border-b-4 border-emerald-950 pb-5 mb-6 text-center animate-fade-in-up animation-delay-200 opacity-0">
                <div class="bg-emerald-900 p-2 rounded-xl shadow-lg mb-3 no-print transform hover:rotate-6 transition-transform duration-300">
                    <img src="../gambar/logo.png" class="w-16 h-16 object-cover rounded-lg">
                </div>
                
                <div class="hidden print:block mb-3">
                    <img src="../gambar/logo.png" class="w-20 h-20 object-cover">
                </div>
                
                <h1 class="text-2xl lg:text-3xl font-black text-emerald-900 tracking-wide uppercase">SMK KAMAL 1</h1>
                <p class="text-gray-500 text-xs lg:text-sm mt-0.5 font-medium">Laporan Pendaftaran Peserta Ekstrakurikuler Sekolah</p>
            </div>

            <div class="mb-6 text-center border-b pb-4 border-dashed animate-fade-in-up animation-delay-400 opacity-0">
                <h2 class="text-lg lg:text-2xl font-black text-gray-800 uppercase tracking-wide">
                    <?= $ekskul ? htmlspecialchars($ekskul['nama_ekskul']) : 'Semua Ekstrakurikuler' ?>
                </h2>
                <div class="text-xs text-gray-600 mt-2 space-y-1">
                    <p>Pembimbing: <span class="font-bold text-emerald-800"><?= $ekskul ? htmlspecialchars($ekskul['nama_guru'] ?? 'Belum ada') : 'Semua Pembimbing' ?></span></p>
                    <p>
                        <?php if(!empty($tgl_mulai) && !empty($tgl_selesai)): ?>
                            Periode Laporan: <span class="font-bold text-gray-800"><?= tgl_indo($tgl_mulai) ?></span> s/d <span class="font-bold text-gray-800"><?= tgl_indo($tgl_selesai) ?></span>
                        <?php elseif(!empty($tgl_mulai)): ?>
                            Tanggal Laporan: <span class="font-bold text-gray-800"><?= tgl_indo($tgl_mulai) ?></span>
                        <?php else: ?>
                            Periode Laporan: <span class="font-bold text-gray-800">Semua Waktu</span>
                        <?php endif; ?>
                        <?php if(!empty($filter_status)): ?>
                            | Status: <span class="font-bold text-gray-800"><?= htmlspecialchars($filter_status) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto table-responsive animate-fade-in-up animation-delay-600 opacity-0">
                <table class="w-full text-left text-gray-600 border-collapse border border-gray-200 rounded-lg overflow-hidden shadow-xs">
                    <thead>
                        <tr class="bg-emerald-900 text-white text-[10px] lg:text-xs uppercase">
                            <th class="border px-2 py-3 text-center">No</th>
                            <th class="border px-2 py-3">Tanggal Daftar</th>
                            <th class="border px-2 py-3">NISN</th>
                            <th class="border px-2 py-3">Nama Siswa</th>
                            <th class="border px-2 py-3 text-center">Kelas</th>
                            <th class="border px-2 py-3">Ekskul / Jadwal</th>
                            <th class="border px-2 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php 
                        if($total_pendaftar > 0) {
                            $no = 1;
                            while($siswa = mysqli_fetch_assoc($pendaftaranQuery)){ 
                                $status_class = "bg-green-100 text-green-800 border-green-200";
                                if ($siswa['status'] == 'Ditolak') {
                                    $status_class = "bg-red-100 text-red-800 border-red-200";
                                } elseif ($siswa['status'] == 'Dikeluarkan') {
                                    $status_class = "bg-amber-100 text-amber-800 border-amber-200";
                                }
                        ?>
                            <tr class="hover:bg-gray-50/80 transition-colors duration-150">
                                <td class="border px-2 py-3 text-center text-gray-400"><?= $no++ ?></td>
                                <td class="border px-2 py-3 text-xs text-gray-700"><?= isset($siswa['tanggal_daftar']) ? date('d-m-Y', strtotime($siswa['tanggal_daftar'])) : '-' ?></td>
                                <td class="border px-2 py-3 font-mono text-gray-700"><?= htmlspecialchars($siswa['nisn']) ?></td>
                                <td class="border px-2 py-3 font-bold text-gray-800"><?= htmlspecialchars($siswa['nama_siswa']) ?></td>
                                <td class="border px-2 py-3 text-center"><?= htmlspecialchars($siswa['kelas']) ?></td>
                                <td class="border px-2 py-3 text-[10px]">
                                    <span class="font-bold text-emerald-900 block"><?= htmlspecialchars($siswa['nama_ekskul']) ?></span>
                                    <span class="font-medium text-gray-700 block"><?= htmlspecialchars($siswa['hari']) ?></span>
                                    <span class="text-gray-400"><?= date('H:i', strtotime($siswa['jam_mulai'])) ?> - <?= date('H:i', strtotime($siswa['jam_selesai'])) ?></span>
                                </td>
                                <td class="border px-2 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold border <?= $status_class ?>">
                                        <?= htmlspecialchars($siswa['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php 
                            } 
                        } else {
                            echo '<tr><td colspan="7" class="p-6 text-center text-gray-400 italic">Tidak ditemukan data pendaftaran siswa yang sesuai dengan kriteria filter tanggal tersebut.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-10 text-right text-[10px] text-gray-500 hidden print:block animate-fade-in-up animation-delay-600 opacity-0">
                <p>Bangkalan, <?= date('d') . ' ' . $nama_bulan[date('m')] . ' ' . date('Y') ?></p>
                <p class="mt-12 font-bold text-gray-800 underline">Administrator SMK KAMAL 1</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>