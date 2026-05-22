<?php


if (!isset($koneksi)) {
    include 'koneksi.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pesan_sukses = "";
$pesan_gagal = "";

$id_user = $_SESSION['user']['id_user'] ?? 0;
$user_role = $_SESSION['user']['role'] ?? $_SESSION['user']['level'] ?? 'siswa';

// 1. LOGIKA SISWA: AJUAN PINJAM BARANG
if ($user_role === 'siswa' && isset($_POST['ajukan_peminjaman'])) {
    $id_barang = mysqli_real_escape_string($koneksi, $_POST['id_barang']);
    $jumlah_pinjam = (int) $_POST['jumlah_pinjam'];
    $tanggal_peminjaman = mysqli_real_escape_string($koneksi, $_POST['tanggal_peminjaman']);

    if ($id_barang > 0 && $jumlah_pinjam > 0 && !empty($tanggal_peminjaman)) {
        $cek_stok_q = mysqli_query($koneksi, "SELECT nama, jumlah, status FROM barang WHERE id_barang='$id_barang'");
        $stok_data = mysqli_fetch_assoc($cek_stok_q);

        if ($stok_data) {
            if ($stok_data['status'] !== 'baik') {
                $pesan_gagal = "Gagal mengajukan! Unit perangkat <strong>" . htmlspecialchars($stok_data['nama']) . "</strong> sedang dalam kondisi rusak atau tidak layak pinjam.";
            } elseif ($stok_data['jumlah'] < $jumlah_pinjam) {
                $pesan_gagal = "Gagal mengajukan! Stok unit <strong>" . htmlspecialchars($stok_data['nama']) . "</strong> tidak mencukupi (Sisa stok: " . $stok_data['jumlah'] . ").";
            } else {
                // Default saat diajukan: status_peminjaman='pending', status='dipinjam', kondisi='baik'
                $query_insert = "INSERT INTO peminjaman (id_user, id_barang, jumlah_pinjam, status_peminjaman, tanggal_peminjaman, status, kondisi) 
                                 VALUES ('$id_user', '$id_barang', '$jumlah_pinjam', 'pending', '$tanggal_peminjaman', 'dipinjam', 'baik')";

                $insert = mysqli_query($koneksi, $query_insert);

                if ($insert) {
                    $pesan_sukses = "Ajuan peminjaman sebanyak <strong>$jumlah_pinjam unit</strong> berhasil dikirim! Menunggu verifikasi Admin/Guru.";
                } else {
                    $pesan_gagal = "Gagal menyimpan ajuan peminjaman: " . mysqli_error($koneksi);
                }
            }
        } else {
            $pesan_gagal = "Data perangkat tidak valid atau tidak ditemukan.";
        }
    } else {
        $pesan_gagal = "Form tidak lengkap! Pastikan tanggal peminjaman dan jumlah pinjam telah diisi.";
    }
}

// 2. LOGIKA ADMIN / GURU: PROSES VERIFIKASI (SETUJU / TOLAK)
if (($user_role === 'admin' || $user_role === 'guru') && isset($_POST['aksi_peminjaman'])) {
    $id_peminjaman = mysqli_real_escape_string($koneksi, $_POST['id_peminjaman']);
    $status_baru = mysqli_real_escape_string($koneksi, $_POST['status_baru']); // 'disetujui' atau 'ditolak'

    $cek_pjm = mysqli_query($koneksi, "SELECT p.*, b.nama as nama_barang, b.jumlah as stok_sekarang 
                                        FROM peminjaman p 
                                        JOIN barang b ON p.id_barang = b.id_barang 
                                        WHERE p.id_peminjaman = '$id_peminjaman'");
    $data_pjm = mysqli_fetch_assoc($cek_pjm);

    if ($data_pjm) {
        if ($status_baru === 'disetujui') {
            $id_brg = $data_pjm['id_barang'];
            $jml_pjm = $data_pjm['jumlah_pinjam'];
            $stok_sisa = $data_pjm['stok_sekarang'] - $jml_pjm;

            if ($stok_sisa < 0) {
                $pesan_gagal = "Gagal menyetujui! Stok perangkat <strong>" . htmlspecialchars($data_pjm['nama_barang']) . "</strong> tidak mencukupi.";
            } else {
                mysqli_query($koneksi, "UPDATE peminjaman SET status_peminjaman='disetujui', status='dipinjam' WHERE id_peminjaman='$id_peminjaman'");
                mysqli_query($koneksi, "UPDATE barang SET jumlah='$stok_sisa' WHERE id_barang='$id_brg'");
                $pesan_sukses = "Permintaan peminjaman berhasil disetujui. Stok perangkat telah dikurangi.";
            }
        } elseif ($status_baru === 'ditolak') {
            mysqli_query($koneksi, "UPDATE peminjaman SET status_peminjaman='ditolak' WHERE id_peminjaman='$id_peminjaman'");
            $pesan_sukses = "Permintaan peminjaman perangkat telah ditolak.";
        }
    } else {
        $pesan_gagal = "Data peminjaman tidak ditemukan.";
    }
}

// 3. LOGIKA ADMIN / GURU: PROSES PENGEMBALIAN BARANG (FITUR BARU)
if (($user_role === 'admin' || $user_role === 'guru') && isset($_POST['proses_pengembalian'])) {
    $id_peminjaman = mysqli_real_escape_string($koneksi, $_POST['id_peminjaman']);
    $kondisi_kembali = mysqli_real_escape_string($koneksi, $_POST['kondisi_kembali']); // 'baik' atau 'rusak'
    $tgl_sekarang = date('Y-m-d');

    // Ambil info detail peminjaman
    $cek_pjm = mysqli_query($koneksi, "SELECT p.*, b.jumlah as stok_sekarang FROM peminjaman p 
                                        JOIN barang b ON p.id_barang = b.id_barang 
                                        WHERE p.id_peminjaman = '$id_peminjaman'");
    $data_pjm = mysqli_fetch_assoc($cek_pjm);

    if ($data_pjm) {
        $id_brg = $data_pjm['id_barang'];
        $jml_pjm = $data_pjm['jumlah_pinjam'];
        $stok_baru = $data_pjm['stok_sekarang'] + $jml_pjm;

        // 1. Update status peminjaman menjadi dikembalikan beserta tanggal & kondisi pengembaliannya
        $update_pjm = mysqli_query($koneksi, "UPDATE peminjaman 
                                               SET status='dikembalikan', 
                                                   kondisi='$kondisi_kembali', 
                                                   tanggal_pengembalian='$tgl_sekarang' 
                                               WHERE id_peminjaman='$id_peminjaman'");

        // 2. Kembalikan jumlah stok barang ke tabel barang
        $update_brg = mysqli_query($koneksi, "UPDATE barang SET jumlah='$stok_baru' WHERE id_barang='$id_brg'");

        if ($update_pjm && $update_brg) {
            $pesan_sukses = "Perangkat berhasil dikembalikan dengan kondisi: <strong>" . strtoupper($kondisi_kembali) . "</strong>. Stok barang telah diperbarui otomatis.";
        } else {
            $pesan_gagal = "Gagal memproses pengembalian: " . mysqli_error($koneksi);
        }
    } else {
        $pesan_gagal = "Data transaksi peminjaman tidak valid.";
    }
}
?>

<div class="w-full space-y-6 animate-[fadeIn_0.4s_ease-out]">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-base font-bold text-slate-800">Manajemen Peminjaman & Pengembalian</h2>
            <p class="text-xs text-slate-500 mt-0.5">Sistem sirkulasi log, pengajuan, pengembalian, dan verifikasi
                perangkat laboratorium.</p>
        </div>
        <?php if ($user_role === 'siswa'): ?>
            <button onclick="bukaModalPinjam()"
                class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg flex items-center gap-2 uppercase tracking-wider">
                <span class="material-symbols-outlined text-base">add_circle</span>
                Ajukan Peminjaman
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($pesan_sukses)): ?>
        <div class="p-4 bg-green-50 border border-green-200 rounded-xl flex items-start gap-3 text-green-700 text-xs">
            <span class="material-symbols-outlined text-base text-green-500 mt-0.5">check_circle</span>
            <div><?php echo $pesan_sukses; ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($pesan_gagal)): ?>
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3 text-red-700 text-xs">
            <span class="material-symbols-outlined text-base text-red-500 mt-0.5">error</span>
            <div><?php echo $pesan_gagal; ?></div>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Daftar Sirkulasi Log Perangkat</h3>
            <span class="text-[10px] px-2.5 py-1 bg-slate-200 text-slate-700 font-bold rounded-full uppercase">
                Role: <?php echo htmlspecialchars($user_role); ?>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="border-b border-slate-200 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Peminjam (Siswa)</th>
                        <th class="py-3.5 px-4">Nama Perangkat</th>
                        <th class="py-3.5 px-4 text-center">Tgl Pinjam</th>
                        <th class="py-3.5 px-4 text-center">Jumlah</th>
                        <th class="py-3.5 px-4 text-center">Verifikasi</th>
                        <th class="py-3.5 px-4 text-center">Status Pengembalian</th>
                        <?php if ($user_role === 'admin' || $user_role === 'guru'): ?>
                            <th class="py-3.5 px-5 text-center w-40">Aksi Kelola</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    <?php
                    if ($user_role === 'siswa') {
                        $query_tabel = "SELECT p.*, b.nama as nama_barang, u.nama as nama_peminjam 
                                        FROM peminjaman p
                                        JOIN barang b ON p.id_barang = b.id_barang
                                        JOIN user u ON p.id_user = u.id_user
                                        WHERE p.id_user = '$id_user'
                                        ORDER BY p.id_peminjaman DESC";
                    } else {
                        $query_tabel = "SELECT p.*, b.nama as nama_barang, u.nama as nama_peminjam 
                                        FROM peminjaman p
                                        JOIN barang b ON p.id_barang = b.id_barang
                                        JOIN user u ON p.id_user = u.id_user
                                        ORDER BY CASE WHEN p.status_peminjaman = 'pending' THEN 1 WHEN p.status = 'dipinjam' THEN 2 ELSE 3 END, p.id_peminjaman DESC";
                    }

                    $tampil = mysqli_query($koneksi, $query_tabel);
                    $no = 1;

                    if (mysqli_num_rows($tampil) > 0) {
                        while ($row = mysqli_fetch_assoc($tampil)) {
                            $status_pjm = $row['status_peminjaman'];
                            $status_kembali = $row['status'];
                            $kondisi = $row['kondisi'];

                            // Badge 1: Status Pengajuan (Verifikasi)
                            if ($status_pjm === 'pending') {
                                $badge_verif = "<span class='px-2 py-0.5 bg-amber-50 border border-amber-200 text-amber-700 font-semibold rounded-full text-[10px] uppercase'>Pending</span>";
                            } elseif ($status_pjm === 'disetujui') {
                                $badge_verif = "<span class='px-2 py-0.5 bg-green-50 border border-green-200 text-green-700 font-semibold rounded-full text-[10px] uppercase'>Disetujui</span>";
                            } else {
                                $badge_verif = "<span class='px-2 py-0.5 bg-red-50 border border-red-200 text-red-700 font-semibold rounded-full text-[10px] uppercase'>Ditolak</span>";
                            }

                            // Badge 2: Status Pengembalian Fisik Barang
                            if ($status_pjm !== 'disetujui') {
                                $badge_kembali = "<span class='text-slate-400'>-</span>";
                            } else {
                                if ($status_kembali === 'dipinjam') {
                                    $badge_kembali = "<span class='px-2 py-0.5 bg-blue-50 border border-blue-200 text-blue-700 font-semibold rounded-full text-[10px] uppercase'>Sedang Dipinjam</span>";
                                } elseif ($status_kembali === 'dikembalikan') {
                                    $warna_kondisi = ($kondisi === 'rusak') ? 'text-red-600 bg-red-50 border-red-200' : 'text-slate-600 bg-slate-100 border-slate-300';
                                    $badge_kembali = "<div class='flex flex-col items-center gap-1'>
                                                        <span class='px-2 py-0.5 bg-gray-100 border border-gray-300 text-gray-700 font-semibold rounded-full text-[10px] uppercase'>Dikembalikan</span>
                                                        <span class='text-[9px] px-1.5 py-0.2 rounded border $warna_kondisi font-medium uppercase'>Kondisi: $kondisi</span>
                                                      </div>";
                                } else {
                                    $badge_kembali = "<span class='px-2 py-0.5 bg-red-100 border border-red-300 text-red-700 font-semibold rounded-full text-[10px] uppercase'>Terlambat</span>";
                                }
                            }
                            ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-5 text-center font-medium text-slate-400"><?php echo $no++; ?></td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    <?php echo htmlspecialchars($row['nama_peminjam']); ?>
                                </td>
                                <td class="py-3.5 px-4 font-medium"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                <td class="py-3.5 px-4 text-center font-medium text-slate-500">
                                    <?php echo date('d M Y', strtotime($row['tanggal_peminjaman'])); ?>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-600">
                                    <?php echo $row['jumlah_pinjam']; ?> Unit
                                </td>
                                <td class="py-3.5 px-4 text-center"><?php echo $badge_verif; ?></td>
                                <td class="py-3.5 px-4 text-center"><?php echo $badge_kembali; ?></td>

                                <?php if ($user_role === 'admin' || $user_role === 'guru'): ?>
                                    <td class="py-3.5 px-5 text-center">
                                        <?php if ($status_pjm === 'pending'): ?>
                                            <div class="flex items-center justify-center gap-1.5">
                                                <form method="POST" action=""
                                                    onsubmit="return confirm('Setujui pengajuan perangkat ini dan kurangi stok?');">
                                                    <input type="hidden" name="id_peminjaman"
                                                        value="<?php echo $row['id_peminjaman']; ?>">
                                                    <input type="hidden" name="status_baru" value="disetujui">
                                                    <button type="submit" name="aksi_peminjaman"
                                                        class="p-1.5 bg-green-50 text-green-600 hover:bg-green-100 rounded-lg border border-green-200 transition-colors"
                                                        title="Setujui & Kurangi Stok">
                                                        <span class="material-symbols-outlined text-base block">check</span>
                                                    </button>
                                                </form>
                                                <form method="POST" action=""
                                                    onsubmit="return confirm('Tolak permintaan peminjaman ini?');">
                                                    <input type="hidden" name="id_peminjaman"
                                                        value="<?php echo $row['id_peminjaman']; ?>">
                                                    <input type="hidden" name="status_baru" value="ditolak">
                                                    <button type="submit" name="aksi_peminjaman"
                                                        class="p-1.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg border border-red-200 transition-colors"
                                                        title="Tolak">
                                                        <span class="material-symbols-outlined text-base block">close</span>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php elseif ($status_pjm === 'disetujui' && $status_kembali === 'dipinjam'): ?>
                                            <form method="POST" action=""
                                                onsubmit="return confirm('Selesaikan pengembalian perangkat ini? Stok barang akan dikembalikan.');"
                                                class="flex items-center justify-center gap-1.5">
                                                <input type="hidden" name="id_peminjaman" value="<?php echo $row['id_peminjaman']; ?>">
                                                <select name="kondisi_kembali" required
                                                    class="text-[11px] py-1 px-2 rounded-lg border-slate-200 bg-slate-50 text-slate-700 focus:ring-1 focus:ring-blue-500">
                                                    <option value="baik">Kondisi: Baik</option>
                                                    <option value="rusak">Kondisi: Rusak</option>
                                                </select>
                                                <button type="submit" name="proses_pengembalian"
                                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] px-2.5 py-1.5 rounded-lg transition-colors flex items-center gap-1 uppercase">
                                                    <span class="material-symbols-outlined text-xs">assignment_return</span>
                                                    Kembali
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Transaksi
                                                Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php
                        }
                    } else {
                        $colspan = ($user_role === 'admin' || $user_role === 'guru') ? 8 : 7;
                        echo "<tr><td colspan='$colspan' class='py-10 text-center text-slate-400 font-medium bg-slate-50/30'>Belum ada riwayat pengajuan peminjaman perangkat saat ini.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($user_role === 'siswa'): ?>
    <div id="modalPinjam"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
        <div
            class="bg-white rounded-2xl border border-slate-100 shadow-2xl max-w-lg w-full scale-95 transition-all duration-300 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2 text-slate-800">
                    <span class="material-symbols-outlined text-blue-500">assignment_turned_in</span>
                    <h3 class="text-sm font-bold uppercase tracking-wider">Form Pengajuan Perangkat</h3>
                </div>
                <button onclick="tutupModalPinjam()" class="text-slate-400 hover:text-slate-600 transition-colors block">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form method="POST" action="" class="p-6 space-y-4">
                <div>
                    <label for="id_barang" class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">Pilih
                        Perangkat Lab</label>
                    <select name="id_barang" id="id_barang" required
                        class="block w-full rounded-xl border-slate-200 bg-slate-50/50 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all py-2.5">
                        <option value="">-- Silakan Pilih Unit Perangkat --</option>
                        <?php
                        $list_brg = mysqli_query($koneksi, "SELECT id_barang, nama, merek, jumlah FROM barang WHERE status='baik' AND jumlah > 0 ORDER BY nama ASC");
                        while ($b = mysqli_fetch_assoc($list_brg)) {
                            echo "<option value='{$b['id_barang']}'>" . htmlspecialchars($b['nama']) . " (" . htmlspecialchars($b['merek'] ?? '-') . ") — Tersedia: {$b['jumlah']} Unit</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="tanggal_peminjaman"
                        class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">Tanggal
                        Peminjaman</label>
                    <input type="date" name="tanggal_peminjaman" id="tanggal_peminjaman" required
                        min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>"
                        class="block w-full rounded-xl border-slate-200 bg-slate-50/50 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all py-2.5">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    <div class="sm:col-span-2">
                        <label for="jumlah_pinjam"
                            class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">Jumlah Unit
                            Pinjam</label>
                        <div class="relative rounded-xl shadow-sm">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-sm">filter_1</span>
                            </div>
                            <input type="number" name="jumlah_pinjam" id="jumlah_pinjam" min="1" required
                                placeholder="Contoh: 1"
                                class="block w-full pl-10 pr-3 py-2.5 bg-slate-50/50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        </div>
                    </div>

                    <div>
                        <button type="submit" name="ajukan_peminjaman"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-3 px-4 rounded-xl shadow-md hover:shadow-lg transition-all uppercase tracking-wider flex items-center justify-center gap-1.5 h-[42px]">
                            <span class="material-symbols-outlined text-sm">send</span>
                            Kirim Ajuan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
    function bukaModalPinjam() {
        const m = document.getElementById('modalPinjam');
        if (!m) return;
        const content = m.querySelector('div');
        m.classList.remove('opacity-0', 'pointer-events-none');
        m.classList.add('opacity-100');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }

    function tutupModalPinjam() {
        const m = document.getElementById('modalPinjam');
        if (!m) return;
        const content = m.querySelector('div');
        m.classList.remove('opacity-100');
        m.classList.add('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        m.querySelector('form').reset();
    }

    const modalBox = document.getElementById('modalPinjam');
    if (modalBox) {
        modalBox.addEventListener('click', function (e) {
            if (e.target === this) tutupModalPinjam();
        });
    }
</script>