<?php
// Uji skenario CRUD via HTTP — memakai curl + cookie jar agar pesan flash (session) terbaca
$JAR = tempnam(sys_get_temp_dir(), 'cj');

function req($url, $post = null) {
    global $JAR;
    $body = '';
    for ($i = 0; $i < 3; $i++) {          // ulangi bila respons kosong (transient)
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEJAR       => $JAR,
            CURLOPT_COOKIEFILE      => $JAR,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_TIMEOUT         => 20,
            CURLOPT_HEADER          => false,
        ]);
        if ($post !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        $body = curl_exec($ch);
        curl_close($ch);
        if ($body !== false && $body !== '') break;
    }
    return (string) $body;
}

function sql($query) {
    $out = [];
    exec('C:\\xampp\\mysql\\bin\\mysql.exe -u root -N -B -e ' . escapeshellarg($query) . ' 2>&1', $out);
    return trim(implode("\n", $out));
}

function out($label, $ok, $detail = '') {
    printf("%-54s %s %s\n", $label, $ok ? 'PASS' : 'FAIL', $detail);
}

$B = 'http://localhost/perpus-php/buku/';
$T = $B . 'tambah.php';
$H = $B . 'hapus.php';
$P = 'http://localhost/perpus-php/peminjaman/';

echo "=== MODUL BUKU ===\n";

// S1
$body = req($B);
$n = preg_match_all('/<td>B\d{3}<\/td>/', $body, $m);
out("S1 Daftar buku tampil (7 baris)", $n === 7, "jumlah=$n");

// S2
$body = req($B . '?q=munir');
out("S2 Cari 'munir' -> hanya B003 & B007",
    strpos($body, 'B003') !== false && strpos($body, 'B007') !== false
    && strpos($body, 'B001') === false);

// S3
$body = req($T, ['id_buku'=>'B008','judul'=>'Pemrograman Web Lanjut','pengarang'=>'Ahmad Fikri',
                 'penerbit'=>'Informatika','tahun_terbit'=>'2025','stok'=>'5']);
$flashOk = strpos($body, 'Buku berhasil ditambahkan') !== false; // flash dibaca pada respons POST (sudah redirect)
$body = req($B);
out("S3 Tambah B008 -> pesan sukses + muncul di tabel",
    $flashOk && strpos($body, 'B008') !== false);

// S4
$body = req($T, ['id_buku'=>'B008','judul'=>'Duplikat','pengarang'=>'X','penerbit'=>'',
                 'tahun_terbit'=>'','stok'=>'1']);
out("S4 Duplikat B008 ditolak (error 1062)",
    strpos($body, 'ID buku sudah digunakan') !== false);

// S5
$body = req($T, ['id_buku'=>'X1','judul'=>'Salah','pengarang'=>'X','penerbit'=>'',
                 'tahun_terbit'=>'','stok'=>'1']);
out("S5 ID 'X1' ditolak validasi format Bxxx",
    strpos($body, 'berformat Bxxx') !== false);

// S6
$body6 = req($B . 'edit.php?id=B008', ['id_buku'=>'B008','judul'=>'Pemrograman Web Lanjut',
    'pengarang'=>'Ahmad Fikri','penerbit'=>'Informatika','tahun_terbit'=>'2025','stok'=>'8']);
$stok = sql("SELECT stok FROM db_perpustakaan.buku WHERE id_buku='B008'");
$idEditable = sql("SELECT id_buku FROM db_perpustakaan.buku WHERE id_buku='B008'");
out("S6 Edit B008 -> stok 8, pesan sukses, ID tetap B008",
    $stok === '8' && $idEditable === 'B008'
    && strpos($body6, 'berhasil diperbarui') !== false, "stok=$stok");

// S7
$body7 = req($H, ['id'=>'B008']);
$sisa = sql("SELECT COUNT(*) FROM db_perpustakaan.buku WHERE id_buku='B008'");
$body = req($B);
out("S7 Hapus B008 -> sukses & hilang dari tabel",
    $sisa === '0' && strpos($body7, 'B008 berhasil dihapus') !== false
    && strpos($body, '>B008<') === false, "sisa=$sisa");

