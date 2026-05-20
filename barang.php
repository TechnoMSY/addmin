<?php
// ==========================================
// LOGIKA UTAMA HALAMAN BARANG (CRUD FULL SYSTEM)
// ==========================================
if (!isset($koneksi)) {
    include 'koneksi.php';
}

$pesan_sukses = "";
$pesan_gagal  = "";

// 1. PROSES LOGIKA: TAMBAH BARANG
if (isset($_POST['tambah_barang'])) {
    $nama        = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $merek       = mysqli_real_escape_string($koneksi, $_POST['merek']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $jumlah      = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $warna       = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $status      = mysqli_real_escape_string($koneksi, $_POST['status']);

    if (!empty($nama) && !empty($id_kategori)) {
        $insert = mysqli_query($koneksi, "INSERT INTO barang (id_kategori, nama, merek, jumlah, warna, status) VALUES ('$id_kategori', '$nama', '$merek', '$jumlah', '$warna', '$status')");
        if ($insert) {
            $pesan_sukses = "Perangkat \"<strong>" . htmlspecialchars($nama) . "</strong>\" berhasil didaftarkan ke inventaris!";
        } else {
            $pesan_gagal = "Gagal menyimpan data: " . mysqli_error($koneksi);
        }
    } else {
        $pesan_gagal = "Nama barang dan rumpun kategori wajib diisi!";
    }
}

// 2. PROSES LOGIKA: EDIT / UPDATE BARANG
if (isset($_POST['edit_barang'])) {
    $id_barang   = mysqli_real_escape_string($koneksi, $_POST['id_barang']);
    $nama        = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $merek       = mysqli_real_escape_string($koneksi, $_POST['merek']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $jumlah      = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $warna       = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $status      = mysqli_real_escape_string($koneksi, $_POST['status']);

    if (!empty($id_barang) && !empty($nama)) {
        $update = mysqli_query($koneksi, "UPDATE barang SET id_kategori = '$id_kategori', nama = '$nama', merek = '$merek', jumlah = '$jumlah', warna = '$warna', status = '$status' WHERE id_barang = '$id_barang'");
        if ($update) {
            $pesan_sukses = "Informasi perangkat \"<strong>" . htmlspecialchars($nama) . "</strong>\" berhasil diperbarui!";
        } else {
            $pesan_gagal = "Gagal memperbarui data: " . mysqli_error($koneksi);
        }
    }
}

// 3. PROSES LOGIKA: HAPUS BARANG
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    $cek_nama  = mysqli_query($koneksi, "SELECT nama FROM barang WHERE id_barang = '$id_hapus'");
    $data_nama = mysqli_fetch_assoc($cek_nama);
    
    if ($data_nama) {
        $nama_lama = $data_nama['nama'];
        $delete    = mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang = '$id_hapus'");
        if ($delete) {
            $pesan_sukses = "Perangkat \"<strong>" . htmlspecialchars($nama_lama) . "</strong>\" berhasil dihapus dari sistem!";
        } else {
            $pesan_gagal = "Gagal menghapus data: " . mysqli_error($koneksi);
        }
    }
}

// 4. QUERY AMBIL DATA UTAMA (LEFT JOIN SINKRON DENGAN lab.sql)
$query_barang = mysqli_query($koneksi, "SELECT barang.*, lab_kategori.nama_kategori 
                                        FROM barang 
                                        LEFT JOIN lab_kategori ON barang.id_kategori = lab_kategori.id_kategori 
                                        ORDER BY barang.id_barang DESC");

// 5. QUERY LIST KATEGORI UNTUK MODAL SELECTION
$list_kategori = mysqli_query($koneksi, "SELECT * FROM lab_kategori ORDER BY nama_kategori ASC");
$kategori_options = [];
if ($list_kategori) {
    while ($kat = mysqli_fetch_assoc($list_kategori)) {
        $kategori_options[] = $kat;
    }
}
?>

<div class="mt-16 p-xl max-w-container-max mx-auto w-full">
    
    <?php if (!empty($pesan_sukses)): ?>
        <div id="alert-notif" class="mb-lg p-md bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex justify-between items-center shadow-sm transition-all duration-300">
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
        <div id="alert-notif" class="mb-lg p-md bg-red-50 border border-red-200 text-red-700 rounded-xl flex justify-between items-center shadow-sm transition-all duration-300">
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
            <h1 class="font-display-lg text-display-lg text-primary mb-xs">Data Barang Inventaris</h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant">Manajemen daftar aset komputer, periferal jaringan, dan komponen laboratorium.</p>
        </div>
        <div>
            <button onclick="bukaModalTambah()" class="bg-secondary text-on-secondary px-lg py-2 rounded-lg font-label-md text-label-md flex items-center gap-2 hover:opacity-90 transition-all shadow-sm active:scale-95">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Registrasi Barang
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-surface-container-high overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-surface-container-high">
                        <th class="p-lg font-label-md text-label-md text-primary w-16 text-center">No</th>
                        <th class="p-lg font-label-md text-label-md text-primary">Nama Perangkat</th>
                        <th class="p-lg font-label-md text-label-md text-primary">Merek</th>
                        <th class="p-lg font-label-md text-label-md text-primary">Kategori Rumpun</th>
                        <th class="p-lg font-label-md text-label-md text-primary text-center w-24">Jumlah</th>
                        <th class="p-lg font-label-md text-label-md text-primary w-28">Warna</th>
                        <th class="p-lg font-label-md text-label-md text-primary w-32 text-center">Kondisi</th>
                        <th class="p-lg font-label-md text-label-md text-primary w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high">
                    <?php 
                    $no = 1;
                    if ($query_barang && mysqli_num_rows($query_barang) > 0) {
                        while ($row = mysqli_fetch_assoc($query_barang)) { 
                    ?>
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            <td class="p-lg font-body-md text-body-md text-on-surface text-center"><?php echo $no++; ?></td>
                            <td class="p-lg font-body-md text-body-md font-semibold text-primary">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-on-surface-variant text-[20px]">devices</span>
                                    <?php echo htmlspecialchars($row['nama']); ?>
                                </div>
                            </td>
                            <td class="p-lg font-body-md text-body-md text-on-surface-variant"><?php echo !empty($row['merek']) ? htmlspecialchars($row['merek']) : '-'; ?></td>
                            <td class="p-lg font-body-md text-body-md text-on-surface-variant">
                                <span class="bg-surface-container px-2.5 py-1 rounded-md text-xs font-medium text-primary">
                                    <?php echo !empty($row['nama_kategori']) ? htmlspecialchars($row['nama_kategori']) : 'Tanpa Kategori'; ?>
                                </span>
                            </td>
                            <td class="p-lg font-body-md text-body-md text-primary text-center font-bold"><?php echo htmlspecialchars($row['jumlah']); ?></td>
                            <td class="p-lg font-body-md text-body-md text-on-surface-variant"><?php echo !empty($row['warna']) ? htmlspecialchars($row['warna']) : '-'; ?></td>
                            <td class="p-lg text-center">
                                <?php if ($row['status'] == 'baik'): ?>
                                    <span class="text-emerald-600 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full text-xs font-semibold">Baik</span>
                                <?php elseif ($row['status'] == 'sedang_perbaikan'): ?>
                                    <span class="text-amber-600 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full text-xs font-semibold">Diperbaiki</span>
                                <?php else: ?>
                                    <span class="text-rose-600 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-full text-xs font-semibold">Rusak</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-lg text-center">
                                <div class="flex items-center justify-center gap-sm">
                                    <button onclick="bukaModalEdit('<?php echo $row['id_barang']; ?>', '<?php echo htmlspecialchars($row['nama'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['merek'], ENT_QUOTES); ?>', '<?php echo $row['id_kategori']; ?>', '<?php echo $row['jumlah']; ?>', '<?php echo htmlspecialchars($row['warna'], ENT_QUOTES); ?>', '<?php echo $row['status']; ?>')" 
                                            class="text-primary hover:bg-surface-container p-1.5 rounded-lg transition-colors" title="Ubah Perangkat">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <a href="index.php?page=barang&hapus=<?php echo $row['id_barang']; ?>" 
                                       onclick="return confirm('Hapus perangkat ini dari sistem inventaris lab?')"
                                       class="text-error hover:bg-error/10 p-1.5 rounded-lg transition-colors inline-block" title="Hapus Perangkat">
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
                            <td colspan="8" class="p-xl text-center">
                                <div class="flex flex-col items-center justify-center gap-xs text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[48px] opacity-40">inventory_2</span>
                                    <p class="font-body-lg font-medium">Gudang inventaris kosong</p>
                                    <p class="font-body-sm text-xs">Belum ada data unit komputer atau barang yang tercatat.</p>
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
                <h3 class="font-headline-md text-headline-md font-bold">Registrasi Alat Baru</h3>
            </div>
            <button onclick="tutupModal('modalTambah')" class="text-on-surface-variant hover:bg-surface-container w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="" method="POST" class="p-xl space-y-md">
            <div>
                <label for="nama" class="block font-label-md text-label-md text-primary mb-1.5">Nama Perangkat <span class="text-red-500">*</span></label>
                <input type="text" id="nama" name="nama" required autocomplete="off" placeholder="Contoh: PC Client Lab Utama"
                       class="w-full px-4 py-2 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all" />
            </div>
            <div class="grid grid-cols-2 gap-md">
                <div>
                    <label for="merek" class="block font-label-md text-label-md text-primary mb-1.5">Merek / Vendor</label>
                    <input type="text" id="merek" name="merek" placeholder="Contoh: Lenovo"
                           class="w-full px-4 py-2 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all" />
                </div>
                <div>
                    <label for="warna" class="block font-label-md text-label-md text-primary mb-1.5">Warna Casing</label>
                    <input type="text" id="warna" name="warna" placeholder="Contoh: Hitam"
                           class="w-full px-4 py-2 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all" />
                </div>
            </div>
            <div class="grid grid-cols-3 gap-md">
                <div class="col-span-2">
                    <label for="id_kategori" class="block font-label-md text-label-md text-primary mb-1.5">Rumpun Kategori <span class="text-red-500">*</span></label>
                    <select id="id_kategori" name="id_kategori" required class="w-full px-4 py-2 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($kategori_options as $kat): ?>
                            <option value="<?php echo $kat['id_kategori']; ?>"><?php echo htmlspecialchars($kat['nama_kategori']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="jumlah" class="block font-label-md text-label-md text-primary mb-1.5">Jumlah <span class="text-red-500">*</span></label>
                    <input type="number" id="jumlah" name="jumlah" required min="1" value="1"
                           class="w-full px-4 py-2 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all text-center" />
                </div>
            </div>
            <div>
                <label for="status" class="block font-label-md text-label-md text-primary mb-1.5">Kondisi Fisik Sistem</label>
                <select id="status" name="status" class="w-full px-4 py-2 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all">
                    <option value="baik">Baik (Layak Operasional)</option>
                    <option value="sedang_perbaikan">Sedang Perbaikan (Maintenance)</option>
                    <option value="rusak">Rusak (Mati Total/Afkir)</option>
                </select>
            </div>
            <div class="flex justify-end gap-sm pt-md border-t border-surface-container-high">
                <button type="button" onclick="tutupModal('modalTambah')" class="px-lg py-2 border border-outline-variant rounded-lg font-label-md text-label-md text-on-surface-variant hover:bg-surface-container-low transition-colors">Batal</button>
                <button type="submit" name="tambah_barang" class="px-lg py-2 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:opacity-90 shadow-sm transition-all">Daftarkan Aset</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-300">
    <div class="bg-white rounded-2xl w-full max-w-md border border-surface-container-high shadow-2xl transform scale-95 transition-all duration-300 overflow-hidden">
        <div class="p-xl border-b border-surface-container-high bg-surface-container-low flex justify-between items-center">
            <div class="flex items-center gap-2 text-primary">
                <span class="material-symbols-outlined text-secondary">edit_note</span>
                <h3 class="font-headline-md text-headline-md font-bold">Modifikasi Data Perangkat</h3>
            </div>
            <button onclick="tutupModal('modalEdit')" class="text-on-surface-variant hover:bg-surface-container w-8 h-8 rounded-full flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="" method="POST" class="p-xl space-y-md">
            <input type="hidden" id="edit_id_barang" name="id_barang" />
            <div>
                <label for="edit_nama" class="block font-label-md text-label-md text-primary mb-1.5">Nama Perangkat <span class="text-red-500">*</span></label>
                <input type="text" id="edit_nama" name="nama" required autocomplete="off"
                       class="w-full px-4 py-2 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all" />
            </div>
            <div class="grid grid-cols-2 gap-md">
                <div>
                    <label for="edit_merek" class="block font-label-md text-label-md text-primary mb-1.5">Merek</label>
                    <input type="text" id="edit_merek" name="merek"
                           class="w-full px-4 py-2 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all" />
                </div>
                <div>
                    <label for="edit_warna" class="block font-label-md text-label-md text-primary mb-1.5">Warna</label>
                    <input type="text" id="edit_warna" name="warna"
                           class="w-full px-4 py-2 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all" />
                </div>
            </div>
            <div class="grid grid-cols-3 gap-md">
                <div class="col-span-2">
                    <label for="edit_id_kategori" class="block font-label-md text-label-md text-primary mb-1.5">Kategori Rumpun <span class="text-red-500">*</span></label>
                    <select id="edit_id_kategori" name="id_kategori" required class="w-full px-4 py-2 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all">
                        <?php foreach ($kategori_options as $kat): ?>
                            <option value="<?php echo $kat['id_kategori']; ?>"><?php echo htmlspecialchars($kat['nama_kategori']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="edit_jumlah" class="block font-label-md text-label-md text-primary mb-1.5">Jumlah <span class="text-red-500">*</span></label>
                    <input type="number" id="edit_jumlah" name="jumlah" required min="1"
                           class="w-full px-4 py-2 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all text-center" />
                </div>
            </div>
            <div>
                <label for="edit_status" class="block font-label-md text-label-md text-primary mb-1.5">Kondisi</label>
                <select id="edit_status" name="status" class="w-full px-4 py-2 border border-outline-variant rounded-lg text-sm bg-surface-container-lowest focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all">
                    <option value="baik">Baik (Layak Operasional)</option>
                    <option value="sedang_perbaikan">Sedang Perbaikan (Maintenance)</option>
                    <option value="rusak">Rusak (Mati Total/Afkir)</option>
                </select>
            </div>
            <div class="flex justify-end gap-sm pt-md border-t border-surface-container-high">
                <button type="button" onclick="tutupModal('modalEdit')" class="px-lg py-2 border border-outline-variant rounded-lg font-label-md text-label-md text-on-surface-variant hover:bg-surface-container-low transition-colors">Batal</button>
                <button type="submit" name="edit_barang" class="px-lg py-2 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:opacity-90 shadow-sm transition-all">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    // MODAL ENGINE
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

    function bukaModalTambah() {
        bukaModal('modalTambah');
        setTimeout(() => document.getElementById('nama').focus(), 100);
    }

    function bukaModalEdit(id_barang, nama, merek, id_kategori, jumlah, warna, status) {
        document.getElementById('edit_id_barang').value = id_barang;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_merek').value = merek;
        document.getElementById('edit_id_kategori').value = id_kategori;
        document.getElementById('edit_jumlah').value = jumlah;
        document.getElementById('edit_warna').value = warna;
        document.getElementById('edit_status').value = status;
        bukaModal('modalEdit');
        setTimeout(() => document.getElementById('edit_nama').focus(), 100);
    }

    ['modalTambah', 'modalEdit'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) tutupModal(id);
        });
    });

    // Auto close alert banner dalam 4 detik
    setTimeout(() => {
        const alertBox = document.getElementById('alert-notif');
        if(alertBox) {
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 300);
        }
    }, 4000);
</script>