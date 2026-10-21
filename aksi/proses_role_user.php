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

$id_user = intval($_POST["id_user"] ?? 0);
$id_role = intval($_POST["id_role"] ?? 0);

if ($id_user <= 0 || $id_role <= 0) {
    echo "<script>
        alert('Data role tidak valid!');
        window.location='../admin/role.php';
    </script>";
    exit;
}


/* Ambil nama role */
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama_role FROM trole WHERE id_role = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id_role);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo "<script>
        alert('Role tidak ditemukan!');
        window.location='../admin/role.php';
    </script>";
    exit;
}

$role = mysqli_fetch_assoc($result);
$nama_role = $role["nama_role"];


/* Update user */
$stmtUpdate = mysqli_prepare(
    $koneksi,
    "UPDATE tuser
     SET id_role = ?, role = ?
     WHERE id_user = ?"
);

mysqli_stmt_bind_param(
    $stmtUpdate,
    "isi",
    $id_role,
    $nama_role,
    $id_user
);

if (mysqli_stmt_execute($stmtUpdate)) {

    echo "<script>
        alert('Role berhasil diubah!');
        window.location='../admin/role.php';
    </script>";

} else {

    echo "<script>
        alert('Gagal mengubah role!');
        window.location='../admin/role.php';
    </script>";

}
?>