<?php
session_start();

require_once __DIR__ . "/../config/koneksi.php";

// Pastikan user sudah login
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login.php");
    exit;
}

// Pastikan menggunakan POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../produk.php");
    exit;
}

// Siapkan session keranjang
if (!isset($_SESSION["keranjang"])) {
    $_SESSION["keranjang"] = [];
}

$aksi = $_POST["aksi"] ?? "";
$id_produk = (int) ($_POST["id_produk"] ?? 0);

// Tambah produk
if ($aksi === "tambah") {

    if ($id_produk <= 0) {
        header("Location: ../produk.php");
        exit;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT stok_kg, minimal_pembelian
         FROM tproduk
         WHERE id_produk = ?
         AND status = 'aktif'
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $id_produk);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $produk = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($stmt);

    if (!$produk) {
        header("Location: ../produk.php?status=tidak_tersedia");
        exit;
    }

    $minimal = (float) $produk["minimal_pembelian"];
    $stok = (float) $produk["stok_kg"];

    $jumlah_sekarang = (float) (
        $_SESSION["keranjang"][$id_produk] ?? 0
    );

    $jumlah_baru = $jumlah_sekarang + $minimal;

    if ($jumlah_baru > $stok) {
        header("Location: ../produk.php?status=stok_tidak_cukup");
        exit;
    }

    $_SESSION["keranjang"][$id_produk] = $jumlah_baru;

    header("Location: ../keranjang.php?status=ditambahkan");
    exit;
}

// Perbarui jumlah produk
if ($aksi === "update") {

    $jumlah_input = trim($_POST["jumlah_kg"] ?? "");
    $jumlah = is_numeric($jumlah_input)
        ? (float) $jumlah_input
        : 0;

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT stok_kg, minimal_pembelian
         FROM tproduk
         WHERE id_produk = ?
         AND status = 'aktif'
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $id_produk);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $produk = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($stmt);

    if (
        !$produk ||
        $jumlah < (float) $produk["minimal_pembelian"] ||
        $jumlah > (float) $produk["stok_kg"] ||
        round($jumlah, 2) != $jumlah
    ) {
        header("Location: ../keranjang.php?status=jumlah_tidak_valid");
        exit;
    }

    $_SESSION["keranjang"][$id_produk] = $jumlah;

    header("Location: ../keranjang.php?status=diupdate");
    exit;
}

// Hapus satu produk
if ($aksi === "hapus") {

    unset($_SESSION["keranjang"][$id_produk]);

    header("Location: ../keranjang.php?status=dihapus");
    exit;
}

// Kosongkan seluruh keranjang
if ($aksi === "kosongkan") {

    $_SESSION["keranjang"] = [];

    header("Location: ../keranjang.php?status=dikosongkan");
    exit;
}

header("Location: ../keranjang.php");
exit;