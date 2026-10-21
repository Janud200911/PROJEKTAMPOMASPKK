<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET["id"] ?? 0);

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM trole WHERE id_role = $id"
);

$role = mysqli_fetch_assoc($query);

if (!$role) {
    die("Role tidak ditemukan.");
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Edit Role - Tampomas Berkah</title>

    <link rel="stylesheet" href="../assets/style.css">

</head>

<body>

<div class="admin-form-container">

    <h1>Edit Role</h1>

    <p>Ubah data role pengguna.</p>

    <form action="../aksi/proses_role_edit.php" method="POST">

        <input
            type="hidden"
            name="id_role"
            value="<?= $role["id_role"]; ?>"
        >

        <div class="form-admin">

            <label>Nama Role</label>

            <input
                type="text"
                name="nama_role"
                value="<?= htmlspecialchars($role["nama_role"]); ?>"
                required
            >

        </div>

        <div class="form-admin">

            <label>Keterangan</label>

            <textarea
                name="keterangan"
                rows="4"
            ><?= htmlspecialchars($role["keterangan"]); ?></textarea>

        </div>

        <button type="submit" class="btn-admin">
            Simpan Perubahan
        </button>

        <a href="role.php" class="btn-back">
            Batal
        </a>

    </form>

</div>

</body>

</html>