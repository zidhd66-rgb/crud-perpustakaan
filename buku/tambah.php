<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/functions.php';

$buku = ['id_buku' => '', 'judul' => '', 'pengarang' => '',
         'penerbit' => '', 'tahun_terbit' => '', 'stok' => '0'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buku = ambil_input_buku($_POST);
    $errors = validasi_buku($buku);

    if (!$errors) {
        try {
            $sql = "INSERT INTO buku (id_buku, judul, pengarang, penerbit, tahun_terbit, stok)
                    VALUES (:id, :judul, :pengarang, :penerbit, :tahun, :stok)";
            $pdo->prepare($sql)->execute(param_buku($buku));

            set_flash('success', 'Buku berhasil ditambahkan.');
            redirect('index.php'); // pola PRG
        } catch (PDOException $e) {
            if ((int) $e->errorInfo[1] === 1062) { // duplikat primary key
                $errors[] = 'ID buku sudah digunakan.';
            } else {
                throw $e;
            }
        }
    }
}

$title = 'Tambah Buku';
$mode = 'tambah';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/_form.php';
require __DIR__ . '/../includes/footer.php';
