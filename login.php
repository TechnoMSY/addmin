<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

$error_message = "";
$success_message = "";
$success_redirect = false;
$active_tab = "login"; 

$db_connect = isset($conn) ? $conn : (isset($koneksi) ? $koneksi : null);
if (!$db_connect) {
    die("Eror Sistem: Variabel koneksi database tidak ditemukan. Periksa koneksi.php Anda.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // A. LOGIKA LOGIN
    if (isset($_POST['aksi_login'])) {
        $active_tab = "login";
        $username = mysqli_real_escape_string($db_connect, trim($_POST['username']));
        $password = trim($_POST['password']);

        if (!empty($username) && !empty($password)) {
            $query = "SELECT * FROM user WHERE username = '$username' LIMIT 1";
            $result = mysqli_query($db_connect, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                $user_data = mysqli_fetch_assoc($result);
                
                if (md5($password) === $user_data['password']) {
                    $_SESSION['user'] = [
                        'id_user'  => $user_data['id_user'],
                        'username' => $user_data['username'],
                        'nama'     => $user_data['nama'],
                        'email'    => $user_data['email'],
                        'role'     => $user_data['level'],
                        'alamat'   => $user_data['alamat']
                    ];
                    
                    $_SESSION['login_success'] = "Selamat Datang kembali, <strong>" . htmlspecialchars($user_data['nama']) . "</strong>! Mengalihkan ke Dashboard...";
                    $success_redirect = true;
                } else {
                    $error_message = "Kata sandi yang Anda masukkan salah. Silakan coba lagi.";
                }
            } else {
                $error_message = "Username tidak terdaftar di sistem kami.";
            }
        } else {
            $error_message = "Mohon isi semua kolom username dan password.";
        }
    }

    // B. LOGIKA REGISTER
    if (isset($_POST['aksi_register'])) {
        $active_tab = "register";
        $nama     = mysqli_real_escape_string($db_connect, trim($_POST['nama']));
        $username = mysqli_real_escape_string($db_connect, trim($_POST['username']));
        $email    = mysqli_real_escape_string($db_connect, trim($_POST['email']));
        $password = trim($_POST['password']);
        $alamat   = mysqli_real_escape_string($db_connect, trim($_POST['alamat']));

        if (!empty($nama) && !empty($username) && !empty($email) && !empty($password)) {
            
            $cek_query = "SELECT username, email FROM user WHERE username='$username' OR email='$email' LIMIT 1";
            $cek_res = mysqli_query($db_connect, $cek_query);

            if (mysqli_num_rows($cek_res) > 0) {
                $exist_data = mysqli_fetch_assoc($cek_res);
                if ($exist_data['username'] === $username) {
                    $error_message = "Username sudah digunakan, pilih username lainnya.";
                } else {
                    $error_message = "Alamat Email sudah terdaftar di sistem.";
                }
            } else {
                $password_md5 = md5($password);
                $insert_query = "INSERT INTO user (nama, username, password, email, level, alamat) VALUES ('$nama', '$username', '$password_md5', '$email', 'siswa', '$alamat')";
                
                if (mysqli_query($db_connect, $insert_query)) {
                    $success_message = "Akun berhasil dibuat! Silakan masuk menggunakan tab LOGIN.";
                    $active_tab = "login"; 
                } else {
                    $error_message = "Gagal mendaftarkan akun. Terjadi kesalahan pada server.";
                }
            }
        } else {
            $error_message = "Mohon lengkapi seluruh kolom formulir registrasi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Gerbang Masuk | Lab MAKN</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1" rel="stylesheet" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4 selection:bg-blue-600 selection:text-white">

    <div class="w-full max-w-[440px] bg-white rounded-[32px] border border-slate-200/70 p-8 shadow-xl shadow-slate-100/50 relative overflow-hidden transition-all">
        
        <div class="flex flex-col items-center text-center mb-8">
            <div class="w-14 h-14 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center overflow-hidden shadow-sm p-1.5 mb-4">
                <img src="images/image.png" alt="Logo" class="w-full h-full object-contain" onerror="this.parentNode.innerHTML='<div class=\'w-full h-full bg-blue-600 text-white font-extrabold text-lg flex items-center justify-center\'>L</div>';">
            </div>
            <h1 class="text-lg font-black text-slate-900 tracking-tight uppercase">Lab MAKN Ende</h1>
            <p class="text-xs font-medium text-slate-400 mt-1">Sistem Lab Komputer</p>
        </div>

        <div class="grid grid-cols-2 bg-slate-100 p-1.5 rounded-2xl mb-6">
            <button id="btn-tab-login" onclick="gantiTab('login')" class="py-2.5 text-xs font-bold rounded-xl transition-all tracking-wide uppercase">Masuk</button>
            <button id="btn-tab-register" onclick="gantiTab('register')" class="py-2.5 text-xs font-bold rounded-xl transition-all tracking-wide uppercase">Daftar</button>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="p-3.5 bg-red-50 border border-red-200 text-red-600 rounded-xl text-xs font-semibold flex items-center gap-2.5 mb-5 animate-[shake_0.4s_ease-in-out]">
                <span class="material-symbols-outlined text-base shrink-0">error</span>
                <p><?= $error_message; ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold flex items-center gap-2.5 mb-5 animate-[fadeIn_0.3s_ease-out]">
                <span class="material-symbols-outlined text-base shrink-0">check_circle</span>
                <p><?= $success_message; ?></p>
            </div>
        <?php endif; ?>

        <form id="form-login" method="POST" action="login.php" class="space-y-4">
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-1">Nama Pengguna (Username)</label>
                <div class="relative">
                    <span class="material-symbols-outlined text-slate-400 text-lg absolute left-3.5 top-1/2 -translate-y-1/2">person</span>
                    <input type="text" name="username" required placeholder="Masukkan username Anda" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-blue-600 focus:ring-0 transition-all placeholder:text-slate-400" />
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-1">Kata Sandi (Password)</label>
                <div class="relative">
                    <span class="material-symbols-outlined text-slate-400 text-lg absolute left-3.5 top-1/2 -translate-y-1/2">lock</span>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-blue-600 focus:ring-0 transition-all placeholder:text-slate-400" />
                </div>
            </div>

            <button type="submit" name="aksi_login" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-3 px-4 rounded-xl shadow-md shadow-blue-500/10 transition-colors uppercase tracking-wider mt-2">Masuk Ke Sistem</button>
        </form>

        <form id="form-register" method="POST" action="login.php" class="space-y-4 hidden">
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-1">Nama Lengkap</label>
                <div class="relative">
                    <span class="material-symbols-outlined text-slate-400 text-lg absolute left-3.5 top-1/2 -translate-y-1/2">badge</span>
                    <input type="text" name="nama" required placeholder="Nama Lengkap sesuai identitas" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-blue-600 focus:ring-0 transition-all placeholder:text-slate-400" />
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-1">Username</label>
                <div class="relative">
                    <span class="material-symbols-outlined text-slate-400 text-lg absolute left-3.5 top-1/2 -translate-y-1/2">account_circle</span>
                    <input type="text" name="username" required placeholder="Buat nama pengguna tanpa spasi" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-blue-600 focus:ring-0 transition-all placeholder:text-slate-400" />
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-1">Alamat Email</label>
                <div class="relative">
                    <span class="material-symbols-outlined text-slate-400 text-lg absolute left-3.5 top-1/2 -translate-y-1/2">mail</span>
                    <input type="email" name="email" required placeholder="contoh@domain.com" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-blue-600 focus:ring-0 transition-all placeholder:text-slate-400" />
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-1">Kata Sandi</label>
                <div class="relative">
                    <span class="material-symbols-outlined text-slate-400 text-lg absolute left-3.5 top-1/2 -translate-y-1/2">lock</span>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-blue-600 focus:ring-0 transition-all placeholder:text-slate-400" />
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-1">Alamat Asal</label>
                <div class="relative">
                    <span class="material-symbols-outlined text-slate-400 text-lg absolute left-3.5 top-4">home_pin</span>
                    <textarea name="alamat" rows="2" placeholder="Tuliskan nama kota atau alamat singkat..." class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:border-blue-600 focus:ring-0 transition-all resize-none placeholder:text-slate-400"></textarea>
                </div>
            </div>

            <button type="submit" name="aksi_register" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-3 px-4 rounded-xl shadow-md transition-colors uppercase tracking-wider mt-2">Daftarkan Akun Siswa</button>
        </form>

    </div>

    <?php if ($success_redirect): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-md animate-[fadeIn_0.2s_ease-out]">
            <div class="bg-white rounded-[24px] p-6 max-w-sm w-full border border-slate-100 text-center shadow-2xl">
                <div class="inline-flex items-center justify-center w-14 h-14 text-emerald-500 bg-emerald-50 rounded-2xl mb-4 shadow-sm">
                    <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </div>
                <h3 class="text-sm font-bold text-slate-900 mb-1">Login Berhasil</h3>
                <p class="text-xs font-medium text-slate-500 leading-relaxed px-2">
                    <?= $_SESSION['login_success']; ?>
                </p>
                <div class="w-full bg-slate-100 h-1 rounded-full mt-5 overflow-hidden">
                    <div class="bg-emerald-500 h-1 rounded-full w-full animate-[loadingBar_1.2s_linear_forwards]"></div>
                </div>
            </div>
        </div>
        <script>
            setTimeout(function() { window.location.href = 'index.php'; }, 1200);
        </script>
    <?php endif; ?>

    <script>
        function gantiTab(tab) {
            const formLogin = document.getElementById('form-login');
            const formRegister = document.getElementById('form-register');
            const btnLogin = document.getElementById('btn-tab-login');
            const btnRegister = document.getElementById('btn-tab-register');

            if (tab === 'login') {
                formRegister.classList.add('hidden');
                formLogin.classList.remove('hidden');
                btnLogin.className = "py-2.5 text-xs font-bold rounded-xl transition-all tracking-wide uppercase bg-white text-blue-600 shadow-sm";
                btnRegister.className = "py-2.5 text-xs font-bold rounded-xl transition-all tracking-wide uppercase text-slate-500 hover:text-slate-800";
            } else if (tab === 'register') {
                formLogin.classList.add('hidden');
                formRegister.classList.remove('hidden');
                btnLogin.className = "py-2.5 text-xs font-bold rounded-xl transition-all tracking-wide uppercase text-slate-500 hover:text-slate-800";
                btnRegister.className = "py-2.5 text-xs font-bold rounded-xl transition-all tracking-wide uppercase bg-white text-emerald-600 shadow-sm";
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            gantiTab('<?= $active_tab; ?>');
        });
    </script>

    <style>
        @keyframes loadingBar { from { width: 0%; } to { width: 100%; } }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-4px); } 40%, 80% { transform: translateX(4px); } }
    </style>
</body>
</html>