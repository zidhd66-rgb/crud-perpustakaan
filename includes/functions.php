<?php
session_start();

// Mencegah XSS: ubah karakter khusus HTML menjadi entitas
function e($nilai): string
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

// Pesan flash: disimpan di session, ditampilkan sekali lalu dihapus
function set_flash(string $tipe, string $pesan): void
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

function tampil_errors(array $errors): void
{
    if (!$errors) {
        return;
    }
    echo '<div class="alert alert-danger"><ul class="mb-0">';
    foreach ($errors as $err) {
        echo '<li>' . e($err) . '</li>';
    }
    echo '</ul></div>';
}

// ---------- Khusus modul buku ----------
function ambil_input_buku(array $src): array
{
    return [
        'id_buku' => strtoupper(trim($src['id_buku'] ?? '')),
        'judul' => trim($src['judul'] ?? ''),
        'pengarang' => trim($src['pengarang'] ?? ''),
        'penerbit' => trim($src['penerbit'] ?? ''),
        'tahun_terbit' => trim($src['tahun_terbit'] ?? ''),
        'stok' => trim($src['stok'] ?? '0'),
    ];
}

function validasi_buku(array $b): array
{
    $err = [];
    if (!preg_match('/^B\d{3}$/', $b['id_buku'])) {
        $err[] = 'ID buku harus berformat Bxxx, contoh B008.';
    }
    if ($b['judul'] === '') {
        $err[] = 'Judul wajib diisi.';
    }
    if ($b['pengarang'] === '') {
        $err[] = 'Pengarang wajib diisi.';
    }
    if ($b['tahun_terbit'] !== '' && !ctype_digit($b['tahun_terbit'])) {
        $err[] = 'Tahun terbit harus berupa angka.';
    }
    if (!ctype_digit($b['stok'])) {
        $err[] = 'Stok harus bilangan bulat >= 0.';
    }
    return $err;
}

// Menyiapkan parameter untuk INSERT/UPDATE; string kosong -> NULL
function param_buku(array $b): array
{
    return [
        ':id' => $b['id_buku'],
        ':judul' => $b['judul'],
        ':pengarang' => $b['pengarang'],
        ':penerbit' => $b['penerbit'] !== '' ? $b['penerbit'] : null,
        ':tahun' => $b['tahun_terbit'] !== '' ? (int) $b['tahun_terbit'] : null,
        ':stok' => (int) $b['stok'],
    ];
}
