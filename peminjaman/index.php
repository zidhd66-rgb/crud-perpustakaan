<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/functions.php';

$status = $_GET['status'] ?? '';

$sql = "SELECT p.id_pinjam, a.nama, b.judul, p.tgl_pinjam, p.tgl_kembali, p.status,
        CASE WHEN p.status = 'dipinjam'
             THEN GREATEST(DATEDIFF(CURDATE(), p.tgl_pinjam) - 7, 0)
             ELSE 0 END AS hari_terlambat
        FROM peminjaman p
        INNER JOIN anggota a ON a.id_anggota = p.id_anggota
        INNER JOIN buku b ON b.id_buku = p.id_buku";
$params = [];
if (in_array($status, ['dipinjam', 'kembali'], true)) { // whitelist nilai filter
    $sql .= " WHERE p.status = :status";
    $params[':status'] = $status;
}
$sql .= " ORDER BY p.tgl_pinjam DESC, p.id_pinjam DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$daftar = $stmt->fetchAll();

$title = 'Data Peminjaman';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Data Peminjaman</h3>
    <a href="tambah.php" class="btn btn-success">+ Pinjam Buku</a>
</div>

<form method="get" class="mb-3 d-flex gap-2 align-items-center">
    <label for="status">Status:</label>
    <select name="status" id="status" class="form-select w-auto" onchange="this.form.submit()">
        <option value="">Semua</option>
        <option value="dipinjam" <?= $status === 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
        <option value="kembali" <?= $status === 'kembali' ? 'selected' : '' ?>>Kembali</option>
    </select>
</form>

<table class="table table-bordered table-striped">
    <thead class="table-light">
    <tr>
        <th>ID</th><th>Anggota</th><th>Judul Buku</th><th>Tgl Pinjam</th>
        <th>Tgl Kembali</th><th>Status</th><th>Aksi</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($daftar as $p): ?>
        <tr>
            <td><?= e($p['id_pinjam']) ?></td>
            <td><?= e($p['nama']) ?></td>
            <td><?= e($p['judul']) ?></td>
            <td><?= e($p['tgl_pinjam']) ?></td>
            <td><?= e($p['tgl_kembali'] ?? '-') ?></td>
            <td>
                <?php if ($p['status'] === 'kembali'): ?>
                    <span class="badge bg-success">kembali</span>
                <?php elseif ($p['hari_terlambat'] > 0): ?>
                    <span class="badge bg-danger">terlambat <?= e($p['hari_terlambat']) ?> hari</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">dipinjam</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($p['status'] === 'dipinjam'): ?>
                    <form action="kembalikan.php" method="post" class="d-inline">
                        <input type="hidden" name="id" value="<?= e($p['id_pinjam']) ?>">
                        <button class="btn btn-sm btn-primary">Kembalikan</button>
                    </form>
                <?php endif; ?>
                <form action="hapus.php" method="post" class="d-inline"
                      onsubmit="return confirm('Hapus transaksi ini?')">
                    <input type="hidden" name="id" value="<?= e($p['id_pinjam']) ?>">
                    <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$daftar): ?>
        <tr><td colspan="7" class="text-center">Belum ada transaksi</td></tr>
    <?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../includes/footer.php'; ?>
