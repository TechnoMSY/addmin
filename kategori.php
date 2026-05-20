<?php
// ==========================================
// LOGIKA UTAMA HALAMAN KATEGORI (UPDATED ID_KATEGORI)
// ==========================================
if (!isset($koneksi)) {
    include 'koneksi.php';
}

$pesan_sukses = "";
$pesan_gagal  = "";

// 1. PROSES LOGIKA TAMBAH DATA
if (isset($_POST['tambah_kategori'])) {
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    if (!empty($nama_kategori)) {
        $insert = mysqli_query($koneksi, "INSERT INTO lab_kategori (nama_kategori) VALUES ('$nama_kategori')");
        if ($insert) {
            $pesan_sukses = "Kategori \"<strong>" . htmlspecialchars($nama_kategori) . "</strong>\" berhasil ditambahkan!";
        } else {
            $pesan_gagal = "Gagal menyimpan data: " . mysqli_error($koneksi);
        }
    } else {
        $pesan_gagal = "Nama kategori tidak boleh kosong!";
    }
}

// 2. PROSES LOGIKA EDIT / UPDATE DATA
if (isset($_POST['edit_kategori'])) {
    $id_kategori   = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);

    if (!empty($id_kategori) && !empty($nama_kategori)) {
        // PERUBAHAN: Klausa WHERE sekarang menggunakan id_kategori
        $update = mysqli_query($koneksi, "UPDATE lab_kategori SET nama_kategori = '$nama_kategori' WHERE id_kategori = '$id_kategori'");
        if ($update) {
            $pesan_sukses = "Kategori berhasil diperbarui menjadi \"<strong>" . htmlspecialchars($nama_kategori) . "</strong>\"!";
        } else {
            $pesan_gagal = "Gagal memperbarui data: " . mysqli_error($koneksi);
        }
    } else {
        $pesan_gagal = "Data tidak valid atau nama kategori kosong!";
    }
}

// 3. PROSES LOGIKA HAPUS DATA
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // PERUBAHAN: Pencarian nama dan penghapusan sekarang menggunakan id_kategori
    $cek_nama  = mysqli_query($koneksi, "SELECT nama_kategori FROM lab_kategori WHERE id_kategori = '$id_hapus'");
    $data_nama = mysqli_fetch_assoc($cek_nama);
    
    if ($data_nama) {
        $nama_lama = $data_nama['nama_kategori'];
        $delete    = mysqli_query($koneksi, "DELETE FROM lab_kategori WHERE id_kategori = '$id_hapus'");
        
        if ($delete) {
            $pesan_sukses = "Kategori \"<strong>" . htmlspecialchars($nama_lama) . "</strong>\" berhasil dihapus!";
        } else {
            $pesan_gagal = "Gagal menghapus data: " . mysqli_error($koneksi);
        }
    }
}

// 4. AMBIL DATA KATEGORI UNTUK TABEL (Urut berdasarkan id_kategori terbaru)
$query_kategori = mysqli_query($koneksi, "SELECT * FROM lab_kategori ORDER BY id_kategori DESC");
?>

