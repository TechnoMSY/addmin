<?php
// 1. Jalankan session di baris paling pertama sebelum logika apapun
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Hubungkan ke database
include 'koneksi.php';

// 3. Proteksi halaman: Jika tidak ada session 'user', tendang kembali ke login
if (!isset($_SESSION["user"])) {
    header("location:login.php");
    exit();
}

// 4. Deteksi Gambar Profil / Avatar User dari database session
$avatar_src = "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['user']['username']) . "&background=00687a&color=fff";
if (!empty($_SESSION['user']['foto']) && file_exists("uploads/profile/" . $_SESSION['user']['foto'])) {
    $avatar_src = "uploads/profile/" . $_SESSION['user']['foto'];
}


$nama_user = $_SESSION['user']['nama'] ?? $_SESSION['user']['username'] ?? 'Pengguna';
$role_user = $_SESSION['user']['role'] ?? 'Siswa';

?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="stylesheet" href="https://cloudflare.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-secondary-container": "#006172",
                        "primary-fixed": "#d8e3fb",
                        "surface": "#fbf8fa",
                        "on-tertiary": "#ffffff",
                        "secondary-fixed-dim": "#4cd7f6",
                        "on-error": "#ffffff",
                        "primary": "#091426",
                        "on-tertiary-fixed": "#271902",
                        "on-tertiary-fixed-variant": "#564427",
                        "on-surface": "#1b1b1d",
                        "on-error-container": "#93000a",
                        "surface-container-high": "#eae7e9",
                        "surface-container-lowest": "#ffffff",
                        "on-primary-fixed": "#111c2d",
                        "on-primary-container": "#8590a6",
                        "surface-variant": "#e4e2e3",
                        "inverse-primary": "#bcc7de",
                        "inverse-surface": "#303032",
                        "secondary-container": "#57dffe",
                        "outline": "#75777d",
                        "primary-container": "#1e293b",
                        "surface-container-low": "#f5f3f4",
                        "inverse-on-surface": "#f3f0f2",
                        "surface-dim": "#dcd9db",
                        "tertiary-fixed": "#fadfb8",
                        "on-secondary-fixed-variant": "#004e5c",
                        "surface-container-highest": "#e4e2e3",
                        "surface-container": "#f0edef",
                        "tertiary-container": "#35260c",
                        "secondary-fixed": "#acedff",
                        "tertiary-fixed-dim": "#ddc39d",
                        "primary-fixed-dim": "#bcc7de",
                        "surface-tint": "#545f73",
                        "on-secondary": "#ffffff",
                        "secondary": "#00687a",
                        "on-background": "#1b1b1d",
                        "on-primary-fixed-variant": "#3c475a",
                        "on-secondary-fixed": "#001f26",
                        "on-tertiary-container": "#a38c6a",
                        "tertiary": "#1e1200",
                        "surface-bright": "#fbf8fa",
                        "error": "#ba1a1a",
                        "on-surface-variant": "#45474c",
                        "error-container": "#ffdad6",
                        "on-primary": "#ffffff",
                        "outline-variant": "#c5c6cd",
                        "background": "#fbf8fa"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "gutter": "24px",
                        "xs": "4px",
                        "container-max": "1440px",
                        "sm": "8px",
                        "xxl": "48px",
                        "unit": "4px",
                        "xl": "32px",
                        "md": "16px",
                        "lg": "24px"
                    },
                    "fontFamily": {
                        "body-lg": ["Inter"],
                        "label-md": ["Inter"],
                        "body-md": ["Inter"],
                        "headline-lg": ["Inter"],
                        "headline-md": ["Inter"],
                        "display-lg": ["Inter"],
                        "headline-lg-mobile": ["Inter"]
                    },
                    "fontSize": {
                        "body-lg": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "label-md": ["12px", { "lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "500" }],
                        "body-md": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "headline-lg": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "headline-md": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "display-lg": ["36px", { "lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "headline-lg-mobile": ["20px", { "lineHeight": "28px", "fontWeight": "600" }]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        /* Animasi Konten Utama Masuk */
        #main-content-canvas {
            opacity: 0;
            transform: scale(0.99) translateY(4px);
            transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #main-content-canvas.page-loaded {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    </style>
</head>

<body class="bg-background text-on-background min-h-screen">



    <div id="loading-overlay" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-200">
        <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800 shadow-2xl flex flex-col items-center gap-4">
            <div class="w-12 h-12 border-4 border-slate-700 border-t-emerald-500 rounded-full animate-spin"></div>
            <p class="text-slate-300 font-medium text-sm tracking-wide">Memuat halaman...</p>
        </div>
    </div>

    <aside class="bg-white h-screen w-64 fixed left-0 top-0 flex flex-col py-xl px-md shadow-sm border-r border-gray-200 z-50">
        <div class="flex items-center gap-3 px-4 mb-xxl">
            
            <div class="w-10 h-10 flex items-center justify-center">
                <img src="images/logo.png" alt="Logo Lab" class="w-full h-full object-contain" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Lab&background=059669&color=fff';">
            </div>
            <div class="flex flex-col">
                <span class="font-headline-md text-headline-md font-bold text-gray-900 tracking-tight">Lab MAKN</span>
                <span class="text-gray-400 text-[10px] uppercase tracking-widest font-bold">Inventori Lab Komputer</span>
            </div>
        </div>

        <nav class="flex-1 space-y-1.5">
            <a class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] === 'home') ? 'bg-emerald-50 text-emerald-600 font-semibold border-l-4 border-emerald-500 pl-3' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/70 pl-4'; ?> rounded-r-lg py-3 flex items-center gap-3 transition-all duration-200" href="index.php?page=home">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="font-body-md text-body-md">Dashboard</span>
            </a>
            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'barang') ? 'bg-emerald-50 text-emerald-600 font-semibold border-l-4 border-emerald-500 pl-3' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/70 pl-4'; ?> rounded-r-lg py-3 flex items-center gap-3 transition-all duration-200" href="index.php?page=barang">
                <span class="material-symbols-outlined">inventory_2</span>
                <span class="font-body-md text-body-md">Barang</span>
            </a>
            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'peminjaman') ? 'bg-emerald-50 text-emerald-600 font-semibold border-l-4 border-emerald-500 pl-3' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/70 pl-4'; ?> rounded-r-lg py-3 flex items-center gap-3 transition-all duration-200" href="index.php?page=peminjaman">
                <span class="material-symbols-outlined">swap_horiz</span>
                <span class="font-body-md text-body-md">Peminjaman</span>
            </a>
            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'kategori') ? 'bg-emerald-50 text-emerald-600 font-semibold border-l-4 border-emerald-500 pl-3' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/70 pl-4'; ?> rounded-r-lg py-3 flex items-center gap-3 transition-all duration-200" href="index.php?page=kategori">
                <span class="material-symbols-outlined">category</span>
                <span class="font-body-md text-body-md">Kategori</span>
            </a>
            <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'pengaturan') ? 'bg-emerald-50 text-emerald-600 font-semibold border-l-4 border-emerald-500 pl-3' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/70 pl-4'; ?> rounded-r-lg py-3 flex items-center gap-3 transition-all duration-200" href="index.php?page=pengaturan">
                <span class="material-symbols-outlined">settings</span>
                <span class="font-body-md text-body-md">Pengaturan</span>
            </a>
        </nav>

        <div class="mt-auto border-t border-gray-200 pt-lg space-y-1">
            <a href="index.php?page=barang" class="w-full text-center block bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-lg px-4 py-3 shadow-md shadow-emerald-500/10 hover:shadow-emerald-500/20 active:scale-[0.98] transition-all duration-150">
                New Entry
            </a>
            <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?');" class="flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all duration-200">
                <span class="material-symbols-outlined text-[20px]">logout</span>
                <span class="font-medium">Log Out</span>
            </a>
        </div>
    </aside>

    <main class="ml-64 min-h-screen flex flex-col">
        <header class="fixed top-0 right-0 w-[calc(100%-16rem)] z-40 flex justify-between items-center h-16 px-lg bg-surface/80 backdrop-blur-md border-b border-surface-container-high transition-colors duration-200">
            <div class="flex items-center gap-xl w-full max-w-2xl">
                <div class="relative w-full">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                    <input class="w-full pl-10 pr-4 py-2 bg-surface-container-low border border-outline-variant rounded-lg font-body-md text-body-md focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary" placeholder="Search inventory, labs, or orders..." type="text" />
                </div>
            </div>
            
            <div class="relative">
                <button id="btn-dropdown-profil" class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-surface-container transition-all group focus:outline-none">
                    <div class="text-right hidden sm:block">
                        <p class="font-label-md text-label-md text-primary font-semibold group-hover:text-secondary transition-colors"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></p>
                        <p class="text-[10px] text-on-surface-variant uppercase tracking-wider"><?php echo $_SESSION['user']['level']; ?></p>
                    </div>
                    <img id="header-avatar" alt="Profile" class="w-10 h-10 rounded-full border border-outline-variant object-cover group-hover:border-secondary transition-all" src="<?php echo $avatar_src; ?>" />
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px] group-hover:text-secondary transition-transform duration-200" id="icon-chevron-profil">expand_more</span>
                </button>

                <div id="dropdown-menu-profil" class="absolute right-0 mt-2 w-56 bg-white border border-surface-container-high rounded-xl shadow-xl py-2 opacity-0 scale-95 pointer-events-none transition-all duration-200 transform origin-top-right z-50">
                    <div class="px-4 py-2 border-b border-surface-container-high mb-1">
                        <p class="text-xs text-on-surface-variant font-medium">Masuk Sebagai:</p>
                        <p class="text-sm font-bold text-primary truncate"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></p>
                    </div>
                    
                    <a href="index.php?page=pengaturan" class="flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface hover:bg-surface-container-low hover:text-secondary transition-all">
                        <span class="material-symbols-outlined text-[20px] text-on-surface-variant">account_circle</span>
                        <span>Profil Saya</span>
                    </a>
                    <a href="index.php?page=pengaturan" class="flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface hover:bg-surface-container-low hover:text-secondary transition-all">
                        <span class="material-symbols-outlined text-[20px] text-on-surface-variant">settings</span>
                        <span>Pengaturan Akun</span>
                    </a>
                    
                    <div class="border-t border-surface-container-high mt-1 pt-1">
                        <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?');" class="flex items-center gap-3 px-4 py-2.5 text-sm text-error hover:bg-rose-50 transition-all">
                            <span class="material-symbols-outlined text-[20px]">logout</span>
                            <span class="font-semibold">Keluar Aplikasi</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div id="main-content-canvas" class="container-fluid flex-1">          
            <?php
                            $page = isset($_GET['page']) ? $_GET['page'] : 'home';
                            if($page === 'home') : ?>
                <div class="d-sm-flex align-items-center justify-content-between mb-4 mt-16 pt-4 px-xl max-w-container-max mx-auto w-full">
                   <div class="welcome-box relative overflow-hidden">
                        <h1 class="font-headline-lg md:text-3xl font-bold">
                            Selamat Datang, <span class="nama-user text-yellow-300"><?= htmlspecialchars($nama_user); ?></span> 👋
                        </h1>
                        <h4 class="text-sm md:text-base font-semibold opacity-95 mt-2 flex items-center gap-2">
                            Sebagai: <span class="bg-white/20 px-2 py-0.5 rounded text-xs lowercase font-bold tracking-wide border border-white/10 text-yellow-200"><?php echo $_SESSION['user']['level']; ?></span>
                        </h4>
                        
                        <div class="mt-4 p-3 bg-black/30 rounded-xl border border-white/5 backdrop-blur-sm max-w-md flex items-center gap-4 text-white">
                            <div class="flex items-center justify-center w-12 h-12 bg-emerald-500/20 text-emerald-400 rounded-lg">
                                <span class="material-symbols-outlined text-2xl animate-pulse">schedule</span>
                            </div>
                            <div class="flex flex-col">
                                <span id="realtime-clock" class="text-xl font-bold font-mono tracking-wider text-emerald-400">00:00:00</span>
                                <span id="realtime-date" class="text-xs text-slate-300 font-medium">Memuat tanggal...</span>
                            </div>
                        </div>

                        <p class="sub-text mt-4 text-sm opacity-85 leading-relaxed">
                            Semoga harimu menyenangkan dan siap melakukan kontrol serta manajemen aktivitas ekosistem laboratorium hari ini.
                        </p>
                    </div>
                </div>

                <style>
                .welcome-box {
                    background: linear-gradient(135deg, #091426, #00687a);
                    padding: 28px;
                    border-radius: 16px;
                    color: white;
                    box-shadow: 0 10px 25px -5px rgba(0, 104, 122, 0.25);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                }
                .sub-text { margin: 0; }
                </style>
            <?php endif; ?>

            <?php 
                if (file_exists($page . '.php')) {
                    include $page . '.php';
                } else {
                    include '404.php';
                }
            ?>
        </div>
    </main>

    <script>
        function startRealtimeWidget() {
            const clockEl = document.getElementById('realtime-clock');
            const dateEl = document.getElementById('realtime-date');
            
            if (!clockEl || !dateEl) return;

            const namaHari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
            const namaBulan = [
                "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"
            ];

            function updateTime() {
                const sekarang = new Date();
                const jam = String(sekarang.getHours()).padStart(2, '0');
                const menit = String(sekarang.getMinutes()).padStart(2, '0');
                const detik = String(sekarang.getSeconds()).padStart(2, '0');
                clockEl.textContent = `${jam}:${menit}:${detik}`;

                const hari = namaHari[sekarang.getDay()];
                const tanggal = sekarang.getDate();
                const bulan = namaBulan[sekarang.getMonth()];
                const tahun = sekarang.getFullYear();
                dateEl.textContent = `${hari}, ${tanggal} ${bulan} ${tahun}`;
            }

            updateTime();
            setInterval(updateTime, 1000);
        }

        document.addEventListener("DOMContentLoaded", function () {
            startRealtimeWidget();

            const canvas = document.getElementById("main-content-canvas");
            const loadingOverlay = document.getElementById("loading-overlay");
            const navLinks = document.querySelectorAll(".nav-link");

            const btnProfil = document.getElementById("btn-dropdown-profil");
            const menuProfil = document.getElementById("dropdown-menu-profil");
            const chevronProfil = document.getElementById("icon-chevron-profil");

            if (btnProfil && menuProfil) {
                btnProfil.addEventListener("click", function (e) {
                    e.stopPropagation();
                    const isOpen = !menuProfil.classList.contains("opacity-0");
                    if (isOpen) {
                        tutupDropdownProfil();
                    } else {
                        bukaDropdownProfil();
                    }
                });

                document.addEventListener("click", function () {
                    tutupDropdownProfil();
                });

                menuProfil.addEventListener("click", function (e) {
                    e.stopPropagation();
                });
            }

            function bukaDropdownProfil() {
                menuProfil.classList.remove("opacity-0", "scale-95", "pointer-events-none");
                menuProfil.classList.add("opacity-100", "scale-100");
                if (chevronProfil) chevronProfil.style.transform = "rotate(180deg)";
            }

            function tutupDropdownProfil() {
                menuProfil.classList.remove("opacity-100", "scale-100");
                menuProfil.classList.add("opacity-0", "scale-95", "pointer-events-none");
                if (chevronProfil) chevronProfil.style.transform = "rotate(0deg)";
            }

            setTimeout(() => {
                canvas.classList.add("page-loaded");
            }, 50);

            navLinks.forEach(link => {
                link.addEventListener("click", function (e) {
                    if (this.getAttribute("href") && this.getAttribute("href") !== "#") {
                        e.preventDefault();
                        const targetUrl = this.href;

                        loadingOverlay.classList.remove("opacity-0", "pointer-events-none");
                        loadingOverlay.classList.add("opacity-100");

                        canvas.style.opacity = "0";
                        canvas.style.transform = "scale(0.98) translateY(4px)";

                        setTimeout(() => {
                            window.location.href = targetUrl;
                        }, 250);
                    }
                });
            });
        });
    </script>
</body>
</html>