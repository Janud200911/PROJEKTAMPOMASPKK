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

$nama_role = trim($_POST["nama_role"] ?? "");
$keterangan = trim($_POST["keterangan"] ?? "");

if ($nama_role === "") {

    echo "<script>
        alert('Nama role wajib diisi!');
        window.location='../admin/role_tambah.php';
    </script>";

    exit;
}

$query = mysqli_query(
    $koneksi,
    "INSERT INTO trole (nama_role, keterangan)
     VALUES ('$nama_role', '$keterangan')"
);

if (!$query) {

    die(
        "Gagal menambahkan role: "
        . mysqli_error($koneksi)
    );

}

header("Location: ../admin/role.php");
exit;

?>