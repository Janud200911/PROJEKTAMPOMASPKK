```php
<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";

date_default_timezone_set("Asia/Jakarta");

// Pastikan user sudah login
if (
    !isset($_SESSION["login"]) ||
    $_SESSION["login"] !== true
) {
    header("Location: ../login.php");
    exit;
}

// Pastikan form dikirim menggunakan POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../produk.php");
    exit;
}

// Ambil data dari form
$id_user = (int) ($_SESSION["id_user"] ?? 0);
$id_produk = (int) ($_POST["id_produk"] ?? 0);

$jumlah_input = trim($_POST["jumlah_kg"] ?? "");
$jumlah_kg = is_numeric($jumlah_input)
    ? (float) $jumlah_input
    : 0;

$metode = $_POST["metode_pembayaran"] ?? "";
$catatan = trim($_POST["catatan"] ?? "");
$tanggal_pengambilan = trim(
    $_POST["tanggal_pengambilan"] ?? ""
);

$metode_valid = ["cash", "transfer", "qris"];

// Validasi data dasar
if (
    $id_user <= 0 ||
    $id_produk <= 0 ||
    !is_numeric($jumlah_input) ||
    $jumlah_kg <= 0 ||
    !in_array($metode, $metode_valid, true)
) {
    exit("Data pesanan tidak valid. Silakan kembali dan coba lagi.");
}

// Validasi tanggal pengambilan
$tanggal_obj = DateTime::createFromFormat(
    "!Y-m-d",
    $tanggal_pengambilan
);

if (
    !$tanggal_obj ||
    $tanggal_obj->format("Y-m-d") !== $tanggal_pengambilan ||
    $tanggal_pengambilan < date("Y-m-d", strtotime("+1 day"))
) {
    exit(
        "Tanggal pengambilan tidak valid. " .
        "Silakan pilih tanggal mulai besok."
    );
}

// Batasi jumlah pembelian maksimal dua angka desimal
if (round($jumlah_kg, 2) != $jumlah_kg) {
    exit(
        "Jumlah pembelian maksimal dua angka di belakang koma."
    );
}

// Mulai transaksi database
if (!mysqli_begin_transaction($koneksi)) {
    exit("Gagal memulai transaksi. Silakan coba kembali.");
}

try {
    // Ambil dan kunci produk untuk mencegah konflik stok
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT nama_produk, harga_per_kg, stok_kg, minimal_pembelian
         FROM tproduk
         WHERE id_produk = ?
           AND status = 'aktif'
         FOR UPDATE"
    );

    if (!$stmt) {
        throw new Exception("Gagal menyiapkan data produk.");
    }

    mysqli_stmt_bind_param($stmt, "i", $id_produk);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception("Gagal mengambil data produk.");
    }

    $hasil = mysqli_stmt_get_result($stmt);
    $produk = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($stmt);

    if (!$produk) {
        throw new Exception(
            "Produk tidak ditemukan atau sudah tidak aktif."
        );
    }

    $stok = (float) $produk["stok_kg"];
    $minimal = (float) $produk["minimal_pembelian"];
    $harga = (float) $produk["harga_per_kg"];

    // Validasi minimal pembelian
    if ($jumlah_kg < $minimal) {
        throw new Exception(
            "Minimal pembelian adalah " . $minimal . " kg."
        );
    }

    // Validasi stok terbaru
    if ($jumlah_kg > $stok) {
        throw new Exception(
            "Stok tidak mencukupi. Stok tersedia " .
            $stok . " kg."
        );
    }

    // Hitung total harga dari harga database
    $total_harga = round($jumlah_kg * $harga, 2);

    $status = "menunggu";
    $status_pembayaran = "belum_bayar";

    // Simpan pesanan beserta tanggal pengambilan
    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO tpesanan (
            id_user,
            tanggal_pesanan,
            tanggal_pengambilan,
            total_berat,
            total_harga,
            status,
            catatan,
            metode_pembayaran,
            status_pembayaran
        ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception("Gagal menyiapkan penyimpanan pesanan.");
    }

    mysqli_stmt_bind_param(
        $stmt,
        "isddssss",
        $id_user,
        $tanggal_pengambilan,
        $jumlah_kg,
        $total_harga,
        $status,
        $catatan,
        $metode,
        $status_pembayaran
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception("Gagal menyimpan pesanan.");
    }

    $id_pesanan = mysqli_insert_id($koneksi);

    mysqli_stmt_close($stmt);

    // Simpan detail produk pesanan
    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO tdetail_pesanan (
            id_pesanan,
            id_produk,
            jumlah_kg,
            harga_satuan,
            subtotal
        ) VALUES (?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan detail pesanan."
        );
    }

    mysqli_stmt_bind_param(
        $stmt,
        "iiddd",
        $id_pesanan,
        $id_produk,
        $jumlah_kg,
        $harga,
        $total_harga
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception("Gagal menyimpan detail pesanan.");
    }

    mysqli_stmt_close($stmt);

    // Kurangi stok dalam transaksi yang sama
    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE tproduk
         SET stok_kg = stok_kg - ?
         WHERE id_produk = ?
           AND stok_kg >= ?
           AND status = 'aktif'"
    );

    if (!$stmt) {
        throw new Exception(
            "Gagal menyiapkan pembaruan stok."
        );
    }

    mysqli_stmt_bind_param(
        $stmt,
        "did",
        $jumlah_kg,
        $id_produk,
        $jumlah_kg
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception("Gagal memperbarui stok.");
    }

    if (mysqli_stmt_affected_rows($stmt) !== 1) {
        mysqli_stmt_close($stmt);
        throw new Exception(
            "Stok berubah atau tidak mencukupi. Silakan pesan ulang."
        );
    }

    mysqli_stmt_close($stmt);

    // Simpan seluruh perubahan sekaligus
    if (!mysqli_commit($koneksi)) {
        throw new Exception("Gagal menyimpan transaksi.");
    }

    // Arahkan ke halaman pesanan berhasil
    header(
        "Location: ../pesanan_berhasil.php?id=" . $id_pesanan
    );
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);

    error_log(
        "Proses checkout Tampomas Berkah: " .
        $e->getMessage()
    );

    http_response_code(400);

    echo "Pesanan gagal diproses: " .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            "UTF-8"
        );

    echo '<br><a href="../beli.php?id=' .
        $id_produk .
        '">Kembali ke pembelian</a>';
}
