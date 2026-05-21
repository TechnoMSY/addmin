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
            $pesan_sukses = "Perangkat \">" . htmlspecialchars($nama) . "</strong>\" berhasil didaftarkan ke sistem.";
        } else {
            $pesan_gagal = "Gagal menambahkan barang ke database. Silakan coba kembali.";
        }
    } else {
        $pesan_gagal = "Nama perangkat dan Kategori wajib diisi!";
    }
}

// 2. PROSES LOGIKA: EDIT BARANG
if (isset($_POST['edit_barang'])) {
    $id_barang   = mysqli_real_escape_string($koneksi, $_POST['id_barang']);
    $nama        = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $merek       = mysqli_real_escape_string($koneksi, $_POST['merek']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $jumlah      = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $warna       = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $status      = mysqli_real_escape_string($koneksi, $_POST['status']);

    if (!empty($id_barang) && !empty($nama) && !empty($id_kategori)) {
        $update = mysqli_query($koneksi, "UPDATE barang SET id_kategori='$id_kategori', nama='$nama', merek='$merek', jumlah='$jumlah', warna='$warna', status='$status' WHERE id_barang='$id_barang'");
        if ($update) {
            $pesan_sukses = "Data perangkat \"<strong>" . htmlspecialchars($nama) . "</strong>\" berhasil diperbarui.";
        } else {
            $pesan_gagal = "Gagal memperbarui data barang. Silakan periksa kembali.";
        }
    } else {
        $pesan_gagal = "Data tidak lengkap, pembaharuan dibatalkan.";
    }
}

// 3. PROSES LOGIKA: HAPUS BARANG
if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['id'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    $cek_nama = mysqli_query($koneksi, "SELECT nama FROM barang WHERE id_barang='$id_hapus'");
    $data_nama = mysqli_fetch_assoc($cek_nama);
    $nama_terhapus = $data_nama ? $data_nama['nama'] : 'Perangkat';

    $hapus = mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang='$id_hapus'");
    if ($hapus) {
        $pesan_sukses = "Perangkat \"<strong>" . htmlspecialchars($nama_terhapus) . "</strong>\" telah dihapus secara permanen.";
    } else {
        $pesan_gagal = "Gagal menghapus data perangkat dari sistem.";
    }
}

// ==========================================================
// LOGIKA FILTER DARI DATABASE (SISTEM DINAMIS REAL-TIME)
// ==========================================================
$filter_kategori = isset($_GET['filter_kategori']) ? mysqli_real_escape_string($koneksi, $_GET['filter_kategori']) : '';
$filter_status   = isset($_GET['filter_status']) ? mysqli_real_escape_string($koneksi, $_GET['filter_status']) : '';
$filter_waktu    = isset($_GET['filter_waktu']) ? mysqli_real_escape_string($koneksi, $_GET['filter_waktu']) : 'terbaru';

// Array penampung kondisi klausa WHERE SQL
$where_clauses = [];

if (!empty($filter_kategori)) {
    $where_clauses[] = "barang.id_kategori = '$filter_kategori'";
}
if (!empty($filter_status)) {
    $where_clauses[] = "barang.status = '$filter_status'";
}

// Satukan klausa WHERE jika ada filter yang dipilih
$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(' AND ', $where_clauses);
}

// Pengurutan Berdasarkan Waktu input (Memanfaatkan AI / Auto-increment ID)
$order_sql = "ORDER BY barang.id_barang DESC"; // Default: Terbaru
if ($filter_waktu === 'terlama') {
    $order_sql = "ORDER BY barang.id_barang ASC";
}
?>

