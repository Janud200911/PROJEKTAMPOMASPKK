
<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";

// Cek login dan hak akses admin
if (
    !isset($_SESSION["login"]) ||
    $_SESSION["login"] !== true ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: ../login.php");
    exit;
}

// Ambil semua pesanan beserta nama pelanggan dan produknya
$query = mysqli_query($koneksi, "
    SELECT
        p.id_pesanan,
        u.nama AS nama_pelanggan,
        p.tanggal_pesanan,
        p.tanggal_pengambilan,
        p.total_berat,
        p.total_harga,
        p.status,
        p.metode_pembayaran,
        p.status_pembayaran,
        (
            SELECT GROUP_CONCAT(
                CONCAT(pr.nama_produk, ' (', d.jumlah_kg, ' kg)')
                SEPARATOR ', '
            )
            FROM tdetail_pesanan d
            JOIN tproduk pr ON d.id_produk = pr.id_produk
            WHERE d.id_pesanan = p.id_pesanan
        ) AS detail_produk
    FROM tpesanan p
    JOIN tuser u ON p.id_user = u.id_user
    ORDER BY p.tanggal_pesanan DESC
");

if (!$query) {
    die(
        "Gagal mengambil data pesanan: " .
        htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, "UTF-8")
    );
}

// Fungsi untuk mengamankan output
function e($nilai) {
    return htmlspecialchars(
        (string) ($nilai ?? "-"),
        ENT_QUOTES,
        "UTF-8"
    );
}

// Format tanggal
function formatTanggal($tanggal) {
    if (empty($tanggal)) {
        return "Belum ditentukan";
    }

    $timestamp = strtotime($tanggal);

    if ($timestamp === false) {
        return "Belum ditentukan";
    }

    return date("d-m-Y", $timestamp);
}

$total_pesanan = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Pesanan | Tampomas Berkah</title>

    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>

<div class="admin-pesanan-container">

    <div class="admin-pesanan-header">
        <div>
            <h1>Data Pesanan</h1>
            <p>Kelola transaksi pelanggan Tampomas Berkah.</p>
        </div>

        <a href="index.php" class="pesanan-btn-kembali">
            Kembali ke Dashboard
        </a>
    </div>

    <div class="pesanan-ringkasan">
        <div class="pesanan-ringkasan-card">
            <span>Total Pesanan</span>
            <h2><?= $total_pesanan; ?></h2>
        </div>
    </div>

    <?php if (isset($_GET["pesan"])): ?>

        <?php if ($_GET["pesan"] === "berhasil"): ?>
            <div class="pesanan-notifikasi">
                Status pesanan berhasil diperbarui!
            </div>

        <?php elseif ($_GET["pesan"] === "gagal"): ?>
            <div class="pesanan-notifikasi">
                Gagal memperbarui status pesanan.
            </div>

        <?php elseif ($_GET["pesan"] === "sudah_dibatalkan"): ?>
            <div class="pesanan-notifikasi">
                Pesanan sudah dibatalkan dan tidak dapat diaktifkan kembali.
            </div>

        <?php elseif ($_GET["pesan"] === "tidak_valid"): ?>
            <div class="pesanan-notifikasi">
                Permintaan status tidak valid.
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <div class="pesanan-tabel-card">

        <div class="pesanan-tabel-header">
            <h2>Daftar Transaksi</h2>
        </div>

        <div class="pesanan-tabel-scroll">

            <table class="pesanan-tabel">

                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Pelanggan</th>
                        <th>Produk</th>
                        <th>Tanggal Pesanan</th>
                        <th>Tanggal Pengambilan</th>
                        <th>Total Berat</th>
                        <th>Total Harga</th>
                        <th>Pembayaran</th>
                        <th>Status Pesanan</th>
                        <th>Status Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($total_pesanan > 0): ?>

                    <?php while ($pesanan = mysqli_fetch_assoc($query)): ?>

                    <tr>
                        <td>
                            #<?= (int) $pesanan["id_pesanan"]; ?>
                        </td>

                        <td>
                            <strong>
                                <?= e($pesanan["nama_pelanggan"]); ?>
                            </strong>
                        </td>

                        <td>
                            <?= e(
                                $pesanan["detail_produk"]
                                ?: "Detail tidak tersedia"
                            ); ?>
                        </td>

                        <td>
                            <?= e($pesanan["tanggal_pesanan"]); ?>
                        </td>

                        <td>
                            <?= e(
                                formatTanggal($pesanan["tanggal_pengambilan"])
                            ); ?>
                        </td>

                        <td>
                            <?= number_format(
                                (float) $pesanan["total_berat"],
                                2,
                                ',',
                                '.'
                            ); ?> kg
                        </td>

                        <td>
                            <strong>
                                Rp <?= number_format(
                                    (float) $pesanan["total_harga"],
                                    0,
                                    ',',
                                    '.'
                                ); ?>
                            </strong>
                        </td>

                        <td>
                            <?= e(
                                strtoupper($pesanan["metode_pembayaran"])
                            ); ?>
                        </td>

                        <td>
                            <span class="pesanan-status">
                                <?= e($pesanan["status"]); ?>
                            </span>
                        </td>

                        <td>
                            <span class="pesanan-status">
                                <?= e($pesanan["status_pembayaran"]); ?>
                            </span>
                        </td>

                        <td>
                            <form
                                action="../aksi/proses_status_pesanan.php"
                                method="POST"
                                class="form-status-pesanan"
                            >

                                <input
                                    type="hidden"
                                    name="id_pesanan"
                                    value="<?= (int) $pesanan["id_pesanan"]; ?>"
                                >

                                <select name="status" required>
                                    <option
                                        value="menunggu"
                                        <?= $pesanan["status"] === "menunggu"
                                            ? "selected" : ""; ?>
                                    >
                                        Menunggu
                                    </option>

                                    <option
                                        value="diproses"
                                        <?= $pesanan["status"] === "diproses"
                                            ? "selected" : ""; ?>
                                    >
                                        Diproses
                                    </option>

                                    <option
                                        value="siap_diambil"
                                        <?= $pesanan["status"] === "siap_diambil"
                                            ? "selected" : ""; ?>
                                    >
                                        Siap Diambil
                                    </option>

                                    <option
                                        value="selesai"
                                        <?= $pesanan["status"] === "selesai"
                                            ? "selected" : ""; ?>
                                    >
                                        Selesai
                                    </option>

                                    <option
                                        value="dibatalkan"
                                        <?= $pesanan["status"] === "dibatalkan"
                                            ? "selected" : ""; ?>
                                    >
                                        Dibatalkan
                                    </option>
                                </select>

                                <button
                                    type="submit"
                                    class="btn-update-status"
                                >
                                    Update
                                </button>

                            </form>
                        </td>
                    </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="11" class="pesanan-kosong">
                            Belum ada transaksi.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>
    </div>

</div>

</body>
</html>
