<?php
declare(strict_types=1);

return [
    'unf' => [
        'label' => 'UNF (Unnormalized Form)',
        'title' => 'Bentuk Tidak Normal (Data Mentah Nota)',
        'description' => 'Satu baris nota menyimpan banyak barang sekaligus dalam satu kolom (repeating group / multivalued attribute). Kondisi ini memicu anomali update dan menyulitkan agregasi stok.',
        'tables' => [
            [
                'name' => 'Nota_Mentah (UNF)',
                'headers' => ['Nonota', 'TglTran', 'Kasir', 'Pelanggan', 'DaftarProduk (Multivalued)'],
                'rows' => [
                    ['2208030001', '2022-08-03', '001 - Unyil', 'M0001 - indah', 'P001 Indomie (8 x 2100), P002 Wafer Tango (1 x 4300)'],
                    ['2208040001', '2022-08-04', '002 - Melani', 'M0000 - UMUM', 'P001 Indomie (3 x 2100), P002 Wafer (3 x 4300), P003 SariRoti (1 x 7500)'],
                ],
            ],
        ],
    ],
    '1nf' => [
        'label' => '1NF (First Normal Form)',
        'title' => 'Bentuk Normal Pertama (Atribut Atomik)',
        'description' => 'Setiap kolom bernilai tunggal/atomik tanpa repeating group. Namun masih terdapat Partial Dependency: NamaProduk bergantung hanya pada KodeProduk, dan NamaCus bergantung hanya pada KodeCus.',
        'tables' => [
            [
                'name' => 'Transaksi_1NF (PK Komposit: Nonota, KodeProduk)',
                'headers' => ['Nonota', 'TglTran', 'KodeKasir', 'KodeCus', 'NamaCus', 'KodeProduk', 'NamaProduk', 'banyak', 'hargajual'],
                'rows' => [
                    ['2208030001', '2022-08-03', '001', 'M0001', 'indah', 'P001', 'Indomie goreng special', 8, 2100],
                    ['2208030001', '2022-08-03', '001', 'M0001', 'indah', 'P002', 'Wafer Tango', 1, 4300],
                    ['2208040001', '2022-08-04', '002', 'M0000', 'UMUM', 'P001', 'Indomie goreng special', 3, 2100],
                ],
            ],
        ],
    ],
    '2nf' => [
        'label' => '2NF (Second Normal Form)',
        'title' => 'Bentuk Normal Kedua (Bebas Partial Dependency)',
        'description' => 'Atribut yang hanya bergantung pada sebagian PK komposit dipisahkan: detail item ke ItemNota(Nonota, KodeProduk), master produk ke Produk(KodeProduk), dan header ke Nota(Nonota). Pada Nota masih tersisa Transitive Dependency: Nonota -> KodeCus -> NamaCus.',
        'tables' => [
            [
                'name' => 'Nota_2NF (Masih ada ketergantungan transitif ke NamaCus)',
                'headers' => ['Nonota (PK)', 'TglTran', 'KodeKasir', 'KodeCus', 'NamaCus'],
                'rows' => [
                    ['2208030001', '2022-08-03', '001', 'M0001', 'indah'],
                    ['2208040001', '2022-08-04', '002', 'M0000', 'UMUM'],
                ],
            ],
            [
                'name' => 'ItemNota (PK: Nonota, KodeProduk)',
                'headers' => ['Nonota', 'KodeProduk', 'banyak', 'hargajual'],
                'rows' => [
                    ['2208030001', 'P001', 8, 2100],
                    ['2208030001', 'P002', 1, 4300],
                ],
            ],
            [
                'name' => 'Produk (PK: KodeProduk)',
                'headers' => ['KodeProduk', 'NamaProduk', 'Harga', 'stok'],
                'rows' => [
                    ['P001', 'Indomie goreng special', 2100, 97],
                    ['P002', 'Wafer Tango', 4300, 50],
                ],
            ],
        ],
    ],
    '3nf' => [
        'label' => '3NF (Third Normal Form)',
        'title' => 'Bentuk Normal Ketiga (Skema Final db_penjualan)',
        'description' => 'Seluruh Transitive Dependency dihilangkan dengan memisahkan entitas Customer dan Kasir. Setiap atribut non-kunci kini bergantung penuh dan langsung hanya pada Primary Key tabelnya.',
        'tables' => [
            [
                'name' => 'Customer (PK: KodeCus)',
                'headers' => ['KodeCus', 'NamaCus', 'AlmtCus', 'PointTerkumpul'],
                'rows' => [
                    ['M0000', 'UMUM', 'Yogyakarta', 0],
                    ['M0001', 'indah', 'jl.wijaya 255', 40],
                ],
            ],
            [
                'name' => 'Kasir (PK: KodeKasir)',
                'headers' => ['KodeKasir', 'NamaKasir'],
                'rows' => [
                    ['001', 'Unyil'],
                    ['002', 'Melani'],
                ],
            ],
            [
                'name' => 'Nota (PK: Nonota, FK: KodeKasir, KodeCus)',
                'headers' => ['Nonota', 'KodeKasir', 'TglTran', 'KodeCus'],
                'rows' => [
                    ['2208030001', '001', '2022-08-03', 'M0001'],
                    ['2208040001', '002', '2022-08-04', 'M0000'],
                ],
            ],
        ],
    ],
];
