<?php

if (!isset($koneksi)) {
    include 'koneksi.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$user_level = $_SESSION['user']['level'] ?? $_SESSION['user']['role'] ?? 'siswa';


if ($user_level !== 'admin' && $user_level !== 'guru') {
    echo "<script>
        alert('Akses Ditolak! Halaman ini hanya boleh diakses oleh Admin atau Guru.');
        window.location.href = 'index.php?page=home';
    </script>";
    exit();
}


$pesan_sukses = "";
$pesan_gagal = "";

// 1. PROSES LOGIKA: TAMBAH BARANG
if (isset($_POST['tambah_barang'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $merek = mysqli_real_escape_string($koneksi, $_POST['merek']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $jumlah = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $warna = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

    if (!empty($nama) && !empty($id_kategori)) {
        $insert = mysqli_query($koneksi, "INSERT INTO barang (id_kategori, nama, merek, jumlah, warna, status) VALUES ('$id_kategori', '$nama', '$merek', '$jumlah', '$warna', '$status')");
        if ($insert) {
            $pesan_sukses = "Perangkat \"<strong>" . htmlspecialchars($nama) . "</strong>\" berhasil didaftarkan ke sistem.";
        } else {
            $pesan_gagal = "Gagal menambahkan barang ke database. Silakan coba kembali.";
        }
    } else {
        $pesan_gagal = "Nama perangkat dan Kategori wajib diisi!";
    }
}

// 2. PROSES LOGIKA: EDIT BARANG
if (isset($_POST['edit_barang'])) {
    $id_barang = mysqli_real_escape_string($koneksi, $_POST['id_barang']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $merek = mysqli_real_escape_string($koneksi, $_POST['merek']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $jumlah = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $warna = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

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


$filter_kategori = isset($_GET['filter_kategori']) ? mysqli_real_escape_string($koneksi, $_GET['filter_kategori']) : '';
$filter_status = isset($_GET['filter_status']) ? mysqli_real_escape_string($koneksi, $_GET['filter_status']) : '';
$filter_waktu = isset($_GET['filter_waktu']) ? mysqli_real_escape_string($koneksi, $_GET['filter_waktu']) : 'terbaru';

$where_clauses = [];

if (!empty($filter_kategori)) {
    $where_clauses[] = "barang.id_kategori = '$filter_kategori'";
}
if (!empty($filter_status)) {
    $where_clauses[] = "barang.status = '$filter_status'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(' AND ', $where_clauses);
}

$order_sql = "ORDER BY barang.id_barang DESC";
if ($filter_waktu === 'terlama') {
    $order_sql = "ORDER BY barang.id_barang ASC";
}
?>

<div class="w-full space-y-6">

    <?php if (!empty($pesan_sukses)): ?>
        <div id="alert-notif"
            class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl flex items-center justify-between text-xs shadow-sm transition-all duration-300">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-emerald-600">check_circle</span>
                <span><?php echo $pesan_sukses; ?></span>
            </div>
            <button onclick="document.getElementById('alert-notif').remove()"
                class="text-emerald-500 hover:text-emerald-700">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (!empty($pesan_gagal)): ?>
        <div id="alert-notif"
            class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl flex items-center justify-between text-xs shadow-sm transition-all duration-300">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-red-600">error</span>
                <span><?php echo $pesan_gagal; ?></span>
            </div>
            <button onclick="document.getElementById('alert-notif').remove()" class="text-red-500 hover:text-red-700">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-text-dark">Manajemen Inventaris Barang</h2>
            <p class="text-xs text-text-muted mt-0.5">Kelola seluruh aset, perangkat keras, dan komponen laboratorium
                ekosistem MAKN.</p>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <a href="cetak_laporan.php"
                class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all uppercase tracking-wider flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-base">print</span>
                <span>Cetak Laporan</span>
            </a>

            <button onclick="bukaModal('modalTambah')"
                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-all uppercase tracking-wider flex items-center gap-1.5 shadow-md shadow-blue-500/10">
                <span class="material-symbols-outlined text-base">add_circle</span>
                <span>Tambah Aset Baru</span>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/70 p-4 shadow-[0_2px_8px_-3px_rgba(0,0,0,0.02)]">
        <form method="GET" action="index.php" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="page" value="barang">

            <div class="flex flex-col min-w-[160px] flex-1">
                <label for="filter_kategori"
                    class="text-[11px] font-bold text-text-dark mb-1.5 uppercase tracking-wider">Kategori</label>
                <select id="filter_kategori" name="filter_kategori"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:border-brand-blue focus:ring-brand-blue outline-none transition-all">
                    <option value="">-- Semua Kategori --</option>
                    <?php
                    $q_kat = mysqli_query($koneksi, "SELECT * FROM lab_kategori ORDER BY nama_kategori ASC");
                    while ($k = mysqli_fetch_assoc($q_kat)) {
                        $selected = ($filter_kategori == $k['id_kategori']) ? 'selected' : '';
                        echo "<option value='" . $k['id_kategori'] . "' $selected>" . htmlspecialchars($k['nama_kategori']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="flex flex-col min-w-[160px] flex-1">
                <label for="filter_status"
                    class="text-[11px] font-bold text-text-dark mb-1.5 uppercase tracking-wider">Kondisi Fisik</label>
                <select id="filter_status" name="filter_status"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:border-brand-blue focus:ring-brand-blue outline-none transition-all">
                    <option value="">-- Semua Kondisi --</option>
                    <option value="baik" <?php echo $filter_status === 'baik' ? 'selected' : ''; ?>>Baik (Normal)</option>
                    <option value="rusak" <?php echo $filter_status === 'rusak' ? 'selected' : ''; ?>>Rusak (Trouble)
                    </option>
                    <option value="sedang_perbaikan" <?php echo $filter_status === 'sedang_perbaikan' ? 'selected' : ''; ?>>Sedang Perbaikan</option>
                </select>
            </div>

            <div class="flex flex-col min-w-[160px] flex-1">
                <label for="filter_waktu"
                    class="text-[11px] font-bold text-text-dark mb-1.5 uppercase tracking-wider">Urutan Data</label>
                <select id="filter_waktu" name="filter_waktu"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:border-brand-blue focus:ring-brand-blue outline-none transition-all">
                    <option value="terbaru" <?php echo $filter_waktu === 'terbaru' ? 'selected' : ''; ?>>Baru Ditambahkan
                    </option>
                    <option value="terlama" <?php echo $filter_waktu === 'terlama' ? 'selected' : ''; ?>>Lama Ditambahkan
                    </option>
                </select>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <button type="submit"
                    class="flex-1 md:flex-none bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs px-4 py-2.5 rounded-xl flex items-center justify-center gap-1.5 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-base">filter_list</span>
                    Saring
                </button>
                <?php if (!empty($filter_kategori) || !empty($filter_status) || $filter_waktu !== 'terbaru'): ?>
                    <a href="index.php?page=barang"
                        class="flex-1 md:flex-none bg-slate-100 hover:bg-slate-200 text-text-dark font-semibold text-xs px-4 py-2.5 rounded-xl flex items-center justify-center gap-1.5 transition-all">
                        <span class="material-symbols-outlined text-base">restart_alt</span>
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div
        class="bg-white rounded-2xl border border-slate-200/70 shadow-[0_2px_8px_-3px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="border-b border-slate-200 bg-slate-50/50 text-text-muted text-[11px] font-bold uppercase tracking-wider">
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4">Nama Perangkat</th>
                        <th class="p-4">Merek/Brand</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4 text-center">Jumlah</th>
                        <th class="p-4">Warna</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center w-16">QR</th>
                        <th class="p-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-text-dark">
                    <?php
                    $query_string = "SELECT barang.*, lab_kategori.nama_kategori FROM barang LEFT JOIN lab_kategori ON barang.id_kategori = lab_kategori.id_kategori $where_sql $order_sql";
                    $ambil_barang = mysqli_query($koneksi, $query_string);

                    if (mysqli_num_rows($ambil_barang) > 0) {
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($ambil_barang)) {
                            // Badge Status Kondisi
                            $status_badge = "bg-slate-50 text-slate-700 border-slate-200";
                            $status_text = $row['status'];
                            if ($row['status'] == 'baik') {
                                $status_badge = "bg-emerald-50 text-emerald-700 border-emerald-100";
                                $status_text = "Baik (Normal)";
                            } elseif ($row['status'] == 'rusak') {
                                $status_badge = "bg-red-50 text-red-700 border-red-100";
                                $status_text = "Rusak (Trouble)";
                            } elseif ($row['status'] == 'sedang_perbaikan') {
                                $status_badge = "bg-amber-50 text-amber-700 border-amber-100";
                                $status_text = "Perbaikan";
                            }
                            ?>
                            <tr class="hover:bg-slate-50/40 transition-colors">
                                <td class="p-4 text-center text-text-muted font-medium"><?php echo $no++; ?></td>
                                <td class="p-4 font-bold text-sm text-text-dark"><?php echo htmlspecialchars($row['nama']); ?>
                                </td>
                                <td class="p-4 font-medium text-text-muted">
                                    <?php echo !empty($row['merek']) ? htmlspecialchars($row['merek']) : '-'; ?>
                                </td>
                                <td class="p-4">
                                    <span
                                        class="px-2 py-0.5 bg-slate-100 border border-slate-200/60 rounded-md text-[10px] font-medium text-slate-600">
                                        <?php echo htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center font-bold"><?php echo number_format($row['jumlah']); ?> unit</td>
                                <td class="p-4 text-text-muted">
                                    <?php echo !empty($row['warna']) ? htmlspecialchars($row['warna']) : '-'; ?>
                                </td>
                                <td class="p-4 text-center">
                                    <span
                                        class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold border <?php echo $status_badge; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <button
                                        onclick="bukaModalQR('<?php echo $row['id_barang']; ?>', '<?php echo str_replace(['\'', '"'], ['\\\'', '\\"'], htmlspecialchars($row['nama'])); ?>', '<?php echo str_replace(['\'', '"'], ['\\\'', '\\"'], htmlspecialchars($row['merek'])); ?>')"
                                        class="w-7 h-7 bg-slate-50 border border-slate-200 text-slate-600 hover:text-emerald-600 hover:border-emerald-200 rounded-lg flex items-center justify-center transition-all shadow-sm mx-auto"
                                        title="Lihat & Cetak QR Code">
                                        <span class="material-symbols-outlined text-base">qr_code_2</span>
                                    </button>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            onclick="bukaModalEdit('<?php echo $row['id_barang']; ?>', '<?php echo str_replace(['\'', '"'], ['\\\'', '\\"'], htmlspecialchars($row['nama'])); ?>', '<?php echo str_replace(['\'', '"'], ['\\\'', '\\"'], htmlspecialchars($row['merek'])); ?>', '<?php echo $row['id_kategori']; ?>', '<?php echo $row['jumlah']; ?>', '<?php echo str_replace(['\'', '"'], ['\\\'', '\\"'], htmlspecialchars($row['warna'])); ?>', '<?php echo $row['status']; ?>')"
                                            class="w-7 h-7 bg-slate-50 border border-slate-200 text-text-muted hover:text-brand-blue hover:border-blue-200 rounded-lg flex items-center justify-center transition-all shadow-sm"
                                            title="Ubah Data">
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>
                                        <a href="index.php?page=barang&action=hapus&id=<?php echo $row['id_barang']; ?>"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus perangkat ini dari sistem?');"
                                            class="w-7 h-7 bg-slate-50 border border-slate-200 text-text-muted hover:text-red-600 hover:border-red-200 rounded-lg flex items-center justify-center transition-all shadow-sm"
                                            title="Hapus">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="9" class="p-8 text-center text-text-muted italic">Tidak ditemukan adanya aset perangkat keras terdaftar yang cocok.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalQR"
    class="fixed inset-0 bg-slate-950/20 backdrop-blur-sm z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
    <div
        class="bg-white w-full max-w-xs rounded-2xl border border-slate-200 shadow-2xl scale-95 transition-all duration-200 overflow-hidden flex flex-col items-center p-6 text-center">
        <div class="w-full flex justify-between items-center mb-4 pb-2 border-b border-slate-100">
            <h3 class="text-xs font-bold text-text-dark uppercase tracking-wide flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base text-emerald-600">qr_code_scanner</span> QR Label Aset
            </h3>
            <button onclick="tutupModalQR()"
                class="w-6 h-6 rounded-lg text-text-muted hover:bg-slate-100 flex items-center justify-center">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>

        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl mb-4">
            <img id="qr_image_src" src="" alt="QR Code Barang" class="w-40 h-40 object-contain mx-auto">
        </div>

        <div class="w-full space-y-1 text-xs text-left px-1 mb-5">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Nama Perangkat</span>
                <p id="qr_info_nama" class="font-bold text-text-dark break-words"></p>
            </div>
            <div class="pt-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Merek / Brand</span>
                <p id="qr_info_merek" class="font-medium text-text-muted"></p>
            </div>
        </div>

        <div class="w-full flex gap-2">
            <button onclick="tutupModalQR()"
                class="w-1/2 py-2 border border-slate-200 text-text-muted font-semibold rounded-xl hover:bg-slate-50 transition-colors text-xs">Tutup</button>
            <a id="qr_print_link" href=""
                class="w-1/2 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-sm flex items-center justify-center gap-1 transition-colors text-xs">
                <span class="material-symbols-outlined text-sm">print</span> Cetak QR
            </a>
        </div>
    </div>
</div>

<div id="modalTambah"
    class="fixed inset-0 bg-slate-950/20 backdrop-blur-sm z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
    <div
        class="bg-white w-full max-w-md rounded-2xl border border-slate-200 shadow-2xl scale-95 transition-all duration-200 overflow-hidden flex flex-col">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-sm font-bold text-text-dark uppercase tracking-wide flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-brand-blue">add_box</span> Tambah Perangkat Baru
            </h3>
            <button onclick="tutupModal('modalTambah')"
                class="w-6 h-6 rounded-lg text-text-muted hover:bg-slate-100 flex items-center justify-center">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
        <form action="index.php?page=barang" method="POST" class="p-5 space-y-4 text-xs">
            <div class="space-y-1.5">
                <label for="nama" class="block font-bold text-text-dark">Nama Perangkat / Barang *</label>
                <input type="text" id="nama" name="nama" required placeholder="Contoh: PC Server Lenovo ThinkCenter"
                    class="w-full rounded-xl border-slate-200 focus:border-brand-blue focus:ring-brand-blue text-xs p-2.5">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="merek" class="block font-bold text-text-dark">Merek / Brand</label>
                    <input type="text" id="merek" name="merek" placeholder="Lenovo, Asus, dll"
                        class="w-full rounded-xl border-slate-200 focus:border-brand-blue focus:ring-brand-blue text-xs p-2.5">
                </div>
                <div class="space-y-1.5">
                    <label for="id_kategori" class="block font-bold text-text-dark">Kategori Klasifikasi *</label>
                    <select id="id_kategori" name="id_kategori" required
                        class="w-full rounded-xl border-slate-200 focus:border-brand-blue focus:ring-brand-blue text-xs p-2.5">
                        <option value="">-- Pilih --</option>
                        <?php
                        $q_kat2 = mysqli_query($koneksi, "SELECT * FROM lab_kategori ORDER BY nama_kategori ASC");
                        while ($k = mysqli_fetch_assoc($q_kat2)) {
                            echo "<option value='" . $k['id_kategori'] . "'>" . htmlspecialchars($k['nama_kategori']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="jumlah" class="block font-bold text-text-dark">Jumlah Unit *</label>
                    <input type="number" id="jumlah" name="jumlah" min="1" value="1" required
                        class="w-full rounded-xl border-slate-200 focus:border-brand-blue focus:ring-brand-blue text-xs p-2.5">
                </div>
                <div class="space-y-1.5">
                    <label for="warna" class="block font-bold text-text-dark">Warna Unit</label>
                    <input type="text" id="warna" name="warna" placeholder="Hitam, Putih, Silver"
                        class="w-full rounded-xl border-slate-200 focus:border-brand-blue focus:ring-brand-blue text-xs p-2.5">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="status" class="block font-bold text-text-dark">Kondisi Awal Fisik *</label>
                <select id="status" name="status" required
                    class="w-full rounded-xl border-slate-200 focus:border-brand-blue focus:ring-brand-blue text-xs p-2.5">
                    <option value="baik">Baik (Normal Operasional)</option>
                    <option value="rusak">Rusak (Mati / Masalah Hardware)</option>
                    <option value="sedang_perbaikan">Sedang Dalam Perbaikan / Maintenance</option>
                </select>
            </div>

            <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="tutupModal('modalTambah')"
                    class="px-4 py-2 border border-slate-200 rounded-xl font-semibold text-text-muted hover:bg-slate-50">Batal</button>
                <button type="submit" name="tambah_barang"
                    class="px-4 py-2 bg-brand-blue hover:bg-blue-700 text-white font-semibold rounded-xl shadow-sm">Simpan
                    Data</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit"
    class="fixed inset-0 bg-slate-950/20 backdrop-blur-sm z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
    <div
        class="bg-white w-full max-w-md rounded-2xl border border-slate-200 shadow-2xl scale-95 transition-all duration-200 overflow-hidden flex flex-col">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-sm font-bold text-text-dark uppercase tracking-wide flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-brand-blue">edit_note</span> Perbarui Informasi
                Barang
            </h3>
            <button onclick="tutupModal('modalEdit')"
                class="w-6 h-6 rounded-lg text-text-muted hover:bg-slate-100 flex items-center justify-center">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
        <form action="index.php?page=barang" method="POST" class="p-5 space-y-4 text-xs">
            <input type="hidden" id="edit_id_barang" name="id_barang">

            <div class="space-y-1.5">
                <label for="edit_nama" class="block font-bold text-text-dark">Nama Perangkat / Barang *</label>
                <input type="text" id="edit_nama" name="nama" required
                    class="w-full rounded-xl border-slate-200 focus:border-brand-blue focus:ring-brand-blue text-xs p-2.5">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="edit_merek" class="block font-bold text-text-dark">Merek / Brand</label>
                    <input type="text" id="edit_merek" name="merek"
                        class="w-full rounded-xl border-slate-200 focus:border-brand-blue focus:ring-brand-blue text-xs p-2.5">
                </div>
                <div class="space-y-1.5">
                    <label for="edit_id_kategori" class="block font-bold text-text-dark">Kategori Klasifikasi *</label>
                    <select id="edit_id_kategori" name="id_kategori" required
                        class="w-full rounded-xl border-slate-200 focus:border-brand-blue focus:ring-brand-blue text-xs p-2.5">
                        <option value="">-- Pilih --</option>
                        <?php
                        $q_kat3 = mysqli_query($koneksi, "SELECT * FROM lab_kategori ORDER BY nama_kategori ASC");
                        while ($k = mysqli_fetch_assoc($q_kat3)) {
                            echo "<option value='" . $k['id_kategori'] . "'>" . htmlspecialchars($k['nama_kategori']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="edit_jumlah" class="block font-bold text-text-dark">Jumlah Unit *</label>
                    <input type="number" id="edit_jumlah" name="jumlah" min="0" required
                        class="w-full rounded-xl border-slate-200 focus:border-brand-blue focus:ring-brand-blue text-xs p-2.5">
                </div>
                <div class="space-y-1.5">
                    <label for="edit_warna" class="block font-bold text-text-dark">Warna Unit</label>
                    <input type="text" id="edit_warna" name="warna"
                        class="w-full rounded-xl border-slate-200 focus:border-brand-blue focus:ring-brand-blue text-xs p-2.5">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="edit_status" class="block font-bold text-text-dark">Kondisi Fisik *</label>
                <select id="edit_status" name="status" required
                    class="w-full rounded-xl border-slate-200 focus:border-brand-blue focus:ring-brand-blue text-xs p-2.5">
                    <option value="baik">Baik (Normal Operasional)</option>
                    <option value="rusak">Rusak (Mati / Masalah Hardware)</option>
                    <option value="sedang_perbaikan">Sedang Dalam Perbaikan / Maintenance</option>
                </select>
            </div>

            <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="tutupModal('modalEdit')"
                    class="px-4 py-2 border border-slate-200 rounded-xl font-semibold text-text-muted hover:bg-slate-50">Batal</button>
                <button type="submit" name="edit_barang"
                    class="px-4 py-2 bg-brand-blue hover:bg-blue-700 text-white font-semibold rounded-xl shadow-sm">Simpan
                    Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModal(idModal) {
        const m = document.getElementById(idModal);
        const content = m.querySelector('div');
        m.classList.remove('opacity-0', 'pointer-events-none');
        m.classList.add('opacity-100');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }

    function tutupModal(idModal) {
        const m = document.getElementById(idModal);
        const content = m.querySelector('div');
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

    // Fungsi Pengendali Modal QR Code Baru (Realtime & Seamless)
    function bukaModalQR(id_barang, nama, merek) {
        const string_qr = "ID Barang: " + id_barang + " | Nama: " + nama + " | Merek: " + (merek ? merek : '-');
        const url_api = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" + encodeURIComponent(string_qr);

        document.getElementById('qr_image_src').src = url_api;
        document.getElementById('qr_info_nama').textContent = nama;
        document.getElementById('qr_info_merek').textContent = merek ? merek : '-';
        document.getElementById('qr_print_link').href = "index.php?page=qr_barang&id=" + id_barang;

        bukaModal('modalQR');
    }

    function tutupModalQR() {
        const m = document.getElementById('modalQR');
        const content = m.querySelector('div');
        m.classList.remove('opacity-100');
        m.classList.add('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        // Reset src image agar saat dibuka selanjutnya bersih
        document.getElementById('qr_image_src').src = "";
    }

    ['modalTambah', 'modalEdit', 'modalQR'].forEach(id => {
        document.getElementById(id).addEventListener('click', function (e) {
            if (e.target === this) {
                if (id === 'modalQR') tutupModalQR();
                else tutupModal(id);
            }
        });
    });

    // Auto close alert banner dalam 4 detik
    setTimeout(() => {
        const alertBox = document.getElementById('alert-notif');
        if (alertBox) alertBox.remove();
    }, 4000);
</script>