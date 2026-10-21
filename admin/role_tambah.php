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

    <title>Tambah Role - Tampomas Berkah</title>

    <link rel="stylesheet" href="../assets/style.css">

</head>

<body>

<div class="admin-form-container">

    <h1>Tambah Role</h1>

    <p>Tambahkan role baru ke sistem.</p>

    <form action="../aksi/proses_role_tambah.php" method="POST">

        <div class="form-admin">

            <label>Nama Role</label>

            <input
                type="text"
                name="nama_role"
                placeholder="Contoh: admin"
                required
            >

        </div>

        <div class="form-admin">

            <label>Keterangan</label>

            <textarea
                name="keterangan"
                rows="4"
                placeholder="Keterangan role"
            ></textarea>

        </div>

        <button type="submit" class="btn-admin">
            Simpan
        </button>

        <a href="role.php" class="btn-back">
            Batal
        </a>

    </form>

</div>

</body>

</html>