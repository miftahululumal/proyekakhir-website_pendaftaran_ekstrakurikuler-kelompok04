<?php
include '../register/cek_login.php';
include '../database/koneksi.php';

if($_SESSION['role'] != 'admin'){
    header("Location: ../user/dashboard_user.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID Ekskul tidak ditemukan!'); window.location='dashboard_admin.php';</script>";
    exit;
}

$id_ekskul = mysqli_real_escape_string($conn, $_GET['id']);
$ekskulQuery = mysqli_query($conn, "
    SELECT ekskul.*, guru.nama_guru 
    FROM ekskul 
    LEFT JOIN guru ON ekskul.nip_pembimbing = guru.nip 
    WHERE ekskul.id_ekskul = '$id_ekskul'
");
$ekskul = mysqli_fetch_assoc($ekskulQuery);

if (!$ekskul) {
    echo "<script>alert('Data Ekskul tidak ditemukan!'); window.location='dashboard_admin.php';</script>";
    exit;
}

$pendaftaranQuery = mysqli_query($conn, "
    SELECT 
        pendaftaran.*, 
        siswa.nama AS nama_siswa, 
        siswa.kelas, 
        jadwal_ekskul.hari, 
        jadwal_ekskul.jam_mulai, 
        jadwal_ekskul.jam_selesai
    FROM pendaftaran 
    INNER JOIN siswa ON pendaftaran.nisn = siswa.nisn 
    INNER JOIN jadwal_ekskul ON pendaftaran.id_jadwal = jadwal_ekskul.id_jadwal
    WHERE pendaftaran.id_ekskul = '$id_ekskul' 
    AND pendaftaran.status = 'Diterima' 
    ORDER BY pendaftaran.id_pendaftaran DESC
");

$total_pendaftar = mysqli_num_rows($pendaftaranQuery);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Ekskul - <?= htmlspecialchars($ekskul['nama_ekskul']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background-color: white; padding: 0; }
            .print-container { box-shadow: none !important; border: none !important; max-width: 100% !important; padding: 0 !important; }
        }
        /* Penyesuaian agar tabel tidak terlalu lebar di HP */
        @media (max-width: 640px) {
            .table-responsive { font-size: 0.75rem; }
            .table-responsive th, .table-responsive td { padding: 8px 4px !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-2 lg:p-8">

    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-200 p-4 lg:p-8 print-container">
        
        <div class="flex justify-between items-center mb-6 no-print bg-gray-50 p-4 rounded-xl border border-gray-200">
            <a href="dashboard_admin.php" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-lg text-xs font-bold transition-colors">← Kembali</a>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-bold transition-colors shadow-md">Cetak PDF</button>
        </div>

        <div class="flex flex-col items-center justify-center border-b-4 border-emerald-950 pb-5 mb-6 text-center">
            <div class="bg-emerald-900 p-2 rounded-xl shadow-lg mb-3 no-print">
                <img src="../gambar/logo.png" class="w-16 h-16 object-cover rounded-lg">
            </div>
            
            <div class="hidden print:block mb-3">
                <img src="../gambar/logo.png" class="w-20 h-20 object-cover">
            </div>
            
            <h1 class="text-2xl lg:text-3xl font-black text-emerald-900 tracking-wide uppercase">SMK KAMAL 1</h1>
            <p class="text-gray-500 text-xs lg:text-sm mt-0.5 font-medium">Laporan Pendaftaran Peserta Ekstrakurikuler Sekolah</p>
        </div>

        <div class="mb-6 text-center border-b pb-4 border-dashed">
            <h2 class="text-lg lg:text-2xl font-black text-gray-800 uppercase tracking-wide"><?= htmlspecialchars($ekskul['nama_ekskul']) ?></h2>
            <p class="text-xs text-gray-600 mt-2">Pembimbing: <span class="font-bold text-emerald-800"><?= htmlspecialchars($ekskul['nama_guru'] ?? 'Belum ada') ?></span></p>
        </div>

        <div class="overflow-x-auto table-responsive">
            <table class="w-full text-left text-gray-600 border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-emerald-900 text-white text-[10px] lg:text-xs uppercase">
                        <th class="border px-2 py-3 text-center">No</th>
                        <th class="border px-2 py-3">NISN</th>
                        <th class="border px-2 py-3">Nama Siswa</th>
                        <th class="border px-2 py-3 text-center">Kelas</th>
                        <th class="border px-2 py-3">Jadwal</th>
                        <th class="border px-2 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php 
                    if($total_pendaftar > 0) {
                        $no = 1;
                        while($siswa = mysqli_fetch_assoc($pendaftaranQuery)){ 
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="border px-2 py-3 text-center text-gray-400"><?= $no++ ?></td>
                            <td class="border px-2 py-3 font-mono text-gray-700"><?= htmlspecialchars($siswa['nisn']) ?></td>
                            <td class="border px-2 py-3 font-bold text-gray-800"><?= htmlspecialchars($siswa['nama_siswa']) ?></td>
                            <td class="border px-2 py-3 text-center"><?= htmlspecialchars($siswa['kelas']) ?></td>
                            <td class="border px-2 py-3 text-[10px]">
                                <span class="font-bold text-gray-700 block"><?= htmlspecialchars($siswa['hari']) ?></span>
                                <span class="text-gray-400"><?= date('H:i', strtotime($siswa['jam_mulai'])) ?> - <?= date('H:i', strtotime($siswa['jam_selesai'])) ?></span>
                            </td>
                            <td class="border px-2 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold border bg-green-100 text-green-800 border-green-200">
                                    <?= htmlspecialchars($siswa['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php 
                        } 
                    } else {
                        echo '<tr><td colspan="6" class="p-6 text-center text-gray-400 italic">Belum ada data siswa.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="mt-10 text-right text-[10px] text-gray-500 hidden print:block">
            <p>Bangkalan, <?= date('d F Y') ?></p>
            <p class="mt-12 font-bold text-gray-800 underline">Administrator SMK KAMAL 1</p>
        </div>
    </div>
</body>
</html>