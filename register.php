<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar - Tampomas Berkah</title>

    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<div class="auth-page">

    <div class="auth-card">

        <div class="auth-logo">
             Tampomas Berkah
        </div>

        <h1>Daftar</h1>

        <p class="auth-subtitle">
            Buat akun baru
        </p>

        <form action="aksi/proses_register.php" method="POST">

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input
                    type="text"
                    name="nama"
                    placeholder="Nama lengkap"
                    required
                >
            </div>

            <div class="form-group">
                <label>Username</label>
                <input
                    type="text"
                    name="username"
                    placeholder="Username"
                    required
                >
            </div>

            <div class="form-group">
                <label>No. HP</label>
                <input
                    type="text"
                    name="no_hp"
                    placeholder="Nomor HP"
                    required
                >
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea
                    name="alamat"
                    rows="3"
                    placeholder="Alamat"
                    required
                ></textarea>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                >
            </div>

            <button type="submit" class="auth-button">
                Daftar
            </button>

        </form>

        <div class="auth-footer">
            Sudah punya akun?
            <a href="login.php">Login</a>
        </div>

        <a href="index.php" class="back-home">
            ← Kembali ke Beranda
        </a>

    </div>

</div>

</body>
</html>