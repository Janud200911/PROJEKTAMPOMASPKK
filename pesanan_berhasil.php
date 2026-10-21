
<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

date_default_timezone_set("Asia/Jakarta");

// Pastikan pelanggan sudah login
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: login.php");
    exit;
}

$id_user = (int) ($_SESSION["id_user"] ?? 0);
$id_pesanan = (int) ($_GET["id"] ?? 0);

if ($id_user <= 0 || $id_pesanan <= 0) {
    header("Location: pesanan_saya.php");
    exit;
}

// Ambil pesanan milik pelanggan yang sedang login
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        p.id_pesanan,
        p.id_user,
        p.tanggal_pesanan,
        p.tanggal_pengambilan,
        p.total_berat,
        p.total_harga,
        p.status,
        p.catatan,
        p.metode_pembayaran,
        p.status_pembayaran,
        u.nama AS nama_pelanggan
     FROM tpesanan p
     JOIN tuser u ON p.id_user = u.id_user
     WHERE p.id_pesanan = ?
       AND p.id_user = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "ii", $id_pesanan, $id_user);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$pesanan = mysqli_fetch_assoc($hasil);

mysqli_stmt_close($stmt);

if (!$pesanan) {
    http_response_code(404);
    exit("Pesanan tidak ditemukan atau bukan milik akun kamu.");
}

// Ambil rincian produk pesanan
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        d.jumlah_kg,
        d.harga_satuan,
        d.subtotal,
        pr.nama_produk
     FROM tdetail_pesanan d
     LEFT JOIN tproduk pr ON d.id_produk = pr.id_produk
     WHERE d.id_pesanan = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
mysqli_stmt_execute($stmt);

$hasil_detail = mysqli_stmt_get_result($stmt);
$detail_produk = [];

while ($row = mysqli_fetch_assoc($hasil_detail)) {
    $detail_produk[] = $row;
}

mysqli_stmt_close($stmt);

// Fungsi pengaman output
function e($nilai)
{
    return htmlspecialchars(
        (string) ($nilai ?? "-"),
        ENT_QUOTES,
        "UTF-8"
    );
}

// Format Rupiah
function rupiah($nilai)
{
    return "Rp " . number_format(
        (float) $nilai,
        0,
        ",",
        "."
    );
}

// Format tanggal Indonesia
function tanggalIndonesia($tanggal, $denganJam = false)
{
    if (empty($tanggal)) {
        return "Belum ditentukan";
    }

    $timestamp = strtotime($tanggal);

    if ($timestamp === false) {
        return "Belum ditentukan";
    }

    $bulan = [
        1 => "Januari",
        2 => "Februari",
        3 => "Maret",
        4 => "April",
        5 => "Mei",
        6 => "Juni",
        7 => "Juli",
        8 => "Agustus",
        9 => "September",
        10 => "Oktober",
        11 => "November",
        12 => "Desember"
    ];

    $format = date("j", $timestamp) . " " .
        $bulan[(int) date("n", $timestamp)] . " " .
        date("Y", $timestamp);

    if ($denganJam) {
        $format .= " • " . date("H:i", $timestamp) . " WIB";
    }

    return $format;
}

// Label status agar mudah dibaca
function labelStatus($status)
{
    $label = [
        "menunggu" => "Menunggu",
        "diproses" => "Diproses",
        "siap_diambil" => "Siap Diambil",
        "selesai" => "Selesai",
        "dibatalkan" => "Dibatalkan",
        "belum_bayar" => "Belum Bayar",
        "menunggu_verifikasi" => "Menunggu Verifikasi",
        "lunas" => "Lunas"
    ];

    return $label[$status] ?? ucwords(
        str_replace("_", " ", (string) $status)
    );
}

$status_pesanan = (string) $pesanan["status"];
$status_bayar = (string) $pesanan["status_pembayaran"];

$status_dibatalkan = $status_pesanan === "dibatalkan";
$status_lunas = $status_bayar === "lunas";

if ($status_dibatalkan) {
    $kelas_status = "dibatalkan";
} elseif ($status_pesanan === "selesai") {
    $kelas_status = "selesai";
} elseif ($status_pesanan === "siap_diambil") {
    $kelas_status = "siap";
} elseif ($status_pesanan === "diproses") {
    $kelas_status = "diproses";
} else {
    $kelas_status = "menunggu";
}

$kelas_bayar = $status_lunas
    ? "lunas"
    : ($status_dibatalkan ? "dibatalkan" : "belum");

$total_item = count($detail_produk);
$total_berat = (float) $pesanan["total_berat"];
$total_harga = (float) $pesanan["total_harga"];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Struk Pesanan #<?= (int) $pesanan["id_pesanan"]; ?>
        | Tampomas Berkah
    </title>

    <link rel="stylesheet" href="assets/style.css">

</head>

<body>

