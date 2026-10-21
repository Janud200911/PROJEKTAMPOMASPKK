<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

/* TOTAL PRODUK */

$query_produk = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM tproduk"
);

$data_produk = mysqli_fetch_assoc($query_produk);

$total_produk = $data_produk["total"];


/* TOTAL PESANAN */

$query_pesanan = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM tpesanan"
);

$data_pesanan = mysqli_fetch_assoc($query_pesanan);

$total_pesanan = $data_pesanan["total"];


/* TOTAL PELANGGAN */

$query_pelanggan = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM tpela"
);

$data_pelanggan = mysqli_fetch_assoc($query_pelanggan);

$total_pelanggan = $data_pelanggan["total"];

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin - Tampomas Berkah</title>

    <link rel="stylesheet" href="../assets/style.css">

</head>

<body class="admin-body">


    <!-- SIDEBAR -->

    <aside class="admin-sidebar">

        <div class="sidebar-logo">
            🥬 Tampomas Berkah
        </div>

        <div class="sidebar-title">
            ADMIN PANEL
        </div>


        <nav class="sidebar-menu">

            <a href="index.php" class="active">
                Dashboard
            </a>

            <a href="produk.php">
                Produk
            </a>

            <a href="pesanan.php">
                Pesanan
            </a>

            <a href="role.php">
                Data Role
            </a>

            <a href="pembayaran.php">
                Pembayaran
            </a>

        </nav>


        <div class="sidebar-footer">

            <div class="sidebar-user">

                <strong>
                    <?= htmlspecialchars($_SESSION["nama"]); ?>
                </strong>

                <small>
                    Administrator
                </small>

            </div>


            <a href="../logout.php" class="sidebar-logout">
                Logout
            </a>
        </div>

    </aside>


    <!-- MAIN -->

    <main class="admin-main">


        <!-- TOPBAR -->

        <header class="admin-topbar">

            <div>

                <h1>Dashboard</h1>

                <p>
                    Selamat datang di panel admin Tampomas Berkah.
                </p>

            </div>

        </header>


        <!-- CONTENT -->

        <section class="admin-content">


            <div class="dashboard-heading">

                <h2>
                    Ringkasan
                </h2>

                <p>
                    Informasi singkat mengenai sistem Tampomas Berkah.
                </p>

            </div>


            <!-- CARDS -->

            <div class="dashboard-cards">


                <div class="dashboard-card">

                    <span>
                        Total Produk
                    </span>

                    <h2>
                        <?= $total_produk; ?>
                    </h2>

                    <p>
                        Produk tersedia
                    </p>

                    <a href="produk.php">
                        Kelola Produk →
                    </a>

                </div>


                <div class="dashboard-card">

                    <span>
                        Total Pesanan
                    </span>

                    <h2>
                        <?= $total_pesanan; ?>
                    </h2>

                    <p>
                        Pesanan pelanggan
                    </p>

                    <a href="pesanan.php">
                        Lihat Pesanan →
                    </a>

                </div>


                <div class="dashboard-card">

                    <span>
                        Pelanggan
                    </span>

                    <h2>
                        <?= $total_pelanggan; ?>
                    </h2>

                    <p>
                        Pelanggan terdaftar
                    </p>

                    <a href="pelanggan.php">
                        Lihat Pelanggan →
                    </a>

                </div>


            </div>


            <!-- MENU CEPAT -->

            <div class="quick-menu">

                <h2>
                    Menu Cepat
                </h2>


                <div class="quick-grid">


                    <a href="produk.php">

                        <strong>
                            Produk
                        </strong>

                        <span>
                            Lihat dan kelola produk
                        </span>

                    </a>


                    <a href="tambah_produk.php">

                        <strong>
                            + Tambah Produk
                        </strong>

                        <span>
                            Tambahkan produk baru
                        </span>

                    </a>


                    <a href="pesanan.php">

                        <strong>
                            Pesanan
                        </strong>

                        <span>
                            Kelola pesanan pelanggan
                        </span>

                    </a>


                    <a href="pelanggan.php">

                        <strong>
                            Pelanggan
                        </strong>

                        <span>
                            Lihat data pelanggan
                        </span>

                    </a>


                </div>

            </div>


        </section>


    </main>

</body>

</html>