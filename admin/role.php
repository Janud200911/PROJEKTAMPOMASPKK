<?php
session_start();

require_once __DIR__ . "/../config/koneksi.php";

if (!isset($_SESSION["login"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$queryRole = mysqli_query($koneksi, "SELECT * FROM trole ORDER BY id_role ASC");

$queryUser = mysqli_query(
    $koneksi,
    "SELECT 
        u.id_user,
        u.nama,
        u.username,
        u.id_role,
        u.role AS role_lama,
        r.nama_role
    FROM tuser u
    LEFT JOIN trole r ON u.id_role = r.id_role
    ORDER BY u.id_user ASC"
);

if (!$queryUser) {
    die("Query user gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Role - Tampomas Berkah</title>

    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .role-container {
            padding: 30px;
        }

        .role-header {
            margin-bottom: 25px;
        }

        .role-header h1 {
            margin: 0 0 5px;
        }

        .role-header p {
            margin: 0;
            color: #777;
        }

        .role-table-box {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow-x: auto;
        }

        .role-table {
            width: 100%;
            border-collapse: collapse;
        }

        .role-table th {
            background: #f5f7f5;
            padding: 14px;
            text-align: left;
            font-size: 14px;
        }

        .role-table td {
            padding: 14px;
            border-top: 1px solid #eee;
            font-size: 14px;
        }

        .role-table tr:hover {
            background: #fafafa;
        }

        .role-select {
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            min-width: 140px;
            background: white;
        }

        .btn-simpan-role {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            background: #168044;
            color: white;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-simpan-role:hover {
            background: #126b39;
        }

        .role-badge {
            display: inline-block;
            padding: 5px 9px;
            background: #eef7f1;
            color: #168044;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar">

        <div class="sidebar-logo">
            🥬 <span>Tampomas Berkah</span>
        </div>

        <nav class="sidebar-menu">
            <a href="index.php">Dashboard</a>
            <a href="produk.php">Produk</a>
            <a href="pesanan.php">Pesanan</a>
            <a href="role.php" class="active">Role</a>
            <a href="pembayaran.php">Pembayaran</a>
            <a href="../logout.php">Logout</a>
        </nav>

    </aside>


    <!-- CONTENT -->
    <main class="admin-content">

        <div class="role-container">

            <div class="role-header">
                <h1>Data Role</h1>
                <p>Atur hak akses setiap pengguna sistem.</p>
            </div>

            <div class="role-table-box">

                <table class="role-table">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role Saat Ini</th>
                            <th>Ubah Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php
                    $no = 1;

                    while ($user = mysqli_fetch_assoc($queryUser)) {
                    ?>

                        <tr>

                            <td><?= $no++; ?></td>

                            <td>
                                <?= htmlspecialchars($user["nama"]); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($user["username"]); ?>
                            </td>

                            <td>
                                <span class="role-badge">
                                    <?= htmlspecialchars(
                                        $user["nama_role"] ?? $user["role_lama"]
                                    ); ?>
                                </span>
                            </td>

                            <td>

                                <form action="../aksi/proses_role_user.php" method="POST">

                                    <input
                                        type="hidden"
                                        name="id_user"
                                        value="<?= $user["id_user"]; ?>"
                                    >

                                    <select name="id_role" class="role-select">

                                        <?php
                                        mysqli_data_seek($queryRole, 0);

                                        while ($role = mysqli_fetch_assoc($queryRole)) {
                                        ?>

                                            <option
                                                value="<?= $role["id_role"]; ?>"
                                                <?= ($user["id_role"] == $role["id_role"]) ? "selected" : ""; ?>
                                            >
                                                <?= htmlspecialchars($role["nama_role"]); ?>
                                            </option>

                                        <?php } ?>

                                    </select>

                            </td>

                            <td>

                                    <button
                                        type="submit"
                                        class="btn-simpan-role"
                                    >
                                        Simpan
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>

</body>
</html>