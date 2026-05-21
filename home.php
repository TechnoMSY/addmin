<?php
if (!isset($koneksi)) {
    include 'koneksi.php';
}

// 1. Ambil hitungan total data untuk counter box di atas (Dinamis dari DB)
$total_barang_query = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM barang");
$total_barang = mysqli_fetch_assoc($total_barang_query)['total'] ?? 0;

$total_jenis_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang");
$total_jenis = mysqli_fetch_assoc($total_jenis_query)['total'] ?? 0;

$kondisi_baik_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang WHERE status='baik'");
$kondisi_baik = mysqli_fetch_assoc($kondisi_baik_query)['total'] ?? 0;

$kondisi_rusak_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang WHERE status='rusak'");
$kondisi_rusak = mysqli_fetch_assoc($kondisi_rusak_query)['total'] ?? 0;
?>

<div class="mt-16 p-xl max-w-container-max mx-auto w-full">
    <div class="flex justify-between items-end mb-xl">
        <div>
            <h1 class="font-display-lg text-display-lg text-primary mb-xs">Ringkasan Inventaris</h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant">Status terkini dari ekosistem laboratorium komputer Anda secara real-time.</p>
        </div>
        <div class="flex gap-md">
            <button class="bg-white border border-outline-variant text-primary px-lg py-2 rounded-lg font-label-md text-label-md flex items-center gap-2 hover:bg-surface-container-low transition-all">
                <span class="material-symbols-outlined text-[18px]">download</span>
                Unduh Laporan
            </button>
            <a href="index.php?page=barang" class="bg-secondary text-on-secondary px-lg py-2 rounded-lg font-label-md text-label-md flex items-center gap-2 hover:opacity-90 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Permintaan Baru
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-lg mb-xl">
        <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-[0_1px_3px_rgba(0,0,0,0,05)]">
            <div class="flex justify-between items-start mb-md">
                <span class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">Total Unit Barang</span>
                <span class="material-symbols-outlined text-secondary">devices</span>
            </div>
            <h2 class="font-display-lg text-display-lg text-primary font-bold"><?php echo number_format($total_barang); ?></h2>
        </div>
        
        <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-[0_1px_3px_rgba(0,0,0,0,05)]">
            <div class="flex justify-between items-start mb-md">
                <span class="text-on-surface-variant font-label-md text-label-md uppercase tracking-wider">Total Kategori</span>
                <span class="material-symbols-outlined text-secondary">category</span>
            </div>
            <h2 class="font-display-lg text-display-lg text-primary font-bold"><?= mysqli_num_rows(mysqli_query($koneksi, 'SELECT * FROM lab_kategori')); ?></h2>
        </div>

        <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-[0_1px_3px_rgba(0,0,0,0,05)]">
            <div class="flex justify-between items-start mb-md">
                <span class="text-emerald-600 font-label-md text-label-md uppercase tracking-wider">Kondisi Layak/Baik</span>
                <span class="material-symbols-outlined text-emerald-500">check_circle</span>
            </div>
            <h2 class="font-display-lg text-display-lg text-emerald-700 font-bold"><?php echo number_format($kondisi_baik); ?> <span class="text-xs font-normal text-on-surface-variant">item</span></h2>
        </div>

        <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-[0_1px_3px_rgba(0,0,0,0,05)]">
            <div class="flex justify-between items-start mb-md">
                <span class="text-amber-600 font-label-md text-label-md uppercase tracking-wider">Sedang Dipinjam</span>
                <span class="material-symbols-outlined text-amber-500">sync_alt</span>
            </div>
            <h2 class="font-display-lg text-display-lg text-amber-700 font-bold">0 <span class="text-xs font-normal text-on-surface-variant">unit</span></h2>
        </div>

        <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-[0_1px_3px_rgba(0,0,0,0,05)]">
            <div class="flex justify-between items-start mb-md">
                <span class="text-red-600 font-label-md text-label-md uppercase tracking-wider">Rusak/Malfungsi</span>
                <span class="material-symbols-outlined text-red-500">report</span>
            </div>
            <h2 class="font-display-lg text-display-lg text-red-700 font-bold"><?php echo number_format($kondisi_rusak); ?> <span class="text-xs font-normal text-on-surface-variant">item</span></h2>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
        
        <div class="lg:col-span-2 bg-white rounded-xl border border-outline-variant shadow-[0_1px_3px_rgba(0,0,0,0,05)] flex flex-col overflow-hidden">
            <div class="p-lg border-b border-outline-variant flex justify-between items-center bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary animate-pulse">new_releases</span>
                    <h3 class="font-headline-md text-headline-md text-primary font-bold">Barang Terbaru Masuk Sistem</h3>
                </div>
                <a href="index.php?page=barang" class="text-secondary hover:underline text-sm font-semibold flex items-center gap-1">
                    Lihat Semua <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100/70 border-b border-outline-variant">
                            <th class="p-md text-xs font-bold text-on-surface-variant uppercase tracking-wider">Nama Barang</th>
                            <th class="p-md text-xs font-bold text-on-surface-variant uppercase tracking-wider">Merek</th>
                            <th class="p-md text-xs font-bold text-on-surface-variant uppercase tracking-wider text-center">Jumlah</th>
                            <th class="p-md text-xs font-bold text-on-surface-variant uppercase tracking-wider text-center">Status</th>
                            <th class="p-md text-xs font-bold text-on-surface-variant uppercase tracking-wider">Waktu Input</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        <?php
                        // Mengambil 5 barang terbaru berdasarkan id_barang secara descending
                        $query_terbaru = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY id_barang DESC LIMIT 5");
                        
                        if (mysqli_num_rows($query_terbaru) > 0) {
                            while ($barang = mysqli_fetch_assoc($query_terbaru)) {
                                // Pengondisian warna label status
                                $status_badge = "bg-emerald-100 text-emerald-800 border-emerald-200";
                                if ($barang['status'] == 'rusak') {
                                    $status_badge = "bg-red-100 text-red-800 border-red-200";
                                } elseif ($barang['status'] == 'sedang_perbaikan') {
                                    $status_badge = "bg-amber-100 text-amber-800 border-amber-200";
                                }
                                
                                // Format konversi waktu agar rapi
                                $waktu = !empty($barang['waktu_input']) ? date('d M Y - H:i', strtotime($barang['waktu_input'])) : 'Barang Lama';
                        ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="p-md">
                                        <div class="font-semibold text-primary"><?php echo htmlspecialchars($barang['nama']); ?></div>
                                        <div class="text-xs text-on-surface-variant">ID: #<?php echo $barang['id_barang']; ?></div>
                                    </td>
                                    <td class="p-md text-sm text-on-surface-variant">
                                        <?php echo htmlspecialchars($barang['merek'] ?? '-'); ?>
                                    </td>
                                    <td class="p-md text-sm text-center font-bold text-primary">
                                        <?php echo number_format($barang['jumlah']); ?>
                                    </td>
                                    <td class="p-md text-center">
                                        <span class="inline-block px-2 py-1 rounded-md text-xs font-semibold border <?php echo $status_badge; ?>">
                                            <?php echo str_replace('_', ' ', $barang['status']); ?>
                                        </span>
                                    </td>
                                    <td class="p-md text-xs text-slate-500 font-mono">
                                        <div class="flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm text-slate-400">history</span>
                                            <?php echo $waktu; ?> WITA
                                        </div>
                                    </td>
                                </tr>
                        <?php 
                            }
                        } else {
                            echo '<tr><td colspan="5" class="p-xl text-center text-sm text-on-surface-variant">Belum ada data barang di dalam database.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-col gap-lg">
            <div class="bg-white p-xl rounded-xl border border-outline-variant shadow-[0_1px_3px_rgba(0,0,0,0,05)] flex items-center justify-between">
                <div>
                    <h4 class="font-headline-md text-headline-md text-primary mb-xs font-bold">Kesehatan Sistem</h4>
                    <p class="font-body-md text-body-md text-on-surface-variant mb-md">Kondisi kelayakan barang operasional laboratorium saat ini berada di angka 68%.</p>
                    <div class="w-full bg-surface-container-high h-2 rounded-full overflow-hidden">
                        <div class="bg-secondary h-full rounded-full w-[68%]"></div>
                    </div>
                </div>
                <div class="w-24 h-24 rounded-full border-4 border-secondary/20 flex items-center justify-center relative flex-shrink-0">
                    <span class="font-headline-md text-headline-md text-secondary font-bold">68%</span>
                </div>
            </div>
            
            <div class="bg-primary p-xl rounded-xl border border-primary-container flex items-center justify-between text-white overflow-hidden relative">
                <div class="relative z-10">
                    <h4 class="font-headline-md text-headline-md mb-xs font-bold">Audit Lab Mendatang</h4>
                    <p class="font-body-md text-body-md text-on-primary-container/80 mb-md max-w-xs">Pemeriksaan dan keselarasan unit PC Lab Utama dijadwalkan pada hari Jumat ini.</p>
                    <button class="bg-secondary text-on-secondary px-lg py-2 rounded-lg font-label-md text-label-md hover:opacity-90 transition-all font-bold">
                        Siapkan Dokumentasi
                    </button>
                </div>
                <span class="material-symbols-outlined text-[120px] text-white/5 absolute -right-4 -bottom-4 pointer-events-none select-none">assignment</span>
            </div>
        </div>

    </div>
</div>