USE `demi_sesama`;

-- Jalankan bagian ALTER ini jika foreign key kampanye masih mengarah ke tabel donatur.
-- Di dump awal project, id_penyelenggara pada kampanye seharusnya terhubung ke tabel penyelenggara.
ALTER TABLE `kampanye`
  DROP FOREIGN KEY `kampanye_ibfk_1`;

ALTER TABLE `kampanye`
  ADD CONSTRAINT `kampanye_ibfk_1`
  FOREIGN KEY (`id_penyelenggara`) REFERENCES `penyelenggara` (`id_penyelenggara`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

INSERT INTO `penyelenggara`
  (`id_penyelenggara`, `nama_penyelenggara`, `email`, `no_telepon`, `alamat`, `pass`)
VALUES
  (3, 'Sdr. Roihan Jaidid', 'roihan@example.com', '080000000003', 'Melawi, Kalimantan Barat', 'roihan123'),
  (4, 'Sdri. Mirel', 'mirel@example.com', '080000000004', 'Manado, Sulawesi Utara', 'mirel123'),
  (5, 'Sdr. Jeremy Waraney', 'jeremy.waraney@example.com', '080000000005', 'Kupang, NTT', 'jeremy123'),
  (6, 'Sdr. Richard Van Nistelroy', 'richard@example.com', '080000000006', 'Bali', 'richard123'),
  (7, 'PT. Kenangan Bersama', 'kenanganbersama@example.com', '080000000007', 'Jawa', 'kenangan123'),
  (8, 'Sdr. Kevin Puskas Viera', 'kevin.viera@example.com', '080000000008', 'Kalimantan', 'kevin123')
ON DUPLICATE KEY UPDATE
  `nama_penyelenggara` = VALUES(`nama_penyelenggara`),
  `email` = VALUES(`email`),
  `no_telepon` = VALUES(`no_telepon`),
  `alamat` = VALUES(`alamat`),
  `pass` = VALUES(`pass`);

INSERT INTO `kampanye`
  (`id_kampanye`, `id_penyelenggara`, `judul_kampanye`, `kategori`, `lokasi`, `deskripsi`, `target_dana`, `dana_terkumpul`, `batas_waktu`, `gambar_poster`)
VALUES
  (1, 3, 'Banjir Melawi', 'bencana_alam', 'Melawi, Kalimantan Barat', 'Telah Terjadi banjir di Melawi tahun 2024', 100000000, 12674000, '2026-05-18', 'Asset/banjir melawi.jpg'),
  (2, 4, 'Gempa Manado', 'bencana_alam', 'Manado, Sulawesi Utara', 'Gempa di Manado yang mengakibatkan banyaknya rumah', 180000000, 24534000, '2026-06-24', 'Asset/gempa manado.jpg'),
  (3, 5, 'Puting Beliung Kupang', 'bencana_alam', 'Kupang, NTT', 'Bencana puting beliung di Kupang pada Januari 2024', 45000000, 690000, '2026-06-03', 'Asset/puting beliung kupang.jpeg'),
  (4, 6, 'Banjir Bali', 'bencana_alam', 'Bali', 'Banjir di bali pada 10 September 2025', 20000000, 700000, '2026-06-19', 'Asset/banjir bali.jpg'),
  (5, 7, 'Bantuan Pendidikan Anak Desa', 'pendidikan', 'Jawa', 'Bantu Anak Desa Mendapatkan Pendidikan Layak', 150000000, 122050000, '2026-06-03', 'Asset/bantuan penddikan desa.jpg'),
  (6, 8, 'Reboisasi Hutan Gundul', 'lingkungan', 'Kalimantan', 'Tanam 10.000 pohon untuk menghidupi hutan kembali', 50000000, 35000000, '2026-05-15', 'Asset/reboisasi hutan.jpg')
ON DUPLICATE KEY UPDATE
  `id_penyelenggara` = VALUES(`id_penyelenggara`),
  `judul_kampanye` = VALUES(`judul_kampanye`),
  `kategori` = VALUES(`kategori`),
  `lokasi` = VALUES(`lokasi`),
  `deskripsi` = VALUES(`deskripsi`),
  `target_dana` = VALUES(`target_dana`),
  `dana_terkumpul` = VALUES(`dana_terkumpul`),
  `batas_waktu` = VALUES(`batas_waktu`),
  `gambar_poster` = VALUES(`gambar_poster`);

-- Query untuk mengambil data kampanye di index.php.
SELECT
  k.*,
  p.nama_penyelenggara
FROM `kampanye` k
INNER JOIN `penyelenggara` p
  ON p.id_penyelenggara = k.id_penyelenggara
ORDER BY k.batas_waktu ASC
LIMIT 6;
