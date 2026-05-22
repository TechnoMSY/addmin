<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

// Proteksi Halaman: Hanya Admin atau Guru yang boleh mengakses berkas cetak laporan
$user_level = $_SESSION['user']['level'] ?? $_SESSION['user']['role'] ?? 'siswa';
if ($user_level !== 'admin' && $user_level !== 'guru') {
    echo "<script>
        alert('Akses Ditolak! Anda tidak memiliki izin untuk mencetak laporan.');
        window.location.href = 'index.php';
    </script>";
    exit();
}

// Ambil data barang untuk opsi drop-down filter perangkat
$barang_query = mysqli_query($koneksi, "SELECT id_barang, nama, merek FROM barang ORDER BY nama ASC");

// 1. MENYUSUN QUERY SQL DINAMIS BERDASARKAN FILTER YANG DIPILIH
$where_clauses = [];
$filter_info = [];

// Tangkap nilai filter dari form URL (GET)
$f_tgl_mulai = $_GET['tgl_mulai'] ?? '';
$f_tgl_selesai = $_GET['tgl_selesai'] ?? '';
$f_id_barang = $_GET['id_barang'] ?? '';
$f_status_p = $_GET['status_peminjaman'] ?? '';
$f_status_f = $_GET['status_fisik'] ?? '';
$f_kondisi = $_GET['kondisi'] ?? '';

// Filter Tanggal Mulai & Selesai
if (!empty($f_tgl_mulai) && !empty($f_tgl_selesai)) {
    $where_clauses[] = "peminjaman.tanggal_peminjaman BETWEEN '$f_tgl_mulai' AND '$f_tgl_selesai'";
    $filter_info[] = "Periode: " . date('d/m/Y', strtotime($f_tgl_mulai)) . " s.d " . date('d/m/Y', strtotime($f_tgl_selesai));
} elseif (!empty($f_tgl_mulai)) {
    $where_clauses[] = "peminjaman.tanggal_peminjaman >= '$f_tgl_mulai'";
    $filter_info[] = "Sejak Tanggal: " . date('d/m/Y', strtotime($f_tgl_mulai));
} elseif (!empty($f_tgl_selesai)) {
    $where_clauses[] = "peminjaman.tanggal_peminjaman <= '$f_tgl_selesai'";
    $filter_info[] = "Hingga Tanggal: " . date('d/m/Y', strtotime($f_tgl_selesai));
}

// Filter Spesifik Barang
if (!empty($f_id_barang)) {
    $id_barang_int = (int) $f_id_barang;
    $where_clauses[] = "peminjaman.id_barang = $id_barang_int";
    $b_res = mysqli_query($koneksi, "SELECT nama FROM barang WHERE id_barang=$id_barang_int");
    if ($b_data = mysqli_fetch_assoc($b_res)) {
        $filter_info[] = "Perangkat: " . htmlspecialchars($b_data['nama']);
    }
}

// Filter Status Persetujuan Ajuan
if (!empty($f_status_p)) {
    $status_p_clean = mysqli_real_escape_string($koneksi, $f_status_p);
    $where_clauses[] = "peminjaman.status_peminjaman = '$status_p_clean'";
    $filter_info[] = "Persetujuan: " . strtoupper($status_p_clean);
}

// Filter Status Fisik Barang
if (!empty($f_status_f)) {
    $status_f_clean = mysqli_real_escape_string($koneksi, $f_status_f);
    $where_clauses[] = "peminjaman.status = '$status_f_clean'";
    $filter_info[] = "Status Fisik: " . strtoupper($status_f_clean);
}

// Filter Kondisi Barang
if (!empty($f_kondisi)) {
    $kondisi_clean = mysqli_real_escape_string($koneksi, $f_kondisi);
    $where_clauses[] = "peminjaman.kondisi = '$kondisi_clean'";
    $filter_info[] = "Kondisi: " . strtoupper($kondisi_clean);
}

// Gabungkan Klausa WHERE jika ada filter yang dipilih
$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Jalankan Query Data Sirkulasi yang Terfilter
$query_string = "SELECT 
                    peminjaman.*, 
                    user.nama AS nama_siswa, 
                    barang.nama AS nama_barang, 
                    barang.merek AS merek_barang 
                 FROM peminjaman
                 INNER JOIN user ON peminjaman.id_user = user.id_user
                 INNER JOIN barang ON peminjaman.id_barang = barang.id_barang
                 $where_sql
                 ORDER BY peminjaman.id_peminjaman DESC";

$result = mysqli_query($koneksi, $query_string);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Sistem Filter & Cetak Laporan | Lab MAKN</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1"
        rel="stylesheet" />
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* ATURAN CRITICAL SAAT DICETAK KE KERTAS FISIK ATAU PDF */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: #ffffff !important;
                padding: 0 !important;
            }

            .paper-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>

