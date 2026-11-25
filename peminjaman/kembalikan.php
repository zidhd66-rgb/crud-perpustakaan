<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
$id = (int) ($_POST['id'] ?? 0);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id_buku, status FROM peminjaman WHERE id_pinjam = ? FOR UPDATE");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if (!$p || $p['status'] !== 'dipinjam') {
        throw new RuntimeException('Transaksi tidak ditemukan atau sudah dikembalikan.');
    }

    $pdo->prepare("UPDATE peminjaman SET status = 'kembali', tgl_kembali = CURDATE()
                   WHERE id_pinjam = ?")->execute([$id]);
    $pdo->prepare("UPDATE buku SET stok = stok + 1 WHERE id_buku = ?")
        ->execute([$p['id_buku']]);

    $pdo->commit();
    set_flash('success', 'Buku berhasil dikembalikan.');
} catch (PDOException $e) {
    $pdo->rollBack();
    throw $e;
} catch (RuntimeException $e) {
    $pdo->rollBack();
    set_flash('danger', $e->getMessage());
}
redirect('index.php');
