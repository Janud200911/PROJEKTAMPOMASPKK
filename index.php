<?php
session_start();

$isLogin = isset($_SESSION["login"]) && $_SESSION["login"] === true;
$namaUser = $_SESSION["nama"] ?? "";

// Koneksi database
require_once __DIR__ . "/config/koneksi.php";

// Ambil ID produk Wortel
$wortel = mysqli_query(
    $koneksi,
    "SELECT id_produk
     FROM tproduk
     WHERE nama_produk = 'Wortel'
     LIMIT 1"
);

// Ambil ID produk Kentang
$kentang = mysqli_query(
    $koneksi,
    "SELECT id_produk
     FROM tproduk
     WHERE nama_produk = 'Kentang'
     LIMIT 1"
);

$dataWortel = mysqli_fetch_assoc($wortel);
$dataKentang = mysqli_fetch_assoc($kentang);

$idWortel = $dataWortel["id_produk"] ?? 0;
$idKentang = $dataKentang["id_produk"] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tampomas Berkah | Sayuran Segar</title>

    <link rel="stylesheet" href="assets/style.css">

</head>

<body>

   
<!-- NAVBAR -->
<header class="navbar">
    <div class="container nav-container">

        <a href="index.php" class="logo">
            <span>🥬</span>
            Tampomas Berkah
        </a>

        <?php
        $halamanAktif = basename($_SERVER["PHP_SELF"]);
        ?>

        <nav class="nav-menu">

            <a href="index.php"
               class="<?= $halamanAktif === 'index.php' ? 'active' : '' ?>">
                Beranda
            </a>

            <a href="produk.php"
               class="<?= $halamanAktif === 'produk.php' ? 'active' : '' ?>">
                Produk
            </a>

            <a href="pesanan_saya.php"
               class="<?= $halamanAktif === 'pesanan_saya.php' ? 'active' : '' ?>">
                Pesanan Saya
            </a>

            <a href="tentang.php"
               class="<?= $halamanAktif === 'tentang.php' ? 'active' : '' ?>">
                Tentang
            </a>

            <a href="keranjang.php" class="cart-btn">
                🛒 <span>Keranjang</span>
            </a>

            <?php if ($isLogin): ?>

                <div class="user-menu">

                    <div class="user-info">
                        <div class="user-icon">
                            <?= strtoupper(substr($namaUser, 0, 1)); ?>
                        </div>

                        <div class="user-text">
                            <span>Halo,</span>
                            <strong>
                                <?= htmlspecialchars($namaUser, ENT_QUOTES, 'UTF-8'); ?>
                            </strong>
                        </div>
                    </div>

                    <a href="logout.php" class="logout-btn">
                        Logout
                    </a>

                </div>

            <?php else: ?>

                <a href="login.php" class="login-btn">
                    Login
                </a>

            <?php endif; ?>

        </nav>
    </div>