<div class="mt-16 p-xl max-w-container-max mx-auto w-full">
    
    <?php if (!empty($pesan_sukses)): ?>
        <div id="alert-notif" class="mb-lg p-md bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-[24px] text-emerald-600">check_circle</span>
                <span class="font-body-md text-sm"><?php echo $pesan_sukses; ?></span>
            </div>
            <button onclick="document.getElementById('alert-notif').remove()" class="text-emerald-500 hover:text-emerald-800 transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (!empty($pesan_gagal)): ?>
        <div id="alert-notif" class="mb-lg p-md bg-red-50 border border-red-200 text-red-700 rounded-xl flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-[24px] text-red-600">error</span>
                <span class="font-body-md text-sm"><?php echo $pesan_gagal; ?></span>
            </div>
            <button onclick="document.getElementById('alert-notif').remove()" class="text-red-500 hover:text-red-800 transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-end mb-xl">
        <div>
            <h1 class="font-display-lg text-display-lg text-primary mb-xs">Manajemen Kategori</h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant">Kelola rumpun dan klasifikasi kelompok alat/barang laboratorium.</p>
        </div>
        <div class="flex gap-md">
            <button onclick="bukaModalTambah()" class="bg-secondary text-on-secondary px-lg py-2 rounded-lg font-label-md text-label-md flex items-center gap-2 hover:opacity-90 transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Tambah Kategori
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-surface-container-high overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-surface-container-high">
                        <th class="p-lg font-label-md text-label-md text-primary w-24 text-center">No</th>
                        <th class="p-lg font-label-md text-label-md text-primary">Nama Kategori</th>
                        <th class="p-lg font-label-md text-label-md text-primary w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high">
                    <?php 
                    $no = 1;
                    if (mysqli_num_rows($query_kategori) > 0) {
                        while ($row = mysqli_fetch_assoc($query_kategori)) { 
                    ?>
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="p-lg font-body-md text-body-md text-on-surface text-center"><?php echo $no++; ?></td>
                            <td class="p-lg font-body-md text-body-md font-semibold text-primary">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-on-surface-variant text-[20px]">grid_view</span>
                                    <?php echo htmlspecialchars($row['nama_kategori']); ?>
                                </div>
                            </td>
                            <td class="p-lg text-center">
                                <div class="flex items-center justify-center gap-sm">
                                    <button onclick="bukaModalEdit('<?php echo $row['id_kategori']; ?>', '<?php echo htmlspecialchars($row['nama_kategori'], ENT_QUOTES); ?>')" 
                                            class="text-primary hover:bg-surface-container p-1.5 rounded-lg transition-colors" title="Ubah Kategori">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <a href="index.php?page=kategori&hapus=<?php echo $row['id_kategori']; ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')"
                                       class="text-error hover:bg-error/10 p-1.5 rounded-lg transition-colors inline-block" title="Hapus Kategori">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else { 
                    ?>
                        <tr>
                            <td colspan="3" class="p-xl text-center">
                                <div class="flex flex-col items-center justify-center gap-xs text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[48px] opacity-40">folder_open</span>
                                    <p class="font-body-lg font-medium">Belum ada data kategori</p>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalTambah" class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300">
    <div class="bg-white rounded-2xl w-full max-w-md border border-surface-container-high shadow-2xl transform scale-95 transition-all duration-300 overflow-hidden">
        <div class="p-xl border-b border-surface-container-high bg-surface-container-low flex justify-between items-center">
            <div class="flex items-center gap-2 text-primary">
                <span class="material-symbols-outlined text-secondary">add_box</span>
                <h3 class="font-headline-md text-headline-md font-bold">Kategori Baru</h3>
            </div>
            <button onclick="tutupModal('modalTambah')" class="text-on-surface-variant hover:bg-surface-container w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="" method="POST" class="p-xl space-y-md">
            <div>
                <label for="nama_kategori" class="block font-label-md text-label-md text-primary mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                <input type="text" id="nama_kategori" name="nama_kategori" required autocomplete="off" placeholder="Contoh: Perangkat Jaringan"
                       class="w-full px-4 py-2 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all" />
            </div>
            <div class="flex justify-end gap-sm pt-md border-t border-surface-container-high">
                <button type="button" onclick="tutupModal('modalTambah')" class="px-lg py-2 border border-outline-variant rounded-lg font-label-md text-label-md text-on-surface-variant hover:bg-surface-container-low transition-colors">Batal</button>
                <button type="submit" name="tambah_kategori" class="px-lg py-2 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:opacity-90 shadow-sm transition-all">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300">
    <div class="bg-white rounded-2xl w-full max-w-md border border-surface-container-high shadow-2xl transform scale-95 transition-all duration-300 overflow-hidden">
        <div class="p-xl border-b border-surface-container-high bg-surface-container-low flex justify-between items-center">
            <div class="flex items-center gap-2 text-primary">
                <span class="material-symbols-outlined text-secondary">edit_note</span>
                <h3 class="font-headline-md text-headline-md font-bold">Ubah Nama Kategori</h3>
            </div>
            <button onclick="tutupModal('modalEdit')" class="text-on-surface-variant hover:bg-surface-container w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="" method="POST" class="p-xl space-y-md">
            <input type="hidden" id="edit_id_kategori" name="id_kategori" />
            <div>
                <label for="edit_nama_kategori" class="block font-label-md text-label-md text-primary mb-1.5">Nama Kategori Baru <span class="text-red-500">*</span></label>
                <input type="text" id="edit_nama_kategori" name="nama_kategori" required autocomplete="off"
                       class="w-full px-4 py-2 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all" />
            </div>
            <div class="flex justify-end gap-sm pt-md border-t border-surface-container-high">
                <button type="button" onclick="tutupModal('modalEdit')" class="px-lg py-2 border border-outline-variant rounded-lg font-label-md text-label-md text-on-surface-variant hover:bg-surface-container-low transition-colors">Batal</button>
                <button type="submit" name="edit_kategori" class="px-lg py-2 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:opacity-90 shadow-sm transition-all">Perbarui Data</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Fungsi Manajemen Buka/Tutup Modal Umum
    function bukaModal(idModal) {
        const m = document.getElementById(idModal);
        const content = m.querySelector('.transform');
        m.classList.remove('opacity-0', 'pointer-events-none');
        m.classList.add('opacity-100');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }

    function tutupModal(idModal) {
        const m = document.getElementById(idModal);
        const content = m.querySelector('.transform');
        m.classList.remove('opacity-100');
        m.classList.add('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        m.querySelector('form').reset();
    }

    // Spesifik Pemicu Modal Tambah
    function bukaModalTambah() {
        bukaModal('modalTambah');
        setTimeout(() => document.getElementById('nama_kategori').focus(), 100);
    }

    // Spesifik Pemicu Modal Edit (Membawa Data Baris Tabel ke dalam Input Modal)
    function bukaModalEdit(id_kategori, nama) {
        document.getElementById('edit_id_kategori').value = id_kategori;
        document.getElementById('edit_nama_kategori').value = nama;
        bukaModal('modalEdit');
        setTimeout(() => document.getElementById('edit_nama_kategori').focus(), 100);
    }

    // Penutup modal otomatis klik area luar overlay
    ['modalTambah', 'modalEdit'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) tutupModal(id);
        });
    });

    // Auto dismiss Notifikasi setelah 4 detik
    setTimeout(() => {
        const alertBox = document.getElementById('alert-notif');
        if(alertBox) {
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 300);
        }
    }, 4000);
</script>