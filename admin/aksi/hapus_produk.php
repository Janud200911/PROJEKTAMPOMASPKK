<?php

session_start();

require_once __DIR__ . "/../../config/koneksi.php";

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../../login.php");
    exit;
}


$id = $_GET["id"] ?? 0;


$query = mysqli_query(
    $koneksi,
    "DELETE FROM tproduk WHERE id_produk = '$id'"
);


if ($query) {

    echo "<script>
        alert('Produk berhasil dihapus!');
        window.location='../produk.php';
    </script>";

} else {

    die("Gagal menghapus produk: " . mysqli_error($koneksi));

}

?>