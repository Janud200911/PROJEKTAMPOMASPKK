<?php
session_start();

require_once __DIR__ . "/config/koneksi.php";

// Wajib login
if (
    !isset($_SESSION["login"]) ||
    $_SESSION["login"] !== true
) {
    header("Location: login.php");
    exit;
}

$idUser = (int) ($_SESSION["id_user"] ?? 0);
$namaUser = $_SESSION["nama"] ?? "Pelanggan";

// Format rupiah
function rupiah($angka)
{
    return "Rp " . number_format((float) $angka, 0, ",", ".");
}

// Format status
function formatStatus($status)
{
    return ucwords(str_replace("_", " ", $status ?? "-"));
}

// Format tanggal
function formatTanggal($tanggal)
{
    if (empty($tanggal)) {
        return "Belum ditentukan";
    }

    $timestamp = strtotime($tanggal);

    if ($timestamp === false) {
        return "Belum ditentukan";
    }

    return date("d-m-Y", $timestamp);
}

// Ambil detail pesanan
$detailPesanan = null;
$detailProduk = [];
$pesanError = "";

if (isset($_GET["id"])) {
    $idPesanan = (int) $_GET["id"];

    if ($idPesanan <= 0) {
        $pesanError = "Nomor pesanan tidak valid.";
    } else {
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT *
             FROM tpesanan
             WHERE id_pesanan = ?
               AND id_user = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $idPesanan,
            $idUser
        );

        mysqli_stmt_execute($stmt);

        $hasil = mysqli_stmt_get_result($stmt);
        $detailPesanan = mysqli_fetch_assoc($hasil);

        mysqli_stmt_close($stmt);

        if ($detailPesanan) {
            $stmtDetail = mysqli_prepare(
                $koneksi,
                "SELECT d.*, p.nama_produk
                 FROM tdetail_pesanan d
                 LEFT JOIN tproduk p
                    ON d.id_produk = p.id_produk
                 WHERE d.id_pesanan = ?"
            );

            mysqli_stmt_bind_param(
                $stmtDetail,
                "i",
                $idPesanan
            );

            mysqli_stmt_execute($stmtDetail);

            $hasilDetail = mysqli_stmt_get_result($stmtDetail);

            while ($row = mysqli_fetch_assoc($hasilDetail)) {
                $detailProduk[] = $row;
            }

            mysqli_stmt_close($stmtDetail);
        } else {
            $pesanError =
                "Pesanan tidak ditemukan atau bukan milik akun kamu.";
        }
    }
}

// Ambil riwayat pesanan milik pelanggan yang login
$stmtRiwayat = mysqli_prepare(
    $koneksi,
    "SELECT *
     FROM tpesanan
     WHERE id_user = ?
     ORDER BY tanggal_pesanan DESC, id_pesanan DESC"
);

mysqli_stmt_bind_param($stmtRiwayat, "i", $idUser);
mysqli_stmt_execute($stmtRiwayat);

$hasilRiwayat = mysqli_stmt_get_result($stmtRiwayat);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pesanan Saya | Tampomas Berkah</title>

    <link rel="stylesheet" href="assets/style.css">

    <style>
    .riwayat-halaman {
        max-width: 1100px;
        width: 92%;
        margin: 40px auto 60px;
        color: #26352a;
    }

    .riwayat-judul {
        margin-bottom: 8px;
        color: #176b35;
    }

    .riwayat-deskripsi {
        color: #68756b;
        margin-bottom: 25px;
    }

    .riwayat-kartu {
        background: #fff;
        border: 1px solid #e4ebe5;
        border-radius: 12px;
        padding: 22px;
        margin-bottom: 25px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    }

    .riwayat-scroll {
        overflow-x: auto;
    }

    .riwayat-tabel {
        width: 100%;
        border-collapse: collapse;
        min-width: 950px;
    }

    .riwayat-tabel th,
    .riwayat-tabel td {
        padding: 13px 12px;
        border-bottom: 1px solid #edf0ed;
        text-align: left;
        font-size: 14px;
    }

    .riwayat-tabel th {
        background: #f2f8f2;
        color: #285a36;
    }

    .riwayat-tabel tr:last-child td {
        border-bottom: none;
    }

    .riwayat-status {
        display: inline-block;
        padding: 6px 9px;
        border-radius: 20px;
        background: #f0f0f0;
        color: #555;
        font-size: 12px;
        white-space: nowrap;
    }

    .status-lunas,
    .status-selesai,
    .status-siap_diambil {
        background: #e1f5e5;
        color: #176b35;
    }

    .status-menunggu,
    .status-belum_bayar,
    .status-menunggu_verifikasi,
    .status-diproses {
        background: #fff3d8;
        color: #8b6100;
    }

    .status-dibatalkan {
        background: #fde7e7;
        color: #a52626;
    }

    .riwayat-btn-detail,
    .btn-bayar-qris,
    .riwayat-btn-kembali {
        display: inline-block;
        border: none;
        border-radius: 7px;
        padding: 9px 12px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-align: center;
    }

    .riwayat-btn-detail {
        background: #e8f3e9;
        color: #176b35;
    }

    .riwayat-btn-detail:hover {
        background: #d8eadb;
    }

    .btn-bayar-qris {
        margin-top: 6px;
        background: #21823b;
        color: #fff;
    }

    .btn-bayar-qris:hover {
        background: #176b2b;
    }

    .riwayat-btn-kembali {
        margin-bottom: 18px;
        background: #edf2ed;
        color: #285a36;
    }

    .riwayat-notifikasi {
        padding: 14px;
        border-radius: 8px;
        margin: 15px 0;
        background: #fff3d8;
        color: #795500;
    }

    .riwayat-kosong {
        padding: 28px 12px;
        text-align: center;
        color: #68756b;
    }

    .detail-ringkasan {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        margin: 20px 0;
    }

    .detail-item {
        padding: 15px;
        background: #f5f9f5;
        border-radius: 8px;
        overflow-wrap: anywhere;
    }

    .detail-item span {
        display: block;
        color: #68756b;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .detail-item strong {
        color: #245d34;
    }

    .riwayat-aksi {
        min-width: 135px;
    }

    @media (max-width: 600px) {
        .riwayat-kartu {
            padding: 15px;
        }

        .riwayat-halaman {
            margin-top: 25px;
        }
    }
    </style>
</head>

