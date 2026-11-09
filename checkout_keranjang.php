
<?php
session_start();

require_once __DIR__ . "/config/koneksi.php";

date_default_timezone_set("Asia/Jakarta");

if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION["keranjang"]) || !is_array($_SESSION["keranjang"])) {
    header("Location: keranjang.php");
    exit;
}

function e($nilai)
{
    return htmlspecialchars((string) ($nilai ?? ""), ENT_QUOTES, "UTF-8");
}

function rupiah($nilai)
{
    return "Rp " . number_format((float) $nilai, 0, ",", ".");
}

if (empty($_SESSION["csrf_checkout"])) {
    $_SESSION["csrf_checkout"] = bin2hex(random_bytes(32));
}

$items = [];
$total_berat = 0;
$total_harga = 0;
$masalah = [];

foreach ($_SESSION["keranjang"] as $id_produk => $jumlah) {

    $id_produk = (int) $id_produk;
    $jumlah = (float) $jumlah;

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id_produk, nama_produk, harga_per_kg,
                stok_kg, minimal_pembelian
         FROM tproduk
         WHERE id_produk = ?
         AND status = 'aktif'
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $id_produk);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $produk = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($stmt);

    if (!$produk) {
        $masalah[] = "Ada produk yang sudah tidak tersedia. Hapus produk tersebut dari keranjang.";
        continue;
    }

    $stok = (float) $produk["stok_kg"];
    $minimal = (float) $produk["minimal_pembelian"];
    $harga = (float) $produk["harga_per_kg"];

    if (
        $jumlah < $minimal ||
        $jumlah > $stok ||
        round($jumlah, 2) != $jumlah
    ) {
        $masalah[] = "Jumlah " . $produk["nama_produk"] .
            " tidak sesuai minimal pembelian atau stok terbaru.";
    }

    $subtotal = round($jumlah * $harga, 2);

    $produk["jumlah_kg"] = $jumlah;
    $produk["subtotal"] = $subtotal;

    $items[] = $produk;

    $total_berat += $jumlah;
    $total_harga += $subtotal;
}

$tanggal_minimal = date("Y-m-d", strtotime("+1 day"));

$error_checkout = $_SESSION["checkout_error"] ?? "";
unset($_SESSION["checkout_error"]);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Checkout Keranjang | Tampomas Berkah</title>

    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/checkout_keranjang.css">
</head>

<body>

<header class="navbar">

    <div class="nav-container">

        <div class="logo">
            🥬 Tampomas Berkah
        </div>

        <nav class="nav-menu">

            <a href="index.php">Beranda</a>
            <a href="produk.php">Produk</a>
            <a href="keranjang.php">Keranjang</a>
            <a href="pesanan_saya.php">Pesanan Saya</a>
            <a href="index.php#tentang">Tentang</a>

            <div class="user-menu">

                <div class="user-info">

                    <div class="user-icon">
                        <?= e(strtoupper(substr($_SESSION["nama"] ?? "P", 0, 1))); ?>
                    </div>

                    <div class="user-text">
                        <span>Selamat datang</span>
                        <strong><?= e($_SESSION["nama"] ?? "Pelanggan"); ?></strong>
                    </div>

                </div>

                <a href="logout.php" class="logout-btn">
                    Logout
                </a>

            </div>

        </nav>

    </div>

</header>


