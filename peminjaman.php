<?php
// ====================================================
// LOGIKA UTAMA HALAMAN PEMINJAMAN (ROLE-BASED SYSTEM)
// ====================================================
if (!isset($koneksi)) {
    include 'koneksi.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pesan_sukses = "";
$pesan_gagal  = "";

// Ambil info user dari session yang sedang aktif
$id_user   = $_SESSION['user']['id_user'] ?? 0;
$user_role = $_SESSION['user']['role'] ?? 'siswa';

// ----------------------------------------------------
// [PROSES SISWA] LOGIKA INPUT AJUAN PINJAM BARANG
// ----------------------------------------------------
if ($user_role === 'siswa' && isset($_POST['ajukan_peminjaman'])) {
    $id_barang     = mysqli_real_escape_string($koneksi, $_POST['id_barang']);
    $jumlah_pinjam = (int)$_POST['jumlah_pinjam'];
    $tgl_kembali   = mysqli_real_escape_string($koneksi, $_POST['tgl_kembali']);

    if ($id_barang > 0 && $jumlah_pinjam > 0 && !empty($tgl_kembali)) {
        // Cek ketersediaan stok di database
        $cek_stok_q = mysqli_query($koneksi, "SELECT nama, jumlah FROM barang WHERE id_barang = '$id_barang'");
        $data_stok  = mysqli_fetch_assoc($cek_stok_q);

        if ($data_stok) {
            if ($data_stok['jumlah'] >= $jumlah_pinjam) {
                $tgl_pinjam = date('Y-m-d');
                // Status default saat siswa mengajukan adalah 'pending'
                $insert = mysqli_query($koneksi, "INSERT INTO peminjaman (id_user, id_barang, jumlah_pinjam, tgl_peminjaman, tgl_pengembalian, status) 
                          VALUES ('$id_user', '$id_barang', '$jumlah_pinjam', '$tgl_pinjam', '$tgl_kembali', 'pending')");
                
                if ($insert) {
                    $pesan_sukses = "Permohonan pinjam <strong>" . htmlspecialchars($data_stok['nama']) . "</strong> berhasil dikirim! Menunggu konfirmasi Admin/Guru.";
                } else {
                    $pesan_gagal = "Gagal memproses pengajuan: " . mysqli_error($koneksi);
                }
            } else {
                $pesan_gagal = "Stok tidak mencukupi! Sisa stok " . htmlspecialchars($data_stok['nama']) . " saat ini adalah " . $data_stok['jumlah'] . " unit.";
            }
        } else {
            $pesan_gagal = "Barang tidak ditemukan di database.";
        }
    } else {
        $pesan_gagal = "Harap isi jumlah unit dan tanggal batas pengembalian dengan benar.";
    }
}

// ----------------------------------------------------
// [PROSES ADMIN / GURU] LOGIKA KONFIRMASI STATUS PINJAM
// ----------------------------------------------------
if (in_array($user_role, ['admin', 'guru']) && isset($_GET['action'])) {
    $action_id = (int)$_GET['id'];
    $status_to = $_GET['action'];

    if ($action_id > 0) {
        $detail_q = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_peminjaman = '$action_id'");
        $peminjaman = mysqli_fetch_assoc($detail_q);

        if ($peminjaman) {
            $id_b   = $peminjaman['id_barang'];
            $jml_p  = $peminjaman['jumlah_pinjam'];
            $status_sebelumnya = $peminjaman['status'];

            // Aksi 1: Setujui Peminjaman (Potong Stok)
            if ($status_to === 'disetujui' && $status_sebelumnya === 'pending') {
                $update_stok = mysqli_query($koneksi, "UPDATE barang SET jumlah = jumlah - $jml_p WHERE id_barang = '$id_b'");
                $update_status = mysqli_query($koneksi, "UPDATE peminjaman SET status = 'disetujui' WHERE id_peminjaman = '$action_id'");
                
                if ($update_status && $update_stok) {
                    $pesan_sukses = "Peminjaman ID #$action_id Berhasil Disetujui! Stok barang otomatis dikurangi.";
                }
            } 
            // Aksi 2: Tolak Peminjaman
            elseif ($status_to === 'ditolak' && $status_sebelumnya === 'pending') {
                $update_status = mysqli_query($koneksi, "UPDATE peminjaman SET status = 'ditolak' WHERE id_peminjaman = '$action_id'");
                if ($update_status) {
                    $pesan_sukses = "Permohonan peminjaman ID #$action_id telah ditolak.";
                }
            } 
            // Aksi 3: Konfirmasi Pengembalian Barang (Pulangkan Stok semula)
            elseif ($status_to === 'dikembalikan' && $status_sebelumnya === 'disetujui') {
                $update_stok = mysqli_query($koneksi, "UPDATE barang SET jumlah = jumlah + $jml_p WHERE id_barang = '$id_b'");
                $update_status = mysqli_query($koneksi, "UPDATE peminjaman SET status = 'dikembalikan' WHERE id_peminjaman = '$action_id'");
                
                if ($update_status && $update_stok) {
                    $pesan_sukses = "Barang pada ID #$action_id telah dinyatakan kembali. Stok gudang bertambah.";
                }
            }
        }
    }
}
?>

<div class="mt-16 p-xl max-w-container-max mx-auto w-full">
    
    <div class="mb-xl">
        <h1 class="font-display-lg text-display-lg text-primary mb-xs font-bold">Modul Logistik Peminjaman</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant">
            <?= $user_role === 'siswa' ? 'Ajukan permohonan peminjaman barang laboratorium komputer.' : 'Halaman khusus Admin/Guru untuk memvalidasi dan mengontrol sirkulasi alat lab.' ?>
        </p>
    </div>

    <?php if (!empty($pesan_sukses)): ?>
        <div id="alert-notif" class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3 shadow-sm transition-all duration-300">
            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            <div class="text-sm"><?= $pesan_sukses; ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($pesan_gagal)): ?>
        <div id="alert-notif" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3 shadow-sm transition-all duration-300">
            <span class="material-symbols-outlined text-red-600">error</span>
            <div class="text-sm"><?= $pesan_gagal; ?></div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-xl">
        
        <?php if ($user_role === 'siswa'): ?>
            <div class="bg-white p-xl rounded-2xl border border-outline-variant shadow-sm h-fit">
                <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">add_shopping_cart</span> Form Ajukan Pinjaman
                </h3>
                <form action="index.php?page=peminjaman" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Pilih Barang</label>
                        <select name="id_barang" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <option value="">-- Pilih Barang Tersedia --</option>
                            <?php
                            $barang_q = mysqli_query($koneksi, "SELECT * FROM barang WHERE jumlah > 0 AND status = 'baik'");
                            while ($b = mysqli_fetch_assoc($barang_q)) {
                                echo "<option value='{$b['id_barang']}'>{$b['nama']} - {$b['merek']} (Tersedia: {$b['jumlah']})</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Jumlah (Unit)</label>
                        <input type="number" name="jumlah_pinjam" min="1" required placeholder="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Tanggal Batas Pengembalian</label>
                        <input type="date" name="tgl_kembali" required min="<?= date('Y-m-d'); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary" />
                    </div>

                    <button type="submit" name="ajukan_peminjaman" class="w-full py-2.5 bg-primary hover:bg-primary-container text-white rounded-lg text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">send</span> Kirim Pengajuan
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="<?= $user_role === 'siswa' ? 'lg:col-span-2' : 'lg:col-span-3' ?> bg-white rounded-2xl border border-outline-variant shadow-sm overflow-hidden">
            <div class="px-xl py-lg border-b border-outline-variant flex justify-between items-center bg-slate-50/50">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary">list_alt</span> 
                    <?= $user_role === 'siswa' ? 'Riwayat Peminjaman Saya' : 'Daftar Antrean Konfirmasi Siswa' ?>
                </h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary uppercase">Sistem Aktif</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant bg-slate-50 text-xs font-bold text-gray-500 uppercase">
                            <th class="p-4">ID</th>
                            <?php if ($user_role !== 'siswa'): ?>
                                <th class="p-4">Nama Siswa</th>
                            <?php endif; ?>
                            <th class="p-4">Barang</th>
                            <th class="p-4 text-center">Jumlah</th>
                            <th class="p-4">Tgl Pinjam</th>
                            <th class="p-4">Batas Kembali</th>
                            <th class="p-4 text-center">Status</th>
                            <?php if ($user_role !== 'siswa'): ?>
                                <th class="p-4 text-center">Aksi Otoritas</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        <?php
                        // Filter query: Siswa hanya melihat miliknya, Admin/Guru melihat semua data request
                        if ($user_role === 'siswa') {
                            $sql = "SELECT p.*, b.nama as nama_barang, b.merek 
                                    FROM peminjaman p 
                                    JOIN barang b ON p.id_barang = b.id_barang 
                                    WHERE p.id_user = '$id_user' 
                                    ORDER BY p.id_peminjaman DESC";
                        } else {
                            $sql = "SELECT p.*, b.nama as nama_barang, b.merek, u.nama as nama_user 
                                    FROM peminjaman p 
                                    JOIN barang b ON p.id_barang = b.id_barang 
                                    JOIN user u ON p.id_user = u.id_user 
                                    ORDER BY FIELD(p.status, 'pending', 'disetujui', 'ditolak', 'dikembalikan'), p.id_peminjaman DESC";
                        }

                        $query = mysqli_query($koneksi, $sql);

                        if (mysqli_num_rows($query) == 0) {
                            $col_span = $user_role === 'siswa' ? 6 : 8;
                            echo "<tr><td colspan='{$col_span}' class='p-8 text-center text-gray-400 font-medium italic'><span class='material-symbols-outlined text-3xl block mb-1'>folder_open</span>Belum ada rekam data peminjaman.</td></tr>";
                        } else {
                            while ($row = mysqli_fetch_assoc($query)) {
                                // Klasifikasi pewarnaan badge status
                                $badge_class = "bg-amber-100 text-amber-800 border-amber-200"; // pending
                                if ($row['status'] === 'disetujui') $badge_class = "bg-blue-100 text-blue-800 border-blue-200";
                                if ($row['status'] === 'ditolak') $badge_class = "bg-red-100 text-red-800 border-red-200";
                                if ($row['status'] === 'dikembalikan') $badge_class = "bg-emerald-100 text-emerald-800 border-emerald-200";
                                ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4 font-bold text-gray-400">#<?= $row['id_peminjaman']; ?></td>
                                    <?php if ($user_role !== 'siswa'): ?>
                                        <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($row['nama_user']); ?></td>
                                    <?php endif; ?>
                                    <td class="p-4">
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($row['nama_barang']); ?></div>
                                        <div class="text-xs text-gray-400"><?= htmlspecialchars($row['merek']); ?></div>
                                    </td>
                                    <td class="p-4 text-center font-bold"><?= $row['jumlah_pinjam']; ?></td>
                                    <td class="p-4 text-xs"><?= date('d M Y', strtotime($row['tgl_peminjaman'])); ?></td>
                                    <td class="p-4 text-xs"><?= date('d M Y', strtotime($row['tgl_pengembalian'])); ?></td>
                                    <td class="p-4 text-center">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold uppercase border <?= $badge_class; ?>">
                                            <?= $row['status']; ?>
                                        </span>
                                    </td>
                                    
                                    <?php if ($user_role !== 'siswa'): ?>
                                        <td class="p-4 text-center flex items-center justify-center gap-1.5">
                                            <?php if ($row['status'] === 'pending'): ?>
                                                <a href="index.php?page=peminjaman&id=<?= $row['id_peminjaman']; ?>&action=disetujui" 
                                                   class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold flex items-center gap-0.5 transition-all" title="Setujui">
                                                    <span class="material-symbols-outlined text-[14px]">done</span> Setuju
                                                </a>
                                                <a href="index.php?page=peminjaman&id=<?= $row['id_peminjaman']; ?>&action=ditolak" 
                                                   class="px-2 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-bold flex items-center gap-0.5 transition-all" title="Tolak">
                                                    <span class="material-symbols-outlined text-[14px]">close</span> Tolak
                                                </a>
                                            <?php elseif ($row['status'] === 'disetujui'): ?>
                                                <a href="index.php?page=peminjaman&id=<?= $row['id_peminjaman']; ?>&action=dikembalikan" 
                                                   class="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-bold flex items-center gap-1 transition-all" title="Selesai Dikembalikan">
                                                    <span class="material-symbols-outlined text-[14px]">assignment_return</span> Konfirmasi Kembali
                                                </a>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400 italic">Arsip Selesai</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
    // Auto remove alert dalam 4 detik agar halaman rapi kembali
    setTimeout(() => {
        const alertBox = document.getElementById('alert-notif');
        if (alertBox) {
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 300);
        }
    }, 4000);
</script>