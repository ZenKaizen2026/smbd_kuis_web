INSERT INTO `customer` (`KodeCus`, `NamaCus`, `TglLahir`, `JenKelCus`, `AlmtCus`, `HPCus`, `PointTerkumpul`) VALUES
('M0000', 'UMUM', NULL, NULL, 'Yogyakarta', NULL, 0),
('M0001', 'indah', '1990-12-12', 'P', 'jl.wijaya 255', '08162992', 40),
('M0002', 'budi', '1987-05-20', 'L', 'jl.mawar 19', '081239927', 17);

INSERT INTO `kasir` (`KodeKasir`, `NamaKasir`, `Pass`) VALUES
('001', 'Unyil', 'qwerty'),
('002', 'Melani', 'melani'),
('003', 'Doni', 'doni12'),
('005', 'Bambang', 'bamb05');

INSERT INTO `produk` (`KodeProduk`, `NamaProduk`, `Harga`, `stok`) VALUES
('P001', 'Indomie goreng special', 2100, 97),
('P002', 'Wafer Tango', 4300, 50),
('P003', 'SariRoti Coklat Keju', 7500, 30);

INSERT INTO `nota` (`Nonota`, `KodeKasir`, `TglTran`, `KodeCus`) VALUES
('2208030001', '001', '2022-08-03', 'M0001'),
('2208030002', '001', '2022-08-03', 'M0002'),
('2208040001', '002', '2022-08-04', 'M0000'),
('2208050001', '002', '2022-08-05', 'M0001'),
('2611130001', '001', '2026-11-13', 'M0001');

INSERT INTO `itemnota` (`Nonota`, `KodeProduk`, `banyak`, `hargajual`) VALUES
('2208030001', 'P001', 8, 2100),
('2208030001', 'P002', 1, 4300),
('2208030002', 'P003', 2, 7500),
('2208040001', 'P001', 3, 2100),
('2208040001', 'P002', 3, 4300),
('2208040001', 'P003', 1, 7500),
('2208050001', 'P002', 2, 4300);
