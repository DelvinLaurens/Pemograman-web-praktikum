USE `demi_sesama`;

ALTER TABLE `donasi`
  MODIFY `bukti_transfer` varchar(255) DEFAULT NULL;

ALTER TABLE `donasi`
  MODIFY `status` enum('PENDING','VERIFIED','REJECTED','EXPIRED') DEFAULT 'PENDING';

ALTER TABLE `donasi`
  ADD COLUMN IF NOT EXISTS `waktu_kadaluarsa` datetime DEFAULT NULL AFTER `waktu_donasi`;

UPDATE `donasi`
SET `waktu_kadaluarsa` = DATE_ADD(`waktu_donasi`, INTERVAL 30 MINUTE)
WHERE `waktu_kadaluarsa` IS NULL;