</header>



    <!-- HERO -->
    <main>

        <section class="hero">

            <div class="container hero-container">

                <div class="hero-content">

                    <span class="hero-label">
                        SAYURAN SEGAR SETIAP HARI
                    </span>


                    <h1>

                        Sayuran Segar

                        <span>
                            Untuk Kebutuhan Usaha
                        </span>

                    </h1>


                    <p>

                        Sediakan kebutuhan sayuran untuk warung dan usaha
                        Anda dengan produk segar, harga bersahabat,
                        dan pemesanan yang mudah.

                    </p>


                    <div class="hero-buttons">

                        <a
                            href="produk.php"
                            class="btn btn-primary"
                        >
                            Belanja Sekarang →
                        </a>


                        <a
                            href="tentang.php"
                            class="btn btn-secondary"
                        >
                            Tentang Kami
                        </a>

                    </div>


                    <div class="hero-info">

                        <div>

                            <strong>
                                24+
                            </strong>

                            <span>
                                Tahun Berpengalaman
                            </span>

                        </div>


                        <div>

                            <strong>
                                Segar
                            </strong>

                            <span>
                                Setiap Hari
                            </span>

                        </div>


                        <div>

                            <strong>
                                ≥ 5 KG
                            </strong>

                            <span>
                                Minimal Pembelian
                            </span>

                        </div>

                    </div>

                </div>


                <div class="hero-visual">

                    <div class="vegetable-card">

                        <div class="vegetable-circle">
                            🥬
                        </div>


                        <h3>
                            Sayuran Segar
                        </h3>


                        <p>
                            Langsung dari pemasok terpercaya
                        </p>


                        <div class="fresh-badge">
                            ✓ Fresh setiap hari
                        </div>

                    </div>


                    <div class="floating-card floating-one">

                        

                        <span>
                            
                        </span>

                    </div>


                    <div class="floating-card floating-two">

                      

                        <span>
                          
                        </span>

                    </div>

                </div>

            </div>

        </section>


        <!-- KEUNGGULAN -->
        <section class="features">

            <div class="container">

                <div class="section-heading">

                    <span>
                        KENAPA TAMPO​MAS BERKAH?
                    </span>


                    <h2>
                        Kebutuhan Sayuran Jadi Lebih Mudah
                    </h2>

                </div>


                <div class="feature-grid">

                    <div class="feature-card">

                        <div class="feature-icon">
                            🥬
                        </div>


                        <h3>
                            Sayuran Segar
                        </h3>


                        <p>
                            Produk diperoleh dari pemasok dan
                            diperiksa sebelum dijual.
                        </p>

                    </div>


                    <div class="feature-card">

                        <div class="feature-icon">
                            💰
                        </div>


                        <h3>
                            Harga Bersahabat
                        </h3>


                        <p>
                            Cocok untuk kebutuhan stok warung
                            dan usaha.
                        </p>

                    </div>


                    <div class="feature-card">

                        <div class="feature-icon">
                            🛒
                        </div>


                        <h3>
                            Pesan Lebih Mudah
                        </h3>


                        <p>
                            Pesan terlebih dahulu lewat website
                            lalu ambil di lapak.
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- PRODUK UNGGULAN -->
        <section class="products-preview">

            <div class="container">

                <div class="section-heading heading-flex">

                    <div>

                        <span>
                            PRODUK KAMI
                        </span>


                        <h2>
                            Sayuran Pilihan
                        </h2>

                    </div>


                    <a
                        href="produk.php"
                        class="see-all"
                    >
                        Lihat Semua →
                    </a>

                </div>


                <div class="product-grid">


                    <!-- WORTEL -->
                    <div class="product-card">

                        <div class="product-image carrot">
                            🥕
                        </div>


                        <div class="product-info">

                            <span class="product-category">
                                SAYURAN
                            </span>


                            <h3>
                                Wortel
                            </h3>


                            <p>
                                Wortel segar dengan kualitas
                                pilihan.
                            </p>


                            <div class="product-bottom">

                                <div>

                                    <strong>
                                        Rp3.000
                                    </strong>

                                    <span>
                                        / KG
                                    </span>

                                </div>


                                <!-- TOMBOL BELI WORTEL -->
                                <a
                                    href="beli.php?id=<?= $idWortel; ?>"
                                    class="small-btn"
                                >
                                    Beli
                                </a>

                            </div>

                        </div>

                    </div>


                    <!-- KENTANG -->
                    <div class="product-card">

                        <div class="product-image potato">
                            🥔
                        </div>


                        <div class="product-info">

                            <span class="product-category">
                                SAYURAN
                            </span>


                            <h3>
                                Kentang
                            </h3>


                            <p>
                                Kentang segar untuk kebutuhan
                                warung dan usaha.
                            </p>


                            <div class="product-bottom">

                                <div>

                                    <strong>
                                        Rp3.000
                                    </strong>

                                    <span>
                                        / KG
                                    </span>

                                </div>


                                <!-- TOMBOL BELI KENTANG -->
                                <a
                                    href="beli.php?id=<?= $idKentang; ?>"
                                    class="small-btn"
                                >
                                    Beli
                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </main>


    <!-- FOOTER -->
    <footer class="footer">

        <div class="container footer-container">

            <div>

                <a
                    href="index.php"
                    class="logo"
                >
                    🥬 Tampomas Berkah
                </a>


                <p>
                    Menyediakan kebutuhan sayuran segar
                    untuk usaha Anda.
                </p>

            </div>


            <div class="footer-links">

                <a href="index.php">
                    Beranda
                </a>

                <a href="produk.php">
                    Produk
                </a>

                <a href="tentang.php">
                    Tentang
                </a>

            </div>

        </div>


        <div class="footer-bottom">

            © 2026 Tampomas Berkah. Semua hak dilindungi.

        </div>

    </footer>


    <script src="assets/script.js"></script>

</body>

</html>