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
  `username` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `nomor_telepon` varchar(15) NOT NULL,
  `password` varchar(50) NOT NULL,
  PRIMARY KEY (`id_donatur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `penyelenggara` (
  `id_penyelenggara` int(11) NOT NULL AUTO_INCREMENT,
  `nama_penyelenggara` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
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
  `status` ENUM('active', 'completed') NOT NULL DEFAULT 'active',
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
  `id_penyelenggara` int(11) NOT NULL,
  `kode` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL,
  `tipe` enum('qris','ewallet','bank') NOT NULL DEFAULT 'bank',
  `nomor_tujuan` varchar(100) DEFAULT NULL,
  `nama_pemilik` varchar(100) DEFAULT NULL,
  `instruksi` varchar(255) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_metode`),
  KEY `id_penyelenggara` (`id_penyelenggara`),
  CONSTRAINT `metode_pembayaran_ibfk_1`
    FOREIGN KEY (`id_penyelenggara`)
    REFERENCES `penyelenggara` (`id_penyelenggara`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `donatur`
  (`id_donatur`, `nama_lengkap`, `username`, `email`, `nomor_telepon`, `password`)
VALUES
  (1, 'Valentino Kevin Yulianto', 'kevin1', 'kevin@gmail.com', '085432109876', 'kevin123'),
  (2, 'Waraney Maikel Nathaniel Mambu', 'nathan1', 'nathan@gmail.com', '089876543210', 'nathan123');

INSERT INTO `penyelenggara`
  (`id_penyelenggara`, `nama_penyelenggara`, `username`, `email`, `no_telepon`, `alamat`, `pass`)
VALUES
  (1, 'Jeremy Zadrimman Kause', 'jeremy', 'jere@gmail.com', '081234567890', 'Oebobo, Kupang', 'jeremy123'),
  (2, 'Delvin Laurens', 'delvin', 'delvin@gmail.com', '080987654321', 'Gondokusuman, Klitren', 'delpin321');

INSERT INTO `kampanye`
  (`id_kampanye`, `id_penyelenggara`, `judul_kampanye`, `kategori`, `lokasi`, `deskripsi`, `target_dana`, `dana_terkumpul`, `batas_waktu`, `gambar_poster`, `status`)
VALUES
  (1, 1, 'Banjir Melawi', 'bencana_alam', 'Melawi, Kalimantan Barat', 'Telah terjadi banjir di Melawi tahun 2024. Bantuan akan digunakan untuk kebutuhan makan, obat, dan perlengkapan darurat warga terdampak.', 100000000, 12674000, '2026-12-18', 'assets/images/campaigns/banjir-melawi.jpg', 'completed'),
  (2, 2, 'Gempa Manado', 'bencana_alam', 'Manado, Sulawesi Utara', 'Gempa di Manado mengakibatkan banyak rumah rusak. Donasi akan disalurkan untuk bantuan logistik dan perbaikan tempat tinggal.', 180000000, 24534000, '2026-12-24', 'assets/images/campaigns/gempa-manado.jpg', 'active'),
  (3, 1, 'Puting Beliung Kupang', 'bencana_alam', 'Kupang, NTT', 'Bencana puting beliung di Kupang pada Januari 2024 merusak rumah warga. Bantuan digunakan untuk kebutuhan darurat dan pemulihan.', 45000000, 690000, '2026-12-03', 'assets/images/campaigns/puting-beliung-kupang.jpeg', 'active'),
  (4, 2, 'Banjir Bali', 'bencana_alam', 'Bali', 'Banjir di Bali pada 10 September 2025 membuat warga membutuhkan bantuan makanan, pakaian, dan perlengkapan kebersihan.', 20000000, 700000, '2026-12-19', 'assets/images/campaigns/banjir-bali.jpg', 'active'),
  (5, 1, 'Bantuan Pendidikan Anak Desa', 'pendidikan', 'Jawa', 'Bantu anak desa mendapatkan pendidikan layak melalui pengadaan buku, seragam, alat tulis, dan dukungan biaya sekolah.', 150000000, 122050000, '2026-12-03', 'assets/images/campaigns/bantuan-pendidikan-desa.jpg', 'active'),
  (6, 2, 'Reboisasi Hutan Gundul', 'lingkungan', 'Kalimantan', 'Tanam 10.000 pohon untuk membantu memulihkan hutan gundul dan menjaga lingkungan sekitar.', 50000000, 35000000, '2026-12-15', 'assets/images/campaigns/reboisasi-hutan.jpg', 'completed'),
  (7, 1, 'Operasi Katarak Lansia', 'kesehatan', 'Yogyakarta', 'Bantu lansia prasejahtera menjalani operasi katarak agar dapat kembali beraktivitas dengan lebih mandiri.', 75000000, 18500000, '2026-12-14', 'assets/images/campaigns/bantuan-pendidikan-desa.jpg', 'active'),
  (8, 2, 'Dapur Umum Korban Longsor', 'bencana_alam', 'Bogor, Jawa Barat', 'Dapur umum ini menyediakan makanan siap santap, air bersih, dan kebutuhan dasar untuk warga terdampak longsor.', 30000000, 8400000, '2026-12-08', 'assets/images/campaigns/banjir-melawi.jpg', 'active'),
  (9, 1, 'Ambulans Desa Terpencil', 'kesehatan', 'Sumba Timur, NTT', 'Pengadaan ambulans desa akan membantu warga menjangkau fasilitas kesehatan saat kondisi darurat.', 220000000, 44500000, '2026-12-20', 'assets/images/campaigns/puting-beliung-kupang.jpeg', 'active'),
  (10, 2, 'Beasiswa Anak Nelayan', 'pendidikan', 'Makassar, Sulawesi Selatan', 'Donasi digunakan untuk biaya sekolah, seragam, buku, dan transportasi anak keluarga nelayan.', 90000000, 27500000, '2026-12-30', 'assets/images/campaigns/bantuan-pendidikan-desa.jpg', 'active'),
  (11, 1, 'Sumur Bersih Desa Kering', 'sosial', 'Gunungkidul, Yogyakarta', 'Bantu pembangunan sumur bor dan tandon air agar warga memiliki akses air bersih sepanjang musim kemarau.', 65000000, 12900000, '2026-12-05', 'assets/images/campaigns/reboisasi-hutan.jpg', 'active'),
  (12, 2, 'Renovasi Posyandu Desa', 'kesehatan', 'Flores Timur, NTT', 'Renovasi posyandu membantu layanan imunisasi, pemeriksaan ibu hamil, dan pemantauan gizi balita.', 40000000, 6500000, '2026-12-28', 'assets/images/campaigns/kampanye-1779013316-8905.png', 'active'),
  (13, 1, 'Paket Sembako Lansia', 'sosial', 'Surabaya, Jawa Timur', 'Paket sembako bulanan akan disalurkan untuk lansia yang tinggal sendiri dan tidak memiliki penghasilan tetap.', 25000000, 11750000, '2026-12-15', 'assets/images/campaigns/banjir-bali.jpg', 'active'),
  (14, 2, 'Sekolah Darurat Pasca Banjir', 'pendidikan', 'Sintang, Kalimantan Barat', 'Bangun ruang belajar sementara dan lengkapi alat tulis untuk anak-anak yang sekolahnya terdampak banjir.', 85000000, 16100000, '2026-12-18', 'assets/images/campaigns/banjir-melawi.jpg', 'active'),
  (15, 1, 'Pemulihan Rumah Warga Manado', 'pembangunan', 'Manado, Sulawesi Utara', 'Bantuan material bangunan dan tenaga kerja untuk memperbaiki rumah warga yang rusak akibat gempa.', 120000000, 30250000, '2026-12-02', 'assets/images/campaigns/gempa-manado.jpg', 'active'),
  (16, 2, 'Tanam Mangrove Pesisir', 'lingkungan', 'Semarang, Jawa Tengah', 'Gerakan tanam mangrove untuk menahan abrasi, menjaga ekosistem pesisir, dan melibatkan relawan warga.', 55000000, 22900000, '2026-12-22', 'assets/images/campaigns/reboisasi-hutan.jpg', 'active'),
  (17, 1, 'Alat Bantu Difabel', 'kesehatan', 'Bandung, Jawa Barat', 'Pengadaan kursi roda, tongkat, dan alat bantu mobilitas untuk penyandang disabilitas dari keluarga prasejahtera.', 45000000, 9100000, '2026-12-30', 'assets/images/campaigns/bantuan-pendidikan-desa.jpg', 'active'),
  (18, 2, 'Bantuan Modal UMKM Ibu', 'sosial', 'Kupang, NTT', 'Modal usaha kecil membantu para ibu membangun kembali penghasilan keluarga melalui usaha rumahan.', 35000000, 12500000, '2026-12-11', 'assets/images/campaigns/puting-beliung-kupang.jpeg', 'active'),
  (19, 1, 'Perpustakaan Kampung', 'pendidikan', 'Lombok, NTB', 'Bantu penyediaan rak buku, koleksi bacaan anak, dan ruang belajar kecil untuk warga kampung.', 60000000, 20800000, '2026-12-12', 'assets/images/campaigns/bantuan-pendidikan-desa.jpg', 'active'),
  (20, 2, 'Perbaikan Jembatan Desa', 'pembangunan', 'Banyumas, Jawa Tengah', 'Jembatan desa yang rusak akan diperbaiki agar akses sekolah, pasar, dan layanan kesehatan kembali lancar.', 95000000, 15300000, '2026-12-27', 'assets/images/campaigns/reboisasi-hutan.jpg', 'active'),
  (21, 1, 'Bantuan Oksigen Klinik', 'kesehatan', 'Jayapura, Papua', 'Tabung oksigen dan regulator tambahan dibutuhkan untuk meningkatkan layanan klinik komunitas.', 70000000, 9700000, '2026-12-21', 'assets/images/campaigns/kampanye-1779013316-8905.png', 'active'),
  (22, 2, 'Hunian Sementara Korban Banjir', 'bencana_alam', 'Bali', 'Bangun hunian sementara dan sediakan perlengkapan tidur bagi keluarga yang rumahnya terdampak banjir.', 110000000, 33750000, '2026-12-08', 'assets/images/campaigns/banjir-bali.jpg', 'active'),
  (23, 1, 'Paket Sekolah Anak Pesisir', 'pendidikan', 'Belitung, Bangka Belitung', 'Paket sekolah berisi tas, buku, sepatu, dan alat tulis untuk anak-anak di wilayah pesisir.', 42000000, 18200000, '2026-12-03', 'assets/images/campaigns/bantuan-pendidikan-desa.jpg', 'active'),
  (24, 2, 'Bank Sampah Warga', 'lingkungan', 'Depok, Jawa Barat', 'Dukung pengadaan timbangan, gerobak, dan pelatihan warga untuk menjalankan bank sampah komunitas.', 28000000, 7600000, '2026-12-26', 'assets/images/campaigns/reboisasi-hutan.jpg', 'active'),
  (25, 1, 'Bantuan Gizi Balita', 'kesehatan', 'Ende, NTT', 'Paket makanan bergizi dan pendampingan kader akan membantu balita berisiko stunting.', 50000000, 14300000, '2026-12-16', 'assets/images/campaigns/kampanye-1779013316-8905.png', 'active');

INSERT INTO `metode_pembayaran`
  (`id_metode`, `id_penyelenggara`, `kode`, `label`, `tipe`, `nomor_tujuan`, `nama_pemilik`, `instruksi`, `gambar`, `aktif`)
VALUES
  (1, 1, 'qris', 'QRIS', 'qris', NULL, NULL, 'Scan kode QRIS menggunakan aplikasi mobile banking atau e-wallet Anda.', 'assets/images/payments/qris-demo.svg', 1),
  (2, 1, 'bcava', 'BCA Virtual Account', 'bank', '8808 1234 5678', 'Rekening Jeremy', 'Bayar melalui menu Virtual Account BCA sesuai nominal donasi.', NULL, 1),
  (3, 1, 'briva', 'BRI Virtual Account', 'bank', '77788 1234 5678', 'Rekening Jeremy', 'Bayar melalui menu BRIVA sesuai nominal donasi.', NULL, 1),
  (4, 2, 'mandiriva', 'Mandiri Virtual Account', 'bank', '70012 1234 5678', 'Rekening Delvin', 'Bayar melalui menu Virtual Account Mandiri sesuai nominal donasi.', NULL, 1),
  (5, 2, 'dana', 'DANA', 'ewallet', '0812-3456-7890', 'Dana Delvin', 'Transfer ke nomor DANA berikut sesuai nominal donasi.', NULL, 1),
  (6, 2, 'ovo', 'OVO', 'ewallet', '0812-3456-7890', 'OVO Delvin', 'Transfer ke nomor OVO berikut sesuai nominal donasi.', NULL, 1),
  (7, 2, 'gopay', 'GoPay', 'ewallet', '0812-3456-7890', 'GoPay Delvin', 'Transfer ke nomor GoPay berikut sesuai nominal donasi.', NULL, 1);

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
ALTER TABLE `kampanye` AUTO_INCREMENT = 26;
ALTER TABLE `donasi` AUTO_INCREMENT = 6;
