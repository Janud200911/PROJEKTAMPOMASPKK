-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2026 at 04:41 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_tampomas`
--

-- --------------------------------------------------------

--
-- Table structure for table `tdetail_pesanan`
--

CREATE TABLE `tdetail_pesanan` (
  `id_detail` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `harga_satuan` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tdetail_pesanan`
--

INSERT INTO `tdetail_pesanan` (`id_detail`, `id_pesanan`, `id_produk`, `jumlah_kg`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 1, 5.00, 3000.00, 15000.00),
(2, 1, 2, 5.00, 3000.00, 15000.00),
(3, 2, 2, 10.00, 3000.00, 30000.00),
(4, 2, 1, 10.00, 3000.00, 30000.00),
(5, 3, 2, 8.00, 3000.00, 24000.00),
(6, 4, 1, 5.00, 3000.00, 15000.00),
(7, 5, 1, 5.00, 3000.00, 15000.00),
(8, 6, 1, 7.00, 3000.00, 21000.00),
(9, 7, 1, 6.00, 3000.00, 18000.00),
(10, 8, 1, 9.00, 3000.00, 27000.00),
(11, 9, 1, 7.05, 3000.00, 21150.00),
(12, 10, 1, 5.00, 3000.00, 15000.00),
(13, 11, 1, 5.00, 3000.00, 15000.00),
(14, 12, 2, 6.00, 3000.00, 18000.00),
(15, 13, 1, 5.00, 3000.00, 15000.00),
(16, 14, 1, 11.97, 3000.00, 35910.00);

-- --------------------------------------------------------

--
-- Table structure for table `tpela`
--

