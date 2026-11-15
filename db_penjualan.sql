-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 13 Nov 2026 pada 04.02
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_penjualan`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `HapusCustomer` (IN `kd_cus` CHAR(5))   BEGIN
  DELETE FROM Customer WHERE KodeCus = kd_cus;
  SELECT CONCAT('Customer ', kd_cus, ' berhasil dihapus dari sistem.') AS Status;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcBonusPoint` ()   BEGIN
  DECLARE v_kode CHAR(5);
  DECLARE v_point INT;
  DECLARE v_bonus INT;
  DECLARE finished INT DEFAULT 0;
  DECLARE curCustomer CURSOR FOR
    SELECT KodeCus, PointTerkumpul FROM Customer WHERE KodeCus <> 'M0000';
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;

  OPEN curCustomer;
  bonusLoop: LOOP
    FETCH curCustomer INTO v_kode, v_point;
    IF finished = 1 THEN
      LEAVE bonusLoop;
    END IF;
    
    IF v_point >= 100 THEN
      SET v_bonus = 20;
    ELSEIF v_point > 50 THEN
      SET v_bonus = 15;
    ELSEIF v_point > 20 THEN
      SET v_bonus = 10;
    ELSE
      SET v_bonus = 7;
    END IF;
    
    UPDATE Customer 
    SET PointTerkumpul = PointTerkumpul + v_bonus 
    WHERE KodeCus = v_kode;
  END LOOP bonusLoop;
  CLOSE curCustomer;
  
  SELECT KodeCus, NamaCus, PointTerkumpul FROM Customer;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcDaftarNoHP` ()   BEGIN
  DECLARE hp VARCHAR(15);
  DECLARE finished INT DEFAULT 0;
  DECLARE daftarNoHP VARCHAR(2000) DEFAULT '';
  DECLARE curHP CURSOR FOR
    SELECT NULLIF(IF(HPCus IN ('', '-'), 'X', HPCus), 'X') 
    FROM Customer 
    WHERE HPCus IS NOT NULL;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;

  OPEN curHP;
  getHP: LOOP
    FETCH curHP INTO hp;
    IF finished = 1 THEN
      LEAVE getHP;
    END IF;
    IF (hp IS NOT NULL AND hp != '') THEN
      IF (daftarNoHP = '') THEN
        SET daftarNoHP = hp;
      ELSE
        SET daftarNoHP = CONCAT(hp, '|', daftarNoHP);
      END IF;
    END IF;
  END LOOP getHP;
  CLOSE curHP;
  SELECT daftarNoHP AS 'Daftar Kontak (SP Cursor)';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `TambahCustomerBaru` (IN `nm` VARCHAR(50), IN `tl` DATE, IN `jk` CHAR(1), IN `al` VARCHAR(100), IN `hp` VARCHAR(15))   BEGIN
  DECLARE kodebaru CHAR(5);
  SET kodebaru = fcKodeCustomerBaru();
  INSERT INTO Customer (KodeCus, NamaCus, TglLahir, JenKelCus, AlmtCus, HPCus, PointTerkumpul)
  VALUES (kodebaru, nm, tl, jk, IFNULL(al, 'Yogyakarta'), hp, 0);
  SELECT * FROM Customer WHERE KodeCus = kodebaru;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `TambahKasirBaru` (IN `nm` VARCHAR(50), IN `pass` CHAR(6))   BEGIN
  DECLARE kodebaru CHAR(3);
  SET kodebaru = fcKodeKasirBaru();
  INSERT INTO Kasir (KodeKasir, NamaKasir, Pass)
  VALUES (kodebaru, nm, pass);
  SELECT * FROM Kasir WHERE KodeKasir = kodebaru;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `TambahNotaBaru` (IN `kd_kasir` CHAR(3), IN `kd_cus` CHAR(5))   BEGIN
  DECLARE nonota_baru CHAR(10);
  SET nonota_baru = fcNoNotaBaru();
  INSERT INTO Nota (Nonota, KodeKasir, TglTran, KodeCus)
  VALUES (nonota_baru, kd_kasir, CURRENT_DATE, kd_cus);
  SELECT * FROM Nota WHERE Nonota = nonota_baru;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `TambahPointCustomer` (IN `kd_cus` CHAR(5), IN `jml_point` INT)   BEGIN
  UPDATE Customer 
  SET PointTerkumpul = PointTerkumpul + jml_point 
  WHERE KodeCus = kd_cus;
  SELECT KodeCus, NamaCus, PointTerkumpul FROM Customer WHERE KodeCus = kd_cus;
END$$

--
-- Fungsi
--
CREATE DEFINER=`root`@`localhost` FUNCTION `fcCekStok` (`kd` CHAR(4)) RETURNS INT(11) DETERMINISTIC READS SQL DATA BEGIN
  DECLARE hasil INT;
  SELECT stok INTO hasil FROM Produk WHERE KodeProduk = kd;
  RETURN IFNULL(hasil, 0);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `FCDaftarNoHP` () RETURNS VARCHAR(2000) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC READS SQL DATA BEGIN
  DECLARE hp VARCHAR(15);
  DECLARE finished INT DEFAULT 0;
  DECLARE daftarNoHP VARCHAR(2000) DEFAULT '';
  DECLARE curHP CURSOR FOR
    SELECT NULLIF(IF(HPCus IN ('', '-'), 'X', HPCus), 'X') 
    FROM Customer 
    WHERE HPCus IS NOT NULL;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;

  OPEN curHP;
  getHP: LOOP
    FETCH curHP INTO hp;
    IF finished = 1 THEN
      LEAVE getHP;
    END IF;
    IF (hp IS NOT NULL AND hp != '') THEN
      IF (daftarNoHP = '') THEN
        SET daftarNoHP = hp;
      ELSE
        SET daftarNoHP = CONCAT(hp, '|', daftarNoHP);
      END IF;
    END IF;
  END LOOP getHP;
  CLOSE curHP;
  RETURN daftarNoHP;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fcHitungPoint` (`total` INT) RETURNS INT(11) DETERMINISTIC NO SQL BEGIN
  RETURN FLOOR(IFNULL(total, 0) / 10000);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fcKodeCustomerBaru` () RETURNS CHAR(5) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC READS SQL DATA BEGIN
  DECLARE terakhir INT;
  DECLARE baru CHAR(5);
  SELECT IFNULL(MAX(CAST(RIGHT(KodeCus, 4) AS UNSIGNED)), 0) INTO terakhir 
  FROM Customer 
  WHERE KodeCus LIKE 'M%';
  SET baru = CONCAT('M', LPAD(terakhir + 1, 4, '0'));
  RETURN baru;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fcKodeKasirBaru` () RETURNS CHAR(3) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC READS SQL DATA BEGIN
  DECLARE terakhir INT;
  DECLARE baru CHAR(3);
  SELECT IFNULL(MAX(CAST(KodeKasir AS UNSIGNED)), 0) INTO terakhir FROM Kasir;
  SET baru = LPAD(terakhir + 1, 3, '0');
  RETURN baru;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fcNoNotaBaru` () RETURNS CHAR(10) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC READS SQL DATA BEGIN
  DECLARE prefix CHAR(6);
  DECLARE terakhir INT;
  DECLARE baru CHAR(10);
  SET prefix = DATE_FORMAT(CURRENT_DATE, '%y%m%d');
  SELECT IFNULL(MAX(CAST(RIGHT(Nonota, 4) AS UNSIGNED)), 0) INTO terakhir 
  FROM Nota 
  WHERE Nonota LIKE CONCAT(prefix, '%');
  SET baru = CONCAT(prefix, LPAD(terakhir + 1, 4, '0'));
  RETURN baru;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer`
