<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";

// Pastikan admin sudah login
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
$keputusan = $_POST["keputusan"] ?? "";

if (
    $id_pesanan <= 0 ||
    !in_array($keputusan, ["terima", "tolak"], true)
) {
    header("Location: ../admin/pesanan.php?verifikasi=tidak_valid");
    exit;
}

try {
    mysqli_begin_transaction($koneksi);

    // Ambil data pesanan dan kunci baris selama proses verifikasi
    $query = mysqli_prepare(
        $koneksi,
        "SELECT metode_pembayaran, status_pembayaran,
                bukti_pembayaran, status
         FROM tpesanan
         WHERE id_pesanan = ?
         FOR UPDATE"
    );

    mysqli_stmt_bind_param($query, "i", $id_pesanan);
    mysqli_stmt_execute($query);

    $hasil = mysqli_stmt_get_result($query);
    $pesanan = mysqli_fetch_assoc($hasil);

    if (!$pesanan) {
        throw new Exception("Pesanan tidak ditemukan.");
    }

    // Verifikasi hanya untuk transfer dan QRIS
    if (!in_array($pesanan["metode_pembayaran"], ["transfer", "qris"], true)) {
        throw new Exception("Metode pembayaran tidak perlu diverifikasi.");
    }

    // Pesanan yang dibatalkan tidak boleh diverifikasi
    if ($pesanan["status"] === "dibatalkan") {
        throw new Exception("Pesanan sudah dibatalkan.");
    }

    // Bukti pembayaran harus tersedia
    if (empty($pesanan["bukti_pembayaran"])) {
        throw new Exception("Bukti pembayaran belum tersedia.");
    }

    // Pastikan file bukti benar-benar ada
    $lokasi_bukti = __DIR__ . "/../" . $pesanan["bukti_pembayaran"];

    if (!is_file($lokasi_bukti)) {
        throw new Exception("File bukti pembayaran tidak ditemukan.");
    }

    // Hanya pesanan yang menunggu verifikasi yang bisa diproses
    if ($pesanan["status_pembayaran"] !== "menunggu_verifikasi") {
        throw new Exception("Pembayaran sudah pernah diproses atau belum siap diverifikasi.");
    }

    // Tentukan status pembayaran
    $status_baru = ($keputusan === "terima") ? "lunas" : "ditolak";

    $update = mysqli_prepare(
        $koneksi,
        "UPDATE tpesanan
         SET status_pembayaran = ?
         WHERE id_pesanan = ?"
    );

    mysqli_stmt_bind_param($update, "si", $status_baru, $id_pesanan);
    mysqli_stmt_execute($update);

    mysqli_commit($koneksi);

    header("Location: ../admin/pesanan.php?verifikasi=berhasil");
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);

    error_log("Verifikasi pembayaran: " . $e->getMessage());

    header("Location: ../admin/pesanan.php?verifikasi=gagal");
    exit;
}