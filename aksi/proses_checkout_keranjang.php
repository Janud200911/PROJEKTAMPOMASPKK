
<?php
session_start();

require_once __DIR__ . "/../config/koneksi.php";

date_default_timezone_set("Asia/Jakarta");

function kembaliDenganError($pesan)
{
    $_SESSION["checkout_error"] = $pesan;

    header("Location: ../checkout_keranjang.php");
    exit;
}


// Cek login
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login.php");
    exit;
}


// Cek metode request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../keranjang.php");
    exit;
}


// Ambil data
$id_user = (int) ($_SESSION["id_user"] ?? 0);
$keranjang = $_SESSION["keranjang"] ?? [];

$csrf_token = $_POST["csrf_token"] ?? "";

$tanggal_pengambilan = trim(
    $_POST["tanggal_pengambilan"] ?? ""
);

$metode = $_POST["metode_pembayaran"] ?? "";
$catatan = trim($_POST["catatan"] ?? "");


// Validasi dasar
if (
    $id_user <= 0 ||
    !is_array($keranjang) ||
    empty($keranjang)
) {
    kembaliDenganError("Keranjang kosong atau data akun tidak valid.");
}


// Validasi token
if (
    empty($_SESSION["csrf_checkout"]) ||
    !is_string($csrf_token) ||
    !hash_equals($_SESSION["csrf_checkout"], $csrf_token)
) {
    kembaliDenganError("Sesi checkout tidak valid. Silakan coba lagi.");
}


// Validasi pembayaran
$metode_valid = ["cash", "transfer", "qris"];

if (!in_array($metode, $metode_valid, true)) {
    kembaliDenganError("Silakan pilih metode pembayaran.");
}


// Validasi tanggal
$tanggal_obj = DateTime::createFromFormat(
    "!Y-m-d",
    $tanggal_pengambilan
);

if (
    !$tanggal_obj ||
    $tanggal_obj->format("Y-m-d") !== $tanggal_pengambilan ||
    $tanggal_pengambilan < date("Y-m-d", strtotime("+1 day"))
) {
    kembaliDenganError(
        "Tanggal pengambilan tidak valid. Pilih tanggal mulai besok."
    );
}


// Validasi catatan
if (strlen($catatan) > 2000) {
    kembaliDenganError("Catatan terlalu panjang. Maksimal 2.000 karakter.");
}


// Validasi isi keranjang
$cart_items = [];

foreach ($keranjang as $id_produk => $jumlah) {

    $id_produk = (int) $id_produk;
    $jumlah_input = trim((string) $jumlah);

    if (
        $id_produk <= 0 ||
        !is_numeric($jumlah_input) ||
        (float) $jumlah_input <= 0 ||
        round((float) $jumlah_input, 2) != (float) $jumlah_input
    ) {
        kembaliDenganError("Ada jumlah produk yang tidak valid di keranjang.");
    }

    $cart_items[$id_produk] = (float) $jumlah_input;
}

ksort($cart_items, SORT_NUMERIC);


// Mulai transaksi database
if (!mysqli_begin_transaction($koneksi)) {
    kembaliDenganError("Tidak dapat memulai transaksi. Silakan coba lagi.");
}