--

CREATE TABLE `customer` (
  `KodeCus` char(5) NOT NULL,
  `NamaCus` varchar(50) NOT NULL,
  `TglLahir` date DEFAULT NULL,
  `JenKelCus` char(1) DEFAULT NULL,
  `AlmtCus` varchar(100) NOT NULL DEFAULT 'Yogyakarta',
  `HPCus` varchar(15) DEFAULT NULL,
  `PointTerkumpul` int(11) NOT NULL DEFAULT 0
) ;

--
-- Dumping data untuk tabel `customer`
--

INSERT INTO `customer` (`KodeCus`, `NamaCus`, `TglLahir`, `JenKelCus`, `AlmtCus`, `HPCus`, `PointTerkumpul`) VALUES
('M0000', 'UMUM', NULL, NULL, 'Yogyakarta', NULL, 0),
('M0001', 'indah', '1990-12-12', 'P', 'jl.wijaya 255', '08162992', 40),
('M0002', 'budi', '1987-05-20', 'L', 'jl.mawar 19', '081239927', 17);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `daftarhargaproduk`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `daftarhargaproduk` (
`kode` char(4)
,`produk` varchar(100)
,`harga` int(11)
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `itemnota`
--

CREATE TABLE `itemnota` (
  `Nonota` char(10) NOT NULL,
  `KodeProduk` char(4) NOT NULL,
  `banyak` int(11) NOT NULL DEFAULT 1,
  `hargajual` int(11) NOT NULL
) ;

--
-- Dumping data untuk tabel `itemnota`
--

INSERT INTO `itemnota` (`Nonota`, `KodeProduk`, `banyak`, `hargajual`) VALUES
('2208030001', 'P001', 8, 2100),
('2208030001', 'P002', 1, 4300),
('2208030002', 'P003', 2, 7500),
('2208040001', 'P001', 3, 2100),
('2208040001', 'P002', 3, 4300),
('2208040001', 'P003', 1, 7500),
('2208050001', 'P002', 2, 4300);

--
-- Trigger `itemnota`
--
DELIMITER $$
CREATE TRIGGER `tg_batal_jual` AFTER DELETE ON `itemnota` FOR EACH ROW BEGIN
  UPDATE Produk 
  SET stok = stok + OLD.banyak 
  WHERE KodeProduk = OLD.KodeProduk;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tg_produk_dijual` AFTER INSERT ON `itemnota` FOR EACH ROW BEGIN
  UPDATE Produk 
  SET stok = stok - NEW.banyak 
  WHERE KodeProduk = NEW.KodeProduk;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tg_update_itemnota` AFTER UPDATE ON `itemnota` FOR EACH ROW BEGIN
  IF OLD.KodeProduk = NEW.KodeProduk THEN
    UPDATE Produk 
    SET stok = stok + (OLD.banyak - NEW.banyak) 
    WHERE KodeProduk = NEW.KodeProduk;
  ELSE
    UPDATE Produk SET stok = stok + OLD.banyak WHERE KodeProduk = OLD.KodeProduk;
    UPDATE Produk SET stok = stok - NEW.banyak WHERE KodeProduk = NEW.KodeProduk;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kasir`
--

CREATE TABLE `kasir` (
  `KodeKasir` char(3) NOT NULL,
  `NamaKasir` varchar(50) NOT NULL,
  `Pass` char(6) NOT NULL
) ;

--
-- Dumping data untuk tabel `kasir`
--

INSERT INTO `kasir` (`KodeKasir`, `NamaKasir`, `Pass`) VALUES
('001', 'Unyil', 'qwerty'),
('002', 'Melani', 'melani'),
('003', 'Doni', 'doni12'),
('005', 'Bambang', 'bamb05');

--
-- Trigger `kasir`
--
DELIMITER $$
CREATE TRIGGER `tg_insert_kasir` AFTER INSERT ON `kasir` FOR EACH ROW BEGIN
  INSERT INTO LogKasir (KodeKasir, Aksi, Waktu, Keterangan)
  VALUES (
    NEW.KodeKasir, 
    'INSERT', 
    NOW(), 
    CONCAT('Pendaftaran kasir baru: ', NEW.KodeKasir, ' - ', NEW.NamaKasir)
  );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tg_update_kasir` BEFORE UPDATE ON `kasir` FOR EACH ROW BEGIN
  INSERT INTO LogKasir (KodeKasir, Aksi, Waktu, Keterangan)
  VALUES (
    OLD.KodeKasir, 
    'UPDATE', 
    NOW(), 
    CONCAT('UPDATE kode ', OLD.KodeKasir, ' ', OLD.NamaKasir, ' menjadi ', NEW.NamaKasir, ' dan/atau ', OLD.Pass, ' menjadi ', NEW.Pass)
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `logkasir`
--

CREATE TABLE `logkasir` (
  `IdLog` int(11) NOT NULL,
  `KodeKasir` char(3) DEFAULT NULL,
  `Aksi` varchar(20) NOT NULL,
  `Waktu` datetime NOT NULL DEFAULT current_timestamp(),
  `Keterangan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `nota`
--

CREATE TABLE `nota` (
  `Nonota` char(10) NOT NULL,
  `KodeKasir` char(3) NOT NULL,
  `TglTran` date NOT NULL,
  `KodeCus` char(5) NOT NULL
) ;

--
-- Dumping data untuk tabel `nota`
--

INSERT INTO `nota` (`Nonota`, `KodeKasir`, `TglTran`, `KodeCus`) VALUES
('2208030001', '001', '2022-08-03', 'M0001'),
('2208030002', '001', '2022-08-03', 'M0002'),
('2208040001', '002', '2022-08-04', 'M0000'),
('2208050001', '002', '2022-08-05', 'M0001'),
('2611130001', '001', '2026-11-13', 'M0001');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `KodeProduk` char(4) NOT NULL,
  `NamaProduk` varchar(100) NOT NULL,
  `Harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0
) ;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`KodeProduk`, `NamaProduk`, `Harga`, `stok`) VALUES
('P001', 'Indomie goreng special', 2100, 97),
('P002', 'Wafer Tango', 4300, 50),
('P003', 'SariRoti Coklat Keju', 7500, 30);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `tampiltotalnota`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `tampiltotalnota` (
`nonota` char(10)
,`tgltran` date
,`total nota` decimal(42,0)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `viewdetailtransaksi`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `viewdetailtransaksi` (
`Nonota` char(10)
,`Tanggal` date
,`Kasir` varchar(50)
,`Pelanggan` varchar(50)
,`KodeBarang` char(4)
,`NamaBarang` varchar(100)
,`banyak` int(11)
,`hargajual` int(11)
,`Subtotal` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `viewtransaksikasir`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `viewtransaksikasir` (
`KodeKasir` char(3)
,`NamaKasir` varchar(50)
,`JumlahTransaksi` bigint(21)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `daftarhargaproduk`
--
DROP TABLE IF EXISTS `daftarhargaproduk`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `daftarhargaproduk`  AS SELECT `produk`.`KodeProduk` AS `kode`, ucase(`produk`.`NamaProduk`) AS `produk`, `produk`.`Harga` AS `harga` FROM `produk` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `tampiltotalnota`
--
DROP TABLE IF EXISTS `tampiltotalnota`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `tampiltotalnota`  AS SELECT `n`.`Nonota` AS `nonota`, `n`.`TglTran` AS `tgltran`, sum(`i`.`banyak` * `i`.`hargajual`) AS `total nota` FROM (`nota` `n` join `itemnota` `i` on(`n`.`Nonota` = `i`.`Nonota`)) GROUP BY `n`.`Nonota`, `n`.`TglTran` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `viewdetailtransaksi`
--
DROP TABLE IF EXISTS `viewdetailtransaksi`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `viewdetailtransaksi`  AS SELECT `n`.`Nonota` AS `Nonota`, `n`.`TglTran` AS `Tanggal`, `k`.`NamaKasir` AS `Kasir`, `c`.`NamaCus` AS `Pelanggan`, `p`.`KodeProduk` AS `KodeBarang`, `p`.`NamaProduk` AS `NamaBarang`, `i`.`banyak` AS `banyak`, `i`.`hargajual` AS `hargajual`, `i`.`banyak`* `i`.`hargajual` AS `Subtotal` FROM ((((`nota` `n` join `kasir` `k` on(`k`.`KodeKasir` = `n`.`KodeKasir`)) join `customer` `c` on(`c`.`KodeCus` = `n`.`KodeCus`)) join `itemnota` `i` on(`i`.`Nonota` = `n`.`Nonota`)) join `produk` `p` on(`p`.`KodeProduk` = `i`.`KodeProduk`)) ;

-- --------------------------------------------------------

--
-- Struktur untuk view `viewtransaksikasir`
--
DROP TABLE IF EXISTS `viewtransaksikasir`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `viewtransaksikasir`  AS SELECT `k`.`KodeKasir` AS `KodeKasir`, `k`.`NamaKasir` AS `NamaKasir`, count(`n`.`Nonota`) AS `JumlahTransaksi` FROM (`kasir` `k` left join `nota` `n` on(`n`.`KodeKasir` = `k`.`KodeKasir`)) GROUP BY `k`.`KodeKasir`, `k`.`NamaKasir` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`KodeCus`);

--
-- Indeks untuk tabel `itemnota`
--
ALTER TABLE `itemnota`
  ADD PRIMARY KEY (`Nonota`,`KodeProduk`),
  ADD KEY `fk_item_produk` (`KodeProduk`);

--
-- Indeks untuk tabel `kasir`
--
ALTER TABLE `kasir`
  ADD PRIMARY KEY (`KodeKasir`);

--
-- Indeks untuk tabel `logkasir`
--
ALTER TABLE `logkasir`
  ADD PRIMARY KEY (`IdLog`);

--
-- Indeks untuk tabel `nota`
--
ALTER TABLE `nota`
  ADD PRIMARY KEY (`Nonota`),
  ADD KEY `fk_nota_kasir` (`KodeKasir`),
  ADD KEY `fk_nota_customer` (`KodeCus`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`KodeProduk`),
  ADD UNIQUE KEY `uq_namaproduk` (`NamaProduk`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `logkasir`
--
ALTER TABLE `logkasir`
  MODIFY `IdLog` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `itemnota`
--
ALTER TABLE `itemnota`
  ADD CONSTRAINT `fk_item_nota` FOREIGN KEY (`Nonota`) REFERENCES `nota` (`Nonota`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_item_produk` FOREIGN KEY (`KodeProduk`) REFERENCES `produk` (`KodeProduk`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `nota`
--
ALTER TABLE `nota`
  ADD CONSTRAINT `fk_nota_customer` FOREIGN KEY (`KodeCus`) REFERENCES `customer` (`KodeCus`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nota_kasir` FOREIGN KEY (`KodeKasir`) REFERENCES `kasir` (`KodeKasir`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
