
<?php
session_start();

require_once __DIR__ . "/config/koneksi.php";

// Cek login
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: login.php");
    exit;
}

// Pastikan session keranjang tersedia
if (!isset($_SESSION["keranjang"])) {
    $_SESSION["keranjang"] = [];
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

$items = [];
$total_berat = 0;
$total_harga = 0;

// Ambil detail setiap produk dalam keranjang
foreach ($_SESSION["keranjang"] as $id_produk => $jumlah) {

    $id_produk = (int) $id_produk;

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
        continue;
    }

    $jumlah = (float) $jumlah;
    $harga = (float) $produk["harga_per_kg"];
    $subtotal = $jumlah * $harga;

    $produk["jumlah_kg"] = $jumlah;
    $produk["subtotal"] = $subtotal;

    $items[] = $produk;

    $total_berat += $jumlah;
    $total_harga += $subtotal;
}

$pesan = [
    "ditambahkan" => "Produk berhasil ditambahkan ke keranjang.",
    "diupdate" => "Jumlah produk berhasil diperbarui.",
    "dihapus" => "Produk berhasil dihapus dari keranjang.",
    "dikosongkan" => "Keranjang berhasil dikosongkan.",
    "jumlah_tidak_valid" => "Jumlah pembelian tidak valid. Periksa stok dan minimal pembelian."
];

$status = $_GET["status"] ?? "";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Keranjang - Tampomas Berkah</title>

    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

    <!-- NAVBAR -->
    <header class="navbar">
        <div class="nav-container">

            <div class="logo">
                🥬 Tampomas Berkah
            </div>

            <nav class="nav-menu">

                <a href="index.php">Beranda</a>

                <a href="produk.php">Produk</a>

                <a href="keranjang.php" class="active">
                    Keranjang
                    (<?= count($_SESSION["keranjang"]); ?>)
                </a>

                <a href="pesanan_saya.php">Pesanan Saya</a>

                <a href="index.php#tentang">Tentang</a>

                <div class="user-menu">

                    <div class="user-info">

                        <div class="user-icon">
                            <?= strtoupper(substr($_SESSION["nama"], 0, 1)); ?>
                        </div>

                        <div class="user-text">
                            <span>Selamat datang</span>

                            <strong>
                                <?= e($_SESSION["nama"]); ?>
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


    <!-- HALAMAN KERANJANG -->
    <main class="keranjang-page">

        <div class="keranjang-header">

            <span class="keranjang-label">
                BELANJAAN KAMU
            </span>

            <h1>Keranjang Belanja</h1>

            <p>
                Periksa kembali sayuran yang ingin kamu pesan.
            </p>

        </div>


        <?php if (isset($pesan[$status])) : ?>

            <div class="keranjang-alert">
                <?= e($pesan[$status]); ?>
            </div>

        <?php endif; ?>


        <?php if (empty($items)) : ?>

            <div class="keranjang-kosong">

                <div class="keranjang-icon">🛒</div>

                <h2>Keranjang Masih Kosong</h2>

                <p>
                    Kamu belum menambahkan produk ke keranjang.
                </p>

                <a href="produk.php" class="btn-lanjut">
                    Lihat Produk
                </a>

            </div>

        <?php else : ?>

            <div class="keranjang-layout">

                <!-- DAFTAR PRODUK -->
                <section class="keranjang-list">

                    <?php foreach ($items as $item) : ?>

                        <div class="keranjang-item">

                            <div class="item-icon">

                                <?php if (stripos($item["nama_produk"], "wortel") !== false) : ?>
                                    🥕
                                <?php elseif (stripos($item["nama_produk"], "kentang") !== false) : ?>
                                    🥔
                                <?php else : ?>
                                    🥬
                                <?php endif; ?>

                            </div>


                            <div class="item-detail">

                                <h3>
                                    <?= e($item["nama_produk"]); ?>
                                </h3>

                                <p>
                                    <?= rupiah($item["harga_per_kg"]); ?> / kg
                                </p>

                                <small>
                                    Stok tersedia:
                                    <?= number_format($item["stok_kg"], 2, ',', '.'); ?> kg
                                </small>

                                <strong class="item-subtotal">
                                    <?= rupiah($item["subtotal"]); ?>
                                </strong>

                            </div>


                            <div class="item-action">

                                <form
                                    action="aksi/keranjang.php"
                                    method="POST"
                                    class="form-update"
                                >

                                    <input type="hidden" name="aksi" value="update">

                                    <input
                                        type="hidden"
                                        name="id_produk"
                                        value="<?= (int) $item["id_produk"]; ?>"
                                    >

                                    <label>Jumlah (kg)</label>

                                    <input
                                        type="number"
                                        name="jumlah_kg"
                                        min="<?= e($item["minimal_pembelian"]); ?>"
                                        max="<?= e($item["stok_kg"]); ?>"
                                        step="0.01"
                                        value="<?= e($item["jumlah_kg"]); ?>"
                                        required
                                    >

                                    <button type="submit" class="btn-update">
                                        Perbarui
                                    </button>

                                </form>


                                <form
                                    action="aksi/keranjang.php"
                                    method="POST"
                                    onsubmit="return confirm('Hapus produk ini dari keranjang?')"
                                >

                                    <input type="hidden" name="aksi" value="hapus">

                                    <input
                                        type="hidden"
                                        name="id_produk"
                                        value="<?= (int) $item["id_produk"]; ?>"
                                    >

                                    <button type="submit" class="btn-hapus">
                                        Hapus
                                    </button>

                                </form>

                            </div>

                        </div>

                    <?php endforeach; ?>


                    <div class="keranjang-footer">

                        <a href="produk.php" class="btn-kembali">
                            ← Tambah Produk Lain
                        </a>

                        <form
                            action="aksi/keranjang.php"
                            method="POST"
                            onsubmit="return confirm('Kosongkan seluruh keranjang?')"
                        >

                            <input type="hidden" name="aksi" value="kosongkan">

                            <button type="submit" class="btn-kosongkan">
                                Kosongkan Keranjang
                            </button>

                        </form>

                    </div>

                </section>


                <!-- RINGKASAN BELANJA -->
                <aside class="ringkasan-keranjang">

                    <h2>Ringkasan Belanja</h2>

                    <div class="ringkasan-row">
                        <span>Jumlah produk</span>
                        <strong><?= count($items); ?> produk</strong>
                    </div>

                    <div class="ringkasan-row">
                        <span>Total berat</span>
                        <strong>
                            <?= number_format($total_berat, 2, ',', '.'); ?> kg
                        </strong>
                    </div>

                    <div class="ringkasan-total">
                        <span>Total Belanja</span>

                        <strong>
                            <?= rupiah($total_harga); ?>
                        </strong>
                    </div>

                    <a href="checkout_keranjang.php" class="btn-checkout">
                        Lanjut Checkout
                    </a>

                    <p>
                        Pembayaran dan tanggal pengambilan
                        akan diisi pada halaman checkout.
                    </p>

                </aside>

            </div>

        <?php endif; ?>

    </main>

</body>
</html>
