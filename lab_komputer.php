<?php
if (!isset($currentPage)) {
    header("Location: index.php?page=home");
    exit();
}

if (strtolower($role_user) !== 'admin' && strtolower($role_user) !== 'guru') {
    echo "<div class='p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs font-medium'>Akses Ditolak.</div>";
    exit();
}

$notif = '';

// LOGIKA PROSES BACKEND
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strtolower($role_user) === 'admin') {
    
    // 1. TAMBAH LAB
    if (isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
        $nama_lab         = mysqli_real_escape_string($koneksi, $_POST['nama_lab']);
        $lokasi           = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
        $penanggung_jawab = mysqli_real_escape_string($koneksi, $_POST['penanggung_jawab']);
        $kapasitas_unit   = intval($_POST['kapasitas_unit']);
        
        $insert = mysqli_query($koneksi, "INSERT INTO lab_komputer (nama_lab, lokasi, penanggung_jawab, kapasitas_unit) VALUES ('$nama_lab', '$lokasi', '$penanggung_jawab', $kapasitas_unit)");
        if ($insert) {
            $notif = "<div id='alert-notif' class='p-4 bg-emerald-50 text-emerald-700 rounded-xl text-xs mb-4 shadow-sm flex items-center gap-2'><span class='material-symbols-outlined text-base'>check_circle</span> Berhasil menambahkan data Lab Komputer baru.</div>";
        }
    }
    
    // 2. EDIT LAB
    if (isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
        $id_lab           = intval($_POST['id_lab']);
        $nama_lab         = mysqli_real_escape_string($koneksi, $_POST['nama_lab']);
        $lokasi           = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
        $penanggung_jawab = mysqli_real_escape_string($koneksi, $_POST['penanggung_jawab']);
        $kapasitas_unit   = intval($_POST['kapasitas_unit']);
        
        $update = mysqli_query($koneksi, "UPDATE lab_komputer SET nama_lab='$nama_lab', lokasi='$lokasi', penanggung_jawab='$penanggung_jawab', kapasitas_unit=$kapasitas_unit WHERE id_lab=$id_lab");
        if ($update) {
            $notif = "<div id='alert-notif' class='p-4 bg-emerald-50 text-emerald-700 rounded-xl text-xs mb-4 shadow-sm flex items-center gap-2'><span class='material-symbols-outlined text-base'>check_circle</span> Perubahan data Lab Komputer berhasil disimpan.</div>";
        }
    }
    
    // 3. HAPUS LAB
    if (isset($_POST['aksi']) && $_POST['aksi'] === 'hapus') {
        $id_lab = intval($_POST['id_lab']);
        $delete = mysqli_query($koneksi, "DELETE FROM lab_komputer WHERE id_lab=$id_lab");
        if ($delete) {
            $notif = "<div id='alert-notif' class='p-4 bg-emerald-50 text-emerald-700 rounded-xl text-xs mb-4 shadow-sm flex items-center gap-2'><span class='material-symbols-outlined text-base'>delete_sweep</span> Data Lab Komputer berhasil dihapus.</div>";
        }
    }
}

$resultLab = mysqli_query($koneksi, "SELECT * FROM lab_komputer ORDER BY nama_lab ASC");
?>

