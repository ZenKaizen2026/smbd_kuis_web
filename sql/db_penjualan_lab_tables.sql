DROP TABLE IF EXISTS `itemnota`;
DROP TABLE IF EXISTS `nota`;
DROP TABLE IF EXISTS `logkasir`;
DROP TABLE IF EXISTS `produk`;
DROP TABLE IF EXISTS `kasir`;
DROP TABLE IF EXISTS `customer`;

DROP VIEW IF EXISTS `daftarhargaproduk`;
DROP VIEW IF EXISTS `tampiltotalnota`;
DROP VIEW IF EXISTS `viewdetailtransaksi`;
DROP VIEW IF EXISTS `viewtransaksikasir`;

CREATE TABLE `customer` (
  `KodeCus` char(5) NOT NULL,
  `NamaCus` varchar(50) NOT NULL,
  `TglLahir` date DEFAULT NULL,
  `JenKelCus` char(1) DEFAULT NULL,
  `AlmtCus` varchar(100) NOT NULL DEFAULT 'Yogyakarta',
  `HPCus` varchar(15) DEFAULT NULL,
  `PointTerkumpul` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`KodeCus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `kasir` (
  `KodeKasir` char(3) NOT NULL,
  `NamaKasir` varchar(50) NOT NULL,
  `Pass` char(6) NOT NULL,
  PRIMARY KEY (`KodeKasir`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `produk` (
  `KodeProduk` char(4) NOT NULL,
  `NamaProduk` varchar(100) NOT NULL,
  `Harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`KodeProduk`),
  UNIQUE KEY `uq_namaproduk` (`NamaProduk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `logkasir` (
  `IdLog` int(11) NOT NULL AUTO_INCREMENT,
  `KodeKasir` char(3) DEFAULT NULL,
  `Aksi` varchar(20) NOT NULL,
  `Waktu` datetime NOT NULL DEFAULT current_timestamp(),
  `Keterangan` varchar(255) NOT NULL,
  PRIMARY KEY (`IdLog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `nota` (
  `Nonota` char(10) NOT NULL,
  `KodeKasir` char(3) NOT NULL,
  `TglTran` date NOT NULL,
  `KodeCus` char(5) NOT NULL,
  PRIMARY KEY (`Nonota`),
  KEY `fk_nota_kasir` (`KodeKasir`),
  KEY `fk_nota_customer` (`KodeCus`),
  CONSTRAINT `fk_nota_customer` FOREIGN KEY (`KodeCus`) REFERENCES `customer` (`KodeCus`) ON UPDATE CASCADE,
  CONSTRAINT `fk_nota_kasir` FOREIGN KEY (`KodeKasir`) REFERENCES `kasir` (`KodeKasir`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `itemnota` (
  `Nonota` char(10) NOT NULL,
  `KodeProduk` char(4) NOT NULL,
  `banyak` int(11) NOT NULL DEFAULT 1,
  `hargajual` int(11) NOT NULL,
  PRIMARY KEY (`Nonota`, `KodeProduk`),
  KEY `fk_item_produk` (`KodeProduk`),
  CONSTRAINT `fk_item_nota` FOREIGN KEY (`Nonota`) REFERENCES `nota` (`Nonota`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_item_produk` FOREIGN KEY (`KodeProduk`) REFERENCES `produk` (`KodeProduk`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
