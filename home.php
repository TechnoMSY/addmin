<?php
if (!isset($koneksi)) {
    include 'koneksi.php';
}

// 1. Hitungan data statistik dinamis dari database
$total_barang_query = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM barang");
$total_barang = mysqli_fetch_assoc($total_barang_query)['total'] ?? 0;

// Ambil JUMLAH TOTAL unit yang berstatus baik
$jumlah_baik_query = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM barang WHERE status='baik'");
$jumlah_baik = mysqli_fetch_assoc($jumlah_baik_query)['total'] ?? 0;

$kondisi_baik_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang WHERE status='baik'");
$kondisi_baik = mysqli_fetch_assoc($kondisi_baik_query)['total'] ?? 0;

$kondisi_rusak_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang WHERE status='rusak'");
$kondisi_rusak = mysqli_fetch_assoc($kondisi_rusak_query)['total'] ?? 0;

$total_kategori_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM lab_kategori");
$total_kategori = mysqli_fetch_assoc($total_kategori_query)['total'] ?? 0;

$sedang_dipinjam_query = mysqli_query($koneksi, "SELECT SUM(jumlah_pinjam) as total FROM peminjaman WHERE status_peminjaman='disetujui' AND status IN ('dipinjam', 'terlambat')");
$sedang_dipinjam = mysqli_fetch_assoc($sedang_dipinjam_query)['total'] ?? 0;

$total_lab_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM lab_komputer");
$total_lab = mysqli_fetch_assoc($total_lab_query)['total'] ?? 0;

// LOGIKA HITUNG PERSENTASE KESEHATAN BARANG
$persen_kesehatan = 0;
if ($total_barang > 0) {
    $persen_kesehatan = round(($jumlah_baik / $total_barang) * 100);
}
?>


