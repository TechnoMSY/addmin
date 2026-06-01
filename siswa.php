<?php
// Proteksi baris ganda: Memastikan berkas ini tidak diakses langsung via URL tanpa melalui index.php
if (!isset($currentPage)) {
    header("Location: index.php?page=home");
    exit();
}

// Proteksi Hak Akses Tingkat Lanjut (RBAC): Hanya Admin dan Guru yang boleh mengakses halaman ini
if (strtolower($role_user) !== 'admin' && strtolower($role_user) !== 'guru') {
    echo "<div class='p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs font-medium flex items-center gap-2'>
            <span class='material-symbols-outlined text-base'>error</span>
            Akses Ditolak: Anda tidak memiliki kredensial yang cukup untuk mengelola data siswa.
          </div>";
    exit();
}

$notif = '';

// ==========================================
// LOGIKA PEMROSESAN AKSI (BACKEND ENGINE)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strtolower($role_user) === 'admin') {

    // 1. PROSES TAMBAH SISWA
    if (isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
        $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
        $username = mysqli_real_escape_string($koneksi, $_POST['username']);
        $email = mysqli_real_escape_string($koneksi, $_POST['email']);
        $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
        $password = md5($_POST['password']); // Menggunakan enkripsi MD5 sesuai dump database Anda

        // Cek duplikasi username atau email
        $cek = mysqli_query($koneksi, "SELECT id_user FROM user WHERE username='$username' OR email='$email'");
        if (mysqli_num_rows($cek) > 0) {
            $notif = "<div id='alert-notif' class='p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-xs font-medium flex items-center gap-2 mb-4 shadow-sm'>
                        <span class='material-symbols-outlined text-base'>warning</span> Gagal Menambahkan: Username atau Email sudah terdaftar!
                      </div>";
        } else {
            $insert = mysqli_query($koneksi, "INSERT INTO user (nama, username, password, email, level, alamat) VALUES ('$nama', '$username', '$password', '$email', 'siswa', '$alamat')");
            if ($insert) {
                $notif = "<div id='alert-notif' class='p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-xs font-medium flex items-center gap-2 mb-4 shadow-sm'>
                            <span class='material-symbols-outlined text-base'>check_circle</span> Berhasil menambahkan data siswa baru.
                          </div>";
            }
        }
    }

    // 2. PROSES EDIT SISWA
    if (isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
        $id_user = intval($_POST['id_user']);
        $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
        $username = mysqli_real_escape_string($koneksi, $_POST['username']);
        $email = mysqli_real_escape_string($koneksi, $_POST['email']);
        $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);

        // Proteksi validasi duplikasi untuk user lain
        $cek = mysqli_query($koneksi, "SELECT id_user FROM user WHERE (username='$username' OR email='$email') AND id_user != $id_user");
        if (mysqli_num_rows($cek) > 0) {
            $notif = "<div id='alert-notif' class='p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-xs font-medium flex items-center gap-2 mb-4 shadow-sm'>
                        <span class='material-symbols-outlined text-base'>warning</span> Gagal Memperbarui: Username atau Email sudah digunakan siswa lain!
                      </div>";
        } else {
            // Jika kolom password diisi, ubah password. Jika kosong, pertahankan password lama.
            if (!empty($_POST['password'])) {
                $password = md5($_POST['password']);
                $update = mysqli_query($koneksi, "UPDATE user SET nama='$nama', username='$username', password='$password', email='$email', alamat='$alamat' WHERE id_user=$id_user AND level='siswa'");
            } else {
                $update = mysqli_query($koneksi, "UPDATE user SET nama='$nama', username='$username', email='$email', alamat='$alamat' WHERE id_user=$id_user AND level='siswa'");
            }

            if ($update) {
                $notif = "<div id='alert-notif' class='p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-xs font-medium flex items-center gap-2 mb-4 shadow-sm'>
                            <span class='material-symbols-outlined text-base'>check_circle</span> Perubahan data siswa berhasil disimpan.
                          </div>";
            }
        }
    }

    // 3. PROSES HAPUS SISWA
    if (isset($_POST['aksi']) && $_POST['aksi'] === 'hapus') {
        $id_user = intval($_POST['id_user']);
        $delete = mysqli_query($koneksi, "DELETE FROM user WHERE id_user=$id_user AND level='siswa'");
        if ($delete) {
            $notif = "<div id='alert-notif' class='p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-xs font-medium flex items-center gap-2 mb-4 shadow-sm'>
                        <span class='material-symbols-outlined text-base'>delete_sweep</span> Akun siswa berhasil dihapus dari sistem.
                      </div>";
        }
    }
}

