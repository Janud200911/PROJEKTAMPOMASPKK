
<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";

function kembali($status) {
    header("Location: ../pembayaran.php?id=" .
        (int) ($_POST["id_pesanan"] ?? 0) .
        "&upload=" . $status);
    exit;
}

if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pesanan_saya.php");
    exit;
}

$id_user = (int) $_SESSION["id_user"];
$id_pesanan = (int) ($_POST["id_pesanan"] ?? 0);

if ($id_pesanan <= 0 ||
    !isset($_FILES["bukti_pembayaran"])) {
    kembali("gagal");
}

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT status, metode_pembayaran, status_pembayaran
     FROM tpesanan
     WHERE id_pesanan = ? AND id_user = ?"
);

mysqli_stmt_bind_param($stmt, "ii", $id_pesanan, $id_user);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$pesanan = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if (!$pesanan) {
    exit("Pesanan tidak ditemukan.");
}

$metode = strtolower($pesanan["metode_pembayaran"]);
$statusBayar = strtolower($pesanan["status_pembayaran"]);

if (!in_array($metode, ["qris", "transfer"], true)) {
    exit("Metode pembayaran tidak mendukung upload bukti.");
}

if ($pesanan["status"] === "dibatalkan" ||
    in_array($statusBayar, ["lunas", "dibatalkan"], true)) {
    exit("Pesanan ini tidak dapat menerima bukti pembayaran.");
}

$file = $_FILES["bukti_pembayaran"];

if ($file["error"] !== UPLOAD_ERR_OK ||
    $file["size"] <= 0 ||
    $file["size"] > 3 * 1024 * 1024) {
    kembali("gagal");
}

if (!is_uploaded_file($file["tmp_name"])) {
    kembali("gagal");
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file["tmp_name"]);

$tipeDiizinkan = [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
    "image/webp" => "webp"
];

if (!isset($tipeDiizinkan[$mime])) {
    kembali("gagal");
}

$folder = __DIR__ . "/../uploads/";

if (!is_dir($folder) &&
    !mkdir($folder, 0755, true) &&
    !is_dir($folder)) {
    exit("Folder upload tidak dapat dibuat.");
}

$namaFile = "bukti_" . $id_pesanan . "_" .
    bin2hex(random_bytes(8)) . "." . $tipeDiizinkan[$mime];

$tujuan = $folder . $namaFile;
$lokasiBukti = "uploads/" . $namaFile;

if (!move_uploaded_file($file["tmp_name"], $tujuan)) {
    kembali("gagal");
}

$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE tpesanan
     SET bukti_pembayaran = ?,
         status_pembayaran = 'menunggu_verifikasi'
     WHERE id_pesanan = ? AND id_user = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "sii",
    $lokasiBukti,
    $id_pesanan,
    $id_user
);

if (!mysqli_stmt_execute($stmt) ||
    mysqli_stmt_affected_rows($stmt) < 0) {
    mysqli_stmt_close($stmt);
    if (file_exists($tujuan)) {
        unlink($tujuan);
    }
    exit("Gagal menyimpan bukti pembayaran.");
}

mysqli_stmt_close($stmt);

header(
    "Location: ../pembayaran.php?id=" .
    $id_pesanan . "&upload=berhasil"
);
exit;
?>
