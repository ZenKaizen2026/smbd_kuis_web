<?php
declare(strict_types=1);

/**
 * Daftar statement DDL untuk Views, Functions, Stored Procedures, dan Triggers
 * pada db_penjualan_lab (tanpa DELIMITER $$ dan tanpa DEFINER=root@localhost).
 */
return [
    // --- VIEWS ---
    "CREATE OR REPLACE VIEW `daftarhargaproduk` AS
     SELECT `produk`.`KodeProduk` AS `kode`, UCASE(`produk`.`NamaProduk`) AS `produk`, `produk`.`Harga` AS `harga`
     FROM `produk`",

    "CREATE OR REPLACE VIEW `tampiltotalnota` AS
     SELECT `n`.`Nonota` AS `nonota`, `n`.`TglTran` AS `tgltran`, SUM(`i`.`banyak` * `i`.`hargajual`) AS `total nota`
     FROM `nota` `n`
     JOIN `itemnota` `i` ON `n`.`Nonota` = `i`.`Nonota`
     GROUP BY `n`.`Nonota`, `n`.`TglTran`",

    "CREATE OR REPLACE VIEW `viewdetailtransaksi` AS
     SELECT `n`.`Nonota` AS `Nonota`, `n`.`TglTran` AS `Tanggal`, `k`.`NamaKasir` AS `Kasir`,
            `c`.`NamaCus` AS `Pelanggan`, `p`.`KodeProduk` AS `KodeBarang`, `p`.`NamaProduk` AS `NamaBarang`,
            `i`.`banyak` AS `banyak`, `i`.`hargajual` AS `hargajual`, (`i`.`banyak` * `i`.`hargajual`) AS `Subtotal`
     FROM `nota` `n`
     JOIN `kasir` `k` ON `k`.`KodeKasir` = `n`.`KodeKasir`
     JOIN `customer` `c` ON `c`.`KodeCus` = `n`.`KodeCus`
     JOIN `itemnota` `i` ON `i`.`Nonota` = `n`.`Nonota`
     JOIN `produk` `p` ON `p`.`KodeProduk` = `i`.`KodeProduk`",

    "CREATE OR REPLACE VIEW `viewtransaksikasir` AS
     SELECT `k`.`KodeKasir` AS `KodeKasir`, `k`.`NamaKasir` AS `NamaKasir`, COUNT(`n`.`Nonota`) AS `JumlahTransaksi`
     FROM `kasir` `k`
     LEFT JOIN `nota` `n` ON `n`.`KodeKasir` = `k`.`KodeKasir`
     GROUP BY `k`.`KodeKasir`, `k`.`NamaKasir`",

    // --- FUNCTIONS ---
    "DROP FUNCTION IF EXISTS `fcCekStok`",
    "CREATE FUNCTION `fcCekStok` (`kd` CHAR(4)) RETURNS INT(11) DETERMINISTIC READS SQL DATA
     BEGIN
       DECLARE hasil INT;
       SELECT stok INTO hasil FROM produk WHERE KodeProduk = kd;
       RETURN IFNULL(hasil, 0);
     END",

    "DROP FUNCTION IF EXISTS `fcHitungPoint`",
    "CREATE FUNCTION `fcHitungPoint` (`total` INT) RETURNS INT(11) DETERMINISTIC NO SQL
     BEGIN
       RETURN FLOOR(IFNULL(total, 0) / 10000);
     END",

    "DROP FUNCTION IF EXISTS `fcKodeCustomerBaru`",
    "CREATE FUNCTION `fcKodeCustomerBaru` () RETURNS CHAR(5) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC READS SQL DATA
     BEGIN
       DECLARE terakhir INT;
       DECLARE baru CHAR(5);
       SELECT IFNULL(MAX(CAST(RIGHT(KodeCus, 4) AS UNSIGNED)), 0) INTO terakhir
       FROM customer
       WHERE KodeCus LIKE 'M%';
       SET baru = CONCAT('M', LPAD(terakhir + 1, 4, '0'));
       RETURN baru;
     END",

    "DROP FUNCTION IF EXISTS `fcKodeKasirBaru`",
    "CREATE FUNCTION `fcKodeKasirBaru` () RETURNS CHAR(3) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC READS SQL DATA
     BEGIN
       DECLARE terakhir INT;
       DECLARE baru CHAR(3);
       SELECT IFNULL(MAX(CAST(KodeKasir AS UNSIGNED)), 0) INTO terakhir FROM kasir;
       SET baru = LPAD(terakhir + 1, 3, '0');
       RETURN baru;
     END",

    "DROP FUNCTION IF EXISTS `fcNoNotaBaru`",
    "CREATE FUNCTION `fcNoNotaBaru` () RETURNS CHAR(10) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC READS SQL DATA
     BEGIN
       DECLARE prefix CHAR(6);
       DECLARE terakhir INT;
       DECLARE baru CHAR(10);
       SET prefix = DATE_FORMAT(CURRENT_DATE, '%y%m%d');
       SELECT IFNULL(MAX(CAST(RIGHT(Nonota, 4) AS UNSIGNED)), 0) INTO terakhir
       FROM nota
       WHERE Nonota LIKE CONCAT(prefix, '%');
       SET baru = CONCAT(prefix, LPAD(terakhir + 1, 4, '0'));
       RETURN baru;
     END",

    "DROP FUNCTION IF EXISTS `FCDaftarNoHP`",
    "CREATE FUNCTION `FCDaftarNoHP` () RETURNS VARCHAR(2000) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC READS SQL DATA
     BEGIN
       DECLARE hp VARCHAR(15);
       DECLARE finished INT DEFAULT 0;
       DECLARE daftarNoHP VARCHAR(2000) DEFAULT '';
       DECLARE curHP CURSOR FOR
         SELECT NULLIF(IF(HPCus IN ('', '-'), 'X', HPCus), 'X')
         FROM customer
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
     END",

    // --- STORED PROCEDURES ---
    "DROP PROCEDURE IF EXISTS `HapusCustomer`",
    "CREATE PROCEDURE `HapusCustomer` (IN `kd_cus` CHAR(5))
     BEGIN
       DELETE FROM customer WHERE KodeCus = kd_cus;
       SELECT CONCAT('Customer ', kd_cus, ' berhasil dihapus dari sistem.') AS Status;
     END",

    "DROP PROCEDURE IF EXISTS `ProcBonusPoint`",
    "CREATE PROCEDURE `ProcBonusPoint` ()
     BEGIN
       DECLARE v_kode CHAR(5);
       DECLARE v_point INT;
       DECLARE v_bonus INT;
       DECLARE finished INT DEFAULT 0;
       DECLARE curCustomer CURSOR FOR
         SELECT KodeCus, PointTerkumpul FROM customer WHERE KodeCus <> 'M0000';
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
         UPDATE customer
         SET PointTerkumpul = PointTerkumpul + v_bonus
         WHERE KodeCus = v_kode;
       END LOOP bonusLoop;
       CLOSE curCustomer;
       SELECT KodeCus, NamaCus, PointTerkumpul FROM customer;
     END",

    "DROP PROCEDURE IF EXISTS `ProcDaftarNoHP`",
    "CREATE PROCEDURE `ProcDaftarNoHP` ()
     BEGIN
       DECLARE hp VARCHAR(15);
       DECLARE finished INT DEFAULT 0;
       DECLARE daftarNoHP VARCHAR(2000) DEFAULT '';
       DECLARE curHP CURSOR FOR
         SELECT NULLIF(IF(HPCus IN ('', '-'), 'X', HPCus), 'X')
         FROM customer
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
     END",

    "DROP PROCEDURE IF EXISTS `TambahCustomerBaru`",
    "CREATE PROCEDURE `TambahCustomerBaru` (IN `nm` VARCHAR(50), IN `tl` DATE, IN `jk` CHAR(1), IN `al` VARCHAR(100), IN `hp` VARCHAR(15))
     BEGIN
       DECLARE kodebaru CHAR(5);
       SET kodebaru = fcKodeCustomerBaru();
       INSERT INTO customer (KodeCus, NamaCus, TglLahir, JenKelCus, AlmtCus, HPCus, PointTerkumpul)
       VALUES (kodebaru, nm, tl, jk, IFNULL(al, 'Yogyakarta'), hp, 0);
       SELECT * FROM customer WHERE KodeCus = kodebaru;
     END",

    "DROP PROCEDURE IF EXISTS `TambahKasirBaru`",
    "CREATE PROCEDURE `TambahKasirBaru` (IN `nm` VARCHAR(50), IN `pass` CHAR(6))
     BEGIN
       DECLARE kodebaru CHAR(3);
       SET kodebaru = fcKodeKasirBaru();
       INSERT INTO kasir (KodeKasir, NamaKasir, Pass)
       VALUES (kodebaru, nm, pass);
       SELECT * FROM kasir WHERE KodeKasir = kodebaru;
     END",

    "DROP PROCEDURE IF EXISTS `TambahNotaBaru`",
    "CREATE PROCEDURE `TambahNotaBaru` (IN `kd_kasir` CHAR(3), IN `kd_cus` CHAR(5))
     BEGIN
       DECLARE nonota_baru CHAR(10);
       SET nonota_baru = fcNoNotaBaru();
       INSERT INTO nota (Nonota, KodeKasir, TglTran, KodeCus)
       VALUES (nonota_baru, kd_kasir, CURRENT_DATE, kd_cus);
       SELECT * FROM nota WHERE Nonota = nonota_baru;
     END",

    "DROP PROCEDURE IF EXISTS `TambahPointCustomer`",
    "CREATE PROCEDURE `TambahPointCustomer` (IN `kd_cus` CHAR(5), IN `jml_point` INT)
     BEGIN
       UPDATE customer
       SET PointTerkumpul = PointTerkumpul + jml_point
       WHERE KodeCus = kd_cus;
       SELECT KodeCus, NamaCus, PointTerkumpul FROM customer WHERE KodeCus = kd_cus;
     END",

    // --- TRIGGERS ---
    "DROP TRIGGER IF EXISTS `tg_batal_jual`",
    "CREATE TRIGGER `tg_batal_jual` AFTER DELETE ON `itemnota` FOR EACH ROW
     BEGIN
       UPDATE produk
       SET stok = stok + OLD.banyak
       WHERE KodeProduk = OLD.KodeProduk;
     END",

    "DROP TRIGGER IF EXISTS `tg_produk_dijual`",
    "CREATE TRIGGER `tg_produk_dijual` AFTER INSERT ON `itemnota` FOR EACH ROW
     BEGIN
       UPDATE produk
       SET stok = stok - NEW.banyak
       WHERE KodeProduk = NEW.KodeProduk;
     END",

    "DROP TRIGGER IF EXISTS `tg_update_itemnota`",
    "CREATE TRIGGER `tg_update_itemnota` AFTER UPDATE ON `itemnota` FOR EACH ROW
     BEGIN
       IF OLD.KodeProduk = NEW.KodeProduk THEN
         UPDATE produk
         SET stok = stok + (OLD.banyak - NEW.banyak)
         WHERE KodeProduk = NEW.KodeProduk;
       ELSE
         UPDATE produk SET stok = stok + OLD.banyak WHERE KodeProduk = OLD.KodeProduk;
         UPDATE produk SET stok = stok - NEW.banyak WHERE KodeProduk = NEW.KodeProduk;
       END IF;
     END",

    "DROP TRIGGER IF EXISTS `tg_insert_kasir`",
    "CREATE TRIGGER `tg_insert_kasir` AFTER INSERT ON `kasir` FOR EACH ROW
     BEGIN
       INSERT INTO logkasir (KodeKasir, Aksi, Waktu, Keterangan)
       VALUES (
         NEW.KodeKasir,
         'INSERT',
         NOW(),
         CONCAT('Pendaftaran kasir baru: ', NEW.KodeKasir, ' - ', NEW.NamaKasir)
       );
     END",

    "DROP TRIGGER IF EXISTS `tg_update_kasir`",
    "CREATE TRIGGER `tg_update_kasir` BEFORE UPDATE ON `kasir` FOR EACH ROW
     BEGIN
       INSERT INTO logkasir (KodeKasir, Aksi, Waktu, Keterangan)
       VALUES (
         OLD.KodeKasir,
         'UPDATE',
         NOW(),
         CONCAT('UPDATE kode ', OLD.KodeKasir, ' ', OLD.NamaKasir, ' menjadi ', NEW.NamaKasir, ' dan/atau ', OLD.Pass, ' menjadi ', NEW.Pass)
       );
     END",
];
