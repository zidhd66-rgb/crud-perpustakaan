<?php $flash = get_flash(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Perpustakaan') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/">Perpustakaan</a>
        <div class="navbar-nav">
            <a class="nav-link" href="<?= BASE_URL ?>/buku/">Buku</a>
            <a class="nav-link" href="<?= BASE_URL ?>/peminjaman/">Peminjaman</a>
        </div>
    </div>
</nav>
<main class="container">
<?php if ($flash): ?>
<div class="alert alert-<?= e($flash['tipe']) ?>"><?= e($flash['pesan']) ?></div>
<?php endif; ?>
