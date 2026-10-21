<?php

session_start();

require_once __DIR__ . "/../config/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit;
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

// Cek input
if ($username === "" || $password === "") {

    echo "<script>
        alert('Username dan password wajib diisi!');
        window.location='../login.php';
    </script>";

    exit;
}


// Cari username
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM tuser WHERE username = '$username' LIMIT 1"
);

if (!$query) {
    die("Query login gagal: " . mysqli_error($koneksi));
}


// Username ditemukan
if (mysqli_num_rows($query) == 1) {

    $user = mysqli_fetch_assoc($query);

    // Cek password
    if (password_verify($password, $user["password"])) {

        // Simpan session
        $_SESSION["login"] = true;
        $_SESSION["id_user"] = $user["id_user"];
        $_SESSION["nama"] = $user["nama"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["role"] = $user["role"];


        // Redirect berdasarkan role
        if ($user["role"] === "admin") {

            header("Location: ../admin/index.php");
            exit;

        } else {

            header("Location: ../index.php");
            exit;

        }

    } else {

        echo "<script>
            alert('Password salah!');
            window.location='../login.php';
        </script>";

        exit;
    }

} else {

    echo "<script>
        alert('Username tidak ditemukan!');
        window.location='../login.php';
    </script>";

    exit;
}

?>