<div class="w-full space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-text-dark">Ringkasan Inventaris</h2>
            <p class="text-xs text-text-muted mt-0.5">Status terkini dari ekosistem laboratorium komputer Anda secara
                real-time.</p>
        </div>
        <div class="flex items-center gap-2 self-end sm:self-auto">

            <a href="index.php?page=barang"
                class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition-colors flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-sm">add</span>
                Permintaan Baru
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

        <div
            class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-[0_2px_8px_-3px_rgba(0,0,0,0.02)] flex items-start justify-between">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-text-muted tracking-wider uppercase">Total Unit Barang</span>
                <h3 class="text-2xl font-extrabold text-text-dark tracking-tight">
                    <?php echo number_format($total_barang); ?>
                </h3>
            </div>
            <div class="w-7 h-7 bg-blue-50 text-brand-blue rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-base">devices</span>
            </div>
        </div>

        <div
            class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-[0_2px_8px_-3px_rgba(0,0,0,0.02)] flex items-start justify-between">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-text-muted tracking-wider uppercase">Total Kategori</span>
                <h3 class="text-2xl font-extrabold text-text-dark tracking-tight"><?php echo $total_kategori; ?></h3>
            </div>
            <div class="w-7 h-7 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-base">category</span>
            </div>
        </div>

        <div
            class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-[0_2px_8px_-3px_rgba(0,0,0,0.02)] flex items-start justify-between">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-emerald-600 tracking-wider uppercase">Kondisi Layak/Baik</span>
                <h3 class="text-2xl font-extrabold text-emerald-700 tracking-tight">
                    <?php echo number_format($kondisi_baik); ?> <span
                        class="text-xs font-normal text-text-muted">item</span>
                </h3>
            </div>
            <div class="w-7 h-7 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-base">check_circle</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/70 shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-[10px] font-bold uppercase text-amber-500 tracking-wider">Sirkulasi Aktif</span>
                <h3 class="text-2xl font-black text-text-dark tracking-tight"><?= (int) $sedang_dipinjam; ?> <span
                        class="text-xs font-semibold text-text-muted">Unit</span></h3>
                <p class="text-[11px] font-medium text-text-muted">Aset sedang dipinjam siswa</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 shrink-0">
                <span class="material-symbols-outlined text-2xl">pending_actions</span>
            </div>
        </div>

        <div
            class="bg-white p-5 rounded-2xl border border-slate-200/60 shadow-[0_2px_8px_-3px_rgba(0,0,0,0.02)] flex items-start justify-between">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-red-600 tracking-wider uppercase">Rusak/Malfungsi</span>
                <h3 class="text-2xl font-extrabold text-red-700 tracking-tight">
                    <?php echo number_format($kondisi_rusak); ?> <span
                        class="text-xs font-normal text-text-muted">item</span>
                </h3>
            </div>
            <div class="w-7 h-7 bg-red-50 text-red-500 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-base">report</span>
            </div>
        </div>
    </div>

    <div
        class="bg-white p-5 rounded-2xl border border-slate-200/70 shadow-sm flex items-center gap-4 flex-1 min-w-[200px]">
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-2xl">door_open</span>
        </div>
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Fasilitas Ruang</span>
            <h3 class="text-xl font-black text-slate-900 mt-0.5"><?= $total_lab; ?> <span
                    class="text-xs font-medium text-slate-400">Lab</span></h3>
            <p class="text-[11px] font-medium text-slate-400 mt-1">Sektor laboratorium aktif</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div
            class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/70 shadow-sm flex flex-col overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-text-dark">Barang Terbaru Masuk Sistem
                    </h3>
                </div>
                <a href="index.php?page=barang"
                    class="text-brand-blue hover:text-blue-700 text-xs font-bold flex items-center gap-1 transition-colors">
                    Lihat Semua <span class="material-symbols-outlined text-sm">arrow_right_alt</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="border-b border-slate-100 text-text-muted text-[11px] font-bold uppercase tracking-wider bg-slate-50/20">
                            <th class="p-4">Nama Barang</th>
                            <th class="p-4">Merek</th>
                            <th class="p-4 text-center">Jumlah</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4">Waktu Input</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php
                        $query_terbaru = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY id_barang DESC LIMIT 5");
                        if (mysqli_num_rows($query_terbaru) > 0) {
                            while ($barang = mysqli_fetch_assoc($query_terbaru)) {
                                $status_badge = "bg-emerald-50 text-emerald-700 border-emerald-100";
                                if ($barang['status'] == 'rusak') {
                                    $status_badge = "bg-red-50 text-red-700 border-red-100";
                                } elseif ($barang['status'] == 'sedang_perbaikan') {
                                    $status_badge = "bg-amber-50 text-amber-700 border-amber-100";
                                }
                                $waktu = !empty($barang['waktu_input']) ? date('d M Y - H:i', strtotime($barang['waktu_input'])) : 'Barang Lama';
                                ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4">
                                        <div class="font-bold text-text-dark"><?php echo htmlspecialchars($barang['nama']); ?>
                                        </div>
                                        <div class="text-[10px] text-text-muted mt-0.5">ID: #<?php echo $barang['id_barang']; ?>
                                        </div>
                                    </td>
                                    <td class="p-4 text-text-muted"><?php echo htmlspecialchars($barang['merek'] ?? '-'); ?>
                                    </td>
                                    <td class="p-4 text-center font-bold text-text-dark">
                                        <?php echo number_format($barang['jumlah']); ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span
                                            class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide border <?php echo $status_badge; ?>">
                                            <?php echo str_replace('_', ' ', $barang['status']); ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-text-muted font-medium text-[11px]">
                                        <?php echo $waktu; ?> WITA
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="5" class="p-8 text-center text-text-muted italic">Belum ada data barang di database.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            class="bg-white p-5 rounded-2xl border border-slate-20/70 shadow-sm flex items-center justify-between flex-1 gap-4">
            <div class="space-y-3.5 flex-1">
                <div>
                    <span class="text-[9px] font-bold uppercase text-blue-600 tracking-wider">Kondisi Sistem</span>
                    <h4 class="text-sm font-bold text-text-dark mt-1">Kesehatan Aset Lab</h4>
                    <p class="text-xs text-text-muted mt-1 leading-relaxed">Kondisi kelayakan barang operasional saat
                        ini berada di angka <?= $persen_kesehatan; ?>%.</p>
                </div>
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-blue-600 h-full rounded-full transition-all duration-500"
                        style="width: <?= $persen_kesehatan; ?>%"></div>
                </div>
            </div>
            <div
                class="w-16 h-16 rounded-full border-4 border-blue-50 flex items-center justify-center shrink-0 bg-blue-50/30">
                <span class="text-sm font-extrabold text-brand-blue"><?= $persen_kesehatan; ?>%</span>
            </div>
        </div>


    </div>

</div>
</div>