<body class="bg-slate-100 text-slate-800 p-4 md:p-8 antialiased min-h-screen">

    <div class="max-w-6xl mx-auto space-y-6">

        <div
            class="no-print bg-white p-6 rounded-[24px] border border-slate-200/80 shadow-xl shadow-slate-200/40 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center text-blue-600 shadow-sm">
                        <span class="material-symbols-outlined">filter_alt</span>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Panel Saring Konten Laporan</h2>
                        <p class="text-[11px] text-slate-400 font-medium">Pilih kriteria data peminjaman di bawah ini
                            sebelum mencetak dokumen.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="index.php?page=peminjaman"
                        class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition-all uppercase tracking-wider flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">arrow_back</span>
                        Kembali
                    </a>
                    <button onclick="window.print();"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-all uppercase tracking-wider flex items-center justify-center gap-2 shadow-md shadow-blue-500/20">
                        <span class="material-symbols-outlined text-base">print</span>
                        Cetak Laporan Saat Ini
                    </button>
                </div>
            </div>

            <form method="GET" action="cetak_laporan.php" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-1">Dari
                        Tanggal</label>
                    <input type="date" name="tgl_mulai" value="<?= htmlspecialchars($f_tgl_mulai); ?>"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-blue-600 focus:ring-0 transition-all text-slate-700" />
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-1">Sampai
                        Tanggal</label>
                    <input type="date" name="tgl_selesai" value="<?= htmlspecialchars($f_tgl_selesai); ?>"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-blue-600 focus:ring-0 transition-all text-slate-700" />
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-1">Nama
                        Perangkat</label>
                    <select name="id_barang"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-blue-600 focus:ring-0 transition-all text-slate-700">
                        <option value="">Semua Barang</option>
                        <?php
                        mysqli_data_seek($barang_query, 0);
                        while ($b = mysqli_fetch_assoc($barang_query)):
                            ?>
                            <option value="<?= $b['id_barang']; ?>" <?= $f_id_barang == $b['id_barang'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($b['nama']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-1">Status
                        Ajuan</label>
                    <select name="status_peminjaman"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-blue-600 focus:ring-0 transition-all text-slate-700">
                        <option value="">Semua</option>
                        <option value="pending" <?= $f_status_p === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="disetujui" <?= $f_status_p === 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                        <option value="ditolak" <?= $f_status_p === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-1">Status
                        Fisik</label>
                    <select name="status_fisik"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-blue-600 focus:ring-0 transition-all text-slate-700">
                        <option value="">Semua</option>
                        <option value="dipinjam" <?= $f_status_f === 'dipinjam' ? 'selected' : ''; ?>>Dipinjam</option>
                        <option value="dikembalikan" <?= $f_status_f === 'dikembalikan' ? 'selected' : ''; ?>>Dikembalikan
                        </option>
                        <option value="terlambat" <?= $f_status_f === 'terlambat' ? 'selected' : ''; ?>>Terlambat</option>
                    </select>
                </div>

                <div class="space-y-1 flex flex-col justify-between">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-1">Kondisi
                        Barang</label>
                    <div class="flex gap-2">
                        <select name="kondisi"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-blue-600 focus:ring-0 transition-all text-slate-700">
                            <option value="">Semua Kondisi</option>
                            <option value="baik" <?= $f_kondisi === 'baik' ? 'selected' : ''; ?>>Baik</option>
                            <option value="rusak" <?= $f_kondisi === 'rusak' ? 'selected' : ''; ?>>Rusak</option>
                        </select>

                        <button type="submit"
                            class="p-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl flex items-center justify-center transition-colors shrink-0 aspect-square"
                            title="Terapkan Filter">
                            <span class="material-symbols-outlined text-base">search</span>
                        </button>
                        <a href="cetak_laporan.php"
                            class="p-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl flex items-center justify-center transition-colors shrink-0 aspect-square"
                            title="Reset Filter">
                            <span class="material-symbols-outlined text-base">restart_alt</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>


        <div
            class="paper-card bg-white p-8 md:p-12 rounded-[32px] border border-slate-200 shadow-sm min-h-[297mm] flex flex-col justify-between">

            <div>
                <div class="flex items-center gap-5 border-b-4 border-slate-800 pb-5 mb-6">
                    <div
                        class="w-16 h-16 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center p-1.5 shrink-0">
                        <img src="images/image.png" alt="Logo" class="w-full h-full object-contain"
                            onerror="this.parentNode.innerHTML='<span class=\'font-black text-sm text-blue-600\'>MAKN</span>';">
                    </div>
                    <div class="flex-1">
                        <h1 class="text-lg font-black text-slate-900 uppercase tracking-tight">MADRASAH ALIYAH KEJURUAN NEGERI Ende(MAKN Ende)
                            (MAKN)</h1>
                        <p class="text-xs font-bold text-slate-700 uppercase tracking-wide mt-0.5">Laboratorium Komputer
                            & Perangkat Digital Teknis</p>
                        <p class="text-[10px] text-slate-400 font-medium mt-0.5">Jalan Raya Ende-Bajawa KM 21, Desa/Kelurahan Anaraja, Kecamatan Nangapanda, Kabupaten Ende, Nusa Tenggara Timur (NTT).</p>
                    </div>
                </div>

                <div class="text-center my-6 space-y-1">
                    <h2
                        class="text-sm font-extrabold text-slate-900 uppercase tracking-wider underline underline-offset-4">
                        LAPORAN SIRKULASI PEMINJAMAN</h2>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wide">
                        <?php
                        if (count($filter_info) > 0) {
                            echo implode(" &bull; ", $filter_info);
                        } else {
                            echo "Kategori Filter: Menampilkan Seluruh Log Histori Konten";
                        }
                        ?>
                    </p>
                </div>

                <table class="w-full border-collapse border border-slate-300 text-left mt-6">
                    <thead>
                        <tr
                            class="bg-slate-50 text-slate-700 uppercase font-bold text-[9px] tracking-wider border-b border-slate-300">
                            <th class="py-2.5 px-2 border border-slate-300 text-center w-8">No</th>
                            <th class="py-2.5 px-3 border border-slate-300">Nama Siswa</th>
                            <th class="py-2.5 px-3 border border-slate-300">Nama Barang / Perangkat</th>
                            <th class="py-2.5 px-2 border border-slate-300 text-center">Jumlah</th>
                            <th class="py-2.5 px-3 border border-slate-300 text-center">Tgl Pinjam</th>
                            <th class="py-2.5 px-3 border border-slate-300 text-center">Status Ajuan</th>
                            <th class="py-2.5 px-3 border border-slate-300 text-center">Status Fisik</th>
                            <th class="py-2.5 px-3 border border-slate-300 text-center">Tgl Kembali</th>
                            <th class="py-2.5 px-2 border border-slate-300 text-center">Kondisi</th>
                        </tr>
                    </thead>
                    <tbody class="text-[10px] font-medium text-slate-700 divide-y divide-slate-200">
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $tgl_pinjam = date('d/m/Y', strtotime($row['tanggal_peminjaman']));
                                $tgl_kembali = !empty($row['tanggal_pengembalian']) ? date('d/m/Y', strtotime($row['tanggal_pengembalian'])) : '-';
                                ?>
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2.5 px-2 border border-slate-300 text-center font-bold text-slate-400">
                                        <?= $no++; ?>
                                    </td>
                                    <td class="py-2.5 px-3 border border-slate-300 font-bold text-slate-900">
                                        <?= htmlspecialchars($row['nama_siswa']); ?>
                                    </td>
                                    <td class="py-2.5 px-3 border border-slate-300">
                                        <span
                                            class="font-bold text-slate-800"><?= htmlspecialchars($row['nama_barang']); ?></span>
                                        <span
                                            class="text-[9px] text-slate-400 block font-normal"><?= htmlspecialchars($row['merek_barang']); ?></span>
                                    </td>
                                    <td class="py-2.5 px-2 border border-slate-300 text-center font-bold">
                                        <?= (int) $row['jumlah_pinjam']; ?> unit
                                    </td>
                                    <td class="py-2.5 px-3 border border-slate-300 text-center text-slate-500">
                                        <?= $tgl_pinjam; ?>
                                    </td>
                                    <td
                                        class="py-2.5 px-3 border border-slate-300 text-center font-bold uppercase text-[8px] tracking-wide">
                                        <?= htmlspecialchars($row['status_peminjaman']); ?>
                                    </td>
                                    <td
                                        class="py-2.5 px-3 border border-slate-300 text-center font-bold uppercase text-[8px] tracking-wide">
                                        <?= htmlspecialchars($row['status']); ?>
                                    </td>
                                    <td class="py-2.5 px-3 border border-slate-300 text-center text-slate-500">
                                        <?= $tgl_kembali; ?>
                                    </td>
                                    <td class="py-2.5 px-2 border border-slate-300 text-center uppercase font-bold text-[8px]">
                                        <?php if ($row['kondisi'] === 'rusak'): ?>
                                            <span
                                                class="text-red-600 font-extrabold"><?= htmlspecialchars($row['kondisi']); ?></span>
                                        <?php else: ?>
                                            <span class="text-slate-700"><?= htmlspecialchars($row['kondisi']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='9' class='py-10 border border-slate-300 text-center text-slate-400 font-bold bg-slate-50/50'>Tidak ada log data peminjaman perangkat yang memenuhi kriteria filter saat ini.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-12 flex justify-end pt-8">
                <div class="text-center w-60 text-[11px] font-medium">
                    <p>Kupang, <?= date('d F Y'); ?></p>
                    <p class="font-bold uppercase mt-0.5">Kepala Lab Perangkat</p>
                    <div class="h-20"></div>
                    <p class="font-bold border-b border-slate-800 pb-0.5 text-slate-900">___________________________</p>
                    <p class="text-slate-400 text-[9px] font-bold mt-1 uppercase tracking-wide">Petugas Inventaris /
                        Guru</p>
                </div>
            </div>

        </div>

    </div>

</body>

</html>