// Ambil ulang data siswa untuk dirender ke tabel
$querySiswa = "SELECT id_user, nama, username, email, alamat FROM user WHERE level = 'siswa' ORDER BY nama ASC";
$resultSiswa = mysqli_query($koneksi, $querySiswa);
?>

<div class="space-y-6 animate-[fadeIn_0.3s_ease-out]">

    <?php echo $notif; ?>

    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-base font-black text-slate-900 tracking-wide uppercase">Manajemen Data Siswa</h1>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Daftar hak akses dan akun siswa yang terdaftar dalam
                sirkulasi laboratorium.</p>
        </div>
        <?php if (strtolower($role_user) === 'admin'): ?>
            <button onclick="bukaModalTambah()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-blue-500/10">
                <span class="material-symbols-outlined text-sm">person_add</span>
                Tambah Siswa Baru
            </button>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold tracking-widest border-b border-slate-200/60">
                        <th class="px-6 py-3.5 border-r border-slate-100 w-12 text-center">No</th>
                        <th class="px-6 py-3.5">Nama Lengkap</th>
                        <th class="px-6 py-3.5">Username</th>
                        <th class="px-6 py-3.5">Email Sistem</th>
                        <th class="px-6 py-3.5">Alamat Tempat Tinggal</th>
                        <?php if (strtolower($role_user) === 'admin'): ?>
                            <th class="px-6 py-3.5 text-right">Tindakan</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    <?php
                    if (mysqli_num_rows($resultSiswa) > 0):
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($resultSiswa)):
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-slate-400 font-medium border-r border-slate-100 text-center">
                                    <?php echo $no++; ?></td>
                                <td class="px-6 py-4 font-bold text-slate-900"><?php echo htmlspecialchars($row['nama']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="font-mono text-slate-600 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded text-[11px]">
                                        <?php echo htmlspecialchars($row['username']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="px-6 py-4 text-slate-500 max-w-xs truncate"
                                    title="<?php echo htmlspecialchars($row['alamat']); ?>">
                                    <?php echo htmlspecialchars($row['alamat'] ?: '-'); ?>
                                </td>
                                <?php if (strtolower($role_user) === 'admin'): ?>
                                    <td class="px-6 py-4 text-right">
                                        <div class="inline-flex gap-1.5">
                                            <button
                                                onclick="bukaModalEdit('<?php echo $row['id_user']; ?>', '<?php echo addslashes($row['nama']); ?>', '<?php echo addslashes($row['username']); ?>', '<?php echo addslashes($row['email']); ?>', '<?php echo addslashes($row['alamat']); ?>')"
                                                class="p-1.5 hover:bg-amber-50 text-amber-600 hover:text-amber-700 rounded-lg border border-transparent hover:border-amber-200/60 transition-all shadow-sm"
                                                title="Ubah Akun Siswa">
                                                <span class="material-symbols-outlined text-base block">edit</span>
                                            </button>
                                            <button
                                                onclick="mintaHapus('<?php echo $row['id_user']; ?>', '<?php echo addslashes($row['nama']); ?>')"
                                                class="p-1.5 hover:bg-red-50 text-red-500 hover:text-red-600 rounded-lg border border-transparent hover:border-red-200/60 transition-all shadow-sm"
                                                title="Hapus Akun Siswa">
                                                <span class="material-symbols-outlined text-base block">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="<?php echo (strtolower($role_user) === 'admin') ? '6' : '5'; ?>"
                                class="px-6 py-10 text-center text-slate-400 font-medium">
                                <span class="material-symbols-outlined text-3xl block text-slate-300 mb-2">group_off</span>
                                Tidak ada data siswa yang terdaftar di dalam database saat ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div
            class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-wider">
            <span>Total Entitas Terfilter:</span>
            <span class="bg-blue-50 text-blue-600 border border-blue-100 px-2 py-0.5 rounded-md">
                <?php echo mysqli_num_rows($resultSiswa); ?> Siswa Aktif
            </span>
        </div>
    </div>
</div>

<?php if (strtolower($role_user) === 'admin'): ?>
    <form id="formHapus" method="POST" class="hidden">
        <input type="hidden" name="aksi" value="hapus">
        <input type="hidden" name="id_user" id="hapus_id_user">
    </form>

    <div id="modalSiswa"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300 bg-slate-900/40 backdrop-blur-sm">
        <div
            class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full overflow-hidden transform scale-95 transition-all duration-300">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 id="modalTitle" class="text-xs font-black text-slate-900 uppercase tracking-wider">Tambah Data Siswa
                </h3>
                <button onclick="tutupModal()" class="text-slate-400 hover:text-slate-600 font-medium">&times;</button>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="aksi" id="form_aksi" value="tambah">
                <input type="hidden" name="id_user" id="form_id_user">

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama
                        Lengkap</label>
                    <input type="text" name="nama" id="form_nama" required
                        class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Username</label>
                        <input type="text" name="username" id="form_username" required
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Email
                            Sistem</label>
                        <input type="email" name="email" id="form_email" required
                            class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Password</label>
                    <input type="password" name="password" id="form_password"
                        class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                    <p id="passwordHelp" class="text-[9px] text-slate-400 mt-1 hidden">*Kosongkan jika tidak ingin mengubah
                        password lama.</p>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Alamat
                        Rumah</label>
                    <textarea name="alamat" id="form_alamat" rows="2"
                        class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                    <button type="button" onclick="tutupModal()"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition-colors">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/10 transition-colors">Simpan
                        Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalSiswa');
        const modalContent = modal.querySelector('div');

        function bukaModalTambah() {
            document.getElementById('modalTitle').textContent = 'Tambah Data Siswa';
            document.getElementById('form_aksi').value = 'tambah';
            document.getElementById('form_id_user').value = '';
            document.getElementById('form_nama').value = '';
            document.getElementById('form_username').value = '';
            document.getElementById('form_email').value = '';
            document.getElementById('form_alamat').value = '';
            document.getElementById('form_password').required = true;
            document.getElementById('passwordHelp').classList.add('hidden');

            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }

        function bukaModalEdit(id, nama, username, email, alamat) {
            document.getElementById('modalTitle').textContent = 'Ubah Data Siswa';
            document.getElementById('form_aksi').value = 'edit';
            document.getElementById('form_id_user').value = id;
            document.getElementById('form_nama').value = nama;
            document.getElementById('form_username').value = username;
            document.getElementById('form_email').value = email;
            document.getElementById('form_alamat').value = alamat;
            document.getElementById('form_password').required = false; // Opsional saat edit
            document.getElementById('passwordHelp').classList.remove('hidden');

            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }

        function tutupModal() {
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
        }

        function mintaHapus(id, nama) {
            if (confirm(`Apakah Anda yakin ingin menghapus akun siswa "${nama}"?\nSeluruh data histori peminjaman siswa ini akan otomatis terhapus.`)) {
                document.getElementById('hapus_id_user').value = id;
                document.getElementById('formHapus').submit();
            }
        }

        // Menutup modal otomatis jika area luar modal diklik
        modal.addEventListener('click', function (e) {
            if (e.target === this) tutupModal();
        });

        // Auto close alert banner dalam 4 detik
        setTimeout(() => {
            const alertBox = document.getElementById('alert-notif');
            if (alertBox) alertBox.remove();
        }, 4000);
    </script>
<?php endif; ?>