<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM tproduk ORDER BY id_produk DESC"
);

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Produk - Admin Tampomas Berkah</title>

    <link rel="stylesheet" href="../assets/style.css">

</head>

<body class="admin-body">

<header class="admin-topbar">

    <div>
        <h1>Produk</h1>
        <p>Kelola produk Tampomas Berkah.</p>
    </div>

</header>


<main class="admin-content">

    <div style="margin-bottom: 20px;">

        <a href="tambah_produk.php"
           style="
           display:inline-block;
           background:#168044;
           color:white;
           padding:11px 18px;
           border-radius:6px;
           text-decoration:none;
           font-size:14px;
           font-weight:bold;
           ">
            + Tambah Produk
        </a>

    </div>


    <div style="
        background:white;
        border:1px solid #ddd;
        border-radius:8px;
        overflow:hidden;
    ">

        <table style="
            width:100%;
            border-collapse:collapse;
        ">

            <thead>

                <tr style="background:#f5f7f5;">

                    <th style="padding:14px; text-align:left;">No</th>

                    <th style="padding:14px; text-align:left;">
                        Nama Produk
                    </th>

                    <th style="padding:14px; text-align:left;">
                        Deskripsi
                    </th>

                    <th style="padding:14px; text-align:left;">
                        Harga / Kg
                    </th>

                    <th style="padding:14px; text-align:left;">
                        Stok
                    </th>

                    <th style="padding:14px; text-align:left;">
                        Minimal
                    </th>

                    <th style="padding:14px; text-align:left;">
                        Status
                    </th>

                    <th style="padding:14px; text-align:left;">
                        Aksi
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php

            $no = 1;

            while ($produk = mysqli_fetch_assoc($query)):

            ?>

                <tr style="border-top:1px solid #eee;">

                    <td style="padding:14px;">
                        <?= $no++; ?>
                    </td>


                    <td style="padding:14px; font-weight:bold;">
                        <?= htmlspecialchars($produk["nama_produk"]); ?>
                    </td>


                    <td style="padding:14px; color:#666;">
                        <?= htmlspecialchars($produk["deskripsi"]); ?>
                    </td>


                    <td style="padding:14px;">
                        Rp <?= number_format($produk["harga_per_kg"], 0, ",", "."); ?>
                    </td>


                    <td style="padding:14px;">
                        <?= number_format($produk["stok_kg"], 2, ",", "."); ?> kg
                    </td>


                    <td style="padding:14px;">
                        <?= number_format($produk["minimal_pembelian"], 2, ",", "."); ?> kg
                    </td>


                    <td style="padding:14px;">
                        <?= htmlspecialchars($produk["status"]); ?>
                    </td>


                    <td style="padding:14px;">

                        <a href="edit_produk.php?id=<?= $produk["id_produk"]; ?>"
                           style="
                           display:inline-block;
                           padding:8px 12px;
                           background:#eee;
                           color:#222;
                           border-radius:5px;
                           text-decoration:none;
                           font-size:13px;
                           ">
                            Edit
                        </a>


                        <a href="aksi/hapus_produk.php?id=<?= $produk["id_produk"]; ?>"
                           onclick="return confirm('Yakin ingin menghapus produk ini?');"
                           style="
                           display:inline-block;
                           padding:8px 12px;
                           background:#ffe5e5;
                           color:#dc3545;
                           border-radius:5px;
                           text-decoration:none;
                           font-size:13px;
                           margin-left:5px;
                           ">
                            Hapus
                        </a>

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

    </div>


    <div style="margin-top:20px;">

        <a href="index.php"
           style="
           color:#168044;
           text-decoration:none;
           ">
            ← Kembali ke Dashboard
        </a>

    </div>

</main>

</body>

</html>