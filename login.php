<?php
// 1. Memulai session dengan aman di baris paling atas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Memanggil file koneksi database
require_once 'koneksi.php';

$error_message = "";
$success_redirect = false;

// 3. Logika ketika tombol submit / login ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Memastikan koneksi database tersedia (mendukung $conn maupun $koneksi)
    $db_connect = isset($conn) ? $conn : (isset($koneksi) ? $koneksi : null);

    if ($db_connect) {
        $username = mysqli_real_escape_string($db_connect, trim($_POST['username']));
        $password = trim($_POST['password']);
    } else {
        die("Eror Sistem: Variabel koneksi database tidak ditemukan. Periksa koneksi.php Anda.");
    }

    if (!empty($username) && !empty($password)) {

        // Query mencari username di database
        $query = "SELECT * FROM user WHERE username = '$username' LIMIT 1";
        $result = mysqli_query($db_connect, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);

            // VERIFIKASI PASSWORD MENGGUNAKAN MD5 (Sesuai tipe database Anda)
            if (md5($password) === $user_data['password']) {

                // Menyimpan data ke dalam array $_SESSION['user'] sesuai kebutuhan index.php dan home.php
                $_SESSION['user'] = [
                    'id_user'  => isset($user_data['id_user']) ? $user_data['id_user'] : '',
                    'username' => $user_data['username'],
                    'level'    => isset($user_data['level']) ? $user_data['level'] : 'peminjam'
                ];

                // Buat pesan sukses teks flash session
                $_SESSION['login_success'] = "Selamat Datang, " . htmlspecialchars($user_data['username']) . "! Anda berhasil login.";
                
                // Set flag true agar HTML di bawah memunculkan pop-up sukses
                $success_redirect = true;

            } else {
                $error_message = "Username atau password salah.";
            }
        } else {
            $error_message = "Username atau password salah.";
        }
    } else {
        $error_message = "Semua kolom wajib diisi.";
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>LabFlow - Sign In</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary": "#091426",
                        "secondary": "#00687a",
                        "on-secondary": "#ffffff",
                        "on-surface": "#1b1b1d",
                        "on-surface-variant": "#45474c",
                        "outline-variant": "#c5c6cd",
                        "surface-container-high": "#eae7e9",
                        "primary-container": "#1e293b",
                        "error": "#ba1a1a"
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
        .lab-pattern { background-color: #fbf8fa; background-image: radial-gradient(#e4e2e3 1px, transparent 1px); background-size: 32px 32px; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(30, 41, 59, 0.05); }
    </style>
</head>

<body class="antialiased text-on-surface">
    <main class="min-h-screen lab-pattern flex items-center justify-center p-4">
        <div class="w-full max-w-[440px]">
            <div class="flex flex-col items-center mb-6">
                <div class="w-12 h-12 bg-primary-container rounded-xl flex items-center justify-center mb-4 shadow-lg">
                    <span class="material-symbols-outlined text-white text-3xl" style="font-variation-settings: 'FILL' 1;">science</span>
                </div>
                <h1 class="text-3xl font-bold text-primary tracking-tight">LabFlow</h1>
                <p class="text-sm text-on-surface-variant mt-1">Precision Systems Inventory Management</p>
            </div>
            
            <div class="glass-card rounded-xl p-8">
                <header class="mb-6">
                    <h2 class="text-xl font-semibold text-on-surface">Welcome back</h2>
                    <p class="text-sm text-on-surface-variant">Please enter your credentials to continue</p>
                </header>

                <?php if (!empty($error_message)): ?>
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-sm text-red-600 rounded-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">error</span>
                        <?= htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form class="space-y-4" action="" method="POST">
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-on-surface-variant" for="username">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant">
                                <span class="material-symbols-outlined text-[17px]">person</span>
                            </div>
                            <input class="w-full h-12 pl-10 pr-4 bg-white border border-outline-variant rounded-lg text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all outline-none"
                                id="username" name="username" placeholder="Masukkan username Anda" required type="text" />
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-medium text-on-surface-variant" for="password">Password</label>
                            <a class="text-xs font-medium text-secondary hover:underline" href="#">Forgot password?</a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-on-surface-variant">
                                <span class="material-symbols-outlined text-[17px]">lock</span>
                            </div>
                            <input class="w-full h-12 pl-10 pr-4 bg-white border border-outline-variant rounded-lg text-sm focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all outline-none"
                                id="password" name="password" placeholder="••••••••" required type="password" />
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <input class="w-4 h-4 text-secondary border-outline-variant rounded focus:ring-secondary/20" id="remember" type="checkbox" />
                        <label class="ml-2 text-sm text-on-surface-variant" for="remember">Remember me for 30 days</label>
                    </div>
                    
                    <button class="w-full h-12 bg-secondary text-on-secondary font-semibold text-[16px] rounded-lg shadow-md hover:bg-secondary/90 active:scale-[0.98] transition-all flex items-center justify-center gap-2" type="submit">
                        Sign In to Dashboard
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                </form>
                
                <footer class="mt-6 pt-4 border-t border-surface-container-high">
                    <div class="flex items-center justify-center gap-4">
                        <a class="text-xs text-on-surface-variant hover:text-primary transition-colors flex items-center gap-1" href="#">
                            <span class="material-symbols-outlined text-[16px]">help</span>
                            Help Center
                        </a>
                        <span class="text-outline-variant">•</span>
                        <a class="text-xs text-on-surface-variant hover:text-primary transition-colors" href="#">Privacy
                            Policy</a>
                    </div>
                </footer>
            </div>
        </div>
    </main>

    <?php if ($success_redirect === true): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
            
            <div id="toast-success" class="flex flex-col items-center w-full max-w-xs p-6 bg-white rounded-xl shadow-2xl border border-green-100 transform scale-100 transition-all duration-300 text-center">
                
                <div class="inline-flex items-center justify-center flex-shrink-0 w-16 h-16 text-green-500 bg-green-50 rounded-full mb-4 animate-bounce">
                    <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </div>
                
                <h3 class="text-lg font-bold text-gray-900 mb-1">Login Berhasil</h3>
                
                <p class="text-sm font-medium text-gray-500">
                    <?= $_SESSION['login_success']; ?>
                </p>
                
                <div class="w-full bg-gray-100 h-1 rounded-full mt-4 overflow-hidden">
                    <div class="bg-green-500 h-1 rounded-full animate-[pulse_1s_infinite] w-full"></div>
                </div>
            </div>
        </div>

        <script>
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 2000);
        </script>
        
        <?php 
        // Hapus session flash sukses agar tidak mengendap
        unset($_SESSION['login_success']); 
        ?>
    <?php endif; ?>
    </body>

</html>