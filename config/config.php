<?php
// ---------- Pengaturan aplikasi ----------
define('BASE_URL', '/perpus-php'); // isi '' jika menjalankan dengan php -S
date_default_timezone_set('Asia/Jakarta');

// ---------- Pengaturan basis data ----------
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_perpustakaan');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,          // error -> exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // hasil berupa array asosiatif
            PDO::ATTR_EMULATE_PREPARES => false,                   // prepared statement asli
        ]
    );
} catch (PDOException $e) {
    die('Koneksi basis data gagal: ' . $e->getMessage());
}
