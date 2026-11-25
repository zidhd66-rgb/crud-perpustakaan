<?php /* Variabel wajib: $buku, $errors, $mode ('tambah' atau 'edit') */ ?>
<h3 class="mb-3"><?= $mode === 'tambah' ? 'Tambah Buku' : 'Edit Buku' ?></h3>
<?php tampil_errors($errors); ?>

<form method="post" class="card card-body">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">ID Buku</label>
            <input type="text" name="id_buku" class="form-control" maxlength="4"
                   value="<?= e($buku['id_buku']) ?>" <?= $mode === 'edit' ? 'readonly' : '' ?> required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Judul</label>
            <input type="text" name="judul" class="form-control" maxlength="100"
                   value="<?= e($buku['judul']) ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Pengarang</label>
            <input type="text" name="pengarang" class="form-control" maxlength="50"
                   value="<?= e($buku['pengarang']) ?>" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Penerbit</label>
            <input type="text" name="penerbit" class="form-control" maxlength="50"
                   value="<?= e($buku['penerbit']) ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Tahun Terbit</label>
            <input type="number" name="tahun_terbit" class="form-control" min="1900" max="2100"
                   value="<?= e($buku['tahun_terbit']) ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Stok</label>
            <input type="number" name="stok" class="form-control" min="0"
                   value="<?= e($buku['stok']) ?>" required>
        </div>
    </div>
    <div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </div>
</form>