<div class="mt-16 p-xl max-w-container-max mx-auto w-full">
    
    <?php if (!empty($pesan_sukses)): ?>
        <div id="alert-notif" class="mb-lg p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl text-emerald-800 text-sm flex items-center justify-between shadow-sm transition-all duration-300">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <span><?php echo $pesan_sukses; ?></span>
            </div>
            <button onclick="document.getElementById('alert-notif').remove();" class="text-emerald-500 hover:text-emerald-700 font-bold text-lg">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!empty($pesan_gagal)): ?>
        <div id="alert-notif" class="mb-lg p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-800 text-sm flex items-center justify-between shadow-sm transition-all duration-300">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-rose-600">error</span>
                <span><?php echo $pesan_gagal; ?></span>
            </div>
            <button onclick="document.getElementById('alert-notif').remove();" class="text-rose-500 hover:text-rose-700 font-bold text-lg">&times;</button>
        </div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-md mb-xl">
        <div>
            <h1 class="font-display-lg text-display-lg text-primary mb-xs">Manajemen Barang</h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant">Kelola seluruh aset, perangkat keras, dan komponen laboratorium ekosistem MAKN.</p>
        </div>
        <button onclick="bukaModalTambah()" class="bg-emerald-600 text-white font-bold px-lg py-3 rounded-lg flex items-center gap-2 hover:bg-emerald-700 active:scale-[0.98] transition-all shadow-md shadow-emerald-600/10 hover:shadow-emerald-600/20">
            <span class="material-symbols-outlined">add_box</span>
            Registrasi Barang Baru
        </button>
    </div>

    <div class="bg-white border border-outline-variant rounded-xl p-md mb-lg shadow-sm">
        <form method="GET" action="index.php" class="flex flex-wrap items-end gap-md">
            <input type="hidden" name="page" value="barang">

            <div class="flex flex-col min-w-[180px] flex-1 sm:flex-none">
                <label for="filter_kategori" class="text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wider">Kategori</label>
                <select id="filter_kategori" name="filter_kategori" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none">
                    <option value="">-- Semua Kategori --</option>
                    <?php 
                    $q_kat = mysqli_query($koneksi, "SELECT * FROM lab_kategori ORDER BY nama_kategori ASC");
                    while($k = mysqli_fetch_assoc($q_kat)) {
                        $selected = ($filter_kategori == $k['id_kategori']) ? 'selected' : '';
                        echo "<option value='".$k['id_kategori']."' $selected>".htmlspecialchars($k['nama_kategori'])."</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="flex flex-col min-w-[180px] flex-1 sm:flex-none">
                <label for="filter_status" class="text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wider">Kondisi Fisik</label>
                <select id="filter_status" name="filter_status" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none">
                    <option value="">-- Semua Kondisi --</option>
                    <option value="baik" <?php echo $filter_status === 'baik' ? 'selected' : ''; ?>>Baik (Normal)</option>
                    <option value="rusak" <?php echo $filter_status === 'rusak' ? 'selected' : ''; ?>>Rusak (Trouble)</option>
                    <option value="sedang_perbaikan" <?php echo $filter_status === 'sedang_perbaikan' ? 'selected' : ''; ?>>Sedang Perbaikan</option>
                </select>
            </div>

            <div class="flex flex-col min-w-[180px] flex-1 sm:flex-none">
                <label for="filter_waktu" class="text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wider">Urutan Data</label>
                <select id="filter_waktu" name="filter_waktu" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none">
                    <option value="terbaru" <?php echo $filter_waktu === 'terbaru' ? 'selected' : ''; ?>>Baru Ditambahkan</option>
                    <option value="terlama" <?php echo $filter_waktu === 'terlama' ? 'selected' : ''; ?>>Lama Ditambahkan</option>
                </select>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none bg-primary text-white font-semibold text-sm px-lg py-2.5 rounded-lg flex items-center justify-center gap-1.5 hover:bg-slate-800 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">filter_list</span>
                    Filter
                </button>
                <?php if(!empty($filter_kategori) || !empty($filter_status) || $filter_waktu !== 'terbaru'): ?>
                    <a href="index.php?page=barang" class="flex-1 sm:flex-none bg-gray-100 text-gray-700 font-semibold text-sm px-lg py-2.5 rounded-lg flex items-center justify-center gap-1.5 hover:bg-gray-200 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <div class="bg-white border border-outline-variant rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant text-primary font-semibold text-sm">
                        <th class="py-4 px-lg font-bold">No</th>
                        <th class="py-4 px-lg font-bold">Nama Perangkat</th>
                        <th class="py-4 px-lg font-bold">Merek/Brand</th>
                        <th class="py-4 px-lg font-bold">Kategori</th>
                        <th class="py-4 px-lg font-bold text-center">Jumlah</th>
                        <th class="py-4 px-lg">Warna</th>
                        <th class="py-4 px-lg text-center">Status</th>
                        <th class="py-4 px-lg text-right">Aksi Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant text-sm text-gray-700">
                    <?php
                    // Query Utama yang datanya disaring dinamis lewat klausa WHERE hasil filter
                    $query_string = "SELECT barang.*, lab_kategori.nama_kategori 
                                     FROM barang 
                                     LEFT JOIN lab_kategori ON barang.id_kategori = lab_kategori.id_kategori
                                     $where_sql
                                     $order_sql";
                    
                    $ambil_barang = mysqli_query($koneksi, $query_string);
                    
                    if (mysqli_num_rows($ambil_barang) > 0) {
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($ambil_barang)) {
                            $status_class = "bg-emerald-50 text-emerald-700 border border-emerald-300";
                            $status_text  = "Baik";
                            
                            if ($row['status'] === 'rusak') {
                                $status_class = "bg-rose-50 text-rose-700 border border-rose-200";
                                $status_text  = "Rusak";
                            } elseif ($row['status'] === 'sedang_perbaikan') {
                                $status_class = "bg-amber-50 text-amber-700 border border-amber-200";
                                $status_text  = "Perbaikan";
                            }
                    ?>
                            <tr class="hover:bg-surface-container-lowest/50 transition-colors">
                                <td class="py-4 px-lg font-medium text-gray-400"><?php echo $no++; ?></td>
                                <td class="py-4 px-lg font-semibold text-gray-900"><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td class="py-4 px-lg text-gray-600"><?php echo !empty($row['merek']) ? htmlspecialchars($row['merek']) : '-'; ?></td>
                                <td class="py-4 px-lg">
                                    <span class="bg-slate-100 text-slate-800 text-xs px-2.5 py-1 rounded-md font-medium">
                                        <?php echo htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?>
                                    </span>
                                </td>
                                <td class="py-4 px-lg text-center font-bold text-gray-900"><?php echo htmlspecialchars($row['jumlah']); ?> Unit</td>
                                <td class="py-4 px-lg text-gray-600"><?php echo !empty($row['warna']) ? htmlspecialchars($row['warna']) : '-'; ?></td>
                                <td class="py-4 px-lg text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold inline-block <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td class="py-4 px-lg text-right">
                                    <div class="flex justify-end gap-2">
                                        <button onclick="bukaModalEdit('<?php echo $row['id_barang']; ?>', '<?php echo addslashes($row['nama']); ?>', '<?php echo addslashes($row['merek']); ?>', '<?php echo $row['id_kategori']; ?>', '<?php echo $row['jumlah']; ?>', '<?php echo addslashes($row['warna']); ?>', '<?php echo $row['status']; ?>')" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit Data">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </button>
                                        <a href="index.php?page=barang&action=hapus&id=<?php echo $row['id_barang']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus perangkat kustom <?php echo htmlspecialchars($row['nama']); ?> ini dari sistem secara permanen?');" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Data">
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
                            <td colspan="8" class="text-center py-12 text-gray-400 font-medium">
                                <span class="material-symbols-outlined text-4xl block mb-2 text-gray-300">inventory</span>
                                Tidak ditemukan data barang yang sesuai dengan kriteria filter database Anda.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalTambah" class="fixed inset-0 z-50 bg-slate-950/40 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-xl shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center pb-md border-b border-gray-100 mb-lg">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600">add_box</span>
                Registrasi Barang Baru
            </h3>
            <button onclick="tutupModal('modalTambah')" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
        </div>
        
        <form method="POST" action="index.php?page=barang">
            <div class="space-y-4 mb-lg">
                <div>
                    <label for="nama" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Nama Perangkat / Item <span class="text-red-500">*</span></label>
                    <input type="text" id="nama" name="nama" required placeholder="Contoh: PC Server Core i9 / Router Cisco" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="merek" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Merek / Brand</label>
                        <input type="text" id="merek" name="merek" placeholder="Asus, Cisco, Logi" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none" />
                    </div>
                    <div>
                        <label for="id_kategori" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Kategori Kelompok <span class="text-red-500">*</span></label>
                        <select id="id_kategori" name="id_kategori" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none">
                            <option value="">-- Pilih --</option>
                            <?php 
                            $kat = mysqli_query($koneksi, "SELECT * FROM lab_kategori ORDER BY nama_kategori ASC");
                            while($k = mysqli_fetch_assoc($kat)) {
                                echo "<option value='".$k['id_kategori']."'>".htmlspecialchars($k['nama_kategori'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="jumlah" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Volume Kuantitas <span class="text-red-500">*</span></label>
                        <input type="number" id="jumlah" name="jumlah" required min="0" value="1" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none" />
                    </div>
                    <div>
                        <label for="warna" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Warna Periferal</label>
                        <input type="text" id="warna" name="warna" placeholder="Hitam / Silver" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none" />
                    </div>
                </div>
                <div>
                    <label for="status" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Kondisi Fisik Awal</label>
                    <select id="status" name="status" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none">
                        <option value="baik">Baik (Layani Operasional)</option>
                        <option value="rusak">Rusak / Tidak Dapat Digunakan</option>
                        <option value="sedang_perbaikan">Sedang Diproses Perbaikan (Maintenance)</option>
                    </select>
                </div>
            </div>
            
            <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" onclick="tutupModal('modalTambah')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" name="tambah_barang" class="px-4 py-2 bg-emerald-600 text-white font-bold rounded-lg text-sm hover:bg-emerald-700 transition-colors">Simpan Aset</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="fixed inset-0 z-50 bg-slate-950/40 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-xl shadow-2xl border border-gray-100 transform scale-95 transition-all duration-300">
        <div class="flex justify-between items-center pb-md border-b border-gray-100 mb-lg">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">edit_note</span>
                Modifikasi Informasi Barang
            </h3>
            <button onclick="tutupModal('modalEdit')" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
        </div>
        
        <form method="POST" action="index.php?page=barang">
            <input type="hidden" id="edit_id_barang" name="id_barang" />
            
            <div class="space-y-4 mb-lg">
                <div>
                    <label for="edit_nama" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Nama Perangkat / Item <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_nama" name="nama" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit_merek" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Merek / Brand</label>
                        <input type="text" id="edit_merek" name="merek" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none" />
                    </div>
                    <div>
                        <label for="edit_id_kategori" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Kategori Kelompok <span class="text-red-500">*</span></label>
                        <select id="edit_id_kategori" name="id_kategori" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none">
                            <option value="">-- Pilih --</option>
                            <?php 
                            $kat = mysqli_query($koneksi, "SELECT * FROM lab_kategori ORDER BY nama_kategori ASC");
                            while($k = mysqli_fetch_assoc($kat)) {
                                echo "<option value='".$k['id_kategori']."'>".htmlspecialchars($k['nama_kategori'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit_jumlah" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Volume Kuantitas <span class="text-red-500">*</span></label>
                        <input type="number" id="edit_jumlah" name="jumlah" required min="0" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none" />
                    </div>
                    <div>
                        <label for="edit_warna" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Warna Periferal</label>
                        <input type="text" id="edit_warna" name="warna" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none" />
                    </div>
                </div>
                <div>
                    <label for="edit_status" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Kondisi Fisik Saat Ini</label>
                    <select id="edit_status" name="status" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary outline-none">
                        <option value="baik">Baik (Layani Operasional)</option>
                        <option value="rusak">Rusak / Tidak Dapat Digunakan</option>
                        <option value="sedang_perbaikan">Sedang Diproses Perbaikan (Maintenance)</option>
                    </select>
                </div>
            </div>
            
            <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" onclick="tutupModal('modalEdit')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" name="edit_barang" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg text-sm hover:bg-blue-700 transition-colors">Perbarui Data</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModal(id) {
        const m = document.getElementById(id);
        const content = m.querySelector('.bg-white');
        m.classList.remove('opacity-0', 'pointer-events-none');
        m.classList.add('opacity-100');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }

    function tutupModal(id) {
        const m = document.getElementById(id);
        const content = m.querySelector('.bg-white');
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
        if (alertBox) {
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 300);
        }
    }, 4000);
</script>