```php
<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

// Cek login
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: login.php");
    exit;
}

// Ambil ID produk
$id_produk = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($id_produk <= 0) {
    header("Location: produk.php");
    exit;
}

// Ambil data produk
$stmt = mysqli_prepare($koneksi, "
    SELECT *
    FROM tproduk
    WHERE id_produk = ?
    AND status = 'aktif'
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "i", $id_produk);
mysqli_stmt_execute($stmt);

$query = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($query);

mysqli_stmt_close($stmt);

if (!$produk) {
    echo "Produk tidak ditemukan atau sudah tidak tersedia.";
    exit;
}

$nama_user = $_SESSION["nama"] ?? "";

$stok = (float) $produk["stok_kg"];
$minimal = (float) $produk["minimal_pembelian"];
$harga = (float) $produk["harga_per_kg"];

// Tanggal pengambilan paling cepat besok
$tanggal_minimal = date("Y-m-d", strtotime("+1 day"));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Pemesanan <?= htmlspecialchars($produk["nama_produk"]); ?> |
        Tampomas Berkah
    </title>

    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<div class="beli-container">

    <a href="produk.php" class="btn-kembali">
        ← Kembali ke Produk
    </a>

    <div class="beli-card">

        <h2>Pemesanan Stok Sayuran</h2>

        <p>
            Pesan sayuran terlebih dahulu agar stok dapat
            disiapkan sebelum pengambilan di lapak Tampomas Berkah.
        </p>

        <div class="produk-info">

            <h3>
                <?= htmlspecialchars($produk["nama_produk"]); ?>
            </h3>

            <p>
                Harga:
                <strong>
                    Rp <?= number_format($harga, 0, ',', '.'); ?>/kg
                </strong>
            </p>

            <p>
                Stok tersedia:
                <strong>
                    <?= number_format($stok, 2, ',', '.'); ?> kg
                </strong>
            </p>

            <p>
                Minimal pembelian:
                <?= number_format($minimal, 2, ',', '.'); ?> kg
            </p>

        </div>

        <?php if ($stok < $minimal): ?>

            <p>
                Stok belum mencukupi untuk minimal pembelian.
                Silakan pilih produk lain atau coba kembali nanti.
            </p>

        <?php else: ?>

            <form action="aksi/proses_pesanan.php" method="POST">

                <input
                    type="hidden"
                    name="id_produk"
                    value="<?= (int) $produk["id_produk"]; ?>"
                >

                <div class="form-group">

                    <label>Nama Pemesan</label>

                    <input
                        type="text"
                        value="<?= htmlspecialchars($nama_user); ?>"
                        readonly
                    >

                </div>

                <div class="form-group">

                    <label for="jumlah_kg">Jumlah Pembelian (KG)</label>

                    <input
                        type="number"
                        name="jumlah_kg"
                        id="jumlah_kg"
                        min="<?= htmlspecialchars((string) $minimal); ?>"
                        max="<?= htmlspecialchars((string) $stok); ?>"
                        step="0.01"
                        required
                        oninput="hitungTotal()"
                    >

                    <small>
                        Jumlah pesanan harus sesuai stok yang tersedia.
                    </small>

                </div>

                <div class="total-box">

                    <span>Estimasi Total Harga</span>

                    <strong id="total_harga">Rp 0</strong>

                </div>

                <div class="form-group">

                    <label for="tanggal_pengambilan">
                        Tanggal Pengambilan
                    </label>

                    <input
                        type="date"
                        name="tanggal_pengambilan"
                        id="tanggal_pengambilan"
                        min="<?= $tanggal_minimal; ?>"
                        required
                    >

                    <small>
                        Pilih tanggal pengambilan di lapak Tampomas Berkah.
                    </small>

                </div>

                <div class="form-group">

                    <label for="metode_pembayaran">
                        Metode Pembayaran
                    </label>

                    <select
                        name="metode_pembayaran"
                        id="metode_pembayaran"
                        required
                    >

                        <option value="">-- Pilih Pembayaran --</option>
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                        <option value="qris">QRIS</option>

                    </select>

                </div>

                <div class="form-group">

                    <label for="catatan">Catatan Pesanan</label>

                    <textarea
                        name="catatan"
                        id="catatan"
                        placeholder="Contoh: Mohon siapkan pesanan untuk diambil pagi hari."
                    ></textarea>

                </div>

                <button type="submit" class="btn-pesan">
                    Ajukan Pemesanan
                </button>

            </form>

        <?php endif; ?>

    </div>

</div>

<script>
const harga = <?= json_encode($harga); ?>;
const stok = <?= json_encode($stok); ?>;
const minimal = <?= json_encode($minimal); ?>;

function hitungTotal() {
    const input = document.getElementById("jumlah_kg");
    const jumlah = parseFloat(input.value) || 0;

    if (jumlah > stok) {
        input.setCustomValidity("Jumlah melebihi stok yang tersedia.");
    } else if (jumlah > 0 && jumlah < minimal) {
        input.setCustomValidity("Jumlah belum mencapai minimal pembelian.");
    } else {
        input.setCustomValidity("");
    }

    const total = jumlah * harga;

    document.getElementById("total_harga").innerText =
        "Rp " + total.toLocaleString("id-ID", {
            maximumFractionDigits: 2
        });
}
</script>

</body>
</html>
```