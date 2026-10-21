<?php

require_once __DIR__ . "/../config/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../register.php");
    exit;
}

$nama = trim($_POST["nama"] ?? "");
$username = trim($_POST["username"] ?? "");
$no_hp = trim($_POST["no_hp"] ?? "");
$alamat = trim($_POST["alamat"] ?? "");
$password = $_POST["password"] ?? "";


// Cek data wajib
if ($nama === "" || $username === "" || $password === "") {

    echo "<script>
        alert('Nama, username, dan password wajib diisi!');
        window.location='../register.php';
    </script>";

    exit;
}


// Cek username
$cek = mysqli_query(
    $koneksi,
    "SELECT id_user FROM tuser WHERE username = '$username'"
);

if (!$cek) {
    die("Query cek username gagal: " . mysqli_error($koneksi));
}


if (mysqli_num_rows($cek) > 0) {

    echo "<script>
        alert('Username sudah digunakan!');
        window.location='../register.php';
    </script>";

    exit;
}


// Enkripsi password
$password_hash = password_hash($password, PASSWORD_DEFAULT);


// Simpan user
$query = mysqli_query(
    $koneksi,
    "INSERT INTO tuser
    (nama, username, password, no_hp, alamat, role)
    VALUES
    ('$nama', '$username', '$password_hash', '$no_hp', '$alamat', 'user')"
);


if (!$query) {

    die("Registrasi gagal: " . mysqli_error($koneksi));

}


echo "<script>
    alert('Registrasi berhasil! Silakan login.');
    window.location='../login.php';
</script>";

?>