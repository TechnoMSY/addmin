<?php
// 1. Pastikan koneksi dan session sudah berjalan
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
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);

    if (!empty($id_kategori) && !empty($nama_kategori)) {
        $update = mysqli_query($koneksi, "UPDATE lab_kategori SET nama_kategori='$nama_kategori' WHERE id_kategori='$id_kategori'");
        if ($update) {
            $pesan_sukses = "Nama kategori berhasil diubah menjadi \"<strong>" . htmlspecialchars($nama_kategori) . "</strong>\".";
        } else {
            $pesan_gagal = "Gagal memperbarui data: " . mysqli_error($koneksi);
        }
    } else {
        $pesan_gagal = "Parameter edit tidak lengkap.";
    }
}

// 3. PROSES LOGIKA HAPUS DATA
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Proteksi: Cek apakah kategori masih dipakai oleh data barang
    $cek_relasi = mysqli_query($koneksi, "SELECT COUNT(*) as digunakan FROM barang WHERE id_kategori='$id_hapus'");
    $relasi_data = mysqli_fetch_assoc($cek_relasi);

    if (($relasi_data['digunakan'] ?? 0) > 0) {
        $pesan_gagal = "Gagal menghapus! Kategori ini masih digunakan oleh " . $relasi_data['digunakan'] . " aset barang.";
    } else {
        $delete = mysqli_query($koneksi, "DELETE FROM lab_kategori WHERE id_kategori='$id_hapus'");
        if ($delete) {
            $pesan_sukses = "Kategori berhasil dihapus dari sistem.";
        } else {
            $pesan_gagal = "Gagal menghapus data dari database.";
        }
    }
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
            <h2 class="text-base font-bold text-text-dark">Manajemen Kategori Barang</h2>
            <p class="text-xs text-text-muted mt-0.5">Klasifikasikan pengelompokan aset laboratorium untuk mempermudah
                pemetaan pencarian.</p>
        </div>
        <button onclick="bukaModalTambah()"
            class="bg-brand-blue hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 shadow-sm self-end sm:self-auto uppercase tracking-wide">
            <span class="material-symbols-outlined text-base">folder_open</span>
            Tambah Kategori
        </button>
    </div>

    <div
        class="bg-white rounded-2xl border border-slate-200/70 shadow-[0_2px_8px_-3px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="border-b border-slate-200 bg-slate-50/50 text-text-muted text-[11px] font-bold uppercase tracking-wider">
                        <th class="p-4 w-20 text-center">ID</th>
                        <th class="p-4">Nama Klasifikasi / Kategori</th>
                        <th class="p-4 text-center w-40">Total Aset Terkait</th>
                        <th class="p-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    <?php
                    $sql = "SELECT k.*, COUNT(b.id_barang) as total_barang 
                            FROM lab_kategori k 
                            LEFT JOIN barang b ON k.id_kategori = b.id_kategori 
                            GROUP BY k.id_kategori 
                            ORDER BY k.id_kategori DESC";
                    $query = mysqli_query($koneksi, $sql);

                    if (mysqli_num_rows($query) > 0) {
                        while ($row = mysqli_fetch_assoc($query)) {
                            ?>
                            <tr class="hover:bg-slate-50/40 transition-colors">
                                <td class="p-4 text-center text-text-muted font-medium">#<?php echo $row['id_kategori']; ?></td>
                                <td class="p-4 font-bold text-text-dark text-sm">
                                    <?php echo htmlspecialchars($row['nama_kategori']); ?>
                                </td>
                                <td class="p-4 text-center">
                                    <span
                                        class="inline-block px-3 py-1 bg-slate-50 text-slate-700 font-bold text-[11px] rounded-lg border border-slate-100">
                                        <?php echo number_format($row['total_barang']); ?> Perangkat
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            onclick="bukaModalEdit('<?php echo $row['id_kategori']; ?>', '<?php echo str_replace(["'", '"'], ["\\'", '\\"'], htmlspecialchars($row['nama_kategori'])); ?>')"
                                            class="w-7 h-7 bg-slate-50 border border-slate-200 text-text-muted hover:text-brand-blue hover:border-blue-200 rounded-lg flex items-center justify-center transition-all shadow-sm"
                                            title="Ubah Nama">
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>
                                        <a href="index.php?page=kategori&action=hapus&id=<?php echo $row['id_kategori']; ?>"
                                            onclick="return confirm('Hapus klasifikasi kategori ini?');"
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
                        echo '<tr><td colspan="4" class="p-8 text-center text-text-muted italic">Belum ada kategori terdaftar dalam sistem.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalTambah"
    class="fixed inset-0 bg-slate-950/20 backdrop-blur-sm z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
    <div
        class="bg-white w-full max-w-sm rounded-2xl border border-slate-200 shadow-2xl scale-95 transition-all duration-200 overflow-hidden flex flex-col">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-sm font-bold text-text-dark uppercase tracking-wide flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-brand-blue">create_new_folder</span> Tambah
                Kategori Baru
            </h3>
            <button onclick="tutupModal('modalTambah')"
                class="w-6 h-6 rounded-lg text-text-muted hover:bg-slate-100 flex items-center justify-center">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
        <form action="index.php?page=kategori" method="POST" class="p-5 space-y-4 text-xs">
            <div class="space-y-1.5">
                <label for="nama_kategori" class="block font-bold text-text-dark">Nama Kategori *</label>
                <input type="text" id="nama_kategori" name="nama_kategori" required
                    placeholder="Contoh: Perangkat Jaringan, Output Device"
                    class="w-full rounded-xl border-slate-200 focus:border-brand-blue focus:ring-brand-blue text-xs p-2.5">
            </div>
            <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="tutupModal('modalTambah')"
                    class="px-4 py-2 border border-slate-200 rounded-xl font-semibold text-text-muted hover:bg-slate-50">Batal</button>
                <button type="submit" name="tambah_kategori"
                    class="px-4 py-2 bg-brand-blue hover:bg-blue-700 text-white font-semibold rounded-xl shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit"
    class="fixed inset-0 bg-slate-950/20 backdrop-blur-sm z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
    <div
        class="bg-white w-full max-w-sm rounded-2xl border border-slate-200 shadow-2xl scale-95 transition-all duration-200 overflow-hidden flex flex-col">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-sm font-bold text-text-dark uppercase tracking-wide flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-brand-blue">drive_file_rename_outline</span>
                Perbarui Nama Kategori
            </h3>
            <button onclick="tutupModal('modalEdit')"
                class="w-6 h-6 rounded-lg text-text-muted hover:bg-slate-100 flex items-center justify-center">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
        <form action="index.php?page=kategori" method="POST" class="p-5 space-y-4 text-xs">
            <input type="hidden" id="edit_id_kategori" name="id_kategori">
            <div class="space-y-1.5">
                <label for="edit_nama_kategori" class="block font-bold text-text-dark">Nama Kategori Baru *</label>
                <input type="text" id="edit_nama_kategori" name="nama_kategori" required
                    class="w-full rounded-xl border-slate-200 focus:border-brand-blue focus:ring-brand-blue text-xs p-2.5">
            </div>
            <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="tutupModal('modalEdit')"
                    class="px-4 py-2 border border-slate-200 rounded-xl font-semibold text-text-muted hover:bg-slate-50">Batal</button>
                <button type="submit" name="edit_kategori"
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
        setTimeout(() => document.getElementById('nama_kategori').focus(), 100);
    }

    function bukaModalEdit(id_kategori, nama) {
        document.getElementById('edit_id_kategori').value = id_kategori;
        document.getElementById('edit_nama_kategori').value = nama;
        bukaModal('modalEdit');
        setTimeout(() => document.getElementById('edit_nama_kategori').focus(), 100);
    }

    ['modalTambah', 'modalEdit'].forEach(id => {
        document.getElementById(id).addEventListener('click', function (e) {
            if (e.target === this) tutupModal(id);
        });
    });

    setTimeout(() => {
        const alertBox = document.getElementById('alert-notif');
        if (alertBox) alertBox.remove();
    }, 4000);
</script>