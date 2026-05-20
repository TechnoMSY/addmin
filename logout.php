<?php
// 1. Jalankan session agar sistem tahu session mana yang akan dihapus
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Bersihkan semua variabel session
$_SESSION = array();

// 3. Jika ingin menghapus cookie session secara total di browser (opsional tapi bagus untuk keamanan)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Hancurkan session di sisi server
session_destroy();

// 5. Alihkan pengguna kembali ke halaman login
header("Location: login.php");
exit();
?>