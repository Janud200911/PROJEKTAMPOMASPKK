```php
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

// Fungsi keamanan output
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

    return $timestamp === false
        ? "Belum ditentukan"
        : date("d-m-Y", $timestamp);
}

// Ambil data pesanan
$query = "
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
        p.bukti_pembayaran,
        GROUP_CONCAT(
            CONCAT(pr.nama_produk, ' (', d.jumlah_kg, ' kg)')
            SEPARATOR ', '
        ) AS daftar_produk
    FROM tpesanan p
    JOIN tuser u ON p.id_user = u.id_user
    LEFT JOIN tdetail_pesanan d ON p.id_pesanan = d.id_pesanan
    LEFT JOIN tproduk pr ON d.id_produk = pr.id_produk
    GROUP BY p.id_pesanan
    ORDER BY p.id_pesanan DESC
";

$result = mysqli_query($koneksi, $query);

if (!$result) {
    die(
        "Gagal mengambil data pesanan: " .
        e(mysqli_error($koneksi))
    );
}

$total_pesanan = mysqli_num_rows($result);
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

        <?php elseif ($_GET["pesan"] === "belum_lunas"): ?>
            <div class="pesanan-notifikasi">
                Pembayaran belum lunas. Pesanan belum bisa diselesaikan.
            </div>

        <?php elseif ($_GET["pesan"] === "sudah_dibatalkan"): ?>
            <div class="pesanan-notifikasi">
                Pesanan sudah dibatalkan dan tidak dapat diaktifkan kembali.
            </div>

        <?php elseif ($_GET["pesan"] === "tidak_valid"): ?>
            <div class="pesanan-notifikasi">
                Permintaan tidak valid.
            </div>

        <?php elseif ($_GET["pesan"] === "gagal"): ?>
            <div class="pesanan-notifikasi">
                Gagal memperbarui data pesanan.
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <?php if (isset($_GET["verifikasi"])): ?>

        <?php if ($_GET["verifikasi"] === "berhasil"): ?>
            <div class="pesanan-notifikasi">
                Verifikasi pembayaran berhasil diproses!
            </div>

        <?php elseif ($_GET["verifikasi"] === "gagal"): ?>
            <div class="pesanan-notifikasi">
                Gagal memproses verifikasi pembayaran.
            </div>

        <?php elseif ($_GET["verifikasi"] === "tidak_valid"): ?>
            <div class="pesanan-notifikasi">
                Data verifikasi tidak valid.
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
                        <th>Bukti Pembayaran</th>
                        <th>Status Pesanan</th>
                        <th>Status Bayar</th>
                        <th>Verifikasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($total_pesanan > 0): ?>

                    <?php while ($pesanan = mysqli_fetch_assoc($result)): ?>

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
                                $pesanan["daftar_produk"]
                                ?: "Detail tidak tersedia"
                            ); ?>
                        </td>

                        <td>
                            <?= e(formatTanggal($pesanan["tanggal_pesanan"])); ?>
                        </td>

                        <td>
                            <?= e(formatTanggal($pesanan["tanggal_pengambilan"])); ?>
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
                            <?= e(strtoupper($pesanan["metode_pembayaran"])); ?>
                        </td>

                        <td>
                            <?php if (!empty($pesanan["bukti_pembayaran"])): ?>

                                <a
                                    href="../<?= e($pesanan["bukti_pembayaran"]); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    Lihat Bukti
                                </a>

                            <?php else: ?>

                                Belum ada

                            <?php endif; ?>
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

                            <?php if (
                                in_array($pesanan["metode_pembayaran"], ["transfer", "qris"], true) &&
                                $pesanan["status_pembayaran"] === "menunggu_verifikasi" &&
                                !empty($pesanan["bukti_pembayaran"]) &&
                                $pesanan["status"] !== "dibatalkan"
                            ): ?>

                                <form
                                    action="../aksi/proses_verifikasi_pembayaran.php"
                                    method="POST"
                                >

                                    <input
                                        type="hidden"
                                        name="id_pesanan"
                                        value="<?= (int) $pesanan["id_pesanan"]; ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="keputusan"
                                        value="terima"
                                        class="btn-update-status"
                                        onclick="return confirm('Terima pembayaran ini?')"
                                    >
                                        Terima
                                    </button>

                                    <button
                                        type="submit"
                                        name="keputusan"
                                        value="tolak"
                                        class="btn-update-status"
                                        onclick="return confirm('Tolak pembayaran ini?')"
                                    >
                                        Tolak
                                    </button>

                                </form>

                            <?php elseif ($pesanan["status_pembayaran"] === "lunas"): ?>

                                <span>Lunas</span>

                            <?php elseif ($pesanan["status_pembayaran"] === "ditolak"): ?>

                                <span>Ditolak</span>

                            <?php else: ?>

                                <span>-</span>

                            <?php endif; ?>

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

                                    <?php
                                    $daftar_status = [
                                        "menunggu" => "Menunggu",
                                        "diproses" => "Diproses",
                                        "siap_diambil" => "Siap Diambil",
                                        "selesai" => "Selesai",
                                        "dibatalkan" => "Dibatalkan"
                                    ];
                                    ?>

                                    <?php foreach ($daftar_status as $nilai => $label): ?>

                                        <option
                                            value="<?= e($nilai); ?>"
                                            <?= $pesanan["status"] === $nilai ? "selected" : ""; ?>
                                        >
                                            <?= e($label); ?>
                                        </option>

                                    <?php endforeach; ?>

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
                        <td colspan="13" class="pesanan-kosong">
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
```