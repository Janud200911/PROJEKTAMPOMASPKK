<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}


$id = $_GET["id"] ?? 0;


$query = mysqli_query(
    $koneksi,
    "SELECT * FROM tproduk WHERE id_produk = '$id'"
);


if (mysqli_num_rows($query) == 0) {

    die("Produk tidak ditemukan.");

}


$produk = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Produk - Tampomas Berkah</title>

    <link rel="stylesheet" href="../assets/style.css">

</head>

<body class="admin-body">


<header class="admin-topbar">

    <div>

        <h1>Edit Produk</h1>

        <p>
            Ubah data produk Tampomas Berkah.
        </p>

    </div>

</header>


<main class="admin-content">


    <div style="
        max-width:700px;
        background:white;
        border:1px solid #ddd;
        border-radius:8px;
        padding:25px;
    ">


        <form action="aksi/update_produk.php" method="POST">


            <input
                type="hidden"
                name="id_produk"
                value="<?= $produk["id_produk"]; ?>"
            >


            <div class="form-group">

                <label>Nama Produk</label>

                <input
                    type="text"
                    name="nama_produk"
                    value="<?= htmlspecialchars($produk["nama_produk"]); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>Deskripsi</label>

                <textarea
                    name="deskripsi"
                    rows="4"
                    required
                ><?= htmlspecialchars($produk["deskripsi"]); ?></textarea>

            </div>


            <div class="form-group">

                <label>Harga per Kg</label>

                <input
                    type="number"
                    name="harga_per_kg"
                    value="<?= $produk["harga_per_kg"]; ?>"
                    min="0"
                    required
                >

            </div>


            <div class="form-group">

                <label>Stok Kg</label>

                <input
                    type="number"
                    name="stok_kg"
                    step="0.01"
                    value="<?= $produk["stok_kg"]; ?>"
                    min="0"
                    required
                >

            </div>


            <div class="form-group">

                <label>Minimal Pembelian Kg</label>

                <input
                    type="number"
                    name="minimal_pembelian"
                    step="0.01"
                    value="<?= $produk["minimal_pembelian"]; ?>"
                    min="0"
                    required
                >

            </div>


            <div class="form-group">

                <label>Status</label>

                <select name="status" required>

                    <option
                        value="aktif"
                        <?= $produk["status"] == "aktif" ? "selected" : ""; ?>
                    >
                        Aktif
                    </option>

                    <option
                        value="nonaktif"
                        <?= $produk["status"] == "nonaktif" ? "selected" : ""; ?>
                    >
                        Nonaktif
                    </option>

                </select>

            </div>


            <button
                type="submit"
                class="auth-button"
            >
                Simpan Perubahan
            </button>


        </form>


        <a
            href="produk.php"
            style="
            display:block;
            margin-top:15px;
            text-align:center;
            color:#666;
            text-decoration:none;
            "
        >
            ← Kembali ke Produk
        </a>


    </div>


</main>

</body>

</html>