<main class="struk-halaman">

    <div class="struk-aksi">
        <a href="pesanan_saya.php"
           class="struk-tombol tombol-kembali">
            ← Pesanan Saya
        </a>

        <button type="button"
                class="struk-tombol tombol-cetak"
                onclick="window.print()">
            Cetak / Simpan PDF
        </button>
    </div>

    <article class="struk-kartu">

        <header class="struk-header">

            <div class="struk-logo">
                <div class="struk-logo-icon">🥬</div>

                <div class="struk-logo-text">
                    <strong>Tampomas Berkah</strong>
                    <span>Sayuran Segar Pilihan</span>
                </div>
            </div>

            <h1>
                <?= $status_dibatalkan
                    ? "Detail Transaksi"
                    : "Struk Pembelian"; ?>
            </h1>

            <p>
                Terima kasih telah berbelanja di Tampomas Berkah.
                <br>
                Simpan struk ini sebagai bukti pesanan kamu.
            </p>

            <div class="struk-nomor">
                <span>NOMOR PESANAN</span>

                <strong>
                    #<?= (int) $pesanan["id_pesanan"]; ?>
                </strong>

                <br>

                <span class="struk-status status-<?= e($kelas_status); ?>">
                    <?= e(labelStatus($status_pesanan)); ?>
                </span>
            </div>

        </header>

        <div class="struk-konten">

            <section class="struk-bagian">

                <h2>Informasi Pelanggan</h2>

                <div class="struk-info">

                    <div class="struk-info-item">
                        <span>Nama pelanggan</span>
                        <strong><?= e($pesanan["nama_pelanggan"]); ?></strong>
                    </div>

                    <div class="struk-info-item">
                        <span>Tanggal transaksi</span>
                        <strong>
                            <?= e(tanggalIndonesia(
                                $pesanan["tanggal_pesanan"],
                                true
                            )); ?>
                        </strong>
                    </div>

                    <div class="struk-info-item">
                        <span>Metode pembayaran</span>
                        <strong>
                            <?= e(strtoupper(
                                $pesanan["metode_pembayaran"]
                            )); ?>
                        </strong>
                    </div>

                    <div class="struk-info-item">
                        <span>Status pembayaran</span>
                        <strong>
                            <span class="struk-status status-<?= e($kelas_bayar); ?>">
                                <?= e(labelStatus($status_bayar)); ?>
                            </span>
                        </strong>
                    </div>

                </div>

            </section>

            <section class="struk-bagian">

                <h2>Rincian Produk</h2>

                <div class="struk-tabel-wrapper">

                    <table class="struk-tabel">

                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Berat</th>
                                <th>Harga/kg</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (count($detail_produk) > 0): ?>

                                <?php foreach ($detail_produk as $item): ?>

                                    <tr>
                                        <td>
                                            <span class="struk-nama-produk">
                                                <?= e(
                                                    $item["nama_produk"]
                                                    ?? "Produk tidak tersedia"
                                                ); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= number_format(
                                                (float) $item["jumlah_kg"],
                                                2,
                                                ",",
                                                "."
                                            ); ?> kg
                                        </td>

                                        <td>
                                            <?= rupiah($item["harga_satuan"]); ?>
                                        </td>

                                        <td>
                                            <strong>
                                                <?= rupiah($item["subtotal"]); ?>
                                            </strong>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="4">
                                        Detail produk tidak tersedia.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

                <div class="struk-total">

                    <div class="struk-total-baris">
                        <span>Total jenis produk</span>
                        <strong><?= $total_item; ?> jenis</strong>
                    </div>

                    <div class="struk-total-baris">
                        <span>Total berat</span>
                        <strong>
                            <?= number_format(
                                $total_berat,
                                2,
                                ",",
                                "."
                            ); ?> kg
                        </strong>
                    </div>

                    <div class="struk-total-bayar">
                        <span>Total pembayaran</span>

                        <strong><?= rupiah($total_harga); ?></strong>
                    </div>

                </div>

            </section>

            <section class="struk-bagian">

                <h2>Informasi Pengambilan</h2>

                <div class="struk-pengambilan">

                    <strong>
                        <?= e(tanggalIndonesia(
                            $pesanan["tanggal_pengambilan"] ?? null
                        )); ?>
                    </strong>

                    <p>
                        Pesanan diambil langsung di lapak Tampomas Berkah.
                        <br>
                        Mohon tunjukkan nomor pesanan saat mengambil barang.
                    </p>

                </div>

            </section>

            <?php if (!empty($pesanan["catatan"])): ?>

                <section class="struk-bagian">

                    <h2>Catatan Pesanan</h2>

                    <div class="struk-catatan">
                        <?= nl2br(e($pesanan["catatan"])); ?>
                    </div>

                </section>

            <?php endif; ?>

        </div>

        <footer class="struk-footer">

            <strong>Terima Kasih! 🥬</strong>

            <p>
                Terima kasih telah mempercayakan kebutuhan sayuran
                kamu kepada Tampomas Berkah.
                <br>
                Sayuran segar, pelayanan terbaik.
            </p>

        </footer>

    </article>

    <p class="struk-catatan-bawah">
        Struk ini dibuat secara otomatis berdasarkan data transaksi.
        Status pesanan dan pembayaran dapat berubah sesuai pembaruan admin.
    </p>

</main>

</body>
</html>
