<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

if (!isset($_SESSION["user"])) {
    header("location:login.php");
    exit();
}

$nama_user = $_SESSION['user']['nama'] ?? $_SESSION['user']['username'] ?? 'Pengguna';
$role_user = $_SESSION['user']['role'] ?? $_SESSION['user']['level'] ?? 'Siswa';
$currentPage = $_GET['page'] ?? 'home';


$master_menus = [
    'kategori' => ['label' => 'Kategori Perangkat', 'icon' => 'category'],
    'barang' => ['label' => 'Stok Perangkat Lab', 'icon' => 'devices'],
    'siswa' => ['label' => 'Data Siswa', 'icon' => 'group'],
    'lab_komputer' => ['label' => 'Lab Komputer', 'icon' => 'door_open']
];


$allowedPages = ['home', 'peminjaman', 'kategori', 'barang', 'siswa', 'lab_komputer'];


?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Iventoris | Lab MAKN Ende</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="icon" href="images/image.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1"
        rel="stylesheet" />

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* ANIMASI KEREN FULLSCREEN LOADING PAGE */
        #page-loader {
            position: fixed;
            inset: 0;
            background: rgba(248, 250, 252, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 0.4s ease-out, visibility 0.4s ease-out;
        }

        .loader-container {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100px;
            height: 100px;
        }

        /* Efek Gelombang Lingkaran Pulse Luar */
        .loader-pulse {
            position: absolute;
            width: 80px;
            height: 80px;
            border-radius: 24px;
            background: rgba(37, 99, 235, 0.15);
            animation: pulseWave 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Spinner Utama Berputar Berlawanan */
        .loader-spinner {
            position: absolute;
            width: 60px;
            height: 60px;
            border: 4px solid transparent;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spinClockwise 1s linear infinite;
        }

        .loader-spinner-inner {
            position: absolute;
            width: 44px;
            height: 44px;
            border: 4px solid transparent;
            border-bottom-color: #10b981;
            border-radius: 50%;
            animation: spinCounterClockwise 0.8s linear infinite;
        }

        /* Container Logo di Tengah Loader */
        .loader-logo-wrapper {
            position: absolute;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: iconPulse 1.5s ease-in-out infinite alternate;
        }

        @keyframes spinClockwise {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes spinCounterClockwise {
            0% {
                transform: rotate(360deg);
            }

            100% {
                transform: rotate(0deg);
            }
        }

        @keyframes pulseWave {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.3);
                opacity: 0.9;
                border-radius: 50%;
            }
        }

        @keyframes iconPulse {
            from {
                transform: scale(0.9);
                opacity: 0.8;
            }

            to {
                transform: scale(1.1);
                opacity: 1;
            }
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased font-normal selection:bg-blue-600 selection:text-white">

    <div id="page-loader">
        <div class="loader-container">
            <div class="loader-pulse"></div>
            <div class="loader-spinner"></div>
            <div class="loader-spinner-inner"></div>
            <div class="loader-logo-wrapper">
                <img src="assets/img/logo.png" alt="Logo" class="w-full h-full object-contain"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span class="hidden text-blue-600 font-bold text-lg">L</span>
            </div>
        </div>
        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-4 animate-pulse">Memuat Halaman...
        </p>
    </div>

    <aside
        class="fixed inset-y-0 right-0 z-40 w-64 bg-white border-l border-slate-200/80 p-5 flex flex-col justify-between shadow-sm">
        <div class="space-y-6">
            <div class="flex items-center gap-3 px-1">
                <div
                    class="w-9 h-9 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center overflow-hidden shadow-sm p-1">
                    <img src="images/image.png" alt="Logo" class="w-full h-full object-contain"
                        onerror="this.parentNode.innerHTML='<div class=\'w-full h-full bg-blue-600 text-white font-extrabold text-sm flex items-center justify-center\'>L</div>';">
                </div>
                <div>
                    <h1 class="text-xs font-black text-slate-900 tracking-wide uppercase">LAB MAKN</h1>
                    <p class="text-[9px] text-slate-400 font-bold tracking-wider uppercase mt-0.5">Ivetoris Lab Komputer
                    </p>
                </div>
            </div>

            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 flex items-center gap-3">
                <div
                    class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center font-bold text-xs border border-blue-100 uppercase">
                    <?php echo substr($nama_user, 0, 2); ?>
                </div>
                <div class="overflow-hidden">
                    <h2 class="text-xs font-bold text-slate-800 truncate"><?php echo htmlspecialchars($nama_user); ?>
                    </h2>
                    <span
                        class="text-[9px] font-bold text-blue-600 uppercase tracking-wide bg-blue-50 px-1.5 py-0.2 rounded border border-blue-100 inline-block mt-0.5">
                        <?php echo htmlspecialchars($role_user); ?>
                    </span>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <span
                        class="px-3 text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Utama</span>
                    <nav class="space-y-1">
                        <a href="index.php?page=home"
                            class="w-full flex items-center gap-3 px-3 py-2 rounded-xl transition-all text-xs <?php echo ($currentPage == 'home') ? 'bg-blue-600 text-white font-semibold shadow-md shadow-blue-500/10' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'; ?>">
                            <span class="material-symbols-outlined text-lg">dashboard</span>
                            <span class="font-medium">Dashboard Utama</span>
                        </a>
                        <a href="index.php?page=peminjaman"
                            class="w-full flex items-center gap-3 px-3 py-2 rounded-xl transition-all text-xs <?php echo ($currentPage == 'peminjaman') ? 'bg-blue-600 text-white font-semibold shadow-md shadow-blue-500/10' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'; ?>">
                            <span class="material-symbols-outlined text-lg">swap_horizontal_circle</span>
                            <span class="font-medium">Sirkulasi Peminjaman</span>
                        </a>
                    </nav>
                </div>

                <?php if (strtolower($role_user) === 'admin' || strtolower($role_user) === 'guru'): ?>
                    <div>
                        <span
                            class="px-3 text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Pengelolaan
                            Sistem</span>
                        <nav class="space-y-1">
                            <?php
                            // Menambahkan menu lab_komputer ke dalam daftar pengelolaan sistem
                            $master_menus = [
                                'kategori' => ['label' => 'Kategori Perangkat', 'icon' => 'category'],
                                'barang' => ['label' => 'Stok Perangkat Lab', 'icon' => 'devices'],
                                'siswa' => ['label' => 'Data Siswa', 'icon' => 'group'],
                                'lab_komputer' => ['label' => 'Lab Komputer', 'icon' => 'door_open'] // Tambahan menu Lab Komputer
                            ];

                            foreach ($master_menus as $key => $menu):
                                $classStyle = ($currentPage == $key)
                                    ? 'bg-blue-600 text-white font-semibold shadow-md shadow-blue-500/10'
                                    : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50';
                                ?>
                                <a href="index.php?page=<?php echo $key; ?>"
                                    class="w-full flex items-center gap-3 px-3 py-2 rounded-xl transition-all text-xs <?php echo $classStyle; ?>">
                                    <span class="material-symbols-outlined text-lg"><?php echo $menu['icon']; ?></span>
                                    <span class="font-medium"><?php echo $menu['label']; ?></span>
                                </a>
                            <?php endforeach; ?>

                            <a href="cetak_laporan.php" target="_blank"
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl transition-all text-xs text-emerald-600 hover:bg-emerald-50 font-semibold border border-dashed border-transparent hover:border-emerald-200 mt-2">
                                <span class="material-symbols-outlined text-lg">print</span>
                                <span class="font-medium">Cetak Laporan</span>
                            </a>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>



            <div class="pt-4 border-t border-slate-100 space-y-3">
                <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?');"
                    class="w-full bg-[#EF4444] hover:bg-red-600 text-white text-xs font-bold rounded-xl py-2.5 flex items-center justify-center gap-2 transition-colors shadow-sm uppercase tracking-wider">
                    <span class="material-symbols-outlined text-base">logout</span>
                    Keluar Sistem
                </a>
                <p class="text-center text-[9px] text-slate-400 font-medium">MAKN Ende V1.3.0 © 2026</p>
            </div>
    </aside>

    <main class="pr-64 min-h-screen flex flex-col">
        <header
            class="h-14 bg-white border-b border-slate-200/80 px-8 flex items-center justify-between sticky top-0 z-30 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400 font-medium">Lokasi Halaman:</span>
                <span
                    class="text-xs font-bold text-slate-800 uppercase tracking-wide bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                    <?php echo $currentPage; ?>
                </span>
            </div>

            <div class="flex items-center gap-2 text-slate-500 font-medium text-xs">
                <span class="material-symbols-outlined text-sm text-slate-400">schedule</span>
                <div id="realtime-clock">Waktu Memuat...</div>
            </div>
        </header>

        <section class="flex-1 p-8 bg-slate-50/50">
            <?php

            $allowedPages = ['home', 'peminjaman', 'kategori', 'barang', 'siswa', 'lab_komputer'];

            if (in_array($currentPage, $allowedPages)) {
                $targetFile = $currentPage . '.php';
                if (file_exists($targetFile)) {
                    include $targetFile;
                } else {
                    echo "<div class='p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs font-medium flex items-center gap-2'>
                            <span class='material-symbols-outlined text-base'>error</span>
                            Error 404: Berkas modul <strong>$targetFile</strong> tidak ditemukan di server.
                          </div>";
                }
            } else {
                echo "<div class='p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-xs font-medium flex items-center gap-2'>
                        <span class='material-symbols-outlined text-base'>warning</span>
                        Akses Ditolak: Modul halaman tidak valid atau tidak diizinkan.
                      </div>";
            }
            ?>
        </section>
    </main>

    <script>
        window.addEventListener('load', function () {
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.style.opacity = '0';
                loader.style.visibility = 'hidden';
            }
        });

        document.querySelectorAll('aside a:not([target="_blank"]):not([href*="logout.php"])').forEach(link => {
            link.addEventListener('click', function (e) {
                if (this.getAttribute('href') && this.getAttribute('href') !== '#') {
                    const loader = document.getElementById('page-loader');
                    if (loader) {
                        loader.style.opacity = '1';
                        loader.style.visibility = 'visible';
                    }
                }
            });
        });

        const clockEl = document.getElementById('realtime-clock');
        if (clockEl) {
            setInterval(() => {
                const now = new Date();
                clockEl.textContent = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                }) + ' WITA';
            }, 1000);
        }
    </script>
</body>

</html>