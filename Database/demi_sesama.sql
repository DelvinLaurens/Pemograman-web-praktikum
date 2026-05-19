-- DemiSesama Full Database Import
-- Import file ini satu kali lewat phpMyAdmin atau MySQL CLI.

CREATE DATABASE IF NOT EXISTS `demi_sesama`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `demi_sesama`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `donasi`;
DROP TABLE IF EXISTS `metode_pembayaran`;
DROP TABLE IF EXISTS `kampanye`;
DROP TABLE IF EXISTS `penyelenggara`;
DROP TABLE IF EXISTS `donatur`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `donatur` (
  `id_donatur` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `nomor_telepon` varchar(15) NOT NULL,
  `password` varchar(50) NOT NULL,
  PRIMARY KEY (`id_donatur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `penyelenggara` (
  `id_penyelenggara` int(11) NOT NULL AUTO_INCREMENT,
  `nama_penyelenggara` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `no_telepon` varchar(15) NOT NULL,
  `alamat` varchar(100) NOT NULL,
  `pass` varchar(50) NOT NULL,
  PRIMARY KEY (`id_penyelenggara`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `kampanye` (
  `id_kampanye` int(11) NOT NULL AUTO_INCREMENT,
  `id_penyelenggara` int(11) NOT NULL,
  `judul_kampanye` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  `deskripsi` varchar(1000) NOT NULL,
  `target_dana` int(15) NOT NULL,
  `dana_terkumpul` int(15) NOT NULL DEFAULT 0,
  `batas_waktu` date NOT NULL,
  `gambar_poster` varchar(255) NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id_kampanye`),
  KEY `id_penyelenggara` (`id_penyelenggara`),
  CONSTRAINT `kampanye_ibfk_1`
    FOREIGN KEY (`id_penyelenggara`)
    REFERENCES `penyelenggara` (`id_penyelenggara`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `donasi` (
  `id_donasi` int(11) NOT NULL AUTO_INCREMENT,
  `id_donatur` int(11) NOT NULL,
  `id_kampanye` int(11) NOT NULL,
  `nominal_donasi` decimal(15,2) NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `pesan_dukungan` text DEFAULT NULL,
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `status` enum('PENDING','VERIFIED','REJECTED','EXPIRED') DEFAULT 'PENDING',
  `waktu_donasi` timestamp NOT NULL DEFAULT current_timestamp(),
  `waktu_kadaluarsa` datetime DEFAULT NULL,
  PRIMARY KEY (`id_donasi`),
  KEY `id_donatur` (`id_donatur`),
  KEY `id_kampanye` (`id_kampanye`),
  CONSTRAINT `donasi_ibfk_1`
    FOREIGN KEY (`id_donatur`)
    REFERENCES `donatur` (`id_donatur`)
    ON DELETE CASCADE,
  CONSTRAINT `donasi_ibfk_2`
    FOREIGN KEY (`id_kampanye`)
    REFERENCES `kampanye` (`id_kampanye`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `metode_pembayaran` (
  `id_metode` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL,
  `tipe` enum('qris','ewallet','bank') NOT NULL DEFAULT 'bank',
  `nomor_tujuan` varchar(100) DEFAULT NULL,
  `nama_pemilik` varchar(100) DEFAULT NULL,
  `instruksi` varchar(255) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_metode`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `donatur`
  (`id_donatur`, `nama_lengkap`, `email`, `nomor_telepon`, `password`)
VALUES
  (1, 'Valentino Kevin Yulianto', 'kevin@gmail.com', '085432109876', 'kevin123'),
  (2, 'Waraney Maikel Nathaniel Mambu', 'nathan@gmail.com', '089876543210', 'nathan123');

INSERT INTO `penyelenggara`
  (`id_penyelenggara`, `nama_penyelenggara`, `email`, `no_telepon`, `alamat`, `pass`)
VALUES
  (1, 'Jeremy Zadrimman Kause', 'jere@gmail.com', '081234567890', 'Oebobo, Kupang', 'jeremy123'),
  (2, 'Delvin Laurens', 'delvin@gmail.com', '080987654321', 'Gondokusuman, Klitren', 'delpin321');

INSERT INTO `kampanye`
  (`id_kampanye`, `id_penyelenggara`, `judul_kampanye`, `kategori`, `lokasi`, `deskripsi`, `target_dana`, `dana_terkumpul`, `batas_waktu`, `gambar_poster`)
VALUES
  (1, 1, 'Banjir Melawi', 'bencana_alam', 'Melawi, Kalimantan Barat', 'Telah terjadi banjir di Melawi tahun 2024. Bantuan akan digunakan untuk kebutuhan makan, obat, dan perlengkapan darurat warga terdampak.', 100000000, 12674000, '2026-05-18', 'assets/images/campaigns/banjir-melawi.jpg'),
  (2, 2, 'Gempa Manado', 'bencana_alam', 'Manado, Sulawesi Utara', 'Gempa di Manado mengakibatkan banyak rumah rusak. Donasi akan disalurkan untuk bantuan logistik dan perbaikan tempat tinggal.', 180000000, 24534000, '2026-06-24', 'assets/images/campaigns/gempa-manado.jpg'),
  (3, 1, 'Puting Beliung Kupang', 'bencana_alam', 'Kupang, NTT', 'Bencana puting beliung di Kupang pada Januari 2024 merusak rumah warga. Bantuan digunakan untuk kebutuhan darurat dan pemulihan.', 45000000, 690000, '2026-06-03', 'assets/images/campaigns/puting-beliung-kupang.jpeg'),
  (4, 2, 'Banjir Bali', 'bencana_alam', 'Bali', 'Banjir di Bali pada 10 September 2025 membuat warga membutuhkan bantuan makanan, pakaian, dan perlengkapan kebersihan.', 20000000, 700000, '2026-06-19', 'assets/images/campaigns/banjir-bali.jpg'),
  (5, 1, 'Bantuan Pendidikan Anak Desa', 'pendidikan', 'Jawa', 'Bantu anak desa mendapatkan pendidikan layak melalui pengadaan buku, seragam, alat tulis, dan dukungan biaya sekolah.', 150000000, 122050000, '2026-06-03', 'assets/images/campaigns/bantuan-pendidikan-desa.jpg'),
  (6, 2, 'Reboisasi Hutan Gundul', 'lingkungan', 'Kalimantan', 'Tanam 10.000 pohon untuk membantu memulihkan hutan gundul dan menjaga lingkungan sekitar.', 50000000, 35000000, '2026-05-15', 'assets/images/campaigns/reboisasi-hutan.jpg');

INSERT INTO `metode_pembayaran`
  (`id_metode`, `kode`, `label`, `tipe`, `nomor_tujuan`, `nama_pemilik`, `instruksi`, `gambar`, `aktif`)
VALUES
  (1, 'qris', 'QRIS', 'qris', NULL, NULL, 'Scan kode QRIS menggunakan aplikasi mobile banking atau e-wallet Anda.', 'assets/images/payments/qris-demo.svg', 1),
  (2, 'bcava', 'BCA Virtual Account', 'bank', '8808 1234 5678', 'DemiSesama BCA', 'Bayar melalui menu Virtual Account BCA sesuai nominal donasi.', NULL, 1),
  (3, 'briva', 'BRI Virtual Account', 'bank', '77788 1234 5678', 'DemiSesama BRI', 'Bayar melalui menu BRIVA sesuai nominal donasi.', NULL, 1),
  (4, 'mandiriva', 'Mandiri Virtual Account', 'bank', '70012 1234 5678', 'DemiSesama Mandiri', 'Bayar melalui menu Virtual Account Mandiri sesuai nominal donasi.', NULL, 1),
  (5, 'dana', 'DANA', 'ewallet', '0812-3456-7890', 'Yayasan DemiSesama', 'Transfer ke nomor DANA berikut sesuai nominal donasi.', NULL, 1),
  (6, 'ovo', 'OVO', 'ewallet', '0812-3456-7890', 'Yayasan DemiSesama', 'Transfer ke nomor OVO berikut sesuai nominal donasi.', NULL, 1),
  (7, 'gopay', 'GoPay', 'ewallet', '0812-3456-7890', 'Yayasan DemiSesama', 'Transfer ke nomor GoPay berikut sesuai nominal donasi.', NULL, 1);

INSERT INTO `donasi`
  (`id_donasi`, `id_donatur`, `id_kampanye`, `nominal_donasi`, `metode_pembayaran`, `pesan_dukungan`, `bukti_transfer`, `status`, `waktu_donasi`, `waktu_kadaluarsa`)
VALUES
  (1, 1, 1, 10000.00, 'qris', 'Semoga bantuan cepat tersalurkan.', NULL, 'PENDING', '2026-05-04 17:18:24', '2026-05-04 17:48:24'),
  (2, 2, 1, 25000.00, 'bcava', 'Semoga warga tetap kuat.', 'assets/uploads/bukti-transfer/donasi-5-1779013440.png', 'VERIFIED', '2026-05-04 17:20:10', '2026-05-04 17:50:10'),
  (3, 1, 3, 20000.00, 'dana', 'Semoga segera pulih.', 'assets/uploads/bukti-transfer/donasi-5-1779013440.png', 'PENDING', '2026-05-04 17:24:36', '2026-05-04 17:54:36'),
  (4, 2, 5, 15000.00, 'ovo', 'Dukung pendidikan anak desa.', NULL, 'REJECTED', '2026-05-04 17:30:14', '2026-05-04 18:00:14'),
  (5, 1, 6, 500000.00, 'qris', 'Untuk penghijauan kembali.', NULL, 'PENDING', '2026-05-04 17:35:00', '2026-05-04 18:05:00');

ALTER TABLE `donatur` AUTO_INCREMENT = 3;
ALTER TABLE `penyelenggara` AUTO_INCREMENT = 3;
ALTER TABLE `kampanye` AUTO_INCREMENT = 7;
ALTER TABLE `metode_pembayaran` AUTO_INCREMENT = 8;
ALTER TABLE `donasi` AUTO_INCREMENT = 6;
