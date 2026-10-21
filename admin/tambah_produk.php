<?php

session_start();

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Produk - Tampomas Berkah</title>

    <link rel="stylesheet" href="../assets/style.css">

</head>

<body class="admin-body">


<header class="admin-topbar">

    <div>

        <h1>Tambah Produk</h1>

        <p>
            Tambahkan produk baru ke Tampomas Berkah.
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


        <form action="aksi/simpan_produk.php" method="POST">


            <div class="form-group">

                <label>Nama Produk</label>

                <input
                    type="text"
                    name="nama_produk"
                    placeholder="Contoh: Wortel"
                    required
                >

            </div>


            <div class="form-group">

                <label>Deskripsi</label>

                <textarea
                    name="deskripsi"
                    rows="4"
                    placeholder="Deskripsi produk"
                    required
                ></textarea>

            </div>


            <div class="form-group">

                <label>Harga per Kg</label>

                <input
                    type="number"
                    name="harga_per_kg"
                    placeholder="3000"
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
                    min="0"
                    placeholder="50"
                    required
                >

            </div>


            <div class="form-group">

                <label>Minimal Pembelian Kg</label>

                <input
                    type="number"
                    name="minimal_pembelian"
                    step="0.01"
                    min="0"
                    value="5"
                    required
                >

            </div>


            <div class="form-group">

                <label>Status</label>

                <select name="status" required>

                    <option value="aktif">
                        Aktif
                    </option>

                    <option value="nonaktif">
                        Nonaktif
                    </option>

                </select>

            </div>


            <button
                type="submit"
                class="auth-button"
            >
                Simpan Produk
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