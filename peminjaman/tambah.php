<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/functions.php';

$data = ['id_anggota' => '', 'id_buku' => '', 'tgl_pinjam' => date('Y-m-d')];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id_anggota' => trim($_POST['id_anggota'] ?? ''),
        'id_buku' => trim($_POST['id_buku'] ?? ''),
        'tgl_pinjam' => trim($_POST['tgl_pinjam'] ?? ''),
    ];
    if ($data['id_anggota'] === '' || $data['id_buku'] === '') {
        $errors[] = 'Anggota dan buku wajib dipilih.';
    }
    $tgl = DateTime::createFromFormat('Y-m-d', $data['tgl_pinjam']);
    if (!$tgl || $tgl->format('Y-m-d') !== $data['tgl_pinjam']) {
        $errors[] = 'Tanggal pinjam tidak valid.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // Kunci baris buku agar stok tidak diubah proses lain
            $cek = $pdo->prepare("SELECT stok FROM buku WHERE id_buku = ? FOR UPDATE");
            $cek->execute([$data['id_buku']]);
            $stok = $cek->fetchColumn();
            if ($stok === false || (int) $stok < 1) {
                throw new RuntimeException('Stok buku habis atau buku tidak ditemukan.');
            }

            $pdo->prepare(
                "INSERT INTO peminjaman (id_anggota, id_buku, tgl_pinjam, status)
                 VALUES (?, ?, ?, 'dipinjam')"
            )->execute([$data['id_anggota'], $data['id_buku'], $data['tgl_pinjam']]);

            $pdo->prepare("UPDATE buku SET stok = stok - 1 WHERE id_buku = ?")
                ->execute([$data['id_buku']]);

            $pdo->commit();
            set_flash('success', 'Peminjaman berhasil dicatat.');
            redirect('index.php');
        } catch (PDOException $e) { // error basis data: batalkan, lempar lagi
            $pdo->rollBack();
            throw $e;
        } catch (RuntimeException $e) { // pelanggaran aturan bisnis
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}

// Data untuk dropdown
$anggota = $pdo->query("SELECT id_anggota, nama FROM anggota ORDER BY id_anggota")->fetchAll();
$buku = $pdo->query("SELECT id_buku, judul, stok FROM buku WHERE stok > 0 ORDER BY judul")->fetchAll();

$title = 'Pinjam Buku';
require __DIR__ . '/../includes/header.php';
?>
<h3 class="mb-3">Pinjam Buku</h3>
<?php tampil_errors($errors); ?>

<form method="post" class="card card-body">
    <div class="mb-3">
        <label class="form-label">Anggota</label>
        <select name="id_anggota" class="form-select" required>
            <option value="">-- pilih anggota --</option>
            <?php foreach ($anggota as $a): ?>
                <option value="<?= e($a['id_anggota']) ?>"
                    <?= $a['id_anggota'] === $data['id_anggota'] ? 'selected' : '' ?>>
                    <?= e($a['id_anggota'] . ' - ' . $a['nama']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Buku (hanya yang stoknya tersedia)</label>
        <select name="id_buku" class="form-select" required>
            <option value="">-- pilih buku --</option>
            <?php foreach ($buku as $b): ?>
                <option value="<?= e($b['id_buku']) ?>"
                    <?= $b['id_buku'] === $data['id_buku'] ? 'selected' : '' ?>>
                    <?= e($b['judul'] . ' (stok ' . $b['stok'] . ')') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Tanggal Pinjam</label>
        <input type="date" name="tgl_pinjam" class="form-control"
               value="<?= e($data['tgl_pinjam']) ?>" required>
    </div>
    <div>
        <button class="btn btn-primary">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
