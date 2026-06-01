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

// 1. PROSES LOGIKA: TAMBAH BARANG (DENGAN ALOKASI LAB)
if (isset($_POST['tambah_barang'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $merek = mysqli_real_escape_string($koneksi, $_POST['merek']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $id_lab = !empty($_POST['id_lab']) ? intval($_POST['id_lab']) : "NULL"; // Ambil input ID Lab
    $jumlah = intval($_POST['jumlah']);
    $warna = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

    $query = "INSERT INTO barang (nama, merek, id_kategori, id_lab, jumlah, warna, status) 
              VALUES ('$nama', '$merek', " . ($id_kategori ? "'$id_kategori'" : "NULL") . ", $id_lab, '$jumlah', '$warna', '$status')";

    if (mysqli_query($koneksi, $query)) {
        $pesan_sukses = "Perangkat baru berhasil ditambahkan dan dialokasikan ke ruangan lab.";
    } else {
        $pesan_gagal = "Gagal menambah perangkat: " . mysqli_error($koneksi);
    }
}

// 2. PROSES LOGIKA: EDIT BARANG (DENGAN ALOKASI LAB)
if (isset($_POST['edit_barang'])) {
    $id_barang = intval($_POST['id_barang']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $merek = mysqli_real_escape_string($koneksi, $_POST['merek']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $id_lab = !empty($_POST['id_lab']) ? intval($_POST['id_lab']) : "NULL"; // Ambil input ID Lab
    $jumlah = intval($_POST['jumlah']);
    $warna = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

    $query = "UPDATE barang SET 
                nama = '$nama', 
                merek = '$merek', 
                id_kategori = " . ($id_kategori ? "'$id_kategori'" : "NULL") . ", 
                id_lab = $id_lab, 
                jumlah = '$jumlah', 
                warna = '$warna', 
                status = '$status' 
              WHERE id_barang = $id_barang";

    if (mysqli_query($koneksi, $query)) {
        $pesan_sukses = "Informasi perangkat dan lokasi penempatan berhasil diperbarui.";
    } else {
        $pesan_gagal = "Gagal memperbarui perangkat: " . mysqli_error($koneksi);
    }
}

// 3. PROSES LOGIKA: HAPUS BARANG
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    if (mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang = $id_hapus")) {
        echo "<script>alert('Perangkat berhasil dihapus dari inventaris.'); window.location.href='index.php?page=barang';</script>";
        exit();
    } else {
        $pesan_gagal = "Gagal menghapus data perangkat.";
    }
}

// Ambil opsi master kategori & master lab untuk dropdown modal form
$kategori_options = mysqli_query($koneksi, "SELECT * FROM lab_kategori ORDER BY nama_kategori ASC");
$lab_options = mysqli_query($koneksi, "SELECT * FROM lab_komputer ORDER BY nama_lab ASC");

// Query utama: Menggabungkan barang dengan kategori dan lab komputer (LEFT JOIN)
$sql = "SELECT b.*, k.nama_kategori, l.nama_lab 
        FROM barang b 
        LEFT JOIN lab_kategori k ON b.id_kategori = k.id_kategori
        LEFT JOIN lab_komputer l ON b.id_lab = l.id_lab 
        ORDER BY b.id_barang DESC";
$result = mysqli_query($koneksi, $sql);
?>

<div class="space-y-6 animate-[fadeIn_0.3s_ease-out]">
    <?php if ($pesan_sukses): ?>
        <div
            class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-xs font-medium flex items-center gap-2 shadow-sm">
            <?= $pesan_sukses; ?></div>
    <?php endif; ?>
    <?php if ($pesan_gagal): ?>
        <div
            class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs font-medium flex items-center gap-2 shadow-sm">
            <?= $pesan_gagal; ?></div>
    <?php endif; ?>

    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-base font-black text-slate-900 tracking-wide uppercase">Stok Perangkat Laboratorium</h1>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Kelola kuantitas barang, spesifikasi fisik, dan lokasi
                penempatan unit komputer.</p>
        </div>
        <button onclick="bukaModal('modalTambah')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
            <span class="material-symbols-outlined text-sm">add_box</span> Tambah Perangkat Baru
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold tracking-widest border-b border-slate-200/60">
                        <th class="px-6 py-3.5 text-center w-12 border-r border-slate-100">No</th>
                        <th class="px-6 py-3.5">Nama Barang</th>
                        <th class="px-6 py-3.5">Merek</th>
                        <th class="px-6 py-3.5">Kategori</th>
                        <th class="px-6 py-3.5">Posisi Lokasi Lab</th>
                        <th class="px-6 py-3.5 text-center">Jumlah</th>
                        <th class="px-6 py-3.5">Warna</th>
                        <th class="px-6 py-3.5">Status Fisik</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    <?php
                    if (mysqli_num_rows($result) > 0):
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)):
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-slate-400 font-medium text-center border-r border-slate-100">
                                    <?= $no++; ?></td>
                                <td class="px-6 py-4 font-bold text-slate-900"><?= htmlspecialchars($row['nama']); ?></td>
                                <td class="px-6 py-4 text-slate-500"><?= htmlspecialchars($row['merek'] ?: '-'); ?></td>
                                <td class="px-6 py-4"><span
                                        class="bg-blue-50 text-blue-600 px-2 py-0.5 rounded border border-blue-100 text-[11px] font-medium"><?= htmlspecialchars($row['nama_kategori'] ?: 'Umum'); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="bg-purple-50 text-purple-600 px-2 py-0.5 rounded border border-purple-100 text-[11px] font-bold">
                                        <span class="material-symbols-outlined text-[12px] align-middle mr-1">door_open</span>
                                        <?= htmlspecialchars($row['nama_lab'] ?: 'Belum Ditempatkan'); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-900"><?= $row['jumlah']; ?> Unit</td>
                                <td class="px-6 py-4 text-slate-500"><?= htmlspecialchars($row['warna'] ?: '-'); ?></td>
                                <td class="px-6 py-4">
                                    <?php if ($row['status'] == 'baik'): ?>
                                        <span
                                            class="text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 text-[11px]">Normal
                                            (Baik)</span>
                                    <?php elseif ($row['status'] == 'rusak'): ?>
                                        <span
                                            class="text-red-600 font-bold bg-red-50 px-2 py-0.5 rounded border border-red-100 text-[11px]">Rusak
                                            Total</span>
                                    <?php else: ?>
                                        <span
                                            class="text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded border border-amber-100 text-[11px]">Maintenance</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex gap-1.5">
                                        <button
                                            onclick="bukaModalEdit('<?= $row['id_barang']; ?>', '<?= addslashes($row['nama']); ?>', '<?= addslashes($row['merek']); ?>', '<?= $row['id_kategori']; ?>', '<?= $row['id_lab']; ?>', '<?= $row['jumlah']; ?>', '<?= addslashes($row['warna']); ?>', '<?= $row['status']; ?>')"
                                            class="p-1.5 hover:bg-amber-50 text-amber-600 rounded-lg border border-transparent hover:border-amber-200/60 transition-all"><span
                                                class="material-symbols-outlined text-base block">edit</span></button>
                                        <a href="index.php?page=barang&hapus=<?= $row['id_barang']; ?>"
                                            onclick="return confirm('Hapus perangkat ini?')"
                                            class="p-1.5 hover:bg-red-50 text-red-500 rounded-lg border border-transparent hover:border-red-200/60 transition-all"><span
                                                class="material-symbols-outlined text-base block">delete</span></a>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-slate-400 font-medium">Belum ada data barang.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalTambah"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300 bg-slate-900/40 backdrop-blur-sm">
    <div
        class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Tambah Perangkat Baru</h3>
            <button onclick="tutupModal('modalTambah')" class="text-slate-400 text-lg">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="tambah_barang" value="1">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama
                    Barang</label>
                <input type="text" name="nama" required class="w-full rounded-xl border-slate-200 text-xs">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label
                        class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Merek</label>
                    <input type="text" name="merek" class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div>
                    <label
                        class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Warna</label>
                    <input type="text" name="warna" class="w-full rounded-xl border-slate-200 text-xs">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kategori
                        Perangkat</label>
                    <select name="id_kategori" class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="">-- Pilih Kategori --</option>
                        <?php
                        mysqli_data_seek($kategori_options, 0);
                        while ($k = mysqli_fetch_assoc($kategori_options)):
                            ?>
                            <option value="<?= $k['id_kategori']; ?>"><?= htmlspecialchars($k['nama_kategori']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-purple-500 uppercase tracking-wider mb-1">Posisi
                        Alokasi Lab</label>
                    <select name="id_lab"
                        class="w-full rounded-xl border-purple-200 focus:border-purple-500 focus:ring-purple-500 text-xs bg-purple-50/30">
                        <option value="">-- Belum Ditempatkan --</option>
                        <?php
                        mysqli_data_seek($lab_options, 0);
                        while ($l = mysqli_fetch_assoc($lab_options)):
                            ?>
                            <option value="<?= $l['id_lab']; ?>"><?= htmlspecialchars($l['nama_lab']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jumlah
                        Unit</label>
                    <input type="number" name="jumlah" value="1" required min="1"
                        class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status
                        Kondisi</label>
                    <select name="status" class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="baik">Normal (Baik)</option>
                        <option value="rusak">Rusak Total</option>
                        <option value="sedang_perbaikan">Sedang Perbaikan</option>
                    </select>
                </div>
            </div>
            <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="tutupModal('modalTambah')"
                    class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-md">Simpan
                    Barang</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300 bg-slate-900/40 backdrop-blur-sm">
    <div
        class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Ubah Informasi Barang</h3>
            <button onclick="tutupModal('modalEdit')" class="text-slate-400 text-lg">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="edit_barang" value="1">
            <input type="hidden" name="id_barang" id="edit_id_barang">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama
                    Barang</label>
                <input type="text" name="nama" id="edit_nama" required
                    class="w-full rounded-xl border-slate-200 text-xs">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label
                        class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Merek</label>
                    <input type="text" name="merek" id="edit_merek" class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div>
                    <label
                        class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Warna</label>
                    <input type="text" name="warna" id="edit_warna" class="w-full rounded-xl border-slate-200 text-xs">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kategori
                        Perangkat</label>
                    <select name="id_kategori" id="edit_id_kategori" class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="">-- Pilih Kategori --</option>
                        <?php
                        mysqli_data_seek($kategori_options, 0);
                        while ($k = mysqli_fetch_assoc($kategori_options)):
                            ?>
                            <option value="<?= $k['id_kategori']; ?>"><?= htmlspecialchars($k['nama_kategori']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-purple-500 uppercase tracking-wider mb-1">Posisi
                        Alokasi Lab</label>
                    <select name="id_lab" id="edit_id_lab"
                        class="w-full rounded-xl border-purple-200 focus:border-purple-500 focus:ring-purple-500 text-xs bg-purple-50/30">
                        <option value="">-- Belum Ditempatkan --</option>
                        <?php
                        mysqli_data_seek($lab_options, 0);
                        while ($l = mysqli_fetch_assoc($lab_options)):
                            ?>
                            <option value="<?= $l['id_lab']; ?>"><?= htmlspecialchars($l['nama_lab']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jumlah
                        Unit</label>
                    <input type="number" name="jumlah" id="edit_jumlah" required min="1"
                        class="w-full rounded-xl border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status
                        Kondisi</label>
                    <select name="status" id="edit_status" class="w-full rounded-xl border-slate-200 text-xs">
                        <option value="baik">Normal (Baik)</option>
                        <option value="rusak">Rusak Total</option>
                        <option value="sedang_perbaikan">Sedang Perbaikan</option>
                    </select>
                </div>
            </div>
            <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="tutupModal('modalEdit')"
                    class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-md">Simpan
                    Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModal(id) {
        const m = document.getElementById(id);
        const content = m.querySelector('div');
        m.classList.remove('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-95'); content.classList.add('scale-100');
    }

    function tutupModal(id) {
        const m = document.getElementById(id);
        const content = m.querySelector('div');
        m.classList.add('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-100'); content.classList.add('scale-95');
    }

    function bukaModalEdit(id, nama, merek, id_kategori, id_lab, jumlah, warna, status) {
        document.getElementById('edit_id_barang').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_merek').value = merek;
        document.getElementById('edit_id_kategori').value = id_kategori;
        document.getElementById('edit_id_lab').value = id_lab; // Injeksi nilai lab terpilih ke JS modal edit
        document.getElementById('edit_jumlah').value = jumlah;
        document.getElementById('edit_warna').value = warna;
        document.getElementById('edit_status').value = status;
        bukaModal('modalEdit');
    }

    // Penutup modal jika mengklik latar belakang hitam transparan
    ['modalTambah', 'modalEdit'].forEach(id => {
        document.getElementById(id).addEventListener('click', function (e) {
            if (e.target === this) tutupModal(id);
        });
    });
</script>