// S8
$body8 = req($H, ['id'=>'B001']);
$sisa = sql("SELECT COUNT(*) FROM db_perpustakaan.buku WHERE id_buku='B001'");
$body = req($B);
out("S8 Hapus B001 ditolak foreign key (1451)",
    $sisa === '1' && strpos($body8, 'riwayat peminjaman') !== false, "B001 ada=$sisa");

echo "\n=== MODUL PEMINJAMAN ===\n";

// S9
$body = req($P);
$n = preg_match_all('/<td>\d+<\/td>/', $body, $m);
out("S9 12 transaksi tampil (INNER JOIN)", $n === 12, "jumlah=$n");
out("S9b Nama anggota + judul buku tergabung",
    strpos($body, 'Andi Pratama') !== false && strpos($body, 'Sistem Basis Data') !== false);
out("S9c Badge keterlambatan tampil", preg_match('/terlambat \d+ hari/', $body) === 1);

// S10
$body = req($P . '?status=dipinjam');
$adaKembali = strpos($body, 'badge bg-success">kembali');
$adaBaris = preg_match('/badge bg-(warning text-dark">dipinjam|danger">terlambat)/', $body);
out("S10 Filter status 'dipinjam' tampil benar",
    $adaKembali === false && $adaBaris === 1,
    sprintf("len=%d baris_dipinjam=%d", strlen($body), $adaBaris));

// S11
$sebelum = (int) sql("SELECT stok FROM db_perpustakaan.buku WHERE id_buku='B007'");
req($P . 'tambah.php', ['id_anggota'=>'A006','id_buku'=>'B007','tgl_pinjam'=>date('Y-m-d')]);
$sesudah = (int) sql("SELECT stok FROM db_perpustakaan.buku WHERE id_buku='B007'");
out("S11 A006 pinjam B007 -> stok berkurang 1", $sesudah === $sebelum - 1, "$sebelum -> $sesudah");

// S12
$idPinjam = (int) sql("SELECT MAX(id_pinjam) FROM db_perpustakaan.peminjaman WHERE id_anggota='A006' AND id_buku='B007'");
req($P . 'kembalikan.php', ['id'=>$idPinjam]);
$row = sql("SELECT CONCAT(status,'|',tgl_kembali) FROM db_perpustakaan.peminjaman WHERE id_pinjam=$idPinjam");
$stok = (int) sql("SELECT stok FROM db_perpustakaan.buku WHERE id_buku='B007'");
$body = req($P);
out("S12 Kembalikan -> status kembali, tgl hari ini, stok pulih",
    strpos($row, 'kembali|' . date('Y-m-d')) === 0 && $stok === $sebelum,
    "row=$row stok=$stok");

// S13
$stok6 = (int) sql("SELECT stok FROM db_perpustakaan.buku WHERE id_buku='B006'");
for ($i = 0; $i < 2; $i++) {
    req($P . 'tambah.php', ['id_anggota'=>'A005','id_buku'=>'B006','tgl_pinjam'=>date('Y-m-d')]);
}
$stok6s = (int) sql("SELECT stok FROM db_perpustakaan.buku WHERE id_buku='B006'");
$form = req($P . 'tambah.php');
out("S13 Pinjam B006 dua kali -> stok 0 & hilang dari dropdown",
    $stok6s === 0 && strpos($form, 'B006') === false, "stok $stok6 -> $stok6s");

// S14
$id6 = (int) sql("SELECT MAX(id_pinjam) FROM db_perpustakaan.peminjaman WHERE id_buku='B006' AND status='dipinjam'");
$body14 = req($P . 'hapus.php', ['id'=>$id6]);
$sisa6 = (int) sql("SELECT COUNT(*) FROM db_perpustakaan.peminjaman WHERE id_pinjam=$id6");
$stok6k = (int) sql("SELECT stok FROM db_perpustakaan.buku WHERE id_buku='B006'");
out("S14 Hapus transaksi B006 -> terhapus & stok +1",
    $sisa6 === 0 && $stok6k === 1
    && strpos($body14, 'Transaksi berhasil dihapus') !== false,
    "sisa=$sisa6 stok=$stok6k");

@unlink($JAR);
echo "\nSelesai.\n";
