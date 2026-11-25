<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$id = $_POST['id'] ?? '';

try {
    $stmt = $pdo->prepare("DELETE FROM buku WHERE id_buku = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        set_flash('success', "Buku $id berhasil dihapus.");
    } else {
        set_flash('warning', 'Buku tidak ditemukan.');
    }
} catch (PDOException $e) {
    if ((int) $e->errorInfo[1] === 1451) { // ditolak foreign key
        set_flash('danger', 'Buku tidak dapat dihapus karena memiliki riwayat peminjaman.');
    } else {
        throw $e;
    }
}
redirect('index.php');