try {

    $produk_pesanan = [];

    $total_berat = 0;
    $total_harga = 0;


    // Ambil dan kunci stok semua produk
    foreach ($cart_items as $id_produk => $jumlah_kg) {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT nama_produk, harga_per_kg,
                    stok_kg, minimal_pembelian
             FROM tproduk
             WHERE id_produk = ?
             AND status = 'aktif'
             FOR UPDATE"
        );

        if (!$stmt) {
            throw new Exception("Gagal menyiapkan data produk.");
        }

        mysqli_stmt_bind_param($stmt, "i", $id_produk);

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new Exception("Gagal memeriksa stok produk.");
        }

        $hasil = mysqli_stmt_get_result($stmt);
        $produk = mysqli_fetch_assoc($hasil);

        mysqli_stmt_close($stmt);


        if (!$produk) {
            throw new Exception("Salah satu produk sudah tidak tersedia.");
        }


        $stok = (float) $produk["stok_kg"];
        $minimal = (float) $produk["minimal_pembelian"];
        $harga = (float) $produk["harga_per_kg"];


        if ($jumlah_kg < $minimal) {
            throw new Exception(
                "Minimal pembelian " . $produk["nama_produk"] .
                " adalah " . number_format($minimal, 2, ",", ".") . " kg."
            );
        }


        if ($jumlah_kg > $stok) {
            throw new Exception(
                "Stok " . $produk["nama_produk"] .
                " tidak mencukupi. Stok tersedia " .
                number_format($stok, 2, ",", ".") . " kg."
            );
        }


        $subtotal = round($jumlah_kg * $harga, 2);


        $produk_pesanan[] = [
            "id_produk" => $id_produk,
            "nama_produk" => $produk["nama_produk"],
            "jumlah_kg" => $jumlah_kg,
            "harga" => $harga,
            "subtotal" => $subtotal
        ];


        $total_berat += $jumlah_kg;
        $total_harga += $subtotal;

    }


    $total_harga = round($total_harga, 2);

    $status = "menunggu";
    $status_pembayaran = "belum_bayar";


    // Buat satu pesanan utama
    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO tpesanan (
            id_user,
            tanggal_pesanan,
            tanggal_pengambilan,
            total_berat,
            total_harga,
            status,
            catatan,
            metode_pembayaran,
            status_pembayaran
        ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception("Gagal menyiapkan data pesanan.");
    }


    mysqli_stmt_bind_param(
        $stmt,
        "isddssss",
        $id_user,
        $tanggal_pengambilan,
        $total_berat,
        $total_harga,
        $status,
        $catatan,
        $metode,
        $status_pembayaran
    );


    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception("Gagal menyimpan pesanan.");
    }


    $id_pesanan = mysqli_insert_id($koneksi);

    mysqli_stmt_close($stmt);


    // Simpan seluruh detail produk dan kurangi stok
    foreach ($produk_pesanan as $item) {

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO tdetail_pesanan (
                id_pesanan,
                id_produk,
                jumlah_kg,
                harga_satuan,
                subtotal
            ) VALUES (?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            throw new Exception("Gagal menyiapkan detail pesanan.");
        }


        mysqli_stmt_bind_param(
            $stmt,
            "iiddd",
            $id_pesanan,
            $item["id_produk"],
            $item["jumlah_kg"],
            $item["harga"],
            $item["subtotal"]
        );


        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new Exception("Gagal menyimpan detail produk pesanan.");
        }

        mysqli_stmt_close($stmt);


        // Kurangi stok produk
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE tproduk
             SET stok_kg = stok_kg - ?
             WHERE id_produk = ?
             AND stok_kg >= ?
             AND status = 'aktif'"
        );

        if (!$stmt) {
            throw new Exception("Gagal menyiapkan pembaruan stok.");
        }


        mysqli_stmt_bind_param(
            $stmt,
            "did",
            $item["jumlah_kg"],
            $item["id_produk"],
            $item["jumlah_kg"]
        );


        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new Exception("Gagal memperbarui stok produk.");
        }


        if (mysqli_stmt_affected_rows($stmt) !== 1) {
            mysqli_stmt_close($stmt);

            throw new Exception(
                "Stok berubah atau tidak mencukupi. Silakan periksa keranjang."
            );
        }

        mysqli_stmt_close($stmt);

    }


    // Simpan seluruh transaksi
    if (!mysqli_commit($koneksi)) {
        throw new Exception("Gagal menyimpan transaksi.");
    }


    // Kosongkan keranjang setelah pesanan berhasil
    $_SESSION["keranjang"] = [];

    unset(
        $_SESSION["csrf_checkout"],
        $_SESSION["checkout_error"]
    );


    // Buka halaman pesanan berhasil
    header(
        "Location: ../pesanan_berhasil.php?id=" . $id_pesanan
    );

    exit;


} catch (Throwable $e) {

    mysqli_rollback($koneksi);

    error_log(
        "Checkout keranjang Tampomas Berkah: " .
        $e->getMessage()
    );

    kembaliDenganError($e->getMessage());

}