<div class="space-y-6 animate-[fadeIn_0.3s_ease-out]">
    <?php echo $notif; ?>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-base font-black text-slate-900 tracking-wide uppercase">Manajemen Lab Komputer</h1>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Daftar ruangan laboratorium komputer beserta alokasi penempatan aset perangkat.</p>
        </div>
        <?php if (strtolower($role_user) === 'admin'): ?>
            <button onclick="bukaModalTambah()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-blue-500/10">
                <span class="material-symbols-outlined text-sm">add_box</span> Tambah Lab Baru
            </button>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase text-[10px] font-bold tracking-widest border-b border-slate-200/60">
                        <th class="px-6 py-3.5 text-center w-12 border-r border-slate-100">No</th>
                        <th class="px-6 py-3.5">Nama Ruangan Lab</th>
                        <th class="px-6 py-3.5">Lokasi / Area</th>
                        <th class="px-6 py-3.5">Penanggung Jawab</th>
                        <th class="px-6 py-3.5">Kapasitas Unit</th>
                        <?php if (strtolower($role_user) === 'admin'): ?>
                            <th class="px-6 py-3.5 text-right">Tindakan</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    <?php 
                    if (mysqli_num_rows($resultLab) > 0):
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($resultLab)):
                    ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-slate-400 font-medium text-center border-r border-slate-100"><?= $no++; ?></td>
                            <td class="px-6 py-4 font-bold text-slate-900"><?= htmlspecialchars($row['nama_lab']); ?></td>
                            <td class="px-6 py-4 text-slate-500"><?= htmlspecialchars($row['lokasi'] ?: '-'); ?></td>
                            <td class="px-6 py-4 text-slate-600 font-medium"><?= htmlspecialchars($row['penanggung_jawab'] ?: '-'); ?></td>
                            <td class="px-6 py-4"><span class="bg-purple-50 text-purple-600 border border-purple-100 px-2 py-0.5 rounded font-bold text-[11px]"><?= $row['kapasitas_unit']; ?> PC Unit</span></td>
                            <?php if (strtolower($role_user) === 'admin'): ?>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex gap-1.5">
                                        <button onclick="bukaModalEdit('<?= $row['id_lab']; ?>', '<?= addslashes($row['nama_lab']); ?>', '<?= addslashes($row['lokasi']); ?>', '<?= addslashes($row['penanggung_jawab']); ?>', '<?= $row['kapasitas_unit']; ?>')" class="p-1.5 hover:bg-amber-50 text-amber-600 hover:text-amber-700 rounded-lg border border-transparent hover:border-amber-200/60 transition-all shadow-sm"><span class="material-symbols-outlined text-base block">edit</span></button>
                                        <button onclick="mintaHapus('<?= $row['id_lab']; ?>', '<?= addslashes($row['nama_lab']); ?>')" class="p-1.5 hover:bg-red-50 text-red-500 hover:text-red-600 rounded-lg border border-transparent hover:border-red-200/60 transition-all shadow-sm"><span class="material-symbols-outlined text-base block">delete</span></button>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <tr><td colspan="6" class="px-6 py-10 text-center text-slate-400 font-medium">Belum ada data lab komputer.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalLab" class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full overflow-hidden transform scale-95 transition-all duration-300">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 id="modalTitle" class="text-xs font-black text-slate-900 uppercase tracking-wider">Tambah Lab</h3>
            <button onclick="tutupModal()" class="text-slate-400 hover:text-slate-600 font-medium">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="aksi" id="form_aksi" value="tambah">
            <input type="hidden" name="id_lab" id="form_id_lab">

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Ruangan Lab</label>
                <input type="text" name="nama_lab" id="form_nama_lab" required class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Lokasi Gedung / Area</label>
                <input type="text" name="lokasi" id="form_lokasi" class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Penanggung Jawab</label>
                    <input type="text" name="penanggung_jawab" id="form_pj" class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kapasitas (Unit)</label>
                    <input type="number" name="kapasitas_unit" id="form_kapasitas" required min="0" class="w-full rounded-xl border-slate-200 text-xs focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="tutupModal()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold">Simpan</button>
            </div>
        </form>
    </div>
</div>

<form id="formHapus" method="POST" class="hidden">
    <input type="hidden" name="aksi" value="hapus">
    <input type="hidden" name="id_lab" id="hapus_id_lab">
</form>

<script>
    const m = document.getElementById('modalLab');
    const c = m.querySelector('div');

    function bukaModalTambah() {
        document.getElementById('modalTitle').textContent = 'Tambah Lab Komputer';
        document.getElementById('form_aksi').value = 'tambah';
        document.getElementById('form_id_lab').value = '';
        document.getElementById('form_nama_lab').value = '';
        document.getElementById('form_lokasi').value = '';
        document.getElementById('form_pj').value = '';
        document.getElementById('form_kapasitas').value = '0';
        m.classList.remove('opacity-0', 'pointer-events-none');
        c.classList.remove('scale-95'); c.classList.add('scale-100');
    }

    function bukaModalEdit(id, nama, lokasi, pj, kapasitas) {
        document.getElementById('modalTitle').textContent = 'Ubah Lab Komputer';
        document.getElementById('form_aksi').value = 'edit';
        document.getElementById('form_id_lab').value = id;
        document.getElementById('form_nama_lab').value = nama;
        document.getElementById('form_lokasi').value = lokasi;
        document.getElementById('form_pj').value = pj;
        document.getElementById('form_kapasitas').value = kapasitas;
        m.classList.remove('opacity-0', 'pointer-events-none');
        c.classList.remove('scale-95'); c.classList.add('scale-100');
    }

    function tutupModal() {
        m.classList.add('opacity-0', 'pointer-events-none');
        c.classList.remove('scale-100'); c.classList.add('scale-95');
    }

    function mintaHapus(id, nama) {
        if (confirm(`Hapus data "${nama}"?\nBarang yang tertaut pada lab ini tidak akan ikut terhapus, melainkan status ruangannya menjadi kosong (NULL).`)) {
            document.getElementById('hapus_id_lab').value = id;
            document.getElementById('formHapus').submit();
        }
    }

    setTimeout(() => { const a = document.getElementById('alert-notif'); if(a) a.remove(); }, 4000);
</script>