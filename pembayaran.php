
<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: login.php");
    exit;
}

$id_user = (int) $_SESSION["id_user"];
$id_pesanan = (int) ($_GET["id"] ?? 0);

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id_pesanan, tanggal_pesanan, total_berat,
            total_harga, status, metode_pembayaran,
            status_pembayaran, bukti_pembayaran
     FROM tpesanan
     WHERE id_pesanan = ? AND id_user = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $id_pesanan, $id_user);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$pesanan = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if (!$pesanan) {
    exit("Pesanan tidak ditemukan.");
}

function e($nilai) {
    return htmlspecialchars((string) ($nilai ?? "-"), ENT_QUOTES, "UTF-8");
}

$metode = strtolower($pesanan["metode_pembayaran"]);
$statusBayar = strtolower($pesanan["status_pembayaran"]);
$bisaUpload = in_array($metode, ["qris", "transfer"], true)
    && !in_array($statusBayar, ["lunas", "dibatalkan"], true)
    && $pesanan["status"] !== "dibatalkan";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran | Tampomas Berkah</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="qris-container">

    <a href="pesanan_saya.php" class="qris-kembali">
        &larr; Kembali ke Daftar Pesanan
    </a>

    <h1>Pembayaran Pesanan</h1>
    <p class="qris-subtitle">
        Periksa detail pesanan dan kirim bukti pembayaran.
    </p>

    <?php if (isset($_GET["upload"]) && $_GET["upload"] === "berhasil"): ?>
        <p class="qris-info">
            Bukti berhasil dikirim. Tunggu admin memverifikasi pembayaran lu.
        </p>
    <?php elseif (isset($_GET["upload"]) && $_GET["upload"] === "gagal"): ?>
        <p class="qris-info">
            Upload gagal. Periksa file dan coba lagi.
        </p>
    <?php endif; ?>

    <div class="qris-layout">

        <div class="qris-card">
            <h2>Detail Pesanan</h2>

            <div class="qris-detail-row">
                <span>No. Pesanan</span>
                <strong>#<?= (int) $pesanan["id_pesanan"]; ?></strong>
            </div>

            <div class="qris-detail-row">
                <span>Tanggal</span>
                <strong><?= e($pesanan["tanggal_pesanan"]); ?></strong>
            </div>

            <div class="qris-detail-row">
                <span>Total Berat</span>
                <strong>
                    <?= number_format((float) $pesanan["total_berat"], 2, ",", "."); ?> kg
                </strong>
            </div>

            <div class="qris-detail-row">
                <span>Status Pesanan</span>
                <strong><?= e($pesanan["status"]); ?></strong>
            </div>

            <div class="qris-detail-row">
                <span>Metode Pembayaran</span>
                <strong><?= e(strtoupper($metode)); ?></strong>
            </div>

            <div class="qris-detail-row">
                <span>Status Pembayaran</span>
                <strong><?= e(str_replace("_", " ", $statusBayar)); ?></strong>
            </div>

            <div class="qris-total">
                <span>Total Pembayaran</span>
                <strong>
                    Rp <?= number_format((float) $pesanan["total_harga"], 0, ",", "."); ?>
                </strong>
            </div>
        </div>

        <div class="qris-card qris-payment-card">

            <?php if ($metode === "qris"): ?>

                <h2>Pembayaran QRIS</h2>

                <div class="qris-placeholder">
                    <div class="qris-icon">▦</div>
                    <h3>QRIS DEMO</h3>
                    <p>Gambar QRIS resmi belum tersedia.</p>
                    <span>Belum untuk pembayaran sungguhan</span>
                </div>

                <div class="qris-info">
                    QRIS ini masih berupa prototype. Jangan melakukan
                    pembayaran sungguhan melalui gambar atau halaman demo ini.
                </div>

            <?php elseif ($metode === "transfer"): ?>

                <h2>Pembayaran Transfer</h2>

                <div class="qris-info">
                    Informasi rekening penjual belum ditampilkan.
                    Gunakan halaman ini untuk menguji upload bukti saja.
                    Jangan melakukan transfer sungguhan sebelum rekening
                    resmi dikonfirmasi oleh penjual.
                </div>

            <?php else: ?>

                <h2>Metode Pembayaran</h2>
                <p>Metode pembayaran ini tidak mendukung upload bukti.</p>

            <?php endif; ?>

            <?php if ($bisaUpload): ?>

                <hr>

                <h2>Kirim Bukti Pembayaran</h2>

                <p>
                    Pilih foto bukti pembayaran berformat JPG, PNG, atau WEBP.
                    Maksimal ukuran file 3 MB.
                </p>

                <form action="aksi/proses_upload_bukti.php"
                      method="POST"
                      enctype="multipart/form-data">

                    <input type="hidden"
                           name="id_pesanan"
                           value="<?= (int) $pesanan["id_pesanan"]; ?>">

                    <label for="bukti_pembayaran">Foto bukti pembayaran</label>

                    <input type="file"
                           id="bukti_pembayaran"
                           name="bukti_pembayaran"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                           required>

                    <button type="submit" class="btn-update-status">
                        Kirim Bukti Pembayaran
                    </button>

                </form>

            <?php elseif ($statusBayar === "lunas"): ?>

                <p>Pembayaran pesanan ini sudah lunas.</p>

            <?php else: ?>

                <p>Upload bukti tidak tersedia untuk pesanan ini.</p>

            <?php endif; ?>

            <?php if (!empty($pesanan["bukti_pembayaran"])): ?>
                <p>
                    Bukti pembayaran sudah pernah dikirim.
                    Status saat ini:
                    <strong><?= e(str_replace("_", " ", $statusBayar)); ?></strong>
                </p>
            <?php endif; ?>

        </div>

    </div>

</div>

</body>
</html>
