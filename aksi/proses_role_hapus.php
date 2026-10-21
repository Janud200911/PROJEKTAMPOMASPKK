<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: ../admin/role.php");
    exit;
}

$query = mysqli_query(
    $koneksi,
    "DELETE FROM trole WHERE id_role = $id"
);

if (!$query) {

    die(
        "Gagal menghapus role: "
        . mysqli_error($koneksi)
    );

}

header("Location: ../admin/role.php");
exit;

?>