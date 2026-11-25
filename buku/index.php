<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/functions.php';

$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $stmt = $pdo->prepare(
        "SELECT * FROM buku
         WHERE judul LIKE :q1 OR pengarang LIKE :q2
         ORDER BY id_buku"
    );
    $stmt->execute([':q1' => "%$q%", ':q2' => "%$q%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM buku ORDER BY id_buku");
}
$daftar = $stmt->fetchAll();

$title = 'Data Buku';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Data Buku</h3>
    <a href="tambah.php" class="btn btn-success">+ Tambah Buku</a>
</div>

<form method="get" class="mb-3">
    <div class="input-group">
        <input type="text" name="q" class="form-control"
               placeholder="Cari judul / pengarang..." value="<?= e($q) ?>">
        <button class="btn btn-outline-secondary">Cari</button>
    </div>
</form>

<table class="table table-bordered table-striped">
    <thead class="table-light">
    <tr>
        <th>ID</th><th>Judul</th><th>Pengarang</th><th>Penerbit</th>
        <th>Tahun</th><th>Stok</th><th>Aksi</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($daftar as $b): ?>
        <tr>
            <td><?= e($b['id_buku']) ?></td>
            <td><?= e($b['judul']) ?></td>
            <td><?= e($b['pengarang']) ?></td>
            <td><?= e($b['penerbit'] ?? '-') ?></td>
            <td><?= e($b['tahun_terbit'] ?? '-') ?></td>
            <td><?= e($b['stok']) ?></td>
            <td>
                <a href="edit.php?id=<?= urlencode($b['id_buku']) ?>" class="btn btn-sm btn-warning">Edit</a>
                <form action="hapus.php" method="post" class="d-inline"
                      onsubmit="return confirm('Hapus buku ini?')">
                    <input type="hidden" name="id" value="<?= e($b['id_buku']) ?>">
                    <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$daftar): ?>
        <tr><td colspan="7" class="text-center">Data tidak ditemukan</td></tr>
    <?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
