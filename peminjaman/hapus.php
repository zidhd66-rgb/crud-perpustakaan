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
    if (!$p) {
        throw new RuntimeException('Transaksi tidak ditemukan.');
    }
    if ($p['status'] === 'dipinjam') {
        $pdo->prepare("UPDATE buku SET stok = stok + 1 WHERE id_buku = ?")
            ->execute([$p['id_buku']]);
    }
    $pdo->prepare("DELETE FROM peminjaman WHERE id_pinjam = ?")->execute([$id]);

    $pdo->commit();
    set_flash('success', 'Transaksi berhasil dihapus.');
} catch (PDOException $e) {
    $pdo->rollBack();
    throw $e;
} catch (RuntimeException $e) {
    $pdo->rollBack();
    set_flash('danger', $e->getMessage());
}
redirect('index.php');
