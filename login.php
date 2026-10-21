<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Tampomas Berkah</title>

    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<div class="auth-page">

    <div class="auth-card">

        <div class="auth-logo">
             Tampomas Berkah
        </div>

        <h1>Login</h1>

        <p class="auth-subtitle">
            Masuk ke akun Anda
        </p>

        <form action="aksi/proses_login.php" method="POST">

            <div class="form-group">
                <label>Username</label>
                <input
                    type="text"
                    name="username"
                    placeholder="Masukkan username"
                    required
                >
            </div>

            <div class="form-group">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >
            </div>

            <button type="submit" class="auth-button">
                Login
            </button>

        </form>

        <div class="auth-footer">
            Belum punya akun?
            <a href="register.php">Daftar</a>
        </div>

        <a href="index.php" class="back-home">
            ← Kembali ke Beranda
        </a>

    </div>

</div>

</body>
</html>