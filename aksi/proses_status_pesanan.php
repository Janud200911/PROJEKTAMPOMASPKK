
<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";

// Cek hak akses admin
if (
    !isset($_SESSION["login"]) ||
    $_SESSION["login"] !== true ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: ../login.php");
    exit;
}

// Hanya menerima POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../admin/pesanan.php");
    exit;
}

$id_pesanan = (int) ($_POST["id_pesanan"] ?? 0);
$status_baru = $_POST["status"] ?? "";

$status_valid = [
    "menunggu",
    "diproses",
    "siap_diambil",
    "selesai",
    "dibatalkan"
];

if (
    $id_pesanan <= 0 ||
    !in_array($status_baru, $status_valid, true)
) {
    header("Location: ../admin/pesanan.php?pesan=tidak_valid");
    exit;
}

try {
    mysqli_begin_transaction($koneksi);

    // Ambil status pesanan dan pembayaran
    // FOR UPDATE mencegah perubahan bersamaan.
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT status, status_pembayaran, metode_pembayaran
         FROM tpesanan
         WHERE id_pesanan = ?
         FOR UPDATE"
    );

    mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $pesanan = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($stmt);

    if (!$pesanan) {
        throw new Exception("Pesanan tidak ditemukan.");
    }

    $status_lama = $pesanan["status"];
    $status_pembayaran = $pesanan["status_pembayaran"];
    $metode_pembayaran = $pesanan["metode_pembayaran"];

    // Jika status tidak berubah
    if ($status_lama === $status_baru) {
        mysqli_commit($koneksi);
        header("Location: ../admin/pesanan.php?pesan=berhasil");
        exit;
    }

    // Pesanan yang sudah dibatalkan tidak boleh diaktifkan kembali
    if ($status_lama === "dibatalkan") {
        throw new Exception("Pesanan sudah dibatalkan.");
    }

    // Pembayaran transfer dan QRIS harus lunas
    // sebelum pesanan siap diambil atau selesai.
    if (
        in_array($status_baru, ["siap_diambil", "selesai"], true) &&
        in_array($metode_pembayaran, ["transfer", "qris"], true) &&
        $status_pembayaran !== "lunas"
    ) {
        mysqli_rollback($koneksi);
        header("Location: ../admin/pesanan.php?pesan=belum_lunas");
        exit;
    }

    // Jika pesanan dibatalkan, kembalikan stok produk
    if ($status_baru === "dibatalkan") {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id_produk, jumlah_kg
             FROM tdetail_pesanan
             WHERE id_pesanan = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
        mysqli_stmt_execute($stmt);

        $hasil_detail = mysqli_stmt_get_result($stmt);
        $ada_detail = false;

        while ($detail = mysqli_fetch_assoc($hasil_detail)) {
            $ada_detail = true;

            $id_produk = (int) $detail["id_produk"];
            $jumlah_kg = (float) $detail["jumlah_kg"];

            $stmt_stok = mysqli_prepare(
                $koneksi,
                "UPDATE tproduk
                 SET stok_kg = stok_kg + ?
                 WHERE id_produk = ?"
            );

            mysqli_stmt_bind_param(
                $stmt_stok,
                "di",
                $jumlah_kg,
                $id_produk
            );

            if (!mysqli_stmt_execute($stmt_stok)) {
                mysqli_stmt_close($stmt_stok);
                throw new Exception("Gagal mengembalikan stok.");
            }

            if (mysqli_stmt_affected_rows($stmt_stok) !== 1) {
                mysqli_stmt_close($stmt_stok);
                throw new Exception("Produk tidak ditemukan.");
            }

            mysqli_stmt_close($stmt_stok);
        }

        mysqli_stmt_close($stmt);

        if (!$ada_detail) {
            throw new Exception("Detail pesanan tidak ditemukan.");
        }
    }

    // Perbarui status pesanan
    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE tpesanan
         SET status = ?
         WHERE id_pesanan = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "si",
        $status_baru,
        $id_pesanan
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception("Gagal memperbarui status pesanan.");
    }

    mysqli_stmt_close($stmt);

    // Simpan semua perubahan
    mysqli_commit($koneksi);

    header("Location: ../admin/pesanan.php?pesan=berhasil");
    exit;

} catch (Throwable $e) {

    mysqli_rollback($koneksi);

    error_log("Proses status pesanan: " . $e->getMessage());

    header("Location: ../admin/pesanan.php?pesan=gagal");
    exit;
}
?>
