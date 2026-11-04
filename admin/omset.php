<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";

// Cek login admin
if (
    !isset($_SESSION["login"]) ||
    $_SESSION["login"] !== true ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: ../login.php");
    exit;
}

// Fungsi keamanan
function e($nilai) {
    return htmlspecialchars((string) ($nilai ?? ""), ENT_QUOTES, "UTF-8");
}

// Format rupiah
function rupiah($nilai) {
    return "Rp " . number_format((float) $nilai, 0, ',', '.');
}

// Filter bulan dan tahun
$bulan = $_GET["bulan"] ?? date("m");
$tahun = $_GET["tahun"] ?? date("Y");

if (!preg_match('/^(0[1-9]|1[0-2])$/', $bulan)) {
    $bulan = date("m");
}

if (!preg_match('/^\d{4}$/', $tahun) || (int)$tahun < 2020 || (int)$tahun > 2100) {
    $tahun = date("Y");
}

$periode = $tahun . "-" . $bulan;

// Nama bulan
$nama_bulan = [
    "01" => "Januari",
    "02" => "Februari",
    "03" => "Maret",
    "04" => "April",
    "05" => "Mei",
    "06" => "Juni",
    "07" => "Juli",
    "08" => "Agustus",
    "09" => "September",
    "10" => "Oktober",
    "11" => "November",
    "12" => "Desember"
];

// Query ringkasan omzet
$sql_ringkasan = "
    SELECT
        COUNT(*) AS jumlah_transaksi,
        COALESCE(SUM(total_harga), 0) AS total_omzet
    FROM tpesanan
    WHERE DATE_FORMAT(tanggal_pesanan, '%Y-%m') = ?
    AND status <> 'dibatalkan'
    AND (
        status_pembayaran = 'lunas'
        OR (metode_pembayaran = 'cash' AND status = 'selesai')
    )
";

$stmt_ringkasan = mysqli_prepare($koneksi, $sql_ringkasan);
mysqli_stmt_bind_param($stmt_ringkasan, "s", $periode);
mysqli_stmt_execute($stmt_ringkasan);

$data_ringkasan = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt_ringkasan)
);

mysqli_stmt_close($stmt_ringkasan);

$total_omzet = $data_ringkasan["total_omzet"] ?? 0;
$jumlah_transaksi = $data_ringkasan["jumlah_transaksi"] ?? 0;

// Query transaksi
$sql_transaksi = "
    SELECT
        p.id_pesanan,
        u.nama AS nama_pelanggan,
        p.tanggal_pesanan,
        p.metode_pembayaran,
        p.total_harga,
        p.status,
        p.status_pembayaran
    FROM tpesanan p
    JOIN tuser u ON p.id_user = u.id_user
    WHERE DATE_FORMAT(p.tanggal_pesanan, '%Y-%m') = ?
    AND p.status <> 'dibatalkan'
    AND (
        p.status_pembayaran = 'lunas'
        OR (p.metode_pembayaran = 'cash' AND p.status = 'selesai')
    )
    ORDER BY p.tanggal_pesanan DESC
";

$stmt_transaksi = mysqli_prepare($koneksi, $sql_transaksi);
mysqli_stmt_bind_param($stmt_transaksi, "s", $periode);
mysqli_stmt_execute($stmt_transaksi);

$result_transaksi = mysqli_stmt_get_result($stmt_transaksi);

// Kelompokkan omzet harian
$data_harian = [];

while ($row = mysqli_fetch_assoc($result_transaksi)) {
    $tanggal = date("Y-m-d", strtotime($row["tanggal_pesanan"]));

    if (!isset($data_harian[$tanggal])) {
        $data_harian[$tanggal] = [
            "jumlah" => 0,
            "omzet" => 0
        ];
    }

    $data_harian[$tanggal]["jumlah"]++;
    $data_harian[$tanggal]["omzet"] += (float) $row["total_harga"];
}

