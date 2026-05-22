<?php
if (!isset($koneksi)) {
    include 'koneksi.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_user = $_SESSION['user']['id_user'];
$pesan_sukses = "";
$pesan_gagal = "";

// 1. Ambil data user secara real-time langsung dari database (Data Lama)
$query_user = mysqli_query($koneksi, "SELECT * FROM user WHERE id_user = '$id_user'");
$data_user = mysqli_fetch_assoc($query_user);

// 2. Proses Simpan Data saat tombol disimpan ditekan (Data Baru)
if (isset($_POST['simpan_profil'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    // Validasi agar username atau email tidak kembar dengan akun orang lain
    $cek_kembar = mysqli_query($koneksi, "SELECT * FROM user WHERE (username='$username' OR email='$email') AND id_user != '$id_user'");

    if (mysqli_num_rows($cek_kembar) > 0) {
        $pesan_gagal = "Username atau Email sudah digunakan oleh akun lain!";
    } else {
        // Jika password baru diisi, enkripsi dengan MD5 sesuai skema pangkalan data
        if (!empty($password)) {
            $password_fix = md5($password);
            $query_update = "UPDATE user SET nama='$nama', username='$username', email='$email', password='$password_fix' WHERE id_user='$id_user'";
        } else {
            $query_update = "UPDATE user SET nama='$nama', username='$username', email='$email' WHERE id_user='$id_user'";
        }

        if (mysqli_query($koneksi, $query_update)) {
            $pesan_sukses = "Profil Anda berhasil diperbarui!";

            // Refresh data setelah update agar bagian "Data Lama" langsung berubah otomatis
            $query_user = mysqli_query($koneksi, "SELECT * FROM user WHERE id_user = '$id_user'");
            $data_user = mysqli_fetch_assoc($query_user);

            // PERBAIKAN UTAMA: Menyinkronkan seluruh data session baru agar komponen navbar/header langsung berubah
            $_SESSION['user'] = $data_user;

            echo "<script>
                setTimeout(() => {
                    window.location.href = 'index.php?page=pengaturan';
                }, 1000);
            </script>";
        } else {
            $pesan_gagal = "Gagal menyimpan perubahan: " . mysqli_error($koneksi);
        }
    }
}
?>

<div class="mt-16 p-6 max-w-4xl mx-auto w-full">

    <?php if (!empty($pesan_sukses)): ?>
        <div
            class="mb-4 p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg text-sm flex items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span>
            <?php echo $pesan_sukses; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($pesan_gagal)): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm flex items-center gap-2">
            <span class="material-symbols-outlined">error</span>
            <?php echo $pesan_gagal; ?>
        </div>
    <?php endif; ?>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pengaturan Kredensial</h1>
        <p class="text-sm text-gray-500">Lihat data lama di sebelah kiri dan masukkan data baru di sebelah kanan untuk
            memperbarui akun.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 shadow-sm h-fit space-y-4">
            <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">history</span> Data Lama (Saat Ini)
            </h2>

            <div>
                <span class="text-xs text-gray-500 block">Nama Lengkap:</span>
                <p class="text-sm font-semibold text-gray-800">
                    <?php echo htmlspecialchars($data_user['nama'] ?? '-'); ?>
                </p>
            </div>

            <div>
                <span class="text-xs text-gray-500 block">Username Akun:</span>
                <p class="text-sm font-semibold text-gray-800">
                    @<?php echo htmlspecialchars($data_user['username'] ?? '-'); ?></p>
            </div>

            <div>
                <span class="text-xs text-gray-500 block">Alamat Email:</span>
                <p class="text-sm font-semibold text-gray-800">
                    <?php echo htmlspecialchars($data_user['email'] ?? '-'); ?>
                </p>
            </div>
        </div>

        <div class="md:col-span-2 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <h2 class="text-sm font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1 mb-4">
                <span class="material-symbols-outlined text-sm">edit_note</span> Input Data Baru
            </h2>

            <form action="" method="POST" class="space-y-4">

                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap Baru</label>
                    <input type="text" id="nama" name="nama" required
                        value="<?php echo htmlspecialchars($data_user['nama'] ?? ''); ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username Baru</label>
                        <input type="text" id="username" name="username" required
                            value="<?php echo htmlspecialchars($data_user['username'] ?? ''); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Alamat Email
                            Baru</label>
                        <input type="email" id="email" name="email" required
                            value="<?php echo htmlspecialchars($data_user['email'] ?? ''); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Baru
                        (Kosongkan jika tidak ingin diubah)</label>
                    <input type="password" id="password" name="password" placeholder="••••••••"
                        autocomplete="new-password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                    <a href="index.php?page=home"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition-colors">Batal</a>
                    <button type="submit" name="simpan_profil"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 font-medium shadow-sm transition-colors">
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>