<body>

    <header class="navbar">
        <div class="container nav-container">

            <a href="index.php" class="logo">
                <span>🥬</span> Tampomas Berkah
            </a>

            <nav class="nav-menu">
                <a href="index.php">Beranda</a>
                <a href="produk.php">Produk</a>
                <a href="pesanan_saya.php" class="active">Pesanan Saya</a>
                <a href="tentang.php">Tentang</a>

                <a href="keranjang.php" class="cart-btn">
                    🛒 <span>Keranjang</span>
                </a>

                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-icon">
                            <?= htmlspecialchars(
                            strtoupper(substr($namaUser, 0, 1)),
                            ENT_QUOTES,
                            "UTF-8"
                        ); ?>
                        </div>

                        <div class="user-text">
                            <span>Halo,</span>

                            <strong>
                                <?= htmlspecialchars(
                                $namaUser,
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>
                            </strong>
                        </div>
                    </div>

                    <a href="logout.php" class="logout-btn">
                        Logout
                    </a>
                </div>
            </nav>

        </div>
    </header>

    <main class="riwayat-halaman">

        <h1 class="riwayat-judul">Pesanan Saya</h1>

        <p class="riwayat-deskripsi">
            Halo,
            <?= htmlspecialchars($namaUser, ENT_QUOTES, "UTF-8"); ?>!
            Berikut riwayat dan status pesanan kamu.
        </p>

        <?php if (isset($_GET["sukses"])): ?>
        <div class="riwayat-notifikasi">
            Pesanan berhasil dibuat. Silakan periksa detail pesanan kamu.
        </div>
        <?php endif; ?>

        <?php if (isset($_GET["id"])): ?>

        <a href="pesanan_saya.php" class="riwayat-btn-kembali">
            ← Kembali ke Riwayat
        </a>

        <?php if ($pesanError !== ""): ?>

        <div class="riwayat-kartu">
            <div class="riwayat-notifikasi">
                <?= htmlspecialchars(
                        $pesanError,
                        ENT_QUOTES,
                        "UTF-8"
                    ); ?>
            </div>
        </div>

        <?php elseif ($detailPesanan): ?>

        <section class="riwayat-kartu">

            <h2>
                Detail Pesanan
                #<?= (int) $detailPesanan["id_pesanan"]; ?>
            </h2>

            <div class="detail-ringkasan">

                <div class="detail-item">
                    <span>Tanggal Pesanan</span>
                    <strong>
                        <?= htmlspecialchars(
                                $detailPesanan["tanggal_pesanan"],
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>
                    </strong>
                </div>

                <div class="detail-item">
                    <span>Tanggal Pengambilan</span>
                    <strong>
                        <?= htmlspecialchars(
                                formatTanggal(
                                    $detailPesanan["tanggal_pengambilan"] ?? null
                                ),
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>
                    </strong>
                </div>

                <div class="detail-item">
                    <span>Total Berat</span>
                    <strong>
                        <?= number_format(
                                (float) $detailPesanan["total_berat"],
                                2,
                                ",",
                                "."
                            ); ?> kg
                    </strong>
                </div>

                <div class="detail-item">
                    <span>Total Harga</span>
                    <strong>
                        <?= rupiah($detailPesanan["total_harga"]); ?>
                    </strong>
                </div>

                <div class="detail-item">
                    <span>Status Pesanan</span>
                    <strong>
                        <span class="riwayat-status status-<?= htmlspecialchars(
                                $detailPesanan["status"],
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>">
                            <?= htmlspecialchars(
                                    formatStatus($detailPesanan["status"]),
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                        </span>
                    </strong>
                </div>

                <div class="detail-item">
                    <span>Metode Pembayaran</span>
                    <strong>
                        <?= htmlspecialchars(
                                strtoupper($detailPesanan["metode_pembayaran"]),
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>
                    </strong>
                </div>

                <div class="detail-item">
                    <span>Status Pembayaran</span>
                    <strong>
                        <span class="riwayat-status status-<?= htmlspecialchars(
                                $detailPesanan["status_pembayaran"],
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>">
                            <?= htmlspecialchars(
                                    formatStatus(
                                        $detailPesanan["status_pembayaran"]
                                    ),
                                    ENT_QUOTES,
                                    "UTF-8"
                                ); ?>
                        </span>
                    </strong>
                </div>

            </div>

            <h3>Produk yang Dipesan</h3>

            <div class="riwayat-scroll">
                <table class="riwayat-tabel">

                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Jumlah</th>
                            <th>Harga per Kg</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (count($detailProduk) > 0): ?>

                        <?php foreach ($detailProduk as $produk): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars(
                                                $produk["nama_produk"] ?? "Produk",
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ); ?>
                            </td>

                            <td>
                                <?= number_format(
                                                (float) $produk["jumlah_kg"],
                                                2,
                                                ",",
                                                "."
                                            ); ?> kg
                            </td>

                            <td>
                                <?= rupiah($produk["harga_satuan"]); ?>
                            </td>

                            <td>
                                <?= rupiah($produk["subtotal"]); ?>
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

            <?php if (!empty($detailPesanan["catatan"])): ?>
            <p>
                <strong>Catatan pesanan:</strong><br>

                <?= nl2br(
                            htmlspecialchars(
                                $detailPesanan["catatan"],
                                ENT_QUOTES,
                                "UTF-8"
                            )
                        ); ?>
            </p>
            <?php endif; ?>

            <?php if (
                    $detailPesanan["metode_pembayaran"] === "qris" &&
                    $detailPesanan["status_pembayaran"] !== "lunas" &&
                    $detailPesanan["status"] !== "dibatalkan"
                ): ?>

            <div class="riwayat-notifikasi">
                Pesanan ini menggunakan QRIS dan belum lunas.
                Tekan tombol di bawah untuk membuka halaman pembayaran.
            </div>

            <a href="pembayaran.php?id=<?= (int) $detailPesanan["id_pesanan"]; ?>" class="btn-bayar-qris">
                Bayar QRIS
            </a>

            <?php endif; ?>

        </section>

        <?php endif; ?>

        <?php endif; ?>

        <section class="riwayat-kartu">

            <h2>Riwayat Pesanan</h2>

            <div class="riwayat-scroll">

                <table class="riwayat-tabel">

                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Tanggal Pesanan</th>
                            <th>Tanggal Pengambilan</th>
                            <th>Total Berat</th>
                            <th>Total Harga</th>
                            <th>Status Pesanan</th>
                            <th>Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (mysqli_num_rows($hasilRiwayat) > 0): ?>

                        <?php while ($pesanan = mysqli_fetch_assoc($hasilRiwayat)): ?>

                        <tr>
                            <td>
                                #<?= (int) $pesanan["id_pesanan"]; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                        $pesanan["tanggal_pesanan"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                        formatTanggal(
                                            $pesanan["tanggal_pengambilan"] ?? null
                                        ),
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>
                            </td>

                            <td>
                                <?= number_format(
                                        (float) $pesanan["total_berat"],
                                        2,
                                        ",",
                                        "."
                                    ); ?> kg
                            </td>

                            <td>
                                <?= rupiah($pesanan["total_harga"]); ?>
                            </td>

                            <td>
                                <span class="riwayat-status status-<?= htmlspecialchars(
                                        $pesanan["status"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>">
                                    <?= htmlspecialchars(
                                            formatStatus($pesanan["status"]),
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>
                                </span>
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars(
                                            strtoupper(
                                                $pesanan["metode_pembayaran"]
                                            ),
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>
                                </strong>

                                <br>

                                <span class="riwayat-status status-<?= htmlspecialchars(
                                        $pesanan["status_pembayaran"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>">
                                    <?= htmlspecialchars(
                                            formatStatus(
                                                $pesanan["status_pembayaran"]
                                            ),
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>
                                </span>
                            </td>

                            <td class="riwayat-aksi">

                                <a href="pesanan_saya.php?id=<?= (int) $pesanan["id_pesanan"]; ?>"
                                    class="riwayat-btn-detail">
                                    Detail
                                </a>

                                <?php if (
                                        $pesanan["metode_pembayaran"] === "qris" &&
                                        $pesanan["status_pembayaran"] !== "lunas" &&
                                        $pesanan["status"] !== "dibatalkan"
                                    ): ?>

                                <br>

                                <a href="pembayaran.php?id=<?= (int) $pesanan["id_pesanan"]; ?>" class="btn-bayar-qris">
                                    Bayar QRIS
                                </a>

                                <?php endif; ?>

                            </td>
                        </tr>

                        <?php endwhile; ?>

                        <?php else: ?>

                        <tr>
                            <td colspan="8" class="riwayat-kosong">
                                Kamu belum memiliki pesanan.

                                <br><br>

                                <a href="produk.php" class="riwayat-btn-detail">
                                    Lihat Produk
                                </a>
                            </td>
                        </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>
        </section>

    </main>

    <footer class="footer">
        <div class="footer-bottom">
            © 2026 Tampomas Berkah. Semua hak dilindungi.
        </div>
    </footer>

    <script src="assets/script.js"></script>

</body>

</html>

<?php
mysqli_stmt_close($stmtRiwayat);
?>