mysqli_data_seek($result_transaksi, 0);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Omzet | Tampomas Berkah</title>

    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>

<div class="omset-container">

    <div class="omset-header">
        <div>
            <h1>Data Omzet</h1>
            <p>Laporan pendapatan Tampomas Berkah.</p>
        </div>


            <a href="index.php" class="omset-button">
                Kembali
            </a>
        </div>
    </div>

    <form method="GET" class="omset-filter">

        <div class="omset-filter-group">
            <label for="bulan">Pilih Bulan</label>

            <select name="bulan" id="bulan">
                <?php foreach ($nama_bulan as $angka => $nama): ?>
                    <option
                        value="<?= e($angka); ?>"
                        <?= $bulan === $angka ? "selected" : ""; ?>
                    >
                        <?= e($nama); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="omset-filter-group">
            <label for="tahun">Pilih Tahun</label>

            <select name="tahun" id="tahun">
                <?php for ($y = (int) date("Y") - 5; $y <= (int) date("Y") + 1; $y++): ?>
                    <option
                        value="<?= $y; ?>"
                        <?= (int)$tahun === $y ? "selected" : ""; ?>
                    >
                        <?= $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <button type="submit" class="omset-button">
            Tampilkan
        </button>

    </form>

    <div class="omset-cards">

        <div class="omset-card">
            <span>
                Total Omzet <?= e($nama_bulan[$bulan] . " " . $tahun); ?>
            </span>

            <h2><?= rupiah($total_omzet); ?></h2>
        </div>

        <div class="omset-card">
            <span>Jumlah Transaksi Menghasilkan Omzet</span>

            <h2><?= (int) $jumlah_transaksi; ?> Transaksi</h2>
        </div>

    </div>

    <!-- Rincian omzet harian -->
    <div class="omset-section">

        <h2>Rincian Omzet Harian</h2>

        <div class="omset-table-scroll">

            <table class="omset-table">

                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Tanggal</th>
                        <th>Jumlah Transaksi</th>
                        <th>Total Omzet</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (!empty($data_harian)): ?>

                    <?php $no = 1; ?>

                    <?php foreach ($data_harian as $tanggal => $data): ?>

                        <tr>
                            <td><?= $no++; ?></td>

                            <td>
                                <?= e(date("d-m-Y", strtotime($tanggal))); ?>
                            </td>

                            <td>
                                <?= (int) $data["jumlah"]; ?> transaksi
                            </td>

                            <td>
                                <strong><?= rupiah($data["omzet"]); ?></strong>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="4" class="omset-kosong">
                            Belum ada omzet pada periode ini.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>
    </div>

    <!-- Daftar transaksi -->
    <div class="omset-section">

        <h2>Daftar Transaksi yang Menghasilkan Omzet</h2>

        <div class="omset-table-scroll">

            <table class="omset-table">

                <thead>
                    <tr>
                        <th>No.</th>
                        <th>ID Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Metode Pembayaran</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (mysqli_num_rows($result_transaksi) > 0): ?>

                    <?php $no = 1; ?>

                    <?php while ($pesanan = mysqli_fetch_assoc($result_transaksi)): ?>

                        <tr>
                            <td><?= $no++; ?></td>

                            <td>
                                #<?= (int) $pesanan["id_pesanan"]; ?>
                            </td>

                            <td>
                                <?= e($pesanan["nama_pelanggan"]); ?>
                            </td>

                            <td>
                                <?= e(date(
                                    "d-m-Y",
                                    strtotime($pesanan["tanggal_pesanan"])
                                )); ?>
                            </td>

                            <td>
                                <?= e(strtoupper($pesanan["metode_pembayaran"])); ?>
                            </td>

                            <td>
                                <strong>
                                    <?= rupiah($pesanan["total_harga"]); ?>
                                </strong>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="omset-kosong">
                            Belum ada transaksi yang menghasilkan omzet.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>
    </div>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt_transaksi);
?>