CREATE TABLE `tpela` (
  `id_pelanggan` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tpela`
--

INSERT INTO `tpela` (`id_pelanggan`, `nama`, `username`, `password`, `no_hp`, `alamat`, `created_at`) VALUES
(1, 'Admin Tampomas', 'admin', 'admin123', '08123456789', 'Lapak Tampomas Berkah', '2026-08-28 08:43:41'),
(3, 'Mochamad Ibrahim', 'Bohim', '$2y$10$teGB8fx1pKiAQjThqxpK8uqsVKfaDAwzJqsgXCYFIdQNy2HaZCvRm', '08232323', 'anjay', '2026-08-28 08:46:12'),
(5, 'Dino', 'Dino', '$2y$10$.rSKNiRuxCuD4qEt30Cv/u2Dq6yfA3GLVzpxv1wRka8e/LUlcnHQ2', '0234282823', 'Ci anjing', '2026-09-07 03:01:54');

-- --------------------------------------------------------

--
-- Table structure for table `tpembayaran_info`
--

CREATE TABLE `tpembayaran_info` (
  `id` int(11) NOT NULL,
  `jenis` enum('transfer','qris') NOT NULL,
  `nama_bank` varchar(100) DEFAULT NULL,
  `no_rekening` varchar(50) DEFAULT NULL,
  `atas_nama` varchar(100) DEFAULT NULL,
  `qris_image` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tpembayaran_info`
--

INSERT INTO `tpembayaran_info` (`id`, `jenis`, `nama_bank`, `no_rekening`, `atas_nama`, `qris_image`, `updated_at`) VALUES
(1, 'transfer', 'BCA', '1234567890', 'Tampomas Berkah', NULL, '2026-09-04 08:26:32'),
(2, 'qris', NULL, NULL, 'Tampomas Berkah', 'qris_1788511747.jpg', '2026-09-04 08:49:07');

-- --------------------------------------------------------

--
-- Table structure for table `tpesanan`
--

CREATE TABLE `tpesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `tanggal_pesanan` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_pengambilan` date DEFAULT NULL,
  `total_berat` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('menunggu','diproses','siap_diambil','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
  `catatan` text DEFAULT NULL,
  `metode_pembayaran` enum('cash','transfer','qris') NOT NULL DEFAULT 'cash',
  `status_pembayaran` enum('belum_bayar','menunggu_verifikasi','lunas','ditolak') NOT NULL DEFAULT 'belum_bayar',
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `catatan_pembayaran` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tpesanan`
--

INSERT INTO `tpesanan` (`id_pesanan`, `id_user`, `tanggal_pesanan`, `tanggal_pengambilan`, `total_berat`, `total_harga`, `status`, `catatan`, `metode_pembayaran`, `status_pembayaran`, `bukti_pembayaran`, `catatan_pembayaran`) VALUES
(1, 3, '2026-08-28 08:47:02', NULL, 10.00, 30000.00, 'selesai', '', 'cash', 'belum_bayar', NULL, NULL),
(2, 3, '2026-08-31 05:16:12', NULL, 20.00, 60000.00, 'selesai', '', 'cash', 'belum_bayar', NULL, NULL),
(3, 3, '2026-09-04 08:28:38', NULL, 8.00, 24000.00, 'menunggu', '', 'qris', 'belum_bayar', NULL, NULL),
(4, 3, '2026-09-04 08:49:42', NULL, 5.00, 15000.00, 'menunggu', '', 'transfer', 'belum_bayar', NULL, NULL),
(5, 3, '2026-10-14 00:16:22', NULL, 5.00, 15000.00, 'dibatalkan', 'Nanti Di Ambil Sore ya mass', 'cash', 'belum_bayar', NULL, NULL),
(6, 3, '2026-10-14 00:50:30', NULL, 7.00, 21000.00, 'menunggu', 'juj', 'qris', 'menunggu_verifikasi', 'uploads/bukti_6_58eacec9c67a3880.jpg', NULL),
(7, 5, '2026-10-14 00:57:36', NULL, 6.00, 18000.00, 'menunggu', 'Nnai', 'qris', 'belum_bayar', NULL, NULL),
(8, 3, '2026-10-16 01:14:49', NULL, 9.00, 27000.00, 'selesai', 'kikiki', 'cash', 'belum_bayar', NULL, NULL),
(9, 3, '2026-10-16 01:17:38', NULL, 7.05, 21150.00, 'menunggu', 'Nanti di Ambil Ke Lapak', 'cash', 'belum_bayar', NULL, NULL),
(10, 6, '2026-10-19 03:25:25', NULL, 5.00, 15000.00, 'menunggu', 'Bbead', 'cash', 'belum_bayar', NULL, NULL),
(11, 3, '2026-10-19 03:28:59', NULL, 5.00, 15000.00, '', 'bkfk', 'cash', 'belum_bayar', NULL, NULL),
(12, 3, '2026-10-20 23:53:18', '2026-10-24', 6.00, 18000.00, 'dibatalkan', 'Nanti di ambil siapin ya mas', 'cash', 'belum_bayar', NULL, NULL),
(13, 3, '2026-10-21 00:27:01', '2026-10-22', 5.00, 15000.00, 'menunggu', 'ddffdgsrgsg', 'cash', 'belum_bayar', NULL, NULL),
(14, 3, '2026-10-21 00:54:13', '2026-10-23', 11.97, 35910.00, 'diproses', 'anjeng', 'cash', 'belum_bayar', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tproduk`
--

CREATE TABLE `tproduk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga_per_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stok_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `minimal_pembelian` decimal(10,2) NOT NULL DEFAULT 5.00,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tproduk`
--

INSERT INTO `tproduk` (`id_produk`, `nama_produk`, `deskripsi`, `harga_per_kg`, `stok_kg`, `minimal_pembelian`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Wortel', 'Wortel segar pilihan langsung dari petani. Minimal pembelian 5 KG seharga Rp15.000.', 3000.00, 20.98, 5.00, 'aktif', '2026-08-28 08:43:41', '2026-10-21 00:54:13'),
(2, 'Kentang', 'Kentang berkualitas untuk kebutuhan warung dan usaha kuliner. Minimal pembelian 5 KG seharga Rp15.000.', 3000.00, 57.00, 5.00, 'aktif', '2026-08-28 08:43:41', '2026-10-21 00:02:20');

-- --------------------------------------------------------

--
-- Table structure for table `trole`
--

CREATE TABLE `trole` (
  `id_role` int(11) NOT NULL,
  `nama_role` varchar(50) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trole`
--

INSERT INTO `trole` (`id_role`, `nama_role`, `keterangan`, `created_at`) VALUES
(1, 'admin', 'Mengelola seluruh sistem', '2026-10-03 01:42:24'),
(2, 'user', 'Menggunakan sistem sebagai pelanggan', '2026-10-03 01:42:24');

-- --------------------------------------------------------

--
-- Table structure for table `tuser`
--

CREATE TABLE `tuser` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `id_role` int(11) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tuser`
--

INSERT INTO `tuser` (`id_user`, `nama`, `username`, `id_role`, `password`, `no_hp`, `alamat`, `role`, `created_at`) VALUES
(1, 'Bohim', 'admin', 1, '$2y$10$eNaUOXh5veSMJVz4PNO4z.2rTUzk901kqGmzXsmenAowmdsOIhEse', '081234567890', 'Tampomas Berkah', 'admin', '2026-09-23 04:45:52'),
(2, 'Junaedi', 'Junaedi', 2, '$2y$10$kAVYSS270aklGFsoPRgGS.xTdyWkBIQB9Bln1Eg4WIqIToLiTO57a', '02934242', 'gfsf', 'user', '2026-09-23 04:56:13'),
(3, 'IKOO', 'IKOO', 2, '$2y$10$8WQIshfPezvU54grN9oFvevnw51Di6mszN7ZzBySagzhpzMhEelj6', '08232323', 'DSD', 'user', '2026-09-28 02:31:01'),
(4, 'ibrahim', 'ibrahim', 2, '$2y$10$u/mU/9CXoGs3NbngMKqueOeXVMUqEugS8GCd0CiQbtoGnXGIxEB0m', '0234282823', 'dede', 'user', '2026-10-03 02:04:00'),
(5, 'Junz', 'Junz', NULL, '$2y$10$pqh21Qse.R38697ktum2r..TfM.fNNrBMF.xeUtIqMIol90Dw7phi', '081223726833', 'Cimahi', 'user', '2026-10-14 00:56:59'),
(6, 'MsAtta', 'A', NULL, '$2y$10$A/GfkVjk5jljej31osNK2u9IUTylYYPqh80Q0FPAbA5cYHn6cgOsC', '0897956556', 'Ciboog', 'user', '2026-10-19 03:23:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tdetail_pesanan`
--
ALTER TABLE `tdetail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `tpela`
--
ALTER TABLE `tpela`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `tpembayaran_info`
--
ALTER TABLE `tpembayaran_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tpesanan`
--
ALTER TABLE `tpesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `fk_tpesanan_user` (`id_user`);

--
-- Indexes for table `tproduk`
--
ALTER TABLE `tproduk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indexes for table `trole`
--
ALTER TABLE `trole`
  ADD PRIMARY KEY (`id_role`),
  ADD UNIQUE KEY `nama_role` (`nama_role`);

--
-- Indexes for table `tuser`
--
ALTER TABLE `tuser`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tdetail_pesanan`
--
ALTER TABLE `tdetail_pesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tpela`
--
ALTER TABLE `tpela`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tpembayaran_info`
--
ALTER TABLE `tpembayaran_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tpesanan`
--
ALTER TABLE `tpesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tproduk`
--
ALTER TABLE `tproduk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `trole`
--
ALTER TABLE `trole`
  MODIFY `id_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tuser`
--
ALTER TABLE `tuser`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tdetail_pesanan`
--
ALTER TABLE `tdetail_pesanan`
  ADD CONSTRAINT `tdetail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `tpesanan` (`id_pesanan`) ON DELETE CASCADE,
  ADD CONSTRAINT `tdetail_pesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `tproduk` (`id_produk`) ON DELETE CASCADE;

--
-- Constraints for table `tpesanan`
--
ALTER TABLE `tpesanan`
  ADD CONSTRAINT `fk_tpesanan_user` FOREIGN KEY (`id_user`) REFERENCES `tuser` (`id_user`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
