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
            <button class="bg-secondary text-on-secondary px-lg py-2 rounded-lg font-label-md text-label-md flex items-center gap-2 hover:opacity-90 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Permintaan Baru
            </button>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-lg mb-xl">
        <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-[0_2px_4px_rgba(30,41,59,0.05)] hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-md">
                <div class="w-12 h-12 bg-secondary/10 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">devices</span>
                </div>
                <span class="text-on-secondary-container bg-secondary-fixed/30 px-3 py-1 rounded-full text-[10px] font-bold tracking-tight uppercase">Sistem</span>
            </div>
            <p class="font-label-md text-label-md text-on-surface-variant mb-xs">Total Barang</p>
            <h3 class="font-display-lg text-display-lg text-primary"><?= mysqli_num_rows(mysqli_query($koneksi, 'SELECT * FROM barang')); ?></h3>
            <p class="font-body-md text-body-md text-on-secondary-container mt-sm flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">trending_up</span>
                +4% bulan ini
            </p>
        </div>

        <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-[0_2px_4px_rgba(30,41,59,0.05)] hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-md">
                <div class="w-12 h-12 bg-error/10 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">broken_image</span>
                </div>
                <span class="text-error bg-error-container/30 px-3 py-1 rounded-full text-[10px] font-bold tracking-tight uppercase">Kritis</span>
            </div>
            <p class="font-label-md text-label-md text-on-surface-variant mb-xs">Barang Rusak</p>
            <h3 class="font-display-lg text-display-lg text-primary"><?= mysqli_num_rows(mysqli_query($koneksi, 'SELECT * FROM barang WHERE status = "rusak"')); ?></h3>
            <p class="font-body-md text-body-md text-error mt-sm flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">priority_high</span>
                <?= mysqli_num_rows(mysqli_query($koneksi, 'SELECT * FROM barang WHERE status = "rusak"')); ?> butuh perbaikan segera
            </p>
        </div>

        <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-[0_2px_4px_rgba(30,41,59,0.05)] hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-md">
                <div class="w-12 h-12 bg-tertiary-fixed/30 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-tertiary" style="font-variation-settings: 'FILL' 1;">history</span>
                </div>
                <span class="text-tertiary bg-tertiary-fixed/50 px-3 py-1 rounded-full text-[10px] font-bold tracking-tight uppercase">Aktif</span>
            </div>
            <p class="font-label-md text-label-md text-on-surface-variant mb-xs">Peminjaman</p>
            <h3 class="font-display-lg text-display-lg text-primary"><?= mysqli_num_rows(mysqli_query($koneksi, 'SELECT * FROM peminjaman')); ?></h3>
            <p class="font-body-md text-body-md text-on-tertiary-fixed-variant mt-sm flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">schedule</span>
                Rata-rata kembali 3 hari
            </p>
        </div>

        <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-[0_2px_4px_rgba(30,41,59,0.05)] hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-md">
                <div class="w-12 h-12 bg-tertiary-fixed/30 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-tertiary" style="font-variation-settings: 'FILL' 1;">grid_view</span>
                </div>
                <span class="text-tertiary bg-tertiary-fixed/50 px-3 py-1 rounded-full text-[10px] font-bold tracking-tight uppercase">Kelompok</span>
            </div>
            <p class="font-label-md text-label-md text-on-surface-variant mb-xs">Kategori</p>
            <h3 class="font-display-lg text-display-lg text-primary"><?= mysqli_num_rows(mysqli_query($koneksi, 'SELECT * FROM lab_kategori')); ?></h3>
            <p class="font-body-md text-body-md text-on-tertiary-fixed-variant mt-sm flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">layers</span>
                Klasifikasi rumpun PC
            </p>
        </div>

        <div class="bg-white p-lg rounded-xl border border-outline-variant shadow-[0_2px_4px_rgba(30,41,59,0.05)] hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-md">
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </div>
                <span class="text-primary bg-primary-fixed/50 px-3 py-1 rounded-full text-[10px] font-bold tracking-tight uppercase">Operasional</span>
            </div>
            <p class="font-label-md text-label-md text-on-surface-variant mb-xs">Barang Baik</p>
            <h3 class="font-display-lg text-display-lg text-primary"><?= mysqli_num_rows(mysqli_query($koneksi, 'SELECT * FROM barang WHERE status = "baik"')); ?></h3>
            <p class="font-body-md text-body-md text-on-primary-fixed-variant mt-sm flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">verified</span>
                Semua sistem normal
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
        <div class="lg:col-span-2 bg-white rounded-xl border border-outline-variant shadow-[0_2px_4px_rgba(30,41,59,0.05)] p-lg">
            <div class="flex items-center justify-between mb-xl">
                <div>
                    <h4 class="font-headline-md text-headline-md text-primary">Sirkulasi & Trafik Lab</h4>
                    <p class="font-body-md text-body-md text-on-surface-variant">Frekuensi penggunaan komputer unit vs sirkulasi peminjaman alat harian</p>
                </div>
                <div class="flex bg-surface-container-low p-1 rounded-lg">
                    <button class="px-4 py-1 text-label-md font-label-md bg-white text-primary rounded-md shadow-sm">Komputer</button>
                    <button class="px-4 py-1 text-label-md font-label-md text-on-surface-variant hover:text-primary transition-colors">Periferal</button>
                </div>
            </div>
            <div class="h-64 flex items-end justify-between gap-4 px-4 pb-4 mt-8 border-b border-surface-container-high">
                <div class="flex-1 flex flex-col items-center gap-2">
                    <div class="w-full bg-secondary-fixed/40 rounded-t-sm h-[40%] relative group">
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-primary text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">25 Unit</div>
                    </div>
                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-tighter">Sen</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2">
                    <div class="w-full bg-secondary rounded-t-sm h-[85%] relative group">
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-primary text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">42 Unit</div>
                    </div>
                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-tighter">Sel</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2">
                    <div class="w-full bg-secondary-fixed/40 rounded-t-sm h-[55%] relative group"></div>
                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-tighter">Rab</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2">
                    <div class="w-full bg-secondary rounded-t-sm h-[70%] relative group"></div>
                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-tighter">Kam</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2">
                    <div class="w-full bg-secondary-fixed/40 rounded-t-sm h-[60%] relative group"></div>
                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-tighter">Jum</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2">
                    <div class="w-full bg-secondary-fixed/40 rounded-t-sm h-[20%] relative group"></div>
                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-tighter">Sab</span>
                </div>
                <div class="flex-1 flex flex-col items-center gap-2">
                    <div class="w-full bg-secondary rounded-t-sm h-[10%] relative group"></div>
                    <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-tighter">Min</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-outline-variant shadow-[0_2px_4px_rgba(30,41,59,0.05)] flex flex-col">
            <div class="p-lg border-b border-surface-container-high">
                <h4 class="font-headline-md text-headline-md text-primary">Barang Terbaru</h4>
                <p class="font-body-md text-body-md text-on-surface-variant">Pengadaan atau registrasi perangkat minggu ini</p>
            </div>
            <div class="flex-1 overflow-auto">
                <table class="w-full text-left">
                    <thead class="bg-surface-container-low">
                        <tr>
                            <th class="px-lg py-3 font-label-md text-label-md text-on-surface-variant">PERANGKAT</th>
                            <th class="px-lg py-3 font-label-md text-label-md text-on-surface-variant">MEREK</th>
                            <th class="px-lg py-3 font-label-md text-label-md text-on-surface-variant text-right">JML</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high">
                        <tr class="hover:bg-surface-container-low/50 transition-colors cursor-pointer">
                            <td class="px-lg py-4">
                                <p class="font-body-md text-body-md text-primary font-semibold">Router Board</p>
                                <p class="text-[11px] text-on-surface-variant">MikroTik RB951</p>
                            </td>
                            <td class="px-lg py-4 font-body-md text-body-md text-on-surface-variant">MikroTik</td>
                            <td class="px-lg py-4 font-body-md text-body-md text-primary text-right font-semibold">5</td>
                        </tr>
                        <tr class="hover:bg-surface-container-low/50 transition-colors cursor-pointer">
                            <td class="px-lg py-4">
                                <p class="font-body-md text-body-md text-primary font-semibold">Monitor LED 24"</p>
                                <p class="text-[11px] text-on-surface-variant">IPS Panel FHD</p>
                            </td>
                            <td class="px-lg py-4 font-body-md text-body-md text-on-surface-variant">LG</td>
                            <td class="px-lg py-4 font-body-md text-body-md text-primary text-right font-semibold">20</td>
                        </tr>
                        <tr class="hover:bg-surface-container-low/50 transition-colors cursor-pointer">
                            <td class="px-lg py-4">
                                <p class="font-body-md text-body-md text-primary font-semibold">Switch Hub</p>
                                <p class="text-[11px] text-on-surface-variant">24-Port Gigabit</p>
                            </td>
                            <td class="px-lg py-4 font-body-md text-body-md text-on-surface-variant">Cisco</td>
                            <td class="px-lg py-4 font-body-md text-body-md text-primary text-right font-semibold">2</td>
                        </tr>
                        <tr class="hover:bg-surface-container-low/50 transition-colors cursor-pointer">
                            <td class="px-lg py-4">
                                <p class="font-body-md text-body-md text-primary font-semibold">Keyboard & Mouse</p>
                                <p class="text-[11px] text-on-surface-variant">Combo USB Wired</p>
                            </td>
                            <td class="px-lg py-4 font-body-md text-body-md text-on-surface-variant">Logitech</td>
                            <td class="px-lg py-4 font-body-md text-body-md text-primary text-right font-semibold">15</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-surface-container-high">
                <a class="text-secondary font-label-md text-label-md flex items-center justify-center gap-1 hover:underline" href="index.php?page=barang">
                    Lihat Semua Inventaris <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>

    <div class="mt-xl grid grid-cols-1 md:grid-cols-2 gap-lg">
        <div class="bg-surface-container-lowest p-xl rounded-xl border border-outline-variant flex items-center gap-lg">
            <div class="flex-1">
                <h4 class="font-headline-md text-headline-md text-primary mb-xs">Kesehatan Inventaris</h4>
                <p class="font-body-md text-body-md text-on-surface-variant mb-md">Kondisi kelayakan barang operasional laboratorium saat ini berada di angka 68%. Penggantian komponen dianjurkan untuk periferal kritis.</p>
                <div class="w-full bg-surface-container-high h-2 rounded-full overflow-hidden">
                    <div class="bg-secondary h-full rounded-full w-[68%]"></div>
                </div>
            </div>
            <div class="w-24 h-24 rounded-full border-4 border-secondary/20 flex items-center justify-center relative">
                <span class="font-headline-md text-headline-md text-secondary">68%</span>
            </div>
        </div>
        <div class="bg-primary p-xl rounded-xl border border-primary-container flex items-center justify-between text-white overflow-hidden relative">
            <div class="relative z-10">
                <h4 class="font-headline-md text-headline-md mb-xs">Audit Lab Mendatang</h4>
                <p class="font-body-md text-body-md text-on-primary-container/80 mb-md max-w-xs">Pemeriksaan dan keselarasan unit PC Lab Utama dijadwalkan pada hari Jumat ini.</p>
                <button class="bg-secondary text-on-secondary px-lg py-2 rounded-lg font-label-md text-label-md hover:opacity-90 transition-all">
                    Siapkan Dokumentasi
                </button>
            </div>
            <span class="material-symbols-outlined text-[120px] text-white/5 absolute -right-4 -bottom-4 select-none">verified_user</span>
        </div>
    </div>
</div>