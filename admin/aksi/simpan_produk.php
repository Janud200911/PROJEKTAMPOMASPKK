<?php

session_start();

require_once __DIR__ . "/../../config/koneksi.php";

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../../login.php");
    exit;
}


$nama_produk = trim($_POST["nama_produk"] ?? "");
$deskripsi = trim($_POST["deskripsi"] ?? "");
$harga_per_kg = $_POST["harga_per_kg"] ?? 0;
$stok_kg = $_POST["stok_kg"] ?? 0;
$minimal_pembelian = $_POST["minimal_pembelian"] ?? 0;
$status = $_POST["status"] ?? "aktif";


if (
    $nama_produk === "" ||
    $deskripsi === "" ||
    $harga_per_kg === "" ||
    $stok_kg === "" ||
    $minimal_pembelian === ""
) {

    echo "<script>
        alert('Semua data wajib diisi!');
        window.location='../tambah_produk.php';
    </script>";

    exit;
}


$stmt = mysqli_prepare(
    $koneksi,
    "INSERT INTO tproduk
    (nama_produk, deskripsi, harga_per_kg, stok_kg, minimal_pembelian, status)
    VALUES (?, ?, ?, ?, ?, ?)"
);


mysqli_stmt_bind_param(
    $stmt,
    "ssddds",
    $nama_produk,
    $deskripsi,
    $harga_per_kg,
    $stok_kg,
    $minimal_pembelian,
    $status
);


if (mysqli_stmt_execute($stmt)) {

    echo "<script>
        alert('Produk berhasil ditambahkan!');
        window.location='../produk.php';
    </script>";

} else {

    die("Gagal menambahkan produk: " . mysqli_error($koneksi));

}

?>