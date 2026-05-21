<?php
if (!isset($koneksi)) {
    include 'koneksi.php';
}

// Ambil ID Barang dari URL (Contoh: index.php?page=qr_barang&id=1)
$id_barang = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : 0;

// Ambil data barang dari database
$query = mysqli_query($koneksi, "SELECT * FROM barang WHERE id_barang = '$id_barang'");
$barang = mysqli_fetch_assoc($query);

if (!$barang) {
    echo "<div class='mt-20 p-6 text-center text-red-500 font-bold'>Barang tidak ditemukan!</div>";
    return;
}

// Menentukan teks/data yang akan dimasukkan ke dalam QR Code
// Anda bisa memasukkan ID Barang saja, atau URL lengkap sistem Anda
$string_qr = "ID Barang: " . $barang['id_barang'] . " | Nama: " . $barang['nama'] . " | Merek: " . $barang['merek'];

// Encode teks ke format URL agar aman dikirim ke QR Server API
$url_qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($string_qr);
?>

<div class="mt-20 p-6 max-w-md mx-auto w-full">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-xl overflow-hidden">
        
        <div class="bg-slate-900 p-4 text-center text-white">
            <span class="material-symbols-outlined text-3xl text-emerald-400 animate-pulse">qr_code_2</span>
            <h3 class="font-bold text-lg">QR Code Inventaris</h3>
            <p class="text-xs text-slate-400">Pindai untuk memeriksa informasi unit</p>
        </div>

        <div class="p-8 flex flex-col items-center justify-center bg-slate-50 border-b border-gray-100">
            <div class="p-4 bg-white rounded-xl shadow-md border border-gray-200">
                <img src="<?php echo $url_qr_api; ?>" alt="QR Code Barang" class="w-48 h-48 transition-transform hover:scale-105 duration-200" />
            </div>
            <span class="mt-4 text-xs font-mono bg-slate-200 text-slate-700 px-3 py-1 rounded-full font-bold">
                #<?php echo $barang['id_barang']; ?>
            </span>
        </div>

        <div class="p-6 space-y-3 bg-white">
            <div>
                <label class="text-xs text-gray-400 block font-bold uppercase">Nama Komoditas</label>
                <p class="text-base font-bold text-slate-800"><?php echo htmlspecialchars($barang['nama']); ?></p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-400 block font-bold uppercase">Merek</label>
                    <p class="text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($barang['merek'] ?? '-'); ?></p>
                </div>
                <div>
                    <label class="text-xs text-gray-400 block font-bold uppercase">Status Kelayakan</label>
                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-xs font-bold uppercase border <?php echo $barang['status'] === 'baik' ? 'bg-emerald-100 border-emerald-200 text-emerald-800' : 'bg-red-100 border-red-200 text-red-800'; ?>">
                        <?php echo $barang['status']; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-gray-100 flex gap-2">
            <a href="index.php?page=barang" class="w-full text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors">
                Kembali
            </a>
            <button onclick="window.print();" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold px-4 py-2 rounded-lg text-sm flex items-center justify-center gap-1 shadow-sm transition-colors">
                <span class="material-symbols-outlined text-sm">print</span> Cetak Label
            </button>
        </div>

    </div>
</div>