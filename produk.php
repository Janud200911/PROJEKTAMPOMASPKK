<?php
session_start();

require_once __DIR__ . "/config/koneksi.php";

if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: login.php");
    exit;
}

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM tproduk
     WHERE status = 'aktif'
     ORDER BY id_produk ASC"
);

if (!$query) {
    die("Gagal mengambil data produk: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Produk - Tampomas Berkah</title>

    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/produk.css">

</head>

<body>

    <!-- NAVBAR -->
    <header class="navbar">

        <div class="nav-container">

            <div class="logo">
                🥬 Tampomas Berkah
            </div>

            <nav class="nav-menu">

                <a href="index.php">
                    Beranda
                </a>

                <a href="produk.php" class="active">
                    Produk
                </a>

                <a href="index.php#tentang">
                    Tentang
                </a>

                <div class="user-menu">

                    <div class="user-info">

                        <div class="user-icon">
                            <?= strtoupper(substr($_SESSION["nama"], 0, 1)); ?>
                        </div>

                        <div class="user-text">

                            <span>
                                Selamat datang
                            </span>

                            <strong>
                                <?= htmlspecialchars($_SESSION["nama"]); ?>
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


    <!-- PRODUK -->
    <main class="produk-page">

        <div class="produk-header">

            <span class="produk-label">
                PRODUK KAMI
            </span>

            <h1>
                Sayuran Pilihan
            </h1>

            <p>
                Pilih sayuran segar yang ingin kamu pesan.
            </p>

        </div>


        <div class="produk-grid">

            <?php while ($produk = mysqli_fetch_assoc($query)) : ?>

                <div class="produk-card">

                    <!-- GAMBAR / IKON -->
                    <div class="produk-image">

                        <?php if (stripos($produk["nama_produk"], "wortel") !== false): ?>

                            🥕

                        <?php elseif (stripos($produk["nama_produk"], "kentang") !== false): ?>

                            🥔

                        <?php else: ?>

                            🥬

                        <?php endif; ?>

                    </div>


                    <!-- INFORMASI -->
                    <div class="produk-info">

                        <span class="produk-kategori">
                            SAYURAN
                        </span>


                        <h2>
                            <?= htmlspecialchars($produk["nama_produk"]); ?>
                        </h2>


                        <p>
                            <?= htmlspecialchars($produk["deskripsi"]); ?>
                        </p>


                        <div class="produk-bottom">

                            <div class="produk-harga">

                                Rp<?= number_format(
                                    $produk["harga_per_kg"],
                                    0,
                                    ',',
                                    '.'
                                ); ?>

                                <span>
                                    / KG
                                </span>

                            </div>


                            <?php if ($produk["stok_kg"] >= $produk["minimal_pembelian"]) : ?>

                                <a
                                    href="beli.php?id=<?= $produk["id_produk"]; ?>"
                                    class="btn-beli"
                                >
                                    Beli
                                </a>

                            <?php else : ?>

                                <span class="stok-habis">
                                    Stok Tidak Cukup
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    </main>

</body>

</html>