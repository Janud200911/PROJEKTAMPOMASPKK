<?php

session_start();

require_once __DIR__ . "/../../config/koneksi.php";

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../../login.php");
    exit;
}


$id_produk = $_POST["id_produk"] ?? 0;

$nama_produk = trim($_POST["nama_produk"] ?? "");

$deskripsi = trim($_POST["deskripsi"] ?? "");

$harga_per_kg = $_POST["harga_per_kg"] ?? 0;

$stok_kg = $_POST["stok_kg"] ?? 0;

$minimal_pembelian = $_POST["minimal_pembelian"] ?? 0;

$status = $_POST["status"] ?? "aktif";


$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE tproduk SET
        nama_produk = ?,
        deskripsi = ?,
        harga_per_kg = ?,
        stok_kg = ?,
        minimal_pembelian = ?,
        status = ?,
        updated_at = CURRENT_TIMESTAMP
    WHERE id_produk = ?"
);


mysqli_stmt_bind_param(
    $stmt,
    "ssdddsi",
    $nama_produk,
    $deskripsi,
    $harga_per_kg,
    $stok_kg,
    $minimal_pembelian,
    $status,
    $id_produk
);


if (mysqli_stmt_execute($stmt)) {

    echo "<script>
        alert('Produk berhasil diperbarui!');
        window.location='../produk.php';
    </script>";

} else {

    die("Gagal memperbarui produk: " . mysqli_error($koneksi));

}

?>