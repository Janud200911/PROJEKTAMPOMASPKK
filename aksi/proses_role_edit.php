<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../admin/role.php");
    exit;
}

$id = intval($_POST["id_role"] ?? 0);

$nama_role = trim($_POST["nama_role"] ?? "");
$keterangan = trim($_POST["keterangan"] ?? "");

if ($id <= 0 || $nama_role === "") {

    echo "<script>
        alert('Data role tidak lengkap!');
        window.location='../admin/role.php';
    </script>";

    exit;
}

$query = mysqli_query(
    $koneksi,
    "UPDATE trole
     SET nama_role = '$nama_role',
         keterangan = '$keterangan'
     WHERE id_role = $id"
);

if (!$query) {

    die(
        "Gagal mengubah role: "
        . mysqli_error($koneksi)
    );

}

header("Location: ../admin/role.php");
exit;

?>