<main class="checkout-page">

    <div class="checkout-heading">

        <span>LANGKAH TERAKHIR</span>

        <h1>Checkout Pesanan</h1>

        <p>
            Periksa pesanan dan lengkapi informasi pengambilan.
        </p>

    </div>


    <?php if ($error_checkout !== "") : ?>

        <div class="checkout-alert error">
            <?= e($error_checkout); ?>
        </div>

    <?php endif; ?>


    <?php if (!empty($masalah)) : ?>

        <div class="checkout-alert error">

            <strong>Periksa kembali keranjang:</strong>

            <ul>
                <?php foreach (array_unique($masalah) as $pesan) : ?>
                    <li><?= e($pesan); ?></li>
                <?php endforeach; ?>
            </ul>

            <a href="keranjang.php">
                Kembali ke Keranjang
            </a>

        </div>

    <?php endif; ?>


    <?php if (empty($items)) : ?>

        <div class="checkout-card checkout-empty">

            <h2>Produk Tidak Tersedia</h2>

            <p>
                Silakan periksa kembali keranjang belanja kamu.
            </p>

            <a href="keranjang.php" class="checkout-button">
                Kembali ke Keranjang
            </a>

        </div>

    <?php else : ?>

        <div class="checkout-layout">

            <!-- FORM PEMESANAN -->
            <section class="checkout-card">

                <h2>Informasi Pemesanan</h2>

                <form
                    action="aksi/proses_checkout_keranjang.php"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e($_SESSION["csrf_checkout"]); ?>"
                    >

                    <div class="checkout-field">

                        <label>Nama Pemesan</label>

                        <input
                            type="text"
                            value="<?= e($_SESSION["nama"] ?? ""); ?>"
                            readonly
                        >

                    </div>


                    <div class="checkout-field">

                        <label for="tanggal_pengambilan">
                            Tanggal Pengambilan
                        </label>

                        <input
                            type="date"
                            name="tanggal_pengambilan"
                            id="tanggal_pengambilan"
                            min="<?= e($tanggal_minimal); ?>"
                            required
                        >

                        <small>
                            Pesanan diambil langsung di lapak Tampomas Berkah.
                            Tanggal paling cepat adalah besok.
                        </small>

                    </div>


                    <div class="checkout-field">

                        <label for="metode_pembayaran">
                            Metode Pembayaran
                        </label>

                        <select
                            name="metode_pembayaran"
                            id="metode_pembayaran"
                            required
                        >

                            <option value="">
                                -- Pilih Pembayaran --
                            </option>

                            <option value="cash">
                                Cash (Bayar di Lapak)
                            </option>

                            <option value="transfer">
                                Transfer Bank
                            </option>

                            <option value="qris">
                                QRIS
                            </option>

                        </select>

                    </div>


                    <div class="checkout-field">

                        <label for="catatan">
                            Catatan Pesanan
                        </label>

                        <textarea
                            name="catatan"
                            id="catatan"
                            rows="4"
                            placeholder="Contoh: Pesanan akan diambil pagi hari."
                        ></textarea>

                    </div>


                    <button
                        type="submit"
                        class="checkout-button"
                        <?= !empty($masalah) ? "disabled" : ""; ?>
                    >
                        Buat Pesanan
                    </button>

                </form>

            </section>


            <!-- RINGKASAN PESANAN -->
            <aside class="checkout-card checkout-summary">

                <h2>Ringkasan Pesanan</h2>

                <?php foreach ($items as $item) : ?>

                    <div class="checkout-product">

                        <div>

                            <strong>
                                <?= e($item["nama_produk"]); ?>
                            </strong>

                            <span>
                                <?= number_format($item["jumlah_kg"], 2, ",", "."); ?>
                                kg × <?= rupiah($item["harga_per_kg"]); ?>
                            </span>

                        </div>

                        <strong>
                            <?= rupiah($item["subtotal"]); ?>
                        </strong>

                    </div>

                <?php endforeach; ?>


                <div class="checkout-summary-row">

                    <span>Total Berat</span>

                    <strong>
                        <?= number_format($total_berat, 2, ",", "."); ?> kg
                    </strong>

                </div>


                <div class="checkout-total">

                    <span>Total Pembayaran</span>

                    <strong>
                        <?= rupiah($total_harga); ?>
                    </strong>

                </div>


                <a href="keranjang.php" class="checkout-back">
                    ← Kembali ke Keranjang
                </a>

            </aside>

        </div>

    <?php endif; ?>

</main>